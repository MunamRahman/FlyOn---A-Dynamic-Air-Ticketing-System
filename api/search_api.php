<?php
/**
 * Flight Search API
 * Returns flight search results in JSON format
 */

require_once '../config.php';
require_once '../includes/db_connect.php';
require_once '../includes/functions.php';

header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] !== 'GET') {
    jsonResponse(['error' => 'Method not allowed'], 405);
}

$from = sanitize($_GET['from'] ?? '');
$to = sanitize($_GET['to'] ?? '');
$departure_date = $_GET['departure_date'] ?? '';
$passengers = intval($_GET['passengers'] ?? 1);
$class = sanitize($_GET['class'] ?? 'economy');
$return_date = $_GET['return_date'] ?? null;

// Extract city names from "City (CODE)" format if present
$fromCity = preg_replace('/\s*\([A-Z]{3}\)\s*$/', '', $from);
$toCity = preg_replace('/\s*\([A-Z]{3}\)\s*$/', '', $to);

if (empty($fromCity) || empty($toCity) || empty($departure_date)) {
    jsonResponse(['error' => 'Missing required parameters'], 400);
}

try {
    $db = getDB();
    
    // Build query for outbound flights
    $query = "SELECT f.*, 
              al.name as airline_name, al.code as airline_code, al.logo as airline_logo,
              dep.name as departure_airport, dep.code as departure_code, dep.city as departure_city,
              arr.name as arrival_airport, arr.code as arrival_code, arr.city as arrival_city
              FROM flights f
              JOIN airlines al ON f.airline_id = al.id
              JOIN airports dep ON f.departure_airport_id = dep.id
              JOIN airports arr ON f.arrival_airport_id = arr.id
              WHERE DATE(f.departure_time) = ? 
              AND dep.city LIKE ? 
              AND arr.city LIKE ?
              AND f.status = 'scheduled'";
    
    // Add class filter
    $classSeats = $class . '_seats';
    $query .= " AND f.available_seats_{$class} > 0";
    
    $query .= " ORDER BY f.departure_time ASC";
    
    $stmt = $db->prepare($query);
    $stmt->execute([$departure_date, "%$fromCity%", "%$toCity%"]);
    $outboundFlights = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    // Update search count for demand-based pricing
    foreach ($outboundFlights as $flight) {
        $updateStmt = $db->prepare("UPDATE flights SET search_count = search_count + 1 WHERE id = ?");
        $updateStmt->execute([$flight['id']]);
    }
    
    // Calculate dynamic prices
    foreach ($outboundFlights as &$flight) {
        $basePrice = $flight['base_price_' . $class] ?? $flight['base_price_economy'];
        $flight['price'] = calculateDynamicPrice($basePrice, $flight['id'], $flight['departure_time']);
        $flight['duration'] = calculateDuration($flight['departure_time'], $flight['arrival_time']);
    }
    
    $response = [
        'success' => true,
        'outbound' => $outboundFlights,
        'count' => count($outboundFlights)
    ];
    
    // Handle return flights for round trip
    if ($return_date) {
        $returnQuery = str_replace('dep.city LIKE ?', 'arr.city LIKE ?', $query);
        $returnQuery = str_replace('arr.city LIKE ?', 'dep.city LIKE ?', $returnQuery);
        $returnQuery = str_replace('DATE(f.departure_time) = ?', 'DATE(f.departure_time) = ?', $returnQuery);
        
        $stmt = $db->prepare($returnQuery);
        $stmt->execute([$return_date, "%$toCity%", "%$fromCity%"]);
        $returnFlights = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        foreach ($returnFlights as &$flight) {
            $basePrice = $flight['base_price_' . $class] ?? $flight['base_price_economy'];
            $flight['price'] = calculateDynamicPrice($basePrice, $flight['id'], $flight['departure_time']);
            $flight['duration'] = calculateDuration($flight['departure_time'], $flight['arrival_time']);
        }
        
        $response['return'] = $returnFlights;
    }
    
    jsonResponse($response);
    
} catch (PDOException $e) {
    if (APP_DEBUG) {
        jsonResponse(['error' => 'Database error: ' . $e->getMessage()], 500);
    } else {
        jsonResponse(['error' => 'An error occurred while searching flights'], 500);
    }
}

