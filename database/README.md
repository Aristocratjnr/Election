# Election Management System Database

This directory contains SQL files for the Election Management System database.

## Database Structure

The main database structure is defined in `ems.sql`, which includes tables for:
- `admins` - Administrative users
- `students` - Student users who can vote
- `elections` - Election events
- `candidates` - Students running for positions
- `votes` - Voting records
- And more...

## Additional Tables

- `admin_activity_log.sql` - Added manually to track admin activities
  - Tracks login events, security changes, and other administrative actions
  - Foreign key constraint to the `admins` table
  - Required by security.php, profile.php, and activity.php

## Troubleshooting

If you encounter database-related errors, check:
1. Whether all required tables exist
2. Column names match between code and database (especially watch for `adminID` vs `admin_id`)
3. Foreign key constraints are properly set up

## PHP Version Compatibility

Recent changes include:
- Replaced deprecated `FILTER_SANITIZE_STRING` with `FILTER_SANITIZE_FULL_SPECIAL_CHARS` to support PHP 8.1+
- If you encounter deprecation warnings, check for outdated PHP functions and filters

## Maintenance

To update the database structure:
1. Add new SQL files in this directory
2. Import them using phpMyAdmin or MySQL command line:
   ```
   C:\xampp\mysql\bin\mysql -u root ems < database/your_sql_file.sql
   ``` 