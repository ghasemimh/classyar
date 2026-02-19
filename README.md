# Classyar (Overview)

## What This Project Is
Classyar is a custom PHP web application for school/class management integrated with Moodle.
Main capabilities include categories, rooms, courses, teachers, terms, scheduling/program, and enrollment management.

## Tech Structure
- Entry point: `index.php`
- Core routing: `app/core/router.php`, `app/core/routes.php`
- App layers:
  - Controllers: `app/controllers`
  - Models: `app/models`
  - Views: `app/views`
- Services:
  - Moodle API integration: `app/services/moodleAPI.php`
  - Update utility: `app/services/updater.php`
- Database schema/data: `data/*.sql`

## Auth & Session Flow
- User authentication is validated against Moodle session (`MoodleSession`).
- After Moodle validation, local app session (`classyar`) is built/refreshed.
- If a Moodle user is missing in local DB, the app creates a local student record and continues.

## Main Modules (Routes)
- Category management
- Room management
- Course management
- Teacher management
- Term management
- Program/Scheduling
- Enrollment (student/admin modes)
- Settings

## Local Setup Notes
1. Use XAMPP/PHP + MySQL/MariaDB.
2. Import `data/classyar_empty.sql` (and optional seed data if needed).
3. Verify DB and path settings in `app/config.php`.
4. Ensure Moodle URL/token/session path values are valid in `app/config.php`.
5. Open app root via configured URL (e.g. `/moodle/app/classyar`).

## Current Priorities
See `tasks.md` for confirmed, later, and design-phase tasks.




## installation
1. نصب مودل
2. ریختن پروژه در مسیر  /app
3. ساخت توکن برای برنامه
4. ایجاد دیتابیس
5. تکمیل کانفیگ برنامه