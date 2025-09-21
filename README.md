# Vulnerable SQLi Demo

[![Docker](https://img.shields.io/badge/Docker-Required-blue.svg)](https://www.docker.com/products/docker-desktop/)
[![PHP](https://img.shields.io/badge/PHP-8.2-purple.svg)](https://php.net/)
[![MySQL](https://img.shields.io/badge/MySQL-8.0-orange.svg)](https://mysql.com/)
[![Security](https://img.shields.io/badge/Security-Demo%20Only-red.svg)](https://github.com)

> **⚠️ WARNING: This project is intentionally vulnerable to SQL injection and other security flaws. FOR DEMO/LAB USE ONLY. Never run on production or internet-facing systems.**

## Purpose
A deliberately vulnerable PHP+MySQL web app to demonstrate SQL injection (auth bypass, mass update, mass delete) and safe demo/run instructions. Includes phpMyAdmin, DB backup/restore scripts, and a simple UI for demo actions and logs.

## Safety Rules
- Only run in an isolated lab (local VM, isolated host, internal network).
- Use throwaway credentials and dummy data only.
- Always create a DB backup before any demo. Use provided scripts.
- **Never expose to the public internet.**
- This project contains insecure code for education. Treat accordingly.
- **For demo purposes, the login form uses a plain text field for 'Username or Email' to allow SQL injection payloads.**

## Quick Start

### Prerequisites
- [Docker Desktop](https://www.docker.com/products/docker-desktop/) (Windows/Mac/Linux)
- Git (for cloning)

### Installation
1. Clone this repository:
   ```bash
   git clone https://github.com/YOUR_USERNAME/vulnerable-sqli-demo.git
   cd vulnerable-sqli-demo
   ```

2. Build and run the application:
   ```bash
   docker-compose up --build
   ```

3. Access the application:
   - **Main App**: [http://localhost:8080](http://localhost:8080)
   - **phpMyAdmin**: [http://localhost:8081](http://localhost:8081) 
     - Username: `root`
     - Password: `rootpassword`

## Demo Steps
1. Login with `admin@example.com` / `adminpass` (normal flow).
2. Show `app/logs/requests.log` for normal login entry.
3. **Auth bypass:** In login form, use email: `' OR 1=1 -- ` or password: `' OR 1=1 -- ` → should log in as any user.
4. **Mass update:** In dashboard, set email: `user1@example.com' OR 1=1 -- `, new password: `pwned` → all users' passwords change.
5. **Mass delete:** In dashboard, set email: `user1@example.com' OR 1=1 -- ` in delete form → all users deleted.
6. Use backup/restore scripts to reset DB:
   ```sh
   ./scripts/backup_db.sh
   ./scripts/restore_db.sh
   ```

## Backup & Restore
- `scripts/backup_db.sh` — saves DB to `backup_demo.sql`
- `scripts/restore_db.sh` — restores DB from `backup_demo.sql`
- **Warning:** Restore will overwrite all data.

## How to Clean Up
- Stop containers: `docker-compose down`
- Remove volumes: `docker-compose down -v`

## How to Patch (Mitigation)
- Replace string-concatenated SQL with prepared statements (see below):
  ```php
  // BAD (vulnerable):
  $sql = "SELECT * FROM users WHERE email = '$email' AND password = '$password'";
  // GOOD (safe):
  $stmt = $mysqli->prepare('SELECT * FROM users WHERE email = ? AND password = ?');
  $stmt->bind_param('ss', $email, $password);
  $stmt->execute();
  ```
- Hash passwords (bcrypt).
- Use least-privilege DB accounts.

## File Structure
- `app/public/` — PHP app (index.php, login.php, dashboard.php, update.php, delete.php)
- `app/seed/init.sql` — DB schema and seed users
- `app/logs/requests.log` — request log
- `scripts/backup_db.sh`, `scripts/restore_db.sh` — backup/restore scripts
- `docker-compose.yml` — orchestration

## Final Note
**This project is for educational demo only. All vulnerabilities are intentional.**

