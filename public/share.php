<?php
session_start();

require_once __DIR__ . '/../config/db.php';

$token = trim($_GET['token'] ?? '');
$snippet = null;

if (!empty($token)) {
    // Look up snippet by token and ensure it is currently marked public
    $sql = "SELECT s.*, u.username, GROUP_CONCAT(t.name SEPARATOR ',') as tag_names
            FROM snippets s
            JOIN users u ON s.user_id = u.id
            LEFT JOIN snippet_tags st ON s.id = st.snippet_id
            LEFT JOIN tags t ON st.tag_id = t.id
            WHERE s.share_token = ? AND s.is_public = 1
            GROUP BY s.id LIMIT 1";

    $stmt = $pdo->prepare($sql);
    $stmt->execute([$token]);
    $snippet = $stmt->fetch();
}

require_once __DIR__ . '/../includes/header.php';
?>

<div class="row justify-content-center py-4">
    <div class="col-lg-9">
        <?php if (!$snippet): ?>
            <div class="text-center py-5 border rounded-4 bg-white shadow-sm p-4">
                <div class="display-3 text-secondary mb-3">🔒</div>
                <h3 class="fw-bold mb-2">Snippet Unavailable</h3>
                <p class="text-secondary mb-4">
                    This snippet does not exist, has been deleted, or the owner has turned off public sharing.
                </p>
                <a href="/codesnip-vault/" class="btn btn-primary">Go to Home</a>
            </div>
        <?php else: ?>
            <div class="card shadow-sm border-0 mb-4 p-4 rounded-4">
                <div class="d-flex justify-content-between align-items-start mb-3 flex-wrap gap-2">
                    <div>
                        <div class="d-flex align-items-center gap-2 mb-2 flex-wrap">
                            <span class="badge bg-secondary"><?php echo htmlspecialchars(strtoupper($snippet['language'])); ?></span>
                            <span class="badge bg-success">Public Shared Link</span>
                            <?php if (!empty($snippet['tag_names'])): ?>
                                <?php foreach (explode(',', $snippet['tag_names']) as $tagName): ?>
                                    <span class="badge bg-info-subtle text-info-emphasis">#<?php echo htmlspecialchars(trim($tagName)); ?></span>
                                <?php endforeach; ?>
                            <?php endif; ?>
                        </div>
                        <h2 class="fw-bold mb-1"><?php echo htmlspecialchars($snippet['title']); ?></h2>
                        <p class="text-secondary small mb-0">
                            Shared by <strong><?php echo htmlspecialchars($snippet['username']); ?></strong> &bull; 
                            Created on <?php echo date('M d, Y', strtotime($snippet['created_at'])); ?>
                        </p>
                    </div>
                    <div>
                        <a href="/codesnip-vault/" class="btn btn-outline-secondary btn-sm">CodeSnip Vault Home</a>
                    </div>
                </div>

                <?php if (!empty($snippet['description'])): ?>
                    <div class="alert alert-light border mb-4">
                        <p class="mb-0 text-secondary"><?php echo nl2br(htmlspecialchars($snippet['description'])); ?></p>
                    </div>
                <?php endif; ?>

                <!-- Code Block with Copy Button -->
                <div class="position-relative">
                    <pre class="bg-dark text-light p-3 rounded-3 mb-0" style="max-height: 500px; overflow-y: auto;"><code><?php echo htmlspecialchars($snippet['code']); ?></code></pre>
                    <button class="btn btn-sm btn-outline-light position-absolute top-0 end-0 m-2 copy-btn" 
                            data-code="<?php echo htmlspecialchars($snippet['code']); ?>">
                        Copy
                    </button>
                </div>
            </div>
        <?php endif; ?>
    </div>
</div>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>
