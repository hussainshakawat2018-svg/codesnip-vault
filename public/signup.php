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
$success = '';

// If form is submitted
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $user = new User($pdo);

    $username         = trim($_POST['username'] ?? '');
    $email            = trim($_POST['email'] ?? '');
    $password         = $_POST['password'] ?? '';
    $confirm_password = $_POST['confirm_password'] ?? '';

    $result = $user->register($username, $email, $password, $confirm_password);

    if ($result === true) {
        $success = "Account created successfully! You can now log in.";
    } else {
        $error = $result;
    }
}

require_once __DIR__ . '/../includes/header.php';
?>

<div class="row justify-content-center">
    <div class="col-md-6 col-lg-5">
        <div class="card shadow-sm border-0 p-4">
            <h2 class="mb-4 text-center fw-bold">Sign Up</h2>

            <?php if ($error): ?>
                <div class="alert alert-danger py-2"><?php echo htmlspecialchars($error); ?></div>
            <?php endif; ?>

            <?php if ($success): ?>
                <div class="alert alert-success py-2">
                    <?php echo htmlspecialchars($success); ?>
                    <div class="mt-2"><a href="login.php" class="btn btn-sm btn-success">Go to Log In</a></div>
                </div>
            <?php endif; ?>

            <form method="POST" action="" id="signupForm">
                <div class="mb-3">
                    <label for="username" class="form-label">Username</label>
                    <input type="text" class="form-control" id="username" name="username" 
                           placeholder="e.g. alex_coder"
                           value="<?php echo htmlspecialchars($_POST['username'] ?? ''); ?>" required autofocus>
                </div>

                <div class="mb-3">
                    <label for="email" class="form-label">Email</label>
                    <input type="email" class="form-control" id="email" name="email" 
                           placeholder="you@example.com"
                           value="<?php echo htmlspecialchars($_POST['email'] ?? ''); ?>" required>
                </div>

                <div class="mb-3">
                    <label for="password" class="form-label">Password</label>
                    <input type="password" class="form-control" id="password" name="password" 
                           placeholder="At least 6 characters" required minlength="6">
                </div>

                <div class="mb-3">
                    <label for="confirm_password" class="form-label">Confirm Password</label>
                    <input type="password" class="form-control" id="confirm_password" name="confirm_password" 
                           placeholder="Re-type password" required minlength="6">
                    <div id="passwordMismatchMsg" class="text-danger small mt-1 d-none">Passwords do not match!</div>
                </div>

                <button type="submit" class="btn btn-primary w-100 py-2">Sign Up</button>
            </form>

            <p class="mt-3 text-center text-muted">
                Already have an account? <a href="login.php" class="text-decoration-none">Log in</a>
            </p>
        </div>
    </div>
</div>

<script>
// Client-side quick check
const pass = document.getElementById('password');
const confirmPass = document.getElementById('confirm_password');
const mismatchMsg = document.getElementById('passwordMismatchMsg');
const form = document.getElementById('signupForm');

function validatePassword() {
    if (confirmPass.value && pass.value !== confirmPass.value) {
        mismatchMsg.classList.remove('d-none');
        confirmPass.classList.add('is-invalid');
        return false;
    } else {
        mismatchMsg.classList.add('d-none');
        confirmPass.classList.remove('is-invalid');
        return true;
    }
}

pass.addEventListener('input', validatePassword);
confirmPass.addEventListener('input', validatePassword);

form.addEventListener('submit', function(e) {
    if (!validatePassword()) {
        e.preventDefault();
    }
});
</script>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>
