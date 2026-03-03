## School Attendance System (PHP / MariaDB / Bootstrap)

This is a small school **attendance and grading system** built with **plain PHP 8.2**, **MariaDB/MySQL**, and **Bootstrap 5**, designed to run under WAMP/XAMPP and be accessed via `http://localhost/...`.

It supports **admins, managers, teachers, and students**, with features for **courses, classes, lectures, enrollments, attendance, grading, reports, and study materials**.

---

### Main features (current)

- **Authentication & roles**
  - Email + password login (`admin`, `manager`, `teacher`, `student`).
  - Student self-registration with **manager/admin approval**.
  - Session-based auth, role checks for pages and actions.

- **Courses, classes, lectures**
  - Courses with attendance mode (simple/detailed), default lecture duration, and public flag.
  - Classes per course, multiple teachers per class, simple recurring lecture generation.
  - Lectures with date, time, duration, location/room, and “extra” flag.

- **Enrollments & attendance**
  - Enroll students in classes; approval flow (teacher/manager/admin).
  - Attendance per lecture with simple or detailed modes (Present/Late/Excused/Absent).
  - Credited minutes/hours per student and per class.

- **Grading**
  - Optional grading per course.
  - Per-class assessments (tests) and per-student scores.
  - Configurable GPA formula (average or weighted first/second test).

- **Reports**
  - Per-class and per-student attendance reports (print-friendly).
  - Attendance matrix (students x classes) with credited hours.
  - “My grades” page for students, and per-student reports for staff.

- **Files library**
  - Course and class materials uploaded by teachers/managers/admins.
  - Availability windows (`available_from` / `available_until`).
  - Secure downloads via PHP controller with download logging.

- **Internationalization (i18n)**
  - English (`lang/en.php`) and Brazilian Portuguese (`lang/pt_BR.php`).
  - Global default language via settings.

---

### Requirements

- **PHP** 8.2+
- **MariaDB / MySQL**
- WAMP/XAMPP or similar local stack

---

### Installation & setup

1. **Clone / copy the project** into your web root, e.g.:
   - `c:\wamp64\www\development-class`

2. **Create the database** (name is configurable):
   - Example:
     - `CREATE DATABASE development_class CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;`

3. **Configure database connection**
   - Edit `config/database.php` and adjust:
     - `db_host`, `db_name`, `db_user`, `db_pass`.

4. **Run migrations**
   - Import the SQL files under `migrations/` into your database (in order: `001_...`, `002_...`, `003_...` etc.) using your preferred tool (phpMyAdmin, MySQL CLI, etc.).

5. **Access the app**
   - In a browser:
     - `http://localhost/development-class/index.php?route=login`

6. **Initial admin login**
   - The initial admin user is created by the first migration.
   - Default credentials (from migration):
     - Email: `admin@example.com`
     - Password: `Admin123!`

---

### Development notes

- Custom mini-framework with:
  - Front controller (`index.php`), simple router (`core/Router.php`), and helpers (`core/helpers.php`).
  - PDO-based DB access (`core/Database.php`).
  - Auth helper (`core/Auth.php`) and simple translation helper (`__()`).
- Views use plain PHP with Bootstrap 5 from CDN.

---

### Roadmap / future features

High-level ideas are tracked in `TODO.md`. Highlights include:

- Student self-service enrollment in public courses.
- Per-student attendance overview pages.
- Password reset (no SMTP dependency).
- Dark mode, report exports, calendar views.
- Invite-code + QR-based registration options.

---

### Contributing / keeping docs up to date

- When you add or change features:
  - **Update this `README.md`** with any new capabilities or setup steps.
  - **Update `TODO.md`** with new ideas or completed items.
  - Add/adjust translation keys in `lang/en.php` and `lang/pt_BR.php` when you change UI text.

When this is published on GitHub, this `README.md` will serve as the main project description and quickstart guide.

# School Attendance System

PHP 8.2 + MySQL/MariaDB + Bootstrap 5 application for managing classes and attendance.

## Setup

1. **Create database** (e.g. in phpMyAdmin or MySQL):

   ```sql
   CREATE DATABASE development_class CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
   ```

2. **Configure connection** in `config/database.php`:

   - `db_name`: your database name
   - `db_user` / `db_pass`: your MySQL user and password

3. **Run migrations** (in order):

   - `migrations/001_initial_schema.sql`
   - If you see "Unknown column 'name'" when inserting the admin user, run `migrations/002_fix_users_add_name.sql` then run the admin INSERT from `001_initial_schema.sql` again.

4. **Open in browser**:

   - `http://localhost/development-class/` or  
   - `http://localhost/development-class/index.php`

   Default admin: **admin@example.com** / **Admin123!**

## Structure

- `config/` – app and database config
- `core/` – Database, Auth, Router, helpers
- `app/Controllers/` – request handlers
- `views/` – PHP templates
- `lang/` – en.php, pt_BR.php for i18n
- `migrations/` – SQL schema and fixes

## URL routing

Links use query-based routing: `index.php?route=login`. No mod_rewrite required.
