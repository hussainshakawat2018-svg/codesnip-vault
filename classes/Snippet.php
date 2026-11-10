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
}
