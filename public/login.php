<?php
require_once __DIR__ . '/../config/db.php';
require_once __DIR__ . '/../classes/User.php';

$error = '';

// If form is submitted
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $user = new User($pdo);

    $result = $user->login(
        trim($_POST['email'] ?? ''),
        $_POST['password'] ?? ''
    );

    if (is_array($result)) {
        // Login successful — store user data in session
        session_start();
        $_SESSION['user_id']   = $result['id'];
        $_SESSION['username']  = $result['username'];
        $_SESSION['role']      = $result['role'];
        $_SESSION['dark_mode'] = $result['dark_mode'];

        header("Location: dashboard.php");
        exit;
    } else {
        $error = $result;
    }
}

require_once __DIR__ . '/../includes/header.php';
?>

<div class="row justify-content-center">
    <div class="col-md-6">
        <h2 class="mb-4">Log In</h2>

        <?php if ($error): ?>
            <div class="alert alert-danger"><?php echo htmlspecialchars($error); ?></div>
        <?php endif; ?>

        <form method="POST" action="">
            <div class="mb-3">
                <label for="email" class="form-label">Email</label>
                <input type="email" class="form-control" id="email" name="email" 
                       value="<?php echo htmlspecialchars($_POST['email'] ?? ''); ?>" required>
            </div>

            <div class="mb-3">
                <label for="password" class="form-label">Password</label>
                <input type="password" class="form-control" id="password" name="password" required>
            </div>

            <button type="submit" class="btn btn-primary w-100">Log In</button>
        </form>

        <p class="mt-3 text-center">Don't have an account? <a href="signup.php">Sign up</a></p>
    </div>
</div>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>
