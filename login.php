<?php
require_once 'includes/config.php';

if (isLoggedIn()) {
    header("Location: " . APP_URL . "/index.php");
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = trim($_POST['email']);
    $password = $_POST['password'];
    
    $result = loginUser($email, $password);
    
    if ($result['success']) {
        $_SESSION['message'] = "Login successful!";
        $_SESSION['message_type'] = "success";
        header("Location: " . APP_URL . "/index.php");
        exit;
    } else {
        $_SESSION['message'] = $result['message'];
        $_SESSION['message_type'] = "danger";
    }
}

$pageTitle = "Login";
require_once 'includes/header.php';
?>

<div class="row justify-content-center">
    <div class="col-md-6 col-lg-5">
        <div class="card shadow">
            <div class="card-body">
                <h2 class="card-title text-center mb-4">Login</h2>
                
                <form action="<?php echo htmlspecialchars($_SERVER['PHP_SELF']); ?>" method="post">
                    <div class="mb-3">
                        <label for="email" class="form-label">Email</label>
                        <input type="email" class="form-control" id="email" name="email" required>
                    </div>
                    
                    <div class="mb-3">
                        <label for="password" class="form-label">Password</label>
                        <input type="password" class="form-control" id="password" name="password" required>
                    </div>
                    
                    <div class="d-grid gap-2">
                        <button type="submit" class="btn btn-primary">Login</button>
                    </div>
                    
                    <div class="mt-3 text-center">
                        <a href="forgot_password.php">Forgot password?</a>
                    </div>
                </form>
                
                <div class="mt-4 text-center">
                    <p>Don't have an account? <a href="register.php">Register here</a></p>
                </div>
            </div>
        </div>
    </div>
</div>

<?php
require_once 'includes/footer.php';
?>