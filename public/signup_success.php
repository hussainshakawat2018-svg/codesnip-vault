<?php
session_start();
require_once __DIR__ . '/../includes/header.php';
?>

<div class="row justify-content-center py-5">
    <div class="col-md-6 col-lg-5 text-center">
        <div class="card shadow-sm border-0 p-5 rounded-4">
            <div class="mb-3">
                <span class="display-3 text-success">🎉</span>
            </div>
            <h2 class="fw-bold mb-2">Registration Successful!</h2>
            <p class="text-secondary mb-4">
                Your CodeSnip Vault account has been created. You can now log in to access your personal snippet library.
            </p>
            <div>
                <a href="login.php" class="btn btn-primary px-4 py-2 w-100">
                    Go to Log In
                </a>
            </div>
        </div>
    </div>
</div>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>
