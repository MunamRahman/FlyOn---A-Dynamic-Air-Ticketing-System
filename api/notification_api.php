<?php
/**
 * Notification API
 * Handles notification operations (send, list, mark as read)
 */

require_once '../config.php';
require_once '../includes/db_connect.php';
require_once '../includes/functions.php';

header('Content-Type: application/json');

if (!isLoggedIn()) {
    jsonResponse(['error' => 'Unauthorized'], 401);
}

$method = $_SERVER['REQUEST_METHOD'];
$userId = getCurrentUserId();

try {
    $db = getDB();
    
    switch ($method) {
        case 'GET':
            listNotifications($db, $userId);
            break;
            
        case 'POST':
            if (isset($_POST['mark_read'])) {
                markAsRead($db, $userId);
            } else {
                jsonResponse(['error' => 'Invalid action'], 400);
            }
            break;
            
        default:
            jsonResponse(['error' => 'Method not allowed'], 405);
    }
    
} catch (Exception $e) {
    if (APP_DEBUG) {
        jsonResponse(['error' => $e->getMessage()], 500);
    } else {
        jsonResponse(['error' => 'An error occurred'], 500);
    }
}

function listNotifications($db, $userId) {
    $limit = intval($_GET['limit'] ?? 20);
    $offset = intval($_GET['offset'] ?? 0);
    $unreadOnly = isset($_GET['unread_only']) && $_GET['unread_only'] === 'true';
    
    $query = "SELECT * FROM notifications WHERE user_id = ?";
    
    if ($unreadOnly) {
        $query .= " AND is_read = 0";
    }
    
    $query .= " ORDER BY created_at DESC LIMIT ? OFFSET ?";
    
    $stmt = $db->prepare($query);
    $stmt->execute([$userId, $limit, $offset]);
    $notifications = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    // Get unread count
    $stmt = $db->prepare("SELECT COUNT(*) FROM notifications WHERE user_id = ? AND is_read = 0");
    $stmt->execute([$userId]);
    $unreadCount = $stmt->fetchColumn();
    
    jsonResponse([
        'success' => true,
        'notifications' => $notifications,
        'unread_count' => $unreadCount
    ]);
}

function markAsRead($db, $userId) {
    $notificationId = intval($_POST['notification_id'] ?? 0);
    
    if ($notificationId) {
        // Mark specific notification as read
        $stmt = $db->prepare("UPDATE notifications SET is_read = 1, read_at = NOW() WHERE id = ? AND user_id = ?");
        $stmt->execute([$notificationId, $userId]);
    } else {
        // Mark all notifications as read
        $stmt = $db->prepare("UPDATE notifications SET is_read = 1, read_at = NOW() WHERE user_id = ? AND is_read = 0");
        $stmt->execute([$userId]);
    }
    
    jsonResponse(['success' => true, 'message' => 'Notification(s) marked as read']);
}

