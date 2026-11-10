<?php
session_start();

// If user already logged in, redirect to dashboard
if (isset($_SESSION['user_id'])) {
    header("Location: dashboard.php");
    exit;
}

require_once __DIR__ . '/../config/db.php';
require_once __DIR__ . '/../classes/User.php';

$error = '';

// If form is submitted
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $user = new User($pdo);

    $result = $user->login(
        trim($_POST['identifier'] ?? ''),
        $_POST['password'] ?? ''
    );

    if (is_array($result)) {
        // Login successful — store user data in session
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
    <div class="col-md-5">
        <div class="card shadow-sm border-0 p-4">
            <h2 class="mb-4 text-center fw-bold">Log In</h2>

            <?php if ($error): ?>
                <div class="alert alert-danger py-2"><?php echo htmlspecialchars($error); ?></div>
            <?php endif; ?>

            <form method="POST" action="">
                <div class="mb-3">
                    <label for="identifier" class="form-label">Username or Email</label>
                    <input type="text" class="form-control" id="identifier" name="identifier" 
                           placeholder="Enter your username or email"
                           value="<?php echo htmlspecialchars($_POST['identifier'] ?? ''); ?>" required autofocus>
                </div>

                <div class="mb-3">
                    <label for="password" class="form-label">Password</label>
                    <input type="password" class="form-control" id="password" name="password" 
                           placeholder="Enter your password" required>
                </div>

                <button type="submit" class="btn btn-primary w-100 py-2">Log In</button>
            </form>

            <p class="mt-3 text-center text-muted">
                Don't have an account? <a href="signup.php" class="text-decoration-none">Sign up</a>
            </p>
        </div>
    </div>
</div>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>
