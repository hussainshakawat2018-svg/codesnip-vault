<?php
session_start();

// Ensure user is logged in
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit;
}

require_once __DIR__ . '/../config/db.php';
require_once __DIR__ . '/../classes/Snippet.php';

$snippetModel = new Snippet($pdo);
$userId = $_SESSION['user_id'];

$error = '';
$isEdit = false;
$snippetId = isset($_GET['id']) ? (int)$_GET['id'] : 0;

$title       = '';
$language    = 'php';
$description = '';
$code        = '';
$tags        = '';

// Supported languages list
$languages = [
    'php' => 'PHP',
    'javascript' => 'JavaScript',
    'python' => 'Python',
    'html' => 'HTML',
    'css' => 'CSS',
    'sql' => 'SQL',
    'java' => 'Java',
    'cpp' => 'C++',
    'csharp' => 'C#',
    'typescript' => 'TypeScript',
    'bash' => 'Bash / Shell',
    'json' => 'JSON',
    'markdown' => 'Markdown'
];

// If editing, load current snippet data
if ($snippetId > 0) {
    $existing = $snippetModel->getSnippetById($snippetId, $userId);
    if ($existing) {
        $isEdit      = true;
        $title       = $existing['title'];
        $language    = strtolower($existing['language']);
        $description = $existing['description'];
        $code        = $existing['code'];
        $tags        = $existing['tag_names'] ?? '';
    } else {
        // Snippet not found or doesn't belong to this user
        header("Location: dashboard.php");
        exit;
    }
}

// Handle Form Submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $title       = trim($_POST['title'] ?? '');
    $language    = strtolower(trim($_POST['language'] ?? 'php'));
    $description = trim($_POST['description'] ?? '');
    $code        = $_POST['code'] ?? '';
    $tags        = trim($_POST['tags'] ?? '');

    if (empty($title) || empty($code) || empty($language)) {
        $error = "Title, language, and code are required.";
    } else {
        if ($isEdit) {
            $result = $snippetModel->update($snippetId, $userId, $title, $description, $code, $language, $tags);
        } else {
            $result = $snippetModel->create($userId, $title, $description, $code, $language, $tags);
        }

        if ($result === true) {
            header("Location: dashboard.php");
            exit;
        } else {
            $error = $result;
        }
    }
}

require_once __DIR__ . '/../includes/header.php';
?>

<div class="row justify-content-center py-4">
    <div class="col-lg-8">
        <div class="card shadow-sm border-0 p-4">
            <div class="d-flex justify-content-between align-items-center mb-4">
                <h2 class="fw-bold mb-0"><?php echo $isEdit ? 'Edit Snippet' : 'Create New Snippet'; ?></h2>
                <a href="dashboard.php" class="btn btn-outline-secondary btn-sm">← Back to Dashboard</a>
            </div>

            <?php if ($error): ?>
                <div class="alert alert-danger py-2"><?php echo htmlspecialchars($error); ?></div>
            <?php endif; ?>

            <form method="POST" action="">
                <!-- Title & Language in two columns -->
                <div class="row g-3 mb-3">
                    <div class="col-md-8">
                        <label for="title" class="form-label fw-semibold">Snippet Title <span class="text-danger">*</span></label>
                        <input type="text" class="form-control" id="title" name="title" 
                               placeholder="e.g. PDO Database Connection Helper" 
                               value="<?php echo htmlspecialchars($title); ?>" required autofocus>
                    </div>
                    <div class="col-md-4">
                        <label for="language" class="form-label fw-semibold">Language <span class="text-danger">*</span></label>
                        <select class="form-select" id="language" name="language" required>
                            <?php foreach ($languages as $key => $name): ?>
                                <option value="<?php echo $key; ?>" <?php echo ($language === $key) ? 'selected' : ''; ?>>
                                    <?php echo $name; ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                </div>

                <!-- Description -->
                <div class="mb-3">
                    <label for="description" class="form-label fw-semibold">Description (Optional)</label>
                    <textarea class="form-control" id="description" name="description" rows="2" 
                              placeholder="Briefly explain what this code does or when to use it..."><?php echo htmlspecialchars($description); ?></textarea>
                </div>

                <!-- Tags -->
                <div class="mb-3">
                    <label for="tags" class="form-label fw-semibold">Tags (Optional)</label>
                    <input type="text" class="form-control" id="tags" name="tags" 
                           placeholder="Comma-separated: e.g. pdo, auth, helper, mysql" 
                           value="<?php echo htmlspecialchars($tags); ?>">
                    <div class="form-text">Separate tags with commas.</div>
                </div>

                <!-- Code Input -->
                <div class="mb-4">
                    <label for="code" class="form-label fw-semibold">Code <span class="text-danger">*</span></label>
                    <textarea class="form-control font-monospace bg-dark text-light p-3" id="code" name="code" rows="10" 
                              placeholder="// Paste your code here..." required><?php echo htmlspecialchars($code); ?></textarea>
                </div>

                <!-- Action Buttons -->
                <div class="d-flex justify-content-end gap-2">
                    <a href="dashboard.php" class="btn btn-outline-secondary">Cancel</a>
                    <button type="submit" class="btn btn-primary px-4">
                        <?php echo $isEdit ? 'Save Changes' : 'Save Snippet'; ?>
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>
