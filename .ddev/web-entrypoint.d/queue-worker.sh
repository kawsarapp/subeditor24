#!/usr/bin/env bash
mkdir -p /var/www/html/storage/logs
if [ -f /mnt/ddev_config/supervisor/laravel-worker.conf ]; then
    sudo cp /mnt/ddev_config/supervisor/laravel-worker.conf /etc/supervisor/conf.d/laravel-worker.conf
fi
