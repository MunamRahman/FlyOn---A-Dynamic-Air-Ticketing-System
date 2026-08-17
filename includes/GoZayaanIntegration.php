<?php
/**
 * GoZayaan Integration Class
 * Handles fetching and syncing flight data from GoZayaan.com
 */

require_once __DIR__ . '/../config.php';
require_once __DIR__ . '/db_connect.php';

class GoZayaanIntegration {
    private $db;
    private $apiKey;
    private $apiUrl;
    private $lastSyncTime;
    
    public function __construct() {
        $this->db = getDB();
        $this->apiKey = env('GOZAYAAN_API_KEY', '');
        $this->apiUrl = env('GOZAYAAN_API_URL', 'https://gozayaan.com/api/v1');
        $this->lastSyncTime = null;
    }
    
    /**
     * Fetch flight data from GoZayaan
     */
    public function fetchFlightData($flightNumber = null, $date = null) {
        try {
            // If API key is available, use API
            if (!empty($this->apiKey)) {
                return $this->fetchViaAPI($flightNumber, $date);
            } else {
                // Fallback to web scraping
                return $this->fetchViaWebScraping($flightNumber, $date);
            }
        } catch (Exception $e) {
            error_log("GoZayaan Integration Error: " . $e->getMessage());
            return ['success' => false, 'error' => $e->getMessage()];
        }
    }
    
    /**
     * Fetch flight data via API
     */
    private function fetchViaAPI($flightNumber = null, $date = null) {
        $url = $this->apiUrl . '/flights';
        $params = [];
        
        if ($flightNumber) {
            $params['flight_number'] = $flightNumber;
        }
        
        if ($date) {
            $params['date'] = $date;
        }
        
        if (!empty($params)) {
            $url .= '?' . http_build_query($params);
        }
        
        $ch = curl_init();
        curl_setopt_array($ch, [
            CURLOPT_URL => $url,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_HTTPHEADER => [
                'Authorization: Bearer ' . $this->apiKey,
                'Content-Type: application/json',
                'User-Agent: FlyOn/1.0'
            ],
            CURLOPT_TIMEOUT => 30,
            CURLOPT_SSL_VERIFYPEER => true
        ]);
        
        $response = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        $error = curl_error($ch);
        curl_close($ch);
        
        if ($error) {
            throw new Exception("CURL Error: " . $error);
        }
        
        if ($httpCode !== 200) {
            throw new Exception("API returned HTTP $httpCode");
        }
        
        $data = json_decode($response, true);
        
        if (json_last_error() !== JSON_ERROR_NONE) {
            throw new Exception("Invalid JSON response");
        }
        
        return ['success' => true, 'data' => $data];
    }
    
    /**
     * Fetch flight data via web scraping (fallback)
     */
    private function fetchViaWebScraping($flightNumber = null, $date = null) {
        // Note: This is a placeholder. Actual implementation would require
        // analyzing GoZayaan's website structure and using a library like Goutte or Guzzle
        
        $url = 'https://gozayaan.com/flights';
        $params = [];
        
        if ($flightNumber) {
            $params['flight'] = $flightNumber;
        }
        
        if ($date) {
            $params['date'] = $date;
        }
        
        if (!empty($params)) {
            $url .= '?' . http_build_query($params);
        }
        
        $ch = curl_init();
        curl_setopt_array($ch, [
            CURLOPT_URL => $url,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_FOLLOWLOCATION => true,
            CURLOPT_USERAGENT => 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36',
            CURLOPT_TIMEOUT => 30,
            CURLOPT_SSL_VERIFYPEER => true,
            CURLOPT_HTTPHEADER => [
                'Accept: text/html,application/xhtml+xml,application/xml;q=0.9,*/*;q=0.8',
                'Accept-Language: en-US,en;q=0.5'
            ]
        ]);
        
        $html = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);
        
        if ($httpCode !== 200) {
            throw new Exception("Failed to fetch data from GoZayaan. HTTP Code: $httpCode");
        }
        
        // Parse HTML to extract flight data
        $flightData = $this->parseFlightHTML($html);
        
