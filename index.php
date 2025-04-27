<?php
require_once 'includes/config.php';
require_once 'includes/header.php';

$pageTitle = "Home";
?>

<div class="row">
    <div class="col-lg-8 mx-auto">
        <div class="card shadow">
            <div class="card-body text-center">
                <h1 class="display-4 mb-4">Welcome to <?php echo APP_NAME; ?></h1>
                
                <?php if (!isLoggedIn()): ?>
                    <p class="lead">Please <a href="login.php">login</a> or <a href="register.php">register</a> to access the system</p>
                    <div class="d-grid gap-2 d-sm-flex justify-content-sm-center mt-4">
                        <a href="login.php" class="btn btn-primary btn-lg px-4 gap-3">Login</a>
                        <a href="register.php" class="btn btn-outline-secondary btn-lg px-4">Register</a>
                    </div>
                <?php else: ?>
                    <p class="lead">Welcome back, <?php echo htmlspecialchars($_SESSION['first_name']); ?>!</p>
                    <div class="d-grid gap-2 d-sm-flex justify-content-sm-center mt-4">
                        <?php if (isAdmin()): ?>
                            <a href="admin/dashboard.php" class="btn btn-primary btn-lg px-4 gap-3">Admin Dashboard</a>
                        <?php else: ?>
                            <a href="user/dashboard.php" class="btn btn-primary btn-lg px-4 gap-3">User Dashboard</a>
                        <?php endif; ?>
                        <a href="rss_feed.php" class="btn btn-outline-secondary btn-lg px-4">View News Feed</a>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>

<?php
require_once 'includes/footer.php';
?>