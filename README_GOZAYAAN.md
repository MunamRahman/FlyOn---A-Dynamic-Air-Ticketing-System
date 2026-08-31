# GoZayaan Integration Guide

This document explains how to set up and use the GoZayaan integration for automatic flight time updates.

## Overview

The GoZayaan integration allows FlyOn to automatically sync flight departure and arrival times from GoZayaan.com, ensuring your flight data is always up-to-date.

## Features

- ✅ Automatic flight time synchronization
- ✅ Support for API and web scraping methods
- ✅ Manual sync option for individual flights
- ✅ Sync logs for tracking changes
- ✅ Scheduled automatic updates via cron job
- ✅ Admin interface for managing sync operations

## Setup Instructions

### 1. Database Setup

Run the SQL script to create necessary tables:

```sql
-- Import the SQL file
mysql -u root -p flyon_db < database/add_gozayaan_tables.sql
```

Or import it via phpMyAdmin.

### 2. Configuration

Add GoZayaan settings to your `.env` file:

```env
# GoZayaan Integration
GOZAYAAN_API_KEY=your_api_key_here
GOZAYAAN_API_URL=https://gozayaan.com/api/v1
GOZAYAAN_SYNC_ENABLED=true
GOZAYAAN_SYNC_INTERVAL=3600
```

**Note:** If GoZayaan doesn't provide an API key, the system will automatically fall back to web scraping. However, you should contact GoZayaan at [email protected] or +88 09678 332211 to request API access.

### 3. API vs Web Scraping

#### API Method (Recommended)
- More reliable and faster
- Requires API credentials from GoZayaan
- Set `GOZAYAAN_API_KEY` in your `.env` file

#### Web Scraping Method (Fallback)
- Works without API access
- May break if GoZayaan changes their website structure
- Slower and less reliable
- Requires periodic maintenance

## Usage

### Manual Sync via Admin Panel

1. Log in to the admin panel
2. Navigate to **Admin Dashboard** → **Sync GoZayaan**
3. Click **"Sync All Upcoming Flights"** to sync all flights
4. Or click **"Sync"** next to individual flights for targeted updates

### Automatic Sync via Cron Job

Set up a cron job to automatically sync flights at regular intervals.

#### Linux/Mac (crontab)

```bash
# Edit crontab
crontab -e

# Add this line to sync every hour
0 * * * * /usr/bin/php /path/to/FlyOn/cron/sync_gozayaan.php >> /path/to/FlyOn/cron/sync_gozayaan.log 2>&1

# Or sync every 30 minutes
*/30 * * * * /usr/bin/php /path/to/FlyOn/cron/sync_gozayaan.php >> /path/to/FlyOn/cron/sync_gozayaan.log 2>&1
```

#### Windows (Task Scheduler)

1. Open Task Scheduler
2. Create a new task
3. Set trigger: "Daily" or "At startup"
4. Set action: Start a program
   - Program: `C:\xampp\php\php.exe`
   - Arguments: `C:\xampp\htdocs\FlyOn\cron\sync_gozayaan.php`
   - Start in: `C:\xampp\htdocs\FlyOn`

#### XAMPP (Windows - Manual Setup)

Create a batch file `sync_gozayaan.bat`:

```batch
@echo off
cd C:\xampp\htdocs\FlyOn
C:\xampp\php\php.exe cron\sync_gozayaan.php
```

Then schedule it using Windows Task Scheduler.

## How It Works

1. **Fetch Data**: The system connects to GoZayaan (via API or web scraping)
2. **Compare Times**: Compares current flight times with GoZayaan data
3. **Update Database**: Updates departure/arrival times if changes are detected
4. **Log Changes**: Records all updates in `flight_sync_logs` table
5. **Notify Users**: (Optional) Can send notifications to affected users

## Sync Logs

All sync operations are logged in the `flight_sync_logs` table, showing:
- Which flights were updated
- What changed (old vs new times)
- When the sync occurred

View logs in the admin panel under **Sync GoZayaan** → **Recent Sync Logs**.

## Troubleshooting

### Sync Not Working

1. **Check API Key**: Verify `GOZAYAAN_API_KEY` is set correctly in `.env`
2. **Check Logs**: Review `cron/sync_gozayaan.log` for error messages
3. **Test Connection**: Try manual sync from admin panel
4. **Check Permissions**: Ensure PHP can write to log files

### Web Scraping Issues

If using web scraping and it stops working:
1. GoZayaan may have changed their website structure
2. Update the `parseFlightHTML()` method in `includes/GoZayaanIntegration.php`
3. Inspect GoZayaan's HTML to find new selectors
4. Contact GoZayaan for API access (recommended)

### Rate Limiting

The system includes a 1-second delay between requests to avoid overwhelming GoZayaan's servers. If you encounter rate limiting:
- Increase the delay in `syncFlightTimes()` method
- Reduce the number of flights synced per run
- Contact GoZayaan for API access

## Security Considerations

- Never commit `.env` file with API keys
- Use HTTPS for API connections
- Validate all data from external sources
- Implement proper error handling
- Monitor sync logs for suspicious activity

## Support

For issues or questions:
- Check the sync logs in admin panel
- Review `cron/sync_gozayaan.log` file
- Contact GoZayaan support: [email protected] or +88 09678 332211

## Future Enhancements

- Real-time flight status updates
- Automatic user notifications for delays/cancellations
- Integration with multiple flight data providers
- Advanced scheduling and filtering options