        return ['success' => true, 'data' => $flightData];
    }
    
    /**
     * Parse HTML to extract flight information
     * This is a placeholder - actual implementation depends on GoZayaan's HTML structure
     */
    private function parseFlightHTML($html) {
        // Use DOMDocument or SimpleHTMLDom to parse
        $dom = new DOMDocument();
        @$dom->loadHTML($html);
        $xpath = new DOMXPath($dom);
        
        $flights = [];
        
        // Example: Find flight elements (adjust selectors based on actual HTML structure)
        // This is a template - you'll need to inspect GoZayaan's HTML to get correct selectors
        $flightNodes = $xpath->query("//div[contains(@class, 'flight-item')]");
        
        foreach ($flightNodes as $node) {
            $flight = [
                'flight_number' => $this->extractText($xpath, $node, ".//span[contains(@class, 'flight-number')]"),
                'departure_time' => $this->extractText($xpath, $node, ".//span[contains(@class, 'departure-time')]"),
                'arrival_time' => $this->extractText($xpath, $node, ".//span[contains(@class, 'arrival-time')]"),
                'status' => $this->extractText($xpath, $node, ".//span[contains(@class, 'status')]"),
                'price' => $this->extractText($xpath, $node, ".//span[contains(@class, 'price')]")
            ];
            
            if (!empty($flight['flight_number'])) {
                $flights[] = $flight;
            }
        }
        
        return $flights;
    }
    
    /**
     * Extract text from XPath query
     */
    private function extractText($xpath, $context, $query) {
        $nodes = $xpath->query($query, $context);
        if ($nodes->length > 0) {
            return trim($nodes->item(0)->textContent);
        }
        return '';
    }
    
    /**
     * Sync flight times from GoZayaan
     */
    public function syncFlightTimes($flightId = null) {
        try {
            $updatedCount = 0;
            $errors = [];
            
            if ($flightId) {
                // Sync specific flight
                $stmt = $this->db->prepare("SELECT * FROM flights WHERE id = ?");
                $stmt->execute([$flightId]);
                $flight = $stmt->fetch();
                
                if ($flight) {
                    $result = $this->updateFlightFromGoZayaan($flight);
                    if ($result['success']) {
                        $updatedCount++;
                    } else {
                        $errors[] = "Flight #{$flightId}: " . $result['error'];
                    }
                }
            } else {
                // Sync all upcoming flights
                $stmt = $this->db->query("
                    SELECT * FROM flights 
                    WHERE departure_time >= NOW() 
                    AND status = 'scheduled'
                    ORDER BY departure_time ASC
                    LIMIT 100
                ");
                $flights = $stmt->fetchAll();
                
                foreach ($flights as $flight) {
                    $result = $this->updateFlightFromGoZayaan($flight);
                    if ($result['success']) {
                        $updatedCount++;
                    } else {
                        $errors[] = "Flight #{$flight['id']} ({$flight['flight_number']}): " . $result['error'];
                    }
                    
                    // Rate limiting - wait 1 second between requests
                    sleep(1);
                }
            }
            
            // Log sync time
            $this->logSyncTime();
            
            return [
                'success' => true,
                'updated' => $updatedCount,
                'errors' => $errors
            ];
            
        } catch (Exception $e) {
            return [
                'success' => false,
                'error' => $e->getMessage()
            ];
        }
    }
    
    /**
     * Update a single flight from GoZayaan data
     */
    private function updateFlightFromGoZayaan($flight) {
        try {
            // Fetch latest data for this flight
            $gozayaanData = $this->fetchFlightData($flight['flight_number'], date('Y-m-d', strtotime($flight['departure_time'])));
            
            if (!$gozayaanData['success'] || empty($gozayaanData['data'])) {
                return ['success' => false, 'error' => 'No data received from GoZayaan'];
            }
            
            $flightData = is_array($gozayaanData['data']) && isset($gozayaanData['data'][0]) 
                ? $gozayaanData['data'][0] 
                : $gozayaanData['data'];
            
            // Extract and update flight times
            $newDepartureTime = $this->parseDateTime($flightData['departure_time'] ?? $flightData['departureTime'] ?? null);
            $newArrivalTime = $this->parseDateTime($flightData['arrival_time'] ?? $flightData['arrivalTime'] ?? null);
            $newStatus = $this->mapStatus($flightData['status'] ?? 'scheduled');
            
            if (!$newDepartureTime || !$newArrivalTime) {
                return ['success' => false, 'error' => 'Could not parse flight times'];
            }
            
            // Calculate new duration
            $duration = (strtotime($newArrivalTime) - strtotime($newDepartureTime)) / 60; // in minutes
            
            // Update flight in database
            $stmt = $this->db->prepare("
                UPDATE flights 
                SET departure_time = ?,
                    arrival_time = ?,
                    duration = ?,
                    status = ?,
                    updated_at = NOW()
                WHERE id = ?
            ");
            
            $stmt->execute([
                $newDepartureTime,
                $newArrivalTime,
                $duration,
                $newStatus,
                $flight['id']
            ]);
            
            // Log the update
            $this->logFlightUpdate($flight['id'], [
                'old_departure' => $flight['departure_time'],
                'new_departure' => $newDepartureTime,
                'old_arrival' => $flight['arrival_time'],
                'new_arrival' => $newArrivalTime,
                'old_status' => $flight['status'],
                'new_status' => $newStatus
            ]);
            
            return ['success' => true];
            
        } catch (Exception $e) {
            return ['success' => false, 'error' => $e->getMessage()];
        }
    }
    
    /**
     * Parse datetime string to MySQL format
     */
    private function parseDateTime($dateTimeString) {
        if (empty($dateTimeString)) {
            return null;
        }
        
        // Try various formats
        $formats = [
            'Y-m-d H:i:s',
            'Y-m-d\TH:i:s',
            'Y-m-d\TH:i:sP',
            'd/m/Y H:i',
            'm/d/Y H:i',
            'Y-m-d H:i'
        ];
        
        foreach ($formats as $format) {
            $date = DateTime::createFromFormat($format, $dateTimeString);
            if ($date !== false) {
                return $date->format('Y-m-d H:i:s');
            }
        }
        
        // Try strtotime as fallback
        $timestamp = strtotime($dateTimeString);
        if ($timestamp !== false) {
            return date('Y-m-d H:i:s', $timestamp);
        }
        
        return null;
    }
    
    /**
     * Map GoZayaan status to our status enum
     */
    private function mapStatus($status) {
        $statusMap = [
            'scheduled' => 'scheduled',
            'on-time' => 'scheduled',
            'delayed' => 'delayed',
            'cancelled' => 'cancelled',
            'canceled' => 'cancelled',
            'completed' => 'completed',
            'departed' => 'completed'
        ];
        
        $status = strtolower(trim($status));
        return $statusMap[$status] ?? 'scheduled';
    }
    
    /**
     * Log flight update
     */
    private function logFlightUpdate($flightId, $changes) {
        $stmt = $this->db->prepare("
            INSERT INTO flight_sync_logs (flight_id, changes, created_at)
            VALUES (?, ?, NOW())
        ");
        
        // Create sync_logs table if it doesn't exist
        $this->createSyncLogsTable();
        
        $stmt->execute([$flightId, json_encode($changes)]);
    }
    
    /**
     * Create sync logs table if it doesn't exist
     */
    private function createSyncLogsTable() {
        $this->db->exec("
            CREATE TABLE IF NOT EXISTS flight_sync_logs (
                id INT AUTO_INCREMENT PRIMARY KEY,
                flight_id INT UNSIGNED NOT NULL,
                changes TEXT,
                created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                INDEX idx_flight_id (flight_id),
                INDEX idx_created_at (created_at)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4
        ");
    }
    
    /**
     * Log sync time
     */
    private function logSyncTime() {
        $this->lastSyncTime = date('Y-m-d H:i:s');
        // Store in config or database
        $stmt = $this->db->prepare("
            INSERT INTO system_settings (setting_key, setting_value, updated_at)
            VALUES ('gozayaan_last_sync', ?, NOW())
            ON DUPLICATE KEY UPDATE setting_value = ?, updated_at = NOW()
        ");
        
        // Create system_settings table if it doesn't exist
        $this->db->exec("
            CREATE TABLE IF NOT EXISTS system_settings (
                id INT AUTO_INCREMENT PRIMARY KEY,
                setting_key VARCHAR(100) UNIQUE NOT NULL,
                setting_value TEXT,
                updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4
        ");
        
        $stmt->execute([$this->lastSyncTime, $this->lastSyncTime]);
    }
    
    /**
     * Get last sync time
     */
    public function getLastSyncTime() {
        $stmt = $this->db->prepare("SELECT setting_value FROM system_settings WHERE setting_key = 'gozayaan_last_sync'");
        $stmt->execute();
        $result = $stmt->fetch();
        return $result ? $result['setting_value'] : null;
    }
}

