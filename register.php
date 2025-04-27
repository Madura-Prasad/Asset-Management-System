<?php
require_once 'includes/config.php';

if (isLoggedIn()) {
    header("Location: " . APP_URL . "/index.php");
    exit;
}

$errors = [];
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $result = registerUser([
        'employee_no' => trim($_POST['employee_no']),
        'first_name' => trim($_POST['first_name']),
        'surname' => trim($_POST['surname']),
        'email' => trim($_POST['email']),
        'department' => trim($_POST['department']),
        'password' => $_POST['password'],
        'confirm_password' => $_POST['confirm_password']
    ]);
    
    if ($result['success']) {
        $_SESSION['message'] = "Registration successful! Your account is pending approval.";
        $_SESSION['message_type'] = "success";
        header("Location: " . APP_URL . "/login.php");
        exit;
    } else {
        $errors = $result['errors'];
    }
}

$pageTitle = "Register";
require_once 'includes/header.php';
?>

<div class="row justify-content-center">
    <div class="col-md-8 col-lg-6">
        <div class="card shadow">
            <div class="card-body">
                <h2 class="card-title text-center mb-4">Register</h2>
                
                <?php if (!empty($errors)): ?>
                    <div class="alert alert-danger">
                        <ul class="mb-0">
                            <?php foreach ($errors as $error): ?>
                                <li><?php echo htmlspecialchars($error); ?></li>
                            <?php endforeach; ?>
                        </ul>
                    </div>
                <?php endif; ?>
                
                <form action="<?php echo htmlspecialchars($_SERVER['PHP_SELF']); ?>" method="post">
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label for="employee_no" class="form-label">Employee Number</label>
                            <input type="text" class="form-control" id="employee_no" name="employee_no" required>
                        </div>
                        
                        <div class="col-md-6 mb-3">
                            <label for="department" class="form-label">Department</label>
                            <input type="text" class="form-control" id="department" name="department">
                        </div>
                    </div>
                    
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label for="first_name" class="form-label">First Name</label>
                            <input type="text" class="form-control" id="first_name" name="first_name" required>
                        </div>
                        
                        <div class="col-md-6 mb-3">
                            <label for="surname" class="form-label">Surname</label>
                            <input type="text" class="form-control" id="surname" name="surname" required>
                        </div>
                    </div>
                    
                    <div class="mb-3">
                        <label for="email" class="form-label">Email</label>
                        <input type="email" class="form-control" id="email" name="email" required>
                    </div>
                    
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label for="password" class="form-label">Password</label>
                            <input type="password" class="form-control" id="password" name="password" required>
                            <div class="form-text">Minimum <?php echo PASSWORD_MIN_LENGTH; ?> characters. 
                                <?php if (PASSWORD_REQUIRE_UPPERCASE) echo 'At least one uppercase letter. '; ?>
                                <?php if (PASSWORD_REQUIRE_NUMBER) echo 'At least one number. '; ?>
                                <?php if (PASSWORD_REQUIRE_SPECIAL_CHAR) echo 'At least one special character.'; ?>
                            </div>
                        </div>
                        
                        <div class="col-md-6 mb-3">
                            <label for="confirm_password" class="form-label">Confirm Password</label>
                            <input type="password" class="form-control" id="confirm_password" name="confirm_password" required>
                        </div>
                    </div>
                    
                    <div class="d-grid gap-2">
                        <button type="submit" class="btn btn-primary">Register</button>
                    </div>
                    
                    <div class="mt-3 text-center">
                        <p>Already have an account? <a href="login.php">Login here</a></p>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<?php
require_once 'includes/footer.php';
?>