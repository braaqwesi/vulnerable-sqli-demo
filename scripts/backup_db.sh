#!/bin/bash
# DEMO ONLY: Backup script for vulnerable-sqli-demo
set -e
CONTAINER=$(docker-compose ps -q mysql)
docker exec $CONTAINER mysqldump -u root -prootpassword demo > backup_demo.sql
echo "Backup complete: backup_demo.sql"

