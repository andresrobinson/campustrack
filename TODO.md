# Development Class – Future Features To-Do

## A. Discussed features still to implement

1. **Hide grading where not applicable** – Do not show grading UI for courses where grading is disabled (`has_grading` = 0).
2. **Per-student attendance view** – Dedicated view for a student’s attendance across lectures/classes.
3. **Course-level GPA aggregation** – When a student took multiple classes of the same course, use the highest grade for the course.

---

## B. Nice-to-have (not priority)

8. **Report exports** – CSV/Excel export; single-click PDF generation for reports.
9. **Lecture calendar view** – Calendar view of lectures (by class/course).
10. **Download logs UI enhancements** – Filters, search, export for file download history.
11. **Email/notification hooks** – Placeholders or hooks for future email/notifications (enrollment approved, etc.).
12. **UI/UX polish** – General improvements: loading states, better mobile layout, accessibility.
13. **Dark mode** – Theme toggle (light/dark) with preference persisted (e.g. cookie or user setting).
14. **Backup & export tools** – Simple admin interface or CLI commands to export/import core data (users, courses, classes, enrollments, attendance, grades) as SQL or CSV.
15. **Demo data seeder** – Script to populate demo courses, classes, students, enrollments, and attendance for quick testing or screenshots.
16. **Automated tests** – Add a small PHPUnit test suite for key business rules (attendance crediting, enrollment constraints, GPA formulas).
17. **Dev environment / CI** – Optional Docker/devcontainer setup plus a GitHub Actions workflow to run tests and basic checks on each push.
18. **Read-only JSON API** – Lightweight API endpoints (e.g. list courses/classes/attendance summaries) to allow external integrations or dashboards.
19. **Invite QR codes** – Generate and display QR codes for invite links to simplify registration and enrollment.

## C. Completed items (from this list)

- **Student self-registration & approval flow** – Students can register; manager approves before they can log in.
- **Student enrollment in public courses** – Students can request enrollment in courses marked as public; teacher/manager approves.
- **Multiple-class warning for same course** – Warn when a student is enrolled in more than one class of the same course at the same time.
- **Setup script / installer** – CLI script (`php setup.php`) to create the database (if needed), run migrations in order, and perform basic environment checks for a smoother first-time installation.
- **Per-user language preference** – Override global language per user (e.g. student sees PT-BR, teacher sees EN).
- **Password reset** – Implemented using `password_resets` table with development-only reset links (no SMTP required).
- **Security hardening (login lockout)** – Basic rate limiting / lockout on login via `failed_login_attempts` and `locked_until`.
- **Invite codes for registration and enrollment** – Invite code system for student registration (admin/manager) and class enrollment (teachers/managers/admins), with `auto_approve`, usage limits, and expiry.

---

*Last updated: March 2026*
