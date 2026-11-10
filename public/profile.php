<?php
session_start();

// Ensure user is logged in
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit;
}

require_once __DIR__ . '/../config/db.php';
require_once __DIR__ . '/../classes/User.php';

$userModel = new User($pdo);
$userId = $_SESSION['user_id'];

$profileError   = '';
$profileSuccess = '';
$passwordError   = '';
$passwordSuccess = '';

// Handle Profile Info Update
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'update_profile') {
    $newUsername = trim($_POST['username'] ?? '');
    $newEmail    = trim($_POST['email'] ?? '');

    $result = $userModel->updateProfile($userId, $newUsername, $newEmail);
    if ($result === true) {
        $_SESSION['username'] = $newUsername; // Keep session synced
        $profileSuccess = "Profile updated successfully!";
    } else {
        $profileError = $result;
    }
}

// Handle Password Change
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'update_password') {
    $currentPassword = $_POST['current_password'] ?? '';
    $newPassword     = $_POST['new_password'] ?? '';
    $confirmPassword = $_POST['confirm_password'] ?? '';

    $result = $userModel->updatePassword($userId, $currentPassword, $newPassword, $confirmPassword);
    if ($result === true) {
        $passwordSuccess = "Password changed successfully!";
    } else {
        $passwordError = $result;
    }
}

// Fetch fresh user record
$user = $userModel->getUserById($userId);

require_once __DIR__ . '/../includes/header.php';
?>

<div class="row justify-content-center py-4">
    <div class="col-lg-9">
        <!-- Profile Banner Card -->
        <div class="card shadow-sm border-0 mb-4 bg-primary text-white rounded-3 p-4">
            <div class="d-flex align-items-center gap-3">
                <div class="display-5 bg-white text-primary rounded-circle d-flex align-items-center justify-content-center shadow-sm" style="width: 70px; height: 70px;">
                    👤
                </div>
                <div>
                    <h3 class="fw-bold mb-0"><?php echo htmlspecialchars($user['username']); ?></h3>
                    <p class="mb-0 opacity-75">
                        <?php echo htmlspecialchars($user['email']); ?> &bull; 
                        <span class="badge bg-light text-dark text-uppercase"><?php echo htmlspecialchars($user['role']); ?></span> &bull; 
                        Member since <?php echo date('M Y', strtotime($user['created_at'])); ?>
                    </p>
                </div>
            </div>
        </div>

        <div class="row g-4">
            <!-- Edit Profile Card -->
            <div class="col-md-6">
                <div class="card shadow-sm border-0 h-100 p-4">
                    <h4 class="fw-bold mb-3">Edit Profile Info</h4>

                    <?php if ($profileError): ?>
                        <div class="alert alert-danger py-2"><?php echo htmlspecialchars($profileError); ?></div>
                    <?php endif; ?>

                    <?php if ($profileSuccess): ?>
                        <div class="alert alert-success py-2"><?php echo htmlspecialchars($profileSuccess); ?></div>
                    <?php endif; ?>

                    <form method="POST" action="">
                        <input type="hidden" name="action" value="update_profile">

                        <div class="mb-3">
                            <label for="username" class="form-label fw-semibold">Username</label>
                            <input type="text" class="form-control" id="username" name="username" 
                                   value="<?php echo htmlspecialchars($user['username']); ?>" required>
                        </div>

                        <div class="mb-3">
                            <label for="email" class="form-label fw-semibold">Email Address</label>
                            <input type="email" class="form-control" id="email" name="email" 
                                   value="<?php echo htmlspecialchars($user['email']); ?>" required>
                        </div>

                        <button type="submit" class="btn btn-primary w-100 mt-2">Save Profile Changes</button>
                    </form>
                </div>
            </div>

            <!-- Change Password Card -->
            <div class="col-md-6">
                <div class="card shadow-sm border-0 h-100 p-4">
                    <h4 class="fw-bold mb-3">Change Password</h4>

                    <?php if ($passwordError): ?>
                        <div class="alert alert-danger py-2"><?php echo htmlspecialchars($passwordError); ?></div>
                    <?php endif; ?>

                    <?php if ($passwordSuccess): ?>
                        <div class="alert alert-success py-2"><?php echo htmlspecialchars($passwordSuccess); ?></div>
                    <?php endif; ?>

                    <form method="POST" action="">
                        <input type="hidden" name="action" value="update_password">

                        <div class="mb-3">
                            <label for="current_password" class="form-label fw-semibold">Current Password</label>
                            <input type="password" class="form-control" id="current_password" name="current_password" 
                                   placeholder="Enter your current password" required>
                        </div>

                        <div class="mb-3">
                            <label for="new_password" class="form-label fw-semibold">New Password</label>
                            <input type="password" class="form-control" id="new_password" name="new_password" 
                                   placeholder="At least 6 characters" minlength="6" required>
                        </div>

                        <div class="mb-3">
                            <label for="confirm_password" class="form-label fw-semibold">Confirm New Password</label>
                            <input type="password" class="form-control" id="confirm_password" name="confirm_password" 
                                   placeholder="Re-type new password" minlength="6" required>
                        </div>

                        <button type="submit" class="btn btn-dark w-100 mt-2">Update Password</button>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>
