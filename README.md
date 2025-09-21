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
- PowerPoint or compatible viewer (for presentation)

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

### 📚 **Educational Materials**
- **PowerPoint Presentation**: `Adam SQLi Preso.pptx` - Comprehensive slides covering SQL injection concepts, impact analysis, and mitigation strategies
- **Interactive Demo**: Live vulnerable application for hands-on learning
- **Documentation**: Detailed README with step-by-step instructions

## 🚨 **Critical SQL Injection Impact Analysis**

### **Why `' OR 1=1 -- -` Has Different Impact Levels**

This lab demonstrates a crucial security concept: **the same SQL injection payload has vastly different impacts depending on the SQL operation type**.

#### **1. SELECT Statements (Login Bypass) - Limited Impact**
```sql
-- Original query:
SELECT * FROM users WHERE email = 'user@example.com' AND password = 'password'

-- With payload ' OR 1=1 -- -:
SELECT * FROM users WHERE email = '' OR 1=1 -- -' AND password = 'password'
```

**Impact:** Authentication bypass only
- ✅ **Read-only operation** - no data modification
- ✅ **Limited damage** - just gains unauthorized access
- ✅ **Recoverable** - can be fixed by proper authentication

#### **2. UPDATE Statements (Mass Data Modification) - Severe Impact**
```sql
-- Original query:
UPDATE users SET password = 'newpass' WHERE email = 'user@example.com'

-- With payload ' OR 1=1 -- -:
UPDATE users SET password = 'newpass' WHERE email = '' OR 1=1 -- -'
```

**Impact:** Mass data corruption
- ❌ **Modifies ALL records** - every user's password changed
- ❌ **Data integrity loss** - system becomes unusable
- ❌ **Service disruption** - all users locked out
- ❌ **Recovery required** - database restore needed

#### **3. DELETE Statements (Data Destruction) - Catastrophic Impact**
```sql
-- Original query:
DELETE FROM users WHERE email = 'user@example.com'

-- With payload ' OR 1=1 -- -:
DELETE FROM users WHERE email = '' OR 1=1 -- -'
```

**Impact:** Complete data loss
- ❌ **Deletes ALL records** - entire user table wiped
- ❌ **Business continuity loss** - application becomes unusable
- ❌ **Data recovery required** - full database restore needed
- ❌ **Potential legal/compliance issues** - data loss incidents

### **🎯 Key Learning Points**

1. **Same Payload, Different Consequences**: The `' OR 1=1 -- -` payload that seems "harmless" in login bypass becomes catastrophic in UPDATE/DELETE operations.

2. **Operation Context Matters**: Always consider what SQL operation is being performed when assessing injection risk.

3. **Defense in Depth**: Even if login bypass seems "limited," it provides the foothold needed to access destructive operations.

4. **Real-World Impact**: In production systems, UPDATE/DELETE injections can cause:
   - Complete service outages
   - Data loss incidents
   - Compliance violations (GDPR, SOX, PCI-DSS)
   - Legal liability
   - Business reputation damage

## Demo Steps

### **🎯 Learning Objective: Demonstrate SQL Injection Impact Escalation**

This demo shows how the same payload (`' OR 1=1 -- -`) escalates from "harmless" login bypass to catastrophic data destruction.

### **📊 Recommended Demo Flow**
1. **Start with Presentation**: Open `Adam SQLi Preso.pptx` to introduce concepts
2. **Interactive Demo**: Follow the steps below for hands-on experience
3. **Discussion**: Use presentation slides to reinforce key learning points

#### **Step 1: Baseline - Normal Login**
1. Login with `admin@example.com` / `adminpass` (normal flow)
2. Show `app/logs/requests.log` for normal login entry
3. **Key Point**: Demonstrate legitimate access to admin functions

#### **Step 2: Limited Impact - Authentication Bypass**
3. **Auth bypass:** In login form, use email: `' OR 1=1 -- ` or password: `' OR 1=1 -- ` 
4. **Result**: Logs in as admin user
5. **Key Point**: Same payload, but SELECT operation = limited damage (read-only)

#### **Step 3: Severe Impact - Mass Data Corruption**
6. **Mass update:** In dashboard, set email: `user1@example.com' OR 1=1 -- `, new password: `pwned`
7. **Result**: ALL users' passwords changed to "pwned"
8. **Key Point**: Same payload, but UPDATE operation = severe damage (data corruption)
9. **Demonstrate**: Try logging in with any user - all passwords are now "pwned"

#### **Step 4: Catastrophic Impact - Complete Data Loss**
10. **Mass delete:** In dashboard, set email: `user1@example.com' OR 1=1 -- ` in delete form
11. **Result**: ALL users deleted from database
12. **Key Point**: Same payload, but DELETE operation = catastrophic damage (data destruction)
13. **Demonstrate**: Application becomes unusable - no users exist

#### **Step 5: Recovery Demonstration**
14. **Reset database:** Use "Reset DB" button to restore original data
15. **Key Point**: Shows why database backups are critical for SQL injection recovery

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

