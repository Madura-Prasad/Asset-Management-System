<?php
require_once 'config.php';

function isLoggedIn() {
    return isset($_SESSION['user_id']);
}

function isAdmin() {
    return isset($_SESSION['is_admin']) && $_SESSION['is_admin'];
}

function loginUser($email, $password) {
    global $pdo;
    
    $stmt = $pdo->prepare("SELECT * FROM users WHERE email = ? AND is_disabled = 0");
    $stmt->execute([$email]);
    $user = $stmt->fetch();
    
    if ($user && password_verify($password, $user['password_hash'])) {
        if (!$user['is_approved']) {
            return ['success' => false, 'message' => 'Your account is pending approval'];
        }
        
        $_SESSION['user_id'] = $user['id'];
        $_SESSION['email'] = $user['email'];
        $_SESSION['first_name'] = $user['first_name'];
        $_SESSION['is_admin'] = $user['is_admin'];
        
        return ['success' => true];
    }
    
    return ['success' => false, 'message' => 'Invalid email or password'];
}

function registerUser($data) {
    global $pdo;
    
    // Validate input
    $errors = [];
    
    if (empty($data['employee_no'])) {
        $errors[] = 'Employee number is required';
    }
    
    if (empty($data['first_name'])) {
        $errors[] = 'First name is required';
    }
    
    if (empty($data['surname'])) {
        $errors[] = 'Surname is required';
    }
    
    if (empty($data['email']) || !filter_var($data['email'], FILTER_VALIDATE_EMAIL)) {
        $errors[] = 'Valid email is required';
    }
    
    if (empty($data['password']) || !validatePassword($data['password'])) {
        $errors[] = 'Password does not meet requirements';
    }
    
    if ($data['password'] !== $data['confirm_password']) {
        $errors[] = 'Passwords do not match';
    }
    
    if (!empty($errors)) {
        return ['success' => false, 'errors' => $errors];
    }
    
    // Check if email or employee number already exists
    $stmt = $pdo->prepare("SELECT id FROM users WHERE email = ? OR employee_no = ?");
    $stmt->execute([$data['email'], $data['employee_no']]);
    
    if ($stmt->fetch()) {
        return ['success' => false, 'errors' => ['Email or employee number already exists']];
    }
    
    // Hash password
    $passwordHash = password_hash($data['password'], PASSWORD_DEFAULT);
    
    // Insert new user
    try {
        $stmt = $pdo->prepare("INSERT INTO users (employee_no, first_name, surname, email, password_hash, department, created_at) 
                               VALUES (?, ?, ?, ?, ?, ?, NOW())");
        $stmt->execute([
            $data['employee_no'],
            $data['first_name'],
            $data['surname'],
            $data['email'],
            $passwordHash,
            $data['department'] ?? null
        ]);
        
        return ['success' => true];
    } catch (PDOException $e) {
        return ['success' => false, 'errors' => ['Registration failed: ' . $e->getMessage()]];
    }
}

function validatePassword($password) {
    if (strlen($password) < PASSWORD_MIN_LENGTH) {
        return false;
    }
    
    if (PASSWORD_REQUIRE_UPPERCASE && !preg_match('/[A-Z]/', $password)) {
        return false;
    }
    
    if (PASSWORD_REQUIRE_NUMBER && !preg_match('/[0-9]/', $password)) {
        return false;
    }
    
    if (PASSWORD_REQUIRE_SPECIAL_CHAR && !preg_match('/[^A-Za-z0-9]/', $password)) {
        return false;
    }
    
    return true;
}
?>