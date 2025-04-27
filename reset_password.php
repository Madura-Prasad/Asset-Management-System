<?php
require_once 'includes/config.php';
require_once 'includes/auth.php';

if (isLoggedIn()) {
    header("Location: " . APP_URL . "/index.php");
    exit;
}

$error = '';
$success = '';
$showForm = false;
$token = $_GET['token'] ?? '';

if (!empty($token)) {
    try {
        // Verify token
        $stmt = $pdo->prepare("
            SELECT user_id 
            FROM password_reset_tokens 
            WHERE token = ? 
            AND expires_at > NOW() 
            AND is_used = FALSE
        ");
        $stmt->execute([$token]);
        $tokenData = $stmt->fetch();
        
        if ($tokenData) {
            $userId = $tokenData['user_id'];
            $showForm = true;
            
            if ($_SERVER['REQUEST_METHOD'] === 'POST') {
                $password = $_POST['password'];
                $confirmPassword = $_POST['confirm_password'];
                
                // Validate inputs
                if (empty($password) || empty($confirmPassword)) {
                    $error = 'Both password fields are required';
                } elseif ($password !== $confirmPassword) {
                    $error = 'Passwords do not match';
                } elseif (!validatePassword($password)) {
                    $error = 'Password must be at least 8 characters with uppercase, number, and special character';
                } else {
                    // Begin transaction
                    $pdo->beginTransaction();
                    
                    try {
                        // Update password
                        $hashedPassword = password_hash($password, PASSWORD_DEFAULT);
                        $stmt = $pdo->prepare("UPDATE users SET password_hash = ? WHERE id = ?");
                        $stmt->execute([$hashedPassword, $userId]);
                        
                        // Mark token as used
                        $stmt = $pdo->prepare("UPDATE password_reset_tokens SET is_used = TRUE WHERE token = ?");
                        $stmt->execute([$token]);
                        
                        $pdo->commit();
                        
                        $success = 'Password has been reset successfully. <a href="login.php">Login now</a>';
                        $showForm = false;
                    } catch (Exception $e) {
                        $pdo->rollBack();
                        $error = 'Error resetting password. Please try again.';
                    }
                }
            }
        } else {
            $error = 'Invalid or expired password reset link. <a href="forgot_password.php">Request a new one</a>.';
        }
    } catch (PDOException $e) {
        error_log("Password reset error: " . $e->getMessage());
        $error = 'An error occurred. Please try again.';
    }
} else {
    $error = 'No reset token provided. <a href="forgot_password.php">Request a password reset</a>.';
}

$pageTitle = "Reset Password";
require_once 'includes/header.php';
?>

<div class="row justify-content-center">
    <div class="col-md-6 col-lg-5">
        <div class="card shadow">
            <div class="card-body">
                <h2 class="card-title text-center mb-4">Reset Password</h2>
                
                <?php if (!empty($error)): ?>
                    <div class="alert alert-danger"><?php echo $error; ?></div>
                <?php endif; ?>
                
                <?php if (!empty($success)): ?>
                    <div class="alert alert-success"><?php echo $success; ?></div>
                <?php elseif ($showForm): ?>
                    <form method="post">
                        <div class="mb-3">
                            <label for="password" class="form-label">New Password</label>
                            <input type="password" class="form-control" id="password" name="password" required>
                            <div class="form-text">
                                Minimum 8 characters with at least:
                                <ul>
                                    <li>One uppercase letter</li>
                                    <li>One number</li>
                                    <li>One special character</li>
                                </ul>
                            </div>
                        </div>
                        
                        <div class="mb-3">
                            <label for="confirm_password" class="form-label">Confirm New Password</label>
                            <input type="password" class="form-control" id="confirm_password" name="confirm_password" required>
                        </div>
                        
                        <div class="d-grid gap-2">
                            <button type="submit" class="btn btn-primary">Reset Password</button>
                        </div>
                    </form>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>

<?php
require_once 'includes/footer.php';
?>