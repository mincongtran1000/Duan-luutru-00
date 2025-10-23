<?php
require 'includes/db.php';

if (isset($_GET['query'])) {
    $query = '%' . $_GET['query'] . '%';

    $sql = "
        SELECT name FROM products
        WHERE is_hidden = 0 AND name LIKE ?
        ORDER BY created_at DESC
        LIMIT 5
    ";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("s", $query);
    $stmt->execute();
    $result = $stmt->get_result();

    $suggestions = [];
    while ($row = $result->fetch_assoc()) {
        $suggestions[] = $row['name'];
    }

    echo json_encode($suggestions);
}
