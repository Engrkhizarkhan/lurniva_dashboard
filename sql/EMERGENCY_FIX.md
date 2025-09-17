# Quick Fix for Database Migration

## Immediate Solution for Multiple Primary Key Errors

The database has a systematic issue where many tables are created without PRIMARY KEY definitions but later try to add them with ALTER TABLE statements. This causes "Multiple primary key defined" errors.

## Recommended Quick Fix

Instead of applying the full production SQL, use this safer approach:

### Option 1: Use Safe Migration Script
```sql
-- Add this at the beginning of your SQL file to prevent conflicts:

-- Drop tables if they exist to start fresh
SET FOREIGN_KEY_CHECKS = 0;

DROP TABLE IF EXISTS `activity_logs`;
DROP TABLE IF EXISTS `app_admin`;
DROP TABLE IF EXISTS `class_fee_types`;
DROP TABLE IF EXISTS `class_timetable_details`;
-- Add other problematic tables here

SET FOREIGN_KEY_CHECKS = 1;

-- Then run your CREATE TABLE statements
-- This ensures no conflicts with existing PRIMARY KEYS
```

### Option 2: Alternative Deployment Command
Instead of importing the full SQL file, use selective import:

```bash
# Create a clean database
mysql -u lurnivauser -p'lurniva@testVM' -e "DROP DATABASE IF EXISTS lurnivaDB; CREATE DATABASE lurnivaDB;"

# Import the SQL file
mysql -u lurnivauser -p'lurniva@testVM' lurnivaDB < sql/lurnivaDB_production_ready.sql
```

### Option 3: Manual Fix (Immediate)
Run this before your deployment:

```sql
-- Connect to database
mysql -u lurnivauser -p'lurniva@testVM' lurnivaDB

-- Remove problematic constraints if they exist
-- This will prevent the multiple primary key errors
SET FOREIGN_KEY_CHECKS = 0;

-- Check what tables exist and drop conflicting ones if needed
SHOW TABLES;

-- Drop any tables that are causing conflicts
DROP TABLE IF EXISTS activity_logs;
DROP TABLE IF EXISTS app_admin;
DROP TABLE IF EXISTS class_fee_types;
DROP TABLE IF EXISTS class_timetable_details;

SET FOREIGN_KEY_CHECKS = 1;
```

Then run your normal deployment command.

## Long-term Solution

The SQL files need structural fixes to move all PRIMARY KEY definitions to CREATE TABLE statements. This would require:

1. Moving PRIMARY KEY from ALTER TABLE to CREATE TABLE for ~30+ tables
2. Moving AUTO_INCREMENT from ALTER TABLE to CREATE TABLE  
3. Moving indexes from ALTER TABLE to CREATE TABLE where appropriate
4. Testing the complete migration process

## Emergency Deployment

If you need to deploy immediately:

```bash
# 1. Backup current database
mysqldump -u lurnivauser -p'lurniva@testVM' lurnivaDB > backup_emergency_$(date +%Y%m%d_%H%M%S).sql

# 2. Drop and recreate database (CAUTION: THIS REMOVES ALL DATA)
mysql -u lurnivauser -p'lurniva@testVM' -e "DROP DATABASE lurnivaDB; CREATE DATABASE lurnivaDB;"

# 3. Import fresh schema
mysql -u lurnivauser -p'lurniva@testVM' lurnivaDB < sql/lurnivaDB_production_ready.sql
```

---
*Created: September 18, 2025*
