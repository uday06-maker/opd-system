# Hospital OPD Queue Management System

A PHP + MySQL web application to manage hospital outpatient queues.

## Tech Stack
PHP 8.2 | MySQL | Bootstrap 5 | Chart.js | XAMPP

## Features
- Online token booking by department + doctor
- Live queue display with auto-refresh
- Doctor dashboard — call, done, cancel tokens
- Admin panel — manage doctors, departments
- Reports & analytics with 7-day chart
- Role-based access: Admin / Doctor / Patient
- Security: PDO prepared statements, password hashing,
  session regeneration, XSS protection, form validation

## Setup
1. Clone into `htdocs/opd-system`
2. Start Apache + MySQL in XAMPP
3. Import `opd_system.sql` in phpMyAdmin
4. Visit `http://localhost/opd-system`

## Test Credentials
| Role    | Email                  | Password  |
|---------|------------------------|-----------|
| Admin   | admin@hospital.com     | admin123  |
| Doctor  | drramesh@hospital.com  | doctor123 |
| Patient | register on site       | —         |
