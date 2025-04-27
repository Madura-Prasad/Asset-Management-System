<?php
require_once '../includes/config.php';
require_once '../includes/auth.php';

if (!isLoggedIn()) {
    header("Location: " . APP_URL . "/index.php");
    exit;
}

$userId = $_SESSION['user_id'];
$user = getUserById($userId);

// Handle profile update
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $result = updateUserProfile($userId, [
        'first_name' => trim($_POST['first_name']),
        'surname' => trim($_POST['surname']),
        'email' => trim($_POST['email']),
        'department' => trim($_POST['department']),
        'new_password' => $_POST['new_password'],
        'confirm_password' => $_POST['confirm_password']
    ]);
    
    $_SESSION['message'] = $result['message'];
    $_SESSION['message_type'] = $result['success'] ? 'success' : 'danger';
    
    if ($result['success']) {
        $_SESSION['first_name'] = $user['first_name'];
        header("Location: profile.php");
        exit;
    }
}

$pageTitle = "My Profile";
require_once '../includes/header.php';
?>

<div class="container">
    <div class="row justify-content-center">
        <div class="col-md-8">
            <div class="card shadow">
                <div class="card-body">
                    <h2 class="card-title text-center mb-4">My Profile</h2>
                    
                    <form action="<?php echo htmlspecialchars($_SERVER['PHP_SELF']); ?>" method="post">
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label for="employee_no" class="form-label">Employee Number</label>
                                <input type="text" class="form-control" id="employee_no" 
                                       value="<?php echo htmlspecialchars($user['employee_no']); ?>" readonly>
                            </div>
                            
                            <div class="col-md-6 mb-3">
                                <label for="department" class="form-label">Department</label>
                                <input type="text" class="form-control" id="department" name="department"
                                       value="<?php echo htmlspecialchars($user['department'] ?? ''); ?>">
                            </div>
                        </div>
                        
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label for="first_name" class="form-label">First Name</label>
                                <input type="text" class="form-control" id="first_name" name="first_name"
                                       value="<?php echo htmlspecialchars($user['first_name']); ?>" required>
                            </div>
                            
                            <div class="col-md-6 mb-3">
                                <label for="surname" class="form-label">Surname</label>
                                <input type="text" class="form-control" id="surname" name="surname"
                                       value="<?php echo htmlspecialchars($user['surname']); ?>" required>
                            </div>
                        </div>
                        
                        <div class="mb-3">
                            <label for="email" class="form-label">Email</label>
                            <input type="email" class="form-control" id="email" name="email"
                                   value="<?php echo htmlspecialchars($user['email']); ?>" required>
                        </div>
                        
                        <hr class="my-4">
                        
                        <h5 class="mb-3">Change Password</h5>
                        
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label for="new_password" class="form-label">New Password</label>
                                <input type="password" class="form-control" id="new_password" name="new_password">
                                <div class="form-text">Leave blank to keep current password</div>
                            </div>
                            
                            <div class="col-md-6 mb-3">
                                <label for="confirm_password" class="form-label">Confirm Password</label>
                                <input type="password" class="form-control" id="confirm_password" name="confirm_password">
                            </div>
                        </div>
                        
                        <div class="d-grid">
                            <button type="submit" class="btn btn-primary">Update Profile</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>

<?php
require_once '../includes/footer.php';
?>