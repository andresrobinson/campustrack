# Development Class – Future Features To-Do

## A. Discussed features still to implement

1. **Hide grading where not applicable** – Do not show grading UI for courses where grading is disabled (`has_grading` = 0).
2. **Per-student attendance view** – Dedicated view for a student’s attendance across lectures/classes.
3. **Course-level GPA aggregation** – When a student took multiple classes of the same course, use the highest grade for the course.
4. **Password reset** – Implement flow (code ready but not active due to lack of SMTP).

---

## B. Nice-to-have (not priority)

8. **Report exports** – CSV/Excel export; single-click PDF generation for reports.
9. **Lecture calendar view** – Calendar view of lectures (by class/course).
10. **Download logs UI enhancements** – Filters, search, export for file download history.
11. **Per-user language preference** – Override global language per user (e.g. student sees PT-BR, teacher sees EN).
12. **Bulk import tools** – Import users, enrollments, or other data from CSV/Excel.
13. **Email/notification hooks** – Placeholders or hooks for future email/notifications (enrollment approved, etc.).
14. **UI/UX polish** – General improvements: loading states, better mobile layout, accessibility.
15. **Dark mode** – Theme toggle (light/dark) with preference persisted (e.g. cookie or user setting).
16. **Invite codes & QR registration** – Invite code system (optionally scoped to course/class) with QR-code links to `/register`, per-code `auto_approve` flag (pending vs auto-active), and a global default in settings.
17. **Security hardening** – Basic rate limiting / lockout on login, session hardening (ID regeneration, secure cookie flags), and optional audit log for critical admin actions.
18. **Backup & export tools** – Simple admin interface or CLI commands to export/import core data (users, courses, classes, enrollments, attendance, grades) as SQL or CSV.
19. **Demo data seeder** – Script to populate demo courses, classes, students, enrollments, and attendance for quick testing or screenshots.
20. **Automated tests** – Add a small PHPUnit test suite for key business rules (attendance crediting, enrollment constraints, GPA formulas).
21. **Dev environment / CI** – Optional Docker/devcontainer setup plus a GitHub Actions workflow to run tests and basic checks on each push.
22. **Read-only JSON API** – Lightweight API endpoints (e.g. list courses/classes/attendance summaries) to allow external integrations or dashboards.

## C. Completed items (from this list)

- **Student self-registration & approval flow** – Students can register; manager approves before they can log in.
- **Student enrollment in public courses** – Students can request enrollment in courses marked as public; teacher/manager approves.
- **Multiple-class warning for same course** – Warn when a student is enrolled in more than one class of the same course at the same time.
- **Setup script / installer** – CLI script (`php setup.php`) to create the database (if needed), run migrations in order, and perform basic environment checks for a smoother first-time installation.

---

*Last updated: March 2026*
