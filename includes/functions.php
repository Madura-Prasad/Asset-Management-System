<?php
require_once 'config.php';

function getUserById($id) {
    global $pdo;
    $stmt = $pdo->prepare("SELECT * FROM users WHERE id = ?");
    $stmt->execute([$id]);
    return $stmt->fetch();
}

function getAllUsers($approvedOnly = false) {
    global $pdo;
    $sql = "SELECT * FROM users";
    if ($approvedOnly) {
        $sql .= " WHERE is_approved = 1";
    }
    $stmt = $pdo->query($sql);
    return $stmt->fetchAll();
}

function getDevices($availableOnly = false, $ownerId = null) {
    global $pdo;
    $sql = "SELECT d.*, t.name as type_name, u.first_name, u.surname 
            FROM devices d
            LEFT JOIN device_types t ON d.product_type_id = t.id
            LEFT JOIN users u ON d.current_owner_id = u.id";
            
    $conditions = [];
    $params = [];
    
    if ($availableOnly) {
        $conditions[] = "d.is_available = 1";
    }
    
    if ($ownerId !== null) {
        $conditions[] = "d.current_owner_id = ?";
        $params[] = $ownerId;
    }
    
    if (!empty($conditions)) {
        $sql .= " WHERE " . implode(" AND ", $conditions);
    }
    
    $stmt = $pdo->prepare($sql);
    $stmt->execute($params);
    return $stmt->fetchAll();
}

