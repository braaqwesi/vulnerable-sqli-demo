#!/bin/bash
# DEMO ONLY: Restore script for vulnerable-sqli-demo
set -e
CONTAINER=$(docker-compose ps -q mysql)
docker exec -i $CONTAINER mysql -u root -prootpassword demo < backup_demo.sql
echo "Restore complete from backup_demo.sql"

