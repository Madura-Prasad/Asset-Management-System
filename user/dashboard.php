<?php
require_once '../includes/config.php';
require_once '../includes/auth.php';

if (!isLoggedIn()) {
    header("Location: " . APP_URL . "/index.php");
    exit;
}

$userId = $_SESSION['user_id'];
$user = getUserById($userId);
$assignedDevices = getDevices(false, $userId);
$deviceLimit = $user['device_limit'] ?? 1;

$pageTitle = "User Dashboard";
require_once '../includes/header.php';
?>

<div class="container">
    <h1 class="mb-4">Welcome, <?php echo htmlspecialchars($user['first_name']); ?></h1>
    
    <div class="row mb-4">
        <div class="col-md-4">
            <div class="card text-white bg-primary">
                <div class="card-body">
                    <h5 class="card-title">Assigned Devices</h5>
                    <p class="card-text display-6"><?php echo count($assignedDevices); ?> / <?php echo $deviceLimit; ?></p>
                    <a href="devices.php" class="text-white">View Devices</a>
                </div>
            </div>
        </div>
        
        <div class="col-md-4">
            <div class="card text-white bg-success">
                <div class="card-body">
                    <h5 class="card-title">Pending Requests</h5>
                    <p class="card-text display-6">
                        <?php 
                            $stmt = $pdo->prepare("SELECT COUNT(*) FROM device_requests 
                                                  WHERE user_id = ? AND status = 'pending'");
                            $stmt->execute([$userId]);
                            echo $stmt->fetchColumn();
                        ?>
                    </p>
                    <a href="requests.php" class="text-white">View Requests</a>
                </div>
            </div>
        </div>
        
        <div class="col-md-4">
            <div class="card text-white bg-info">
                <div class="card-body">
                    <h5 class="card-title">Available Devices</h5>
                    <p class="card-text display-6">
                        <?php 
                            $stmt = $pdo->query("SELECT COUNT(*) FROM devices WHERE is_available = 1");
                            echo $stmt->fetchColumn();
                        ?>
                    </p>
                    <a href="devices.php" class="text-white">View News Feed</a>
                </div>
            </div>
        </div>
    </div>
    
    <div class="card mb-4">
        <div class="card-header">
            <h5 class="mb-0">Your Assigned Devices</h5>
        </div>
        <div class="card-body">
            <?php if (empty($assignedDevices)): ?>
                <p>You don't have any devices assigned to you.</p>
                <a href="devices.php" class="btn btn-primary">Browse Available Devices</a>
            <?php else: ?>
                <div class="table-responsive">
                    <table class="table table-striped">
                        <thead>
                            <tr>
                                <th>Asset No</th>
                                <th>Name</th>
                                <th>Type</th>
                                <th>Brand</th>
                                <th>Specs</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($assignedDevices as $device): ?>
                                <tr>
                                    <td><?php echo htmlspecialchars($device['asset_no']); ?></td>
                                    <td><?php echo htmlspecialchars($device['name']); ?></td>
                                    <td><?php echo htmlspecialchars($device['type_name'] ?? 'N/A'); ?></td>
                                    <td><?php echo htmlspecialchars($device['brand'] ?? 'N/A'); ?></td>
                                    <td>
                                        <?php if ($device['cpu'] || $device['ram'] || $device['storage']): ?>
                                            <?php echo htmlspecialchars(implode(', ', array_filter([$device['cpu'], $device['ram'], $device['storage']]))); ?>
                                        <?php else: ?>
                                            N/A
                                        <?php endif; ?>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            <?php endif; ?>
        </div>
    </div>
</div>

<?php
require_once '../includes/footer.php';
?>