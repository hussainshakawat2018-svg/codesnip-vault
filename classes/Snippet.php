<?php

class Snippet {
    private $pdo;

    public function __construct($pdo) {
        $this->pdo = $pdo;
    }

    /**
     * Get paginated snippets for a specific user with optional search and language filters
     */
    public function getUserSnippets($user_id, $page = 1, $limit = 10, $search = '', $language = '', $tag = '') {
        $offset = ($page - 1) * $limit;
        $params = [$user_id];
        $whereClause = "WHERE s.user_id = ?";

        if (!empty($search)) {
            $whereClause .= " AND (s.title LIKE ? OR s.description LIKE ? OR s.code LIKE ?)";
            $searchTerm = "%" . $search . "%";
            $params[] = $searchTerm;
            $params[] = $searchTerm;
            $params[] = $searchTerm;
        }

        if (!empty($language)) {
            $whereClause .= " AND s.language = ?";
            $params[] = $language;
        }

        if (!empty($tag)) {
            $whereClause .= " AND t.name = ?";
            $params[] = $tag;
        }

        // Count total matching records for pagination
        $countSql = "SELECT COUNT(DISTINCT s.id) as total 
                     FROM snippets s 
                     LEFT JOIN snippet_tags st ON s.id = st.snippet_id
                     LEFT JOIN tags t ON st.tag_id = t.id
                     $whereClause";
        $countStmt = $this->pdo->prepare($countSql);
        $countStmt->execute($params);
        $total = (int) $countStmt->fetchColumn();

        // Fetch snippets
        $sql = "SELECT s.*, GROUP_CONCAT(t.name SEPARATOR ',') as tag_names
                FROM snippets s
                LEFT JOIN snippet_tags st ON s.id = st.snippet_id
                LEFT JOIN tags t ON st.tag_id = t.id
                $whereClause
                GROUP BY s.id
                ORDER BY s.created_at DESC
                LIMIT ? OFFSET ?";
        
        $stmt = $this->pdo->prepare($sql);
        // Bind integer parameters explicitly for limit and offset
        $paramIndex = 1;
        foreach ($params as $val) {
            $stmt->bindValue($paramIndex++, $val);
        }
        $stmt->bindValue($paramIndex++, (int) $limit, PDO::PARAM_INT);
        $stmt->bindValue($paramIndex++, (int) $offset, PDO::PARAM_INT);
        $stmt->execute();
        $snippets = $stmt->fetchAll();

        return [
            'snippets' => $snippets,
            'total' => $total,
            'page' => $page,
            'totalPages' => ceil($total / $limit)
        ];
    }

    /**
     * Get distinct languages saved by the user (for filter dropdown)
     */
    public function getUserLanguages($user_id) {
        $stmt = $this->pdo->prepare("SELECT DISTINCT language FROM snippets WHERE user_id = ? ORDER BY language ASC");
        $stmt->execute([$user_id]);
        return $stmt->fetchAll(PDO::FETCH_COLUMN);
    }

    /**
     * Get a single snippet by ID (ensuring it belongs to the user)
     */
    public function getSnippetById($id, $user_id) {
        $sql = "SELECT s.*, GROUP_CONCAT(t.name SEPARATOR ', ') as tag_names
                FROM snippets s
                LEFT JOIN snippet_tags st ON s.id = st.snippet_id
                LEFT JOIN tags t ON st.tag_id = t.id
                WHERE s.id = ? AND s.user_id = ?
                GROUP BY s.id LIMIT 1";
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute([$id, $user_id]);
        return $stmt->fetch();
    }

    /**
     * Create a new snippet and process its tags
     */
    public function create($user_id, $title, $description, $code, $language, $tagsString) {
        if (empty($title) || empty($code) || empty($language)) {
            return "Title, language, and code are required.";
        }

        $this->pdo->beginTransaction();
        try {
            $stmt = $this->pdo->prepare("INSERT INTO snippets (user_id, title, description, code, language) VALUES (?, ?, ?, ?, ?)");
            $stmt->execute([$user_id, $title, $description, $code, strtolower($language)]);
            $snippet_id = $this->pdo->lastInsertId();

            // Handle tags
            $this->syncTags($snippet_id, $tagsString);

            $this->pdo->commit();
            return true;
        } catch (Exception $e) {
            $this->pdo->rollBack();
            return "Failed to save snippet: " . $e->getMessage();
        }
    }

    /**
     * Update an existing snippet and synchronize tags
     */
    public function update($id, $user_id, $title, $description, $code, $language, $tagsString) {
        if (empty($title) || empty($code) || empty($language)) {
            return "Title, language, and code are required.";
        }

        $this->pdo->beginTransaction();
        try {
            $stmt = $this->pdo->prepare("UPDATE snippets SET title = ?, description = ?, code = ?, language = ? WHERE id = ? AND user_id = ?");
            $stmt->execute([$title, $description, $code, strtolower($language), $id, $user_id]);

            // Sync tags: first delete existing tags for this snippet
            $deleteTagsStmt = $this->pdo->prepare("DELETE FROM snippet_tags WHERE snippet_id = ?");
            $deleteTagsStmt->execute([$id]);

            // Re-add updated tags
            $this->syncTags($id, $tagsString);

            $this->pdo->commit();
            return true;
        } catch (Exception $e) {
            $this->pdo->rollBack();
            return "Failed to update snippet: " . $e->getMessage();
        }
    }

    /**
     * Delete a snippet (ownership enforced)
     */
    public function delete($id, $user_id) {
        $stmt = $this->pdo->prepare("DELETE FROM snippets WHERE id = ? AND user_id = ?");
        return $stmt->execute([$id, $user_id]);
    }

    /**
     * Helper to process comma-separated tags and associate with snippet
     */
    private function syncTags($snippet_id, $tagsString) {
        if (empty(trim($tagsString))) {
            return;
        }

        $tagNames = array_unique(array_filter(array_map('trim', explode(',', $tagsString))));

        foreach ($tagNames as $name) {
            if (empty($name)) continue;

            // Insert tag if it doesn't already exist
            $findTag = $this->pdo->prepare("SELECT id FROM tags WHERE name = ?");
            $findTag->execute([$name]);
            $tag = $findTag->fetch();

            if ($tag) {
                $tag_id = $tag['id'];
            } else {
                $insertTag = $this->pdo->prepare("INSERT INTO tags (name) VALUES (?)");
                $insertTag->execute([$name]);
                $tag_id = $this->pdo->lastInsertId();
            }

            // Link snippet and tag
            $linkStmt = $this->pdo->prepare("INSERT IGNORE INTO snippet_tags (snippet_id, tag_id) VALUES (?, ?)");
            $linkStmt->execute([$snippet_id, $tag_id]);
        }
    }
}
