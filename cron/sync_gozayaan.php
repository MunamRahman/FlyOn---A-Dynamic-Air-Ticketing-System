<?php
/**
 * Cron Job: Auto-sync Flights from GoZayaan
 * Run this script via cron job to automatically update flight times
 * 
 * Example cron job (runs every hour):
 * 0 * * * * /usr/bin/php /path/to/FlyOn/cron/sync_gozayaan.php
 * 
 * For Windows Task Scheduler:
 * php.exe C:\xampp\htdocs\FlyOn\cron\sync_gozayaan.php
 */

// Set execution time limit
set_time_limit(300); // 5 minutes

// Change to script directory
chdir(__DIR__ . '/..');

require_once 'config.php';
require_once 'includes/GoZayaanIntegration.php';

// Log file
$logFile = __DIR__ . '/sync_gozayaan.log';

function logMessage($message) {
    global $logFile;
    $timestamp = date('Y-m-d H:i:s');
    $logMessage = "[$timestamp] $message\n";
    file_put_contents($logFile, $logMessage, FILE_APPEND);
    echo $logMessage;
}

logMessage("Starting GoZayaan sync job...");

try {
    $gozayaan = new GoZayaanIntegration();
    
    // Sync all upcoming flights
    $result = $gozayaan->syncFlightTimes();
    
    if ($result['success']) {
        logMessage("Sync completed successfully. Updated {$result['updated']} flights.");
        
        if (!empty($result['errors'])) {
            logMessage("Errors encountered: " . count($result['errors']));
            foreach ($result['errors'] as $error) {
                logMessage("  - $error");
            }
        }
    } else {
        logMessage("Sync failed: " . $result['error']);
        exit(1);
    }
    
} catch (Exception $e) {
    logMessage("Fatal error: " . $e->getMessage());
    exit(1);
}

logMessage("GoZayaan sync job completed.");

