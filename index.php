<?php
session_start();

// If user is already logged in, redirect them directly to the dashboard
if (isset($_SESSION['user_id'])) {
    header("Location: public/dashboard.php");
    exit;
}

require_once __DIR__ . '/includes/header.php';
?>

<div class="row align-items-center py-5">
    <div class="col-lg-7 text-center text-lg-start mb-4 mb-lg-0">
        <h1 class="display-4 fw-bold mb-3">Your Personal Code Vault</h1>
        <p class="lead text-secondary mb-4">
            Stop losing valuable code across old projects, messy chats, and random text files.
            CodeSnip Vault gives you a searchable, private library for all the solutions you worked hard to build.
        </p>
        <div class="d-grid gap-2 d-md-flex justify-content-md-start">
            <a href="public/signup.php" class="btn btn-primary btn-lg px-4 me-md-2">Get Started (Free)</a>
            <a href="public/login.php" class="btn btn-outline-secondary btn-lg px-4">Log In</a>
        </div>
    </div>
    <div class="col-lg-5">
        <div class="card shadow-sm border-0 bg-dark text-light p-3 rounded-4">
            <div class="card-body">
                <div class="d-flex align-items-center mb-3">
                    <span class="badge bg-danger me-2">PHP</span>
                    <span class="badge bg-secondary">database</span>
                    <span class="badge bg-secondary">pdo</span>
                </div>
                <h5 class="card-title text-info mb-2">PDO Safe Connection</h5>
                <p class="card-text text-secondary small">Standard secure PDO setup with UTF-8 and exception mode.</p>
                <pre class="bg-black text-success p-3 rounded small mb-0"><code>$pdo = new PDO($dsn, $user, $pass, [
    PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC
]);</code></pre>
            </div>
        </div>
    </div>
</div>

<div class="row g-4 py-4 border-top">
    <div class="col-md-4">
        <div class="card h-100 border-0 shadow-sm p-3">
            <div class="card-body">
                <h5 class="fw-bold text-primary mb-2">🔒 Private by Default</h5>
                <p class="text-secondary small mb-0">
                    Your snippets remain strictly private to your account until you decide to share a read-only secret token link.
                </p>
            </div>
        </div>
    </div>
    <div class="col-md-4">
        <div class="card h-100 border-0 shadow-sm p-3">
            <div class="card-body">
                <h5 class="fw-bold text-success mb-2">⚡ Lightning Fast Search</h5>
                <p class="text-secondary small mb-0">
                    Filter across titles, descriptions, programming languages, and custom tags instantly with Fetch-powered queries.
                </p>
            </div>
        </div>
    </div>
    <div class="col-md-4">
        <div class="card h-100 border-0 shadow-sm p-3">
            <div class="card-body">
                <h5 class="fw-bold text-info mb-2">📋 1-Click Copy & Share</h5>
                <p class="text-secondary small mb-0">
                    Copy snippet code straight to your clipboard or generate secret unguessable links to share with classmates.
                </p>
            </div>
        </div>
    </div>
</div>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