function getDeviceById($id) {
    global $pdo;
    $stmt = $pdo->prepare("SELECT d.*, t.name as type_name FROM devices d 
                          LEFT JOIN device_types t ON d.product_type_id = t.id 
                          WHERE d.id = ?");
    $stmt->execute([$id]);
    return $stmt->fetch();
}

function getDeviceTypes() {
    global $pdo;
    $stmt = $pdo->query("SELECT * FROM device_types");
    return $stmt->fetchAll();
}

function getPendingRequests() {
    global $pdo;
    $stmt = $pdo->query("SELECT r.*, u.first_name, u.surname, d.name as device_name 
                        FROM device_requests r
                        JOIN users u ON r.user_id = u.id
                        JOIN devices d ON r.device_id = d.id
                        WHERE r.status = 'pending'");
    return $stmt->fetchAll();
}

function getUserRequests($userId) {
    global $pdo;
    $stmt = $pdo->prepare("SELECT r.*, d.name as device_name, d.asset_no, d.serial_no, 
                          CASE WHEN r.status = 'pending' THEN 'Pending'
                               WHEN r.status = 'approved' THEN 'Approved'
                               ELSE 'Rejected' END as status_text
                          FROM device_requests r
                          JOIN devices d ON r.device_id = d.id
                          WHERE r.user_id = ?");
    $stmt->execute([$userId]);
    return $stmt->fetchAll();
}

function getRssFeeds() {
    global $pdo;
    $stmt = $pdo->query("SELECT * FROM rss_feeds WHERE is_active = 1");
    return $stmt->fetchAll();
}

function addRssFeed($title, $url, $userId) {
    global $pdo;
    $stmt = $pdo->prepare("INSERT INTO rss_feeds (title, url, added_by, added_date) 
                          VALUES (?, ?, ?, NOW())");
    return $stmt->execute([$title, $url, $userId]);
}

function requestDevice($userId, $deviceId) {
    global $pdo;
    
    // Check if device is available
    $device = getDeviceById($deviceId);
    if (!$device || !$device['is_available']) {
        return ['success' => false, 'message' => 'Device not available'];
    }
    
    // Check if user already has a pending request for this device
    $stmt = $pdo->prepare("SELECT id FROM device_requests 
                          WHERE user_id = ? AND device_id = ? AND status = 'pending'");
    $stmt->execute([$userId, $deviceId]);
    
    if ($stmt->fetch()) {
        return ['success' => false, 'message' => 'You already have a pending request for this device'];
    }
    
    // Create request
    $stmt = $pdo->prepare("INSERT INTO device_requests (user_id, device_id, request_date, status) 
                          VALUES (?, ?, NOW(), 'pending')");
    $success = $stmt->execute([$userId, $deviceId]);
    
    return ['success' => $success, 'message' => $success ? 'Request submitted' : 'Request failed'];
}

function processRequest($requestId, $status, $adminId, $notes = '') {
    global $pdo;
    
    // Validate status value
    $allowedStatuses = ['pending', 'approved', 'rejected'];
    if (!in_array($status, $allowedStatuses)) {
        return ['success' => false, 'message' => 'Invalid status value'];
    }

    // Get request details
    $stmt = $pdo->prepare("SELECT * FROM device_requests WHERE id = ?");
    $stmt->execute([$requestId]);
    $request = $stmt->fetch();
    
    if (!$request) {
        return ['success' => false, 'message' => 'Request not found'];
    }
    
    try {
        // Update request status
        $stmt = $pdo->prepare("UPDATE device_requests 
                              SET status = ?, processed_by = ?, processed_date = NOW(), admin_notes = ?
                              WHERE id = ?");
        $stmt->execute([$status, $adminId, $notes, $requestId]);
        
        // If approved, assign device to user
        if ($status === 'approved') {
            $stmt = $pdo->prepare("UPDATE devices 
                                  SET current_owner_id = ?, is_available = 0
                                  WHERE id = ?");
            $stmt->execute([$request['user_id'], $request['device_id']]);
        }
        
        return ['success' => true, 'message' => 'Request processed'];
    } catch (PDOException $e) {
        error_log("Error processing request: " . $e->getMessage());
        return ['success' => false, 'message' => 'Error processing request'];
    }
}

function updateUserProfile($userId, $data) {
    global $pdo;
    
    $updates = [];
    $params = [];
    
    if (!empty($data['first_name'])) {
        $updates[] = "first_name = ?";
        $params[] = $data['first_name'];
    }
    
    if (!empty($data['surname'])) {
        $updates[] = "surname = ?";
        $params[] = $data['surname'];
    }
    
    if (!empty($data['department'])) {
        $updates[] = "department = ?";
        $params[] = $data['department'];
    }
    
    if (!empty($data['email'])) {
        // Verify email is not already taken by another user
        $stmt = $pdo->prepare("SELECT id FROM users WHERE email = ? AND id != ?");
        $stmt->execute([$data['email'], $userId]);
        if ($stmt->fetch()) {
            return ['success' => false, 'message' => 'Email already in use by another account'];
        }
        
        $updates[] = "email = ?";
        $params[] = $data['email'];
    }
    
    if (!empty($data['new_password'])) {
        if (!validatePassword($data['new_password'])) {
            return ['success' => false, 'message' => 'New password does not meet requirements'];
        }
        
        $updates[] = "password_hash = ?";
        $params[] = password_hash($data['new_password'], PASSWORD_DEFAULT);
    }
    
    if (empty($updates)) {
        return ['success' => false, 'message' => 'No changes to update'];
    }
    
    $sql = "UPDATE users SET " . implode(", ", $updates) . " WHERE id = ?";
    $params[] = $userId;
    
    $stmt = $pdo->prepare($sql);
    $success = $stmt->execute($params);
    
    if ($success && !empty($data['email'])) {
        $_SESSION['email'] = $data['email'];
    }
    
    return ['success' => $success, 'message' => $success ? 'Profile updated' : 'Update failed'];
}

function addDevice($data) {
    global $pdo;
    
    $required = ['asset_no', 'serial_no', 'name'];
    foreach ($required as $field) {
        if (empty($data[$field])) {
            return ['success' => false, 'message' => "$field is required"];
        }
    }
    
    try {
        $stmt = $pdo->prepare("INSERT INTO devices 
                              (asset_no, serial_no, name, brand, product_type_id, cpu, ram, storage, specifications, is_available)
                              VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");
        $stmt->execute([
            $data['asset_no'],
            $data['serial_no'],
            $data['name'],
            $data['brand'] ?? null,
            $data['product_type_id'] ?? null,
            $data['cpu'] ?? null,
            $data['ram'] ?? null,
            $data['storage'] ?? null,
            $data['specifications'] ?? null,
            isset($data['is_available']) ? (int)$data['is_available'] : 1
        ]);
        
        return ['success' => true, 'message' => 'Device added', 'id' => $pdo->lastInsertId()];
    } catch (PDOException $e) {
        return ['success' => false, 'message' => 'Error adding device: ' . $e->getMessage()];
    }
}

function updateDevice($deviceId, $data) {
    global $pdo;
    
    try {
        $stmt = $pdo->prepare("UPDATE devices SET 
                              asset_no = ?, serial_no = ?, name = ?, brand = ?, product_type_id = ?,
                              cpu = ?, ram = ?, storage = ?, specifications = ?, is_available = ?
                              WHERE id = ?");
        $stmt->execute([
            $data['asset_no'],
            $data['serial_no'],
            $data['name'],
            $data['brand'] ?? null,
            $data['product_type_id'] ?? null,
            $data['cpu'] ?? null,
            $data['ram'] ?? null,
            $data['storage'] ?? null,
            $data['specifications'] ?? null,
            isset($data['is_available']) ? (int)$data['is_available'] : 1,
            $deviceId
        ]);
        
        return ['success' => true, 'message' => 'Device updated'];
    } catch (PDOException $e) {
        return ['success' => false, 'message' => 'Error updating device: ' . $e->getMessage()];
    }
}

function deleteDevice($deviceId) {
    global $pdo;
    
    try {
        $stmt = $pdo->prepare("DELETE FROM devices WHERE id = ?");
        $stmt->execute([$deviceId]);
        return ['success' => true, 'message' => 'Device deleted'];
    } catch (PDOException $e) {
        return ['success' => false, 'message' => 'Error deleting device: ' . $e->getMessage()];
    }
}

function approveUser($userId) {
    global $pdo;
    
    try {
        $stmt = $pdo->prepare("UPDATE users SET is_approved = 1 WHERE id = ?");
        $stmt->execute([$userId]);
        return ['success' => true, 'message' => 'User approved'];
    } catch (PDOException $e) {
        return ['success' => false, 'message' => 'Error approving user: ' . $e->getMessage()];
    }
}

function toggleUserStatus($userId) {
    global $pdo;
    
    try {
        $stmt = $pdo->prepare("UPDATE users SET is_disabled = NOT is_disabled WHERE id = ?");
        $stmt->execute([$userId]);
        return ['success' => true, 'message' => 'User status updated'];
    } catch (PDOException $e) {
        return ['success' => false, 'message' => 'Error updating user status: ' . $e->getMessage()];
    }
}

function unassignDevice($deviceId) {
    global $pdo;
    
    try {
        $stmt = $pdo->prepare("UPDATE devices SET current_owner_id = NULL, is_available = 1 WHERE id = ?");
        $stmt->execute([$deviceId]);
        return ['success' => true, 'message' => 'Device unassigned'];
    } catch (PDOException $e) {
        return ['success' => false, 'message' => 'Error unassigning device: ' . $e->getMessage()];
    }
}
?>