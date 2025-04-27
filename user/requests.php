<?php
require_once '../includes/config.php';
require_once '../includes/auth.php';

if (!isLoggedIn()) {
    header("Location: " . APP_URL . "/index.php");
    exit;
}

$userId = $_SESSION['user_id'];
$requests = getUserRequests($userId);

// Handle new request
if (isset($_GET['action']) && $_GET['action'] == 'request' && isset($_GET['device_id'])) {
    $result = requestDevice($userId, $_GET['device_id']);
    $_SESSION['message'] = $result['message'];
    $_SESSION['message_type'] = $result['success'] ? 'success' : 'danger';
    header("Location: requests.php");
    exit;
}

$pageTitle = "My Device Requests";
require_once '../includes/header.php';
?>

<div class="container">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h1>My Device Requests</h1>
        <a href="devices.php" class="btn btn-primary">Request New Device</a>
    </div>
    
    <div class="card">
        <div class="card-body">
            <?php if (empty($requests)): ?>
                <p class="text-center">You haven't made any device requests yet.</p>
            <?php else: ?>
                <div class="table-responsive">
                    <table class="table table-striped">
                        <thead>
                            <tr>
                                <th>Device</th>
                                <th>Asset No</th>
                                <th>Serial No</th>
                                <th>Request Date</th>
                                <th>Status</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($requests as $request): ?>
                                <tr>
                                    <td><?php echo htmlspecialchars($request['device_name']); ?></td>
                                    <td><?php echo htmlspecialchars($request['asset_no']); ?></td>
                                    <td><?php echo htmlspecialchars($request['serial_no']); ?></td>
                                    <td><?php echo date('M j, Y', strtotime($request['request_date'])); ?></td>
                                    <td>
                                        <span class="badge bg-<?php 
                                            echo $request['status'] === 'Approved' ? 'success' : 
                                                 ($request['status'] === 'Rejected' ? 'danger' : 'warning'); 
                                        ?>">
                                            <?php echo $request['status_text']; ?>
                                        </span>
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