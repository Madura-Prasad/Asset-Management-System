<?php
require_once '../includes/config.php';
require_once '../includes/auth.php';

if (!isLoggedIn() || !isAdmin()) {
    header("Location: " . APP_URL . "/index.php");
    exit;
}

$pendingOnly = isset($_GET['pending']) && $_GET['pending'] == 1;
$users = $pendingOnly ? 
    $pdo->query("SELECT * FROM users WHERE is_approved = 0")->fetchAll() : 
    getAllUsers();

$pageTitle = $pendingOnly ? "Pending User Approvals" : "Manage Users";
require_once '../includes/header.php';
?>

<div class="container">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h1><?php echo $pageTitle; ?></h1>
        <?php if (!$pendingOnly): ?>
            <a href="users.php?pending=1" class="btn btn-warning">View Pending Approvals</a>
        <?php endif; ?>
    </div>
    
    <div class="card">
        <div class="card-body">
            <?php if (empty($users)): ?>
                <p class="text-center">No users found</p>
            <?php else: ?>
                <div class="table-responsive">
                    <table class="table table-striped table-hover">
                        <thead>
                            <tr>
                                <th>Employee #</th>
                                <th>Name</th>
                                <th>Email</th>
                                <th>Department</th>
                                <th>Status</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($users as $user): ?>
                                <tr>
                                    <td><?php echo htmlspecialchars($user['employee_no']); ?></td>
                                    <td><?php echo htmlspecialchars($user['first_name'] . ' ' . $user['surname']); ?></td>
                                    <td><?php echo htmlspecialchars($user['email']); ?></td>
                                    <td><?php echo htmlspecialchars($user['department'] ?? 'N/A'); ?></td>
                                    <td>
                                        <?php if ($user['is_disabled']): ?>
                                            <span class="badge bg-danger">Disabled</span>
                                        <?php elseif (!$user['is_approved']): ?>
                                            <span class="badge bg-warning text-dark">Pending Approval</span>
                                        <?php else: ?>
                                            <span class="badge bg-success">Active</span>
                                        <?php endif; ?>
                                    </td>
                                    <td>
                                        <div class="btn-group btn-group-sm">
                                            <?php if (!$user['is_approved']): ?>
                                                <a href="users.php?action=approve&id=<?php echo $user['id']; ?>" 
                                                   class="btn btn-outline-success" title="Approve">
                                                    <i class="bi bi-check-circle"></i>
                                                </a>
                                            <?php endif; ?>
                                            
                                            <a href="users.php?action=toggle&id=<?php echo $user['id']; ?>" 
                                               class="btn btn-outline-<?php echo $user['is_disabled'] ? 'success' : 'danger'; ?>" 
                                               title="<?php echo $user['is_disabled'] ? 'Enable' : 'Disable'; ?>">
                                                <i class="bi bi-<?php echo $user['is_disabled'] ? 'unlock' : 'lock'; ?>"></i>
                                            </a>
                                            
                                            <?php if ($user['is_admin']): ?>
                                                <span class="btn btn-outline-primary" title="Admin User">
                                                    <i class="bi bi-shield-lock"></i>
                                                </span>
                                            <?php endif; ?>
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
// Handle user actions
if (isset($_GET['action']) && isset($_GET['id'])) {
    if ($_GET['action'] == 'approve') {
        $result = approveUser($_GET['id']);
    } elseif ($_GET['action'] == 'toggle') {
        $result = toggleUserStatus($_GET['id']);
    } else {
        $result = ['success' => false, 'message' => 'Invalid action'];
    }
    
    $_SESSION['message'] = $result['message'];
    $_SESSION['message_type'] = $result['success'] ? 'success' : 'danger';
    //header("Location: users.php" . ($pendingOnly ? '?pending=1' : ''));
    exit;
}

require_once '../includes/footer.php';
?>