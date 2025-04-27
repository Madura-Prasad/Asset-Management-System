<?php
require_once '../includes/config.php';
require_once '../includes/auth.php';

if (!isLoggedIn()) {
    header("Location: " . APP_URL . "/index.php");
    exit;
}

$userId = $_SESSION['user_id'];
$assignedDevices = getDevices(false, $userId);
$availableDevices = getDevices(true);

$pageTitle = "My Devices";
require_once '../includes/header.php';
?>

<div class="container">
    <h1 class="mb-4">My Devices</h1>
    
    <div class="card mb-4">
        <div class="card-header">
            <h5 class="mb-0">Currently Assigned to You</h5>
        </div>
        <div class="card-body">
            <?php if (empty($assignedDevices)): ?>
                <p>You don't have any devices assigned to you.</p>
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
    
    <div class="card">
        <div class="card-header">
            <h5 class="mb-0">Available Devices</h5>
        </div>
        <div class="card-body">
            <?php if (empty($availableDevices)): ?>
                <p>No available devices at this time.</p>
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
                                <th>Action</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($availableDevices as $device): ?>
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
                                    <td>
                                        <a href="requests.php?action=request&device_id=<?php echo $device['id']; ?>" 
                                           class="btn btn-sm btn-primary">Request</a>
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