<?php
// Start output buffering at the very first line
ob_start();

require_once 'includes/config.php';
require_once 'includes/auth.php';

if (isLoggedIn()) {
    ob_end_clean();
    header("Location: " . APP_URL . "/index.php");
    exit;
}

$error = '';
$success = '';

// Rate limiting - prevent brute force attacks
$maxAttempts = 3;
$lockoutTime = 300; // 5 minutes in seconds

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = trim($_POST['email']);
    
    // Validate email
    if (empty($email)) {
        $error = 'Email address is required';
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $error = 'Please enter a valid email address';
    } else {
        // Check rate limiting
        $stmt = $pdo->prepare("SELECT attempts, last_attempt FROM password_reset_attempts WHERE email = ?");
        $stmt->execute([$email]);
        $attemptData = $stmt->fetch();
        
        if ($attemptData && $attemptData['attempts'] >= $maxAttempts) {
            $lastAttempt = strtotime($attemptData['last_attempt']);
            $timeSinceLastAttempt = time() - $lastAttempt;
            
            if ($timeSinceLastAttempt < $lockoutTime) {
                $remainingTime = $lockoutTime - $timeSinceLastAttempt;
                $error = "Too many attempts. Please try again in " . ceil($remainingTime/60) . " minutes.";
            }
        }
        
        if (empty($error)) {
            // Check if email exists
            $stmt = $pdo->prepare("SELECT id FROM users WHERE email = ?");
            $stmt->execute([$email]);
            $user = $stmt->fetch();
            
            if ($user) {
                // Generate token
                $token = bin2hex(random_bytes(32));
                $expires = date('Y-m-d H:i:s', strtotime('+1 hour'));
                
                // Delete any existing tokens for this user
                $stmt = $pdo->prepare("DELETE FROM password_reset_tokens WHERE user_id = ?");
                $stmt->execute([$user['id']]);
                
                // Store new token in database
                $stmt = $pdo->prepare("INSERT INTO password_reset_tokens (user_id, token, expires_at) VALUES (?, ?, ?)");
                $stmt->execute([$user['id'], $token, $expires]);
                
                // Update rate limiting
                if ($attemptData) {
                    $stmt = $pdo->prepare("UPDATE password_reset_attempts SET attempts = attempts + 1, last_attempt = NOW() WHERE email = ?");
                } else {
                    $stmt = $pdo->prepare("INSERT INTO password_reset_attempts (email, attempts, last_attempt) VALUES (?, 1, NOW())");
                }
                $stmt->execute([$email]);
                
                // Generate reset link
                $resetLink = APP_URL . "/reset_password.php?token=" . urlencode($token);
                
                // In production, you would send an actual email here
                $success = "Password reset link has been sent to your email. (Demo: <a href='$resetLink'>$resetLink</a>)";
                
                // For demo purposes, we'll log the reset link
                error_log("Password reset link for $email: $resetLink");
            } else {
                // Don't reveal whether email exists in system
                $success = "If this email exists in our system, you will receive a password reset link";
            }
        }
    }
}

$pageTitle = "Forgot Password";
require_once 'includes/header.php';
?>

<div class="row justify-content-center">
    <div class="col-md-6 col-lg-5">
        <div class="card shadow">
            <div class="card-body">
                <h2 class="card-title text-center mb-4">Forgot Password</h2>
                
                <?php if ($error): ?>
                    <div class="alert alert-danger"><?php echo htmlspecialchars($error); ?></div>
                <?php endif; ?>
                
                <?php if ($success): ?>
                    <div class="alert alert-success"><?php echo $success; ?></div>
                <?php else: ?>
                    <form action="<?php echo htmlspecialchars($_SERVER['PHP_SELF']); ?>" method="post" id="resetForm">
                        <div class="mb-3">
                            <label for="email" class="form-label">Email Address</label>
                            <input type="email" class="form-control" id="email" name="email" required>
                        </div>
                        
                        <div class="d-grid gap-2">
                            <button type="submit" class="btn btn-primary">Reset Password</button>
                        </div>
                    </form>
                <?php endif; ?>
                
                <div class="mt-3 text-center">
                    <a href="login.php">Back to Login</a>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
// Client-side validation
document.getElementById('resetForm').addEventListener('submit', function(e) {
    const email = document.getElementById('email').value.trim();
    if (!email) {
        e.preventDefault();
        alert('Please enter your email address');
        return false;
    }
    return true;
});
</script>

<?php
require_once 'includes/footer.php';
?>