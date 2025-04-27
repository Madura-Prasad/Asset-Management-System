<?php
// Start output buffering at the VERY FIRST LINE
ob_start();

require_once '../includes/config.php';
require_once '../includes/auth.php';

if (!isLoggedIn() || !isAdmin()) {
    ob_end_clean();
    header("Location: " . APP_URL . "/index.php");
    exit;
}

// Handle request actions BEFORE any output
if (isset($_GET['action']) && isset($_GET['id'])) {
    $validActions = [
        'approve' => 'approved',  // Map URL action to database status
        'reject' => 'rejected'
    ];
    
    if (array_key_exists($_GET['action'], $validActions)) {
        $result = processRequest(
            $_GET['id'], 
            $validActions[$_GET['action']],  // Use mapped status value
            $_SESSION['user_id'], 
            $_GET['notes'] ?? ''
        );
        
        ob_end_clean();
        $_SESSION['message'] = $result['message'];
        $_SESSION['message_type'] = $result['success'] ? 'success' : 'danger';
        header("Location: requests.php");
        exit;
    }
}

$requests = getPendingRequests();
$pageTitle = "Manage Device Requests";
require_once '../includes/header.php';
?>

<div class="container">
    <h1 class="mb-4">Manage Device Requests</h1>
    
    <div class="card">
        <div class="card-body">
            <?php if (empty($requests)): ?>
                <p class="text-center">No pending requests</p>
            <?php else: ?>
                <div class="table-responsive">
                    <table class="table table-striped table-hover">
                        <thead>
                            <tr>
                                <th>User</th>
                                <th>Device</th>
                                <th>Request Date</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($requests as $request): ?>
                                <tr>
                                    <td><?php echo htmlspecialchars($request['first_name'] . ' ' . $request['surname']); ?></td>
                                    <td><?php echo htmlspecialchars($request['device_name']); ?></td>
                                    <td><?php echo date('M j, Y H:i', strtotime($request['request_date'])); ?></td>
                                    <td>
                                        <div class="btn-group btn-group-sm">
                                            <a href="requests.php?action=approve&id=<?php echo $request['id']; ?>" 
                                               class="btn btn-outline-success" title="Approve">
                                                <i class="bi bi-check-circle"></i>
                                            </a>
                                            <a href="requests.php?action=reject&id=<?php echo $request['id']; ?>" 
                                               class="btn btn-outline-danger" title="Reject">
                                                <i class="bi bi-x-circle"></i>
                                            </a>
                                        </div>
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