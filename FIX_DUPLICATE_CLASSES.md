# Fix Duplicate Classes on Production Server

## Issue
Instructor dashboard showing duplicate classes with incorrect booking counts.

## Changes Made

### 1. Updated InstructorDashboardController.php
Added deduplication logic that:
- Groups classes by name, date, and start time
- Combines bookings from duplicate entries
- Removes duplicate bookings by user

### 2. Created Cleanup Script
File: `database/cleanup_duplicate_classes.php`

## Steps to Fix on Production Server

### Option 1: Apply Code Changes Only (Recommended)
The code changes will handle duplicates automatically. Just deploy the updated `InstructorDashboardController.php` file.

```bash
# On production server
cd /path/to/made-hub
git pull
# Or upload the updated InstructorDashboardController.php file
```

### Option 2: Run Cleanup Script (If you want to fix the database)
This will permanently consolidate duplicate classes.

```bash
# On production server
cd /path/to/made-hub
php database/cleanup_duplicate_classes.php
```

**Important:** The cleanup script will:
- Find classes with identical name, date, time, and instructor
- Keep the first class (lowest ID)
- Move all bookings to the kept class
- Delete duplicate classes

### Option 3: Manual Database Fix
If you prefer to manually fix the database:

```sql
-- Find duplicate classes
SELECT name, class_date, start_time, instructor_id, COUNT(*) as count
FROM fitness_classes
WHERE parent_class_id IS NULL
GROUP BY name, class_date, start_time, instructor_id
HAVING count > 1;

-- For each duplicate group, identify the IDs and consolidate manually
```

## Verification

After applying the fix, the instructor dashboard should:
- Show each class occurrence only once
- Display correct booking counts
- Not show duplicate entries

## Testing

1. Log in as an instructor
2. Check the dashboard
3. Verify each class appears only once per date/time
4. Verify booking counts are correct
5. Click "View Bookings" to confirm all bookings are visible
