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

// Get filter/search inputs
$page     = isset($_GET['page']) ? max(1, (int)$_GET['page']) : 1;
$search   = trim($_GET['search'] ?? '');
$language = trim($_GET['language'] ?? '');
$tag      = trim($_GET['tag'] ?? '');

$data = $snippetModel->getUserSnippets($userId, $page, 10, $search, $language, $tag);
$snippets   = $data['snippets'];
$totalPages = $data['totalPages'];
$totalCount = $data['total'];

// Get languages for dropdown filter
$availableLanguages = $snippetModel->getUserLanguages($userId);

require_once __DIR__ . '/../includes/header.php';
?>

<div class="d-flex justify-content-between align-items-center mb-4 flex-wrap gap-2">
    <div>
        <h2 class="fw-bold mb-0">My Snippets</h2>
        <p class="text-secondary mb-0">Welcome back, <strong><?php echo htmlspecialchars($_SESSION['username']); ?></strong>! You have <?php echo $totalCount; ?> saved snippet(s).</p>
    </div>
    <a href="snippet_form.php" class="btn btn-primary">
        + New Snippet
    </a>
</div>

<!-- Search and Filter Bar -->
<div class="card shadow-sm border-0 mb-4 p-3 bg-body-tertiary">
    <form method="GET" action="dashboard.php" class="row g-2 align-items-center">
        <div class="col-md-5">
            <div class="input-group">
                <span class="input-group-text bg-white">🔍</span>
                <input type="text" name="search" class="form-control" placeholder="Search by title, description or code..." 
                       value="<?php echo htmlspecialchars($search); ?>">
            </div>
        </div>
        <div class="col-md-3">
            <select name="language" class="form-select">
                <option value="">All Languages</option>
                <?php foreach ($availableLanguages as $lang): ?>
                    <option value="<?php echo htmlspecialchars($lang); ?>" <?php echo ($language === $lang) ? 'selected' : ''; ?>>
                        <?php echo htmlspecialchars(ucfirst($lang)); ?>
                    </option>
                <?php endforeach; ?>
            </select>
        </div>
        <div class="col-md-2">
            <button type="submit" class="btn btn-dark w-100">Filter</button>
        </div>
        <?php if (!empty($search) || !empty($language) || !empty($tag)): ?>
            <div class="col-md-2">
                <a href="dashboard.php" class="btn btn-outline-secondary w-100">Reset</a>
            </div>
        <?php endif; ?>
    </form>
</div>

<!-- Snippet Feed -->
<?php if (empty($snippets)): ?>
    <div class="text-center py-5 border rounded-3 bg-white shadow-sm">
        <div class="display-6 text-muted mb-3">📄</div>
        <h4>No snippets found</h4>
        <p class="text-secondary">
            <?php if (!empty($search) || !empty($language) || !empty($tag)): ?>
                Try adjusting your search criteria or resetting filters.
            <?php else: ?>
                You haven't added any snippets yet. Save your first code block!
            <?php endif; ?>
        </p>
        <a href="snippet_form.php" class="btn btn-primary mt-2">Create First Snippet</a>
    </div>
<?php else: ?>
    <div class="row g-4">
        <?php foreach ($snippets as $s): ?>
            <div class="col-12" id="snippet-card-<?php echo $s['id']; ?>">
                <div class="card shadow-sm border-0">
                    <div class="card-body">
                        <div class="d-flex justify-content-between align-items-start mb-2 flex-wrap gap-2">
                            <div>
                                <h4 class="card-title fw-bold mb-1">
                                    <?php echo htmlspecialchars($s['title']); ?>
                                </h4>
                                <div class="d-flex align-items-center gap-2 flex-wrap">
                                    <span class="badge bg-secondary"><?php echo htmlspecialchars(strtoupper($s['language'])); ?></span>
                                    <?php if ($s['is_public']): ?>
                                        <span class="badge bg-success">Shared (Public)</span>
                                    <?php else: ?>
                                        <span class="badge bg-dark-subtle text-dark">Private</span>
                                    <?php endif; ?>

                                    <?php if (!empty($s['tag_names'])): ?>
                                        <?php foreach (explode(',', $s['tag_names']) as $t): ?>
                                            <a href="dashboard.php?tag=<?php echo urlencode(trim($t)); ?>" class="badge bg-info-subtle text-info-emphasis text-decoration-none">
                                                #<?php echo htmlspecialchars(trim($t)); ?>
                                            </a>
                                        <?php endforeach; ?>
                                    <?php endif; ?>
                                </div>
                            </div>
                            <div class="text-secondary small">
                                <?php echo date('M d, Y', strtotime($s['created_at'])); ?>
                            </div>
                        </div>

                        <?php if (!empty($s['description'])): ?>
                            <p class="card-text text-muted mb-3"><?php echo nl2br(htmlspecialchars($s['description'])); ?></p>
                        <?php endif; ?>

                        <!-- Code Box -->
                        <div class="position-relative mb-3">
                            <pre class="bg-dark text-light p-3 rounded-3 mb-0" style="max-height: 250px; overflow-y: auto;"><code><?php echo htmlspecialchars($s['code']); ?></code></pre>
                            <button class="btn btn-sm btn-outline-light position-absolute top-0 end-0 m-2 copy-btn" 
                                    data-code="<?php echo htmlspecialchars($s['code']); ?>">
                                Copy
                            </button>
                        </div>

                        <!-- Card Actions -->
                        <div class="d-flex justify-content-between align-items-center flex-wrap gap-2 pt-2 border-top">
                            <div>
                                <a href="snippet_form.php?id=<?php echo $s['id']; ?>" class="btn btn-sm btn-outline-primary me-2">Edit</a>
                                <button class="btn btn-sm btn-outline-danger delete-btn" data-id="<?php echo $s['id']; ?>">
                                    Delete
                                </button>
                            </div>
                            <div>
                                <?php if ($s['is_public'] && !empty($s['share_token'])): ?>
                                    <a href="share.php?token=<?php echo urlencode($s['share_token']); ?>" target="_blank" class="btn btn-sm btn-outline-success">
                                        View Share Link ↗
                                    </a>
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        <?php endforeach; ?>
    </div>

    <!-- Pagination -->
    <?php if ($totalPages > 1): ?>
        <nav class="mt-4">
            <ul class="pagination justify-content-center">
                <li class="page-item <?php echo ($page <= 1) ? 'disabled' : ''; ?>">
                    <a class="page-link" href="?page=<?php echo $page - 1; ?>&search=<?php echo urlencode($search); ?>&language=<?php echo urlencode($language); ?>&tag=<?php echo urlencode($tag); ?>">Previous</a>
                </li>
                <?php for ($i = 1; $i <= $totalPages; $i++): ?>
                    <li class="page-item <?php echo ($i === $page) ? 'active' : ''; ?>">
                        <a class="page-link" href="?page=<?php echo $i; ?>&search=<?php echo urlencode($search); ?>&language=<?php echo urlencode($language); ?>&tag=<?php echo urlencode($tag); ?>"><?php echo $i; ?></a>
                    </li>
                <?php endfor; ?>
                <li class="page-item <?php echo ($page >= $totalPages) ? 'disabled' : ''; ?>">
                    <a class="page-link" href="?page=<?php echo $page + 1; ?>&search=<?php echo urlencode($search); ?>&language=<?php echo urlencode($language); ?>&tag=<?php echo urlencode($tag); ?>">Next</a>
                </li>
            </ul>
        </nav>
    <?php endif; ?>
<?php endif; ?>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>
