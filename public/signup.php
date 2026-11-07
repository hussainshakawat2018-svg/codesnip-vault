<?php
require_once __DIR__ . '/../config/db.php';
require_once __DIR__ . '/../classes/User.php';

$error = '';
$success = '';

// If form is submitted
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $user = new User($pdo);

    $result = $user->register(
        trim($_POST['username'] ?? ''),
        trim($_POST['email'] ?? ''),
        $_POST['password'] ?? '',
        $_POST['confirm_password'] ?? ''
    );

    if ($result === true) {
        $success = "Account created successfully! You can now log in.";
    } else {
        $error = $result;
    }
}

require_once __DIR__ . '/../includes/header.php';
?>

<div class="row justify-content-center">
    <div class="col-md-6">
        <h2 class="mb-4">Sign Up</h2>

        <?php if ($error): ?>
            <div class="alert alert-danger"><?php echo htmlspecialchars($error); ?></div>
        <?php endif; ?>

        <?php if ($success): ?>
            <div class="alert alert-success">
                <?php echo htmlspecialchars($success); ?>
                <a href="login.php">Click here to log in.</a>
            </div>
        <?php endif; ?>

        <form method="POST" action="">
            <div class="mb-3">
                <label for="username" class="form-label">Username</label>
                <input type="text" class="form-control" id="username" name="username" 
                       value="<?php echo htmlspecialchars($_POST['username'] ?? ''); ?>" required>
            </div>

            <div class="mb-3">
                <label for="email" class="form-label">Email</label>
                <input type="email" class="form-control" id="email" name="email" 
                       value="<?php echo htmlspecialchars($_POST['email'] ?? ''); ?>" required>
            </div>

            <div class="mb-3">
                <label for="password" class="form-label">Password</label>
                <input type="password" class="form-control" id="password" name="password" required>
                <div class="form-text">At least 6 characters.</div>
            </div>

            <div class="mb-3">
                <label for="confirm_password" class="form-label">Confirm Password</label>
                <input type="password" class="form-control" id="confirm_password" name="confirm_password" required>
            </div>

            <button type="submit" class="btn btn-primary w-100">Sign Up</button>
        </form>

        <p class="mt-3 text-center">Already have an account? <a href="login.php">Log in</a></p>
    </div>
</div>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>
