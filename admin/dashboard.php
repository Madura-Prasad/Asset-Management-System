<?php
require_once '../includes/config.php';
require_once '../includes/auth.php';

if (!isLoggedIn() || !isAdmin()) {
    header("Location: " . APP_URL . "/index.php");
    exit;
}

// Get stats for dashboard
$usersCount = $pdo->query("SELECT COUNT(*) FROM users WHERE is_approved = 1")->fetchColumn();
$devicesCount = $pdo->query("SELECT COUNT(*) FROM devices")->fetchColumn();
$availableDevices = $pdo->query("SELECT COUNT(*) FROM devices WHERE is_available = 1")->fetchColumn();
$pendingRequests = $pdo->query("SELECT COUNT(*) FROM device_requests WHERE status = 'pending'")->fetchColumn();
$pendingUsers = $pdo->query("SELECT COUNT(*) FROM users WHERE is_approved = 0")->fetchColumn();

$pageTitle = "Admin Dashboard";
require_once '../includes/header.php';
?>

<div class="container">
    <h1 class="mb-4">Admin Dashboard</h1>
    
    <div class="row mb-4">
        <div class="col-md-4">
            <div class="card text-white bg-primary mb-3">
                <div class="card-body">
                    <h5 class="card-title">Users</h5>
                    <p class="card-text display-6"><?php echo $usersCount; ?></p>
                    <a href="users.php" class="text-white">View Users</a>
                </div>
            </div>
        </div>
        
        <div class="col-md-4">
            <div class="card text-white bg-success mb-3">
                <div class="card-body">
                    <h5 class="card-title">Devices</h5>
                    <p class="card-text display-6"><?php echo $devicesCount; ?></p>
                    <a href="devices.php" class="text-white">View Devices</a>
                </div>
            </div>
        </div>
        
        <div class="col-md-4">
            <div class="card text-white bg-info mb-3">
                <div class="card-body">
                    <h5 class="card-title">Available Devices</h5>
                    <p class="card-text display-6"><?php echo $availableDevices; ?></p>
                    <a href="devices.php?available=1" class="text-white">View Available</a>
                </div>
            </div>
        </div>
    </div>
    
    <div class="row">
        <div class="col-md-6">
            <div class="card mb-4">
                <div class="card-header bg-warning text-dark">
                    <h5 class="mb-0">Pending Requests</h5>
                </div>
                <div class="card-body">
                    <p class="card-text display-6"><?php echo $pendingRequests; ?></p>
                    <a href="requests.php" class="btn btn-warning">Manage Requests</a>
                </div>
            </div>
        </div>
        
        <div class="col-md-6">
            <div class="card mb-4">
                <div class="card-header bg-danger text-white">
                    <h5 class="mb-0">Pending User Approvals</h5>
                </div>
                <div class="card-body">
                    <p class="card-text display-6"><?php echo $pendingUsers; ?></p>
                    <a href="users.php?pending=1" class="btn btn-danger">Review Users</a>
                </div>
            </div>
        </div>
    </div>
    
    <div class="card">
        <div class="card-header">
            <h5 class="mb-0">Recent Device Requests</h5>
        </div>
        <div class="card-body">
            <?php
            $stmt = $pdo->query("SELECT r.*, u.first_name, u.surname, d.name as device_name 
                               FROM device_requests r
                               JOIN users u ON r.user_id = u.id
                               JOIN devices d ON r.device_id = d.id
                               ORDER BY r.request_date DESC LIMIT 5");
            $recentRequests = $stmt->fetchAll();
            
            if (empty($recentRequests)): ?>
                <p>No recent requests</p>
            <?php else: ?>
                <div class="table-responsive">
                    <table class="table table-striped">
                        <thead>
                            <tr>
                                <th>User</th>
                                <th>Device</th>
                                <th>Date</th>
                                <th>Status</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($recentRequests as $request): ?>
                                <tr>
                                    <td><?php echo htmlspecialchars($request['first_name'] . ' ' . $request['surname']); ?></td>
                                    <td><?php echo htmlspecialchars($request['device_name']); ?></td>
                                    <td><?php echo date('M j, Y', strtotime($request['request_date'])); ?></td>
                                    <td>
                                        <span class="badge bg-<?php 
                                            echo $request['status'] === 'approved' ? 'success' : 
                                                 ($request['status'] === 'rejected' ? 'danger' : 'warning'); 
                                        ?>">
                                            <?php echo ucfirst($request['status']); ?>
                                        </span>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
                <a href="requests.php" class="btn btn-primary">View All Requests</a>
            <?php endif; ?>
        </div>
    </div>
</div>

<?php
require_once '../includes/footer.php';
?>