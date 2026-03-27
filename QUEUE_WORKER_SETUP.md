# Queue Worker Setup Guide

The queue worker is CRITICAL for FCM push notifications to work. Without it, lock/unlock commands won't be sent to devices.

## Option 1: Using Screen (Quick Test)

```bash
# Start screen session
screen -S queue

# Start queue worker
php artisan queue:work

# Detach from screen (keep it running)
# Press: Ctrl+A then D

# To reattach later
screen -r queue

# To kill the worker
screen -r queue
# Then press Ctrl+C
```

## Option 2: Using Supervisor (Production - RECOMMENDED)

### Install Supervisor

```bash
sudo yum install supervisor
sudo systemctl enable supervisord
sudo systemctl start supervisord
```

### Configure Laravel Worker

```bash
# Copy the config file
sudo cp laravel-worker.ini /etc/supervisord.d/laravel-worker.ini

# Update supervisor
sudo supervisorctl reread
sudo supervisorctl update

# Start the worker
sudo supervisorctl start laravel-worker:*

# Check status
sudo supervisorctl status
```

### Supervisor Commands

```bash
# Start worker
sudo supervisorctl start laravel-worker:*

# Stop worker
sudo supervisorctl stop laravel-worker:*

# Restart worker (after code updates)
sudo supervisorctl restart laravel-worker:*

# View logs
tail -f storage/logs/worker.log
```

## Option 3: Using Cron (Fallback)

Add to crontab:
```bash
crontab -e
```

Add this line:
```
* * * * * cd /home/ahtisham/emi.chatvoo.com && php artisan schedule:run >> /dev/null 2>&1
```

## Verify Queue Worker is Running

```bash
# Check if process is running
ps aux | grep "queue:work"

# Check queue status
php artisan queue:work --once

# Check failed jobs
php artisan queue:failed

# Retry failed jobs
php artisan queue:retry all
```

## Troubleshooting

### Worker not processing jobs
```bash
# Clear cache
php artisan cache:clear
php artisan config:clear

# Restart worker
sudo supervisorctl restart laravel-worker:*
```

### Check logs
```bash
# Worker logs
tail -f storage/logs/worker.log

# Laravel logs
tail -f storage/logs/laravel.log

# Failed jobs
php artisan queue:failed
```

### Test FCM manually
```bash
php test-fcm.php
```

## Important Notes

- Queue worker MUST be running for FCM to work
- Restart worker after code updates
- Monitor worker logs regularly
- Use supervisor in production (auto-restart on failure)
- Screen is only for testing/development

---

**Current Status**: Queue worker needs to be started on server
**Action Required**: Run supervisor setup commands above
