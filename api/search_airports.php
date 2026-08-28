<?php
/**
 * Airport Search API
 * Returns airports matching the search query for autocomplete
 */

require_once '../config.php';
require_once '../includes/db_connect.php';
require_once '../includes/functions.php';

header('Content-Type: application/json');

if (!isset($_GET['q'])) {
    echo json_encode([]);
    exit;
}

$query = sanitize($_GET['q']);
$db = getDB();

try {
    $stmt = $db->prepare("
        SELECT 
            id,
            name,
            code,
            city,
            country,
            CONCAT(city, ' (', code, ') - ', name) as display_name
        FROM airports 
        WHERE status = 'active' 
        AND (
            city LIKE ? 
            OR code LIKE ? 
            OR name LIKE ?
            OR country LIKE ?
        )
        ORDER BY 
            CASE 
                WHEN country = 'Bangladesh' THEN 0 
                ELSE 1 
            END,
            city ASC
        LIMIT 10
    ");
    
    $searchTerm = "%{$query}%";
    $stmt->execute([$searchTerm, $searchTerm, $searchTerm, $searchTerm]);
    $airports = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    echo json_encode($airports);
    
} catch (PDOException $e) {
    echo json_encode([]);
}
