# Automatic Question Paper Generator (AQPG)

## 1. Introduction
The Automatic Question Paper Generator (AQPG) is a web-based application that streamlines the creation of academic examination papers. It provides an intuitive platform for educators to quickly generate structured question papers with balanced topics, difficulty levels, and formats. The system separates responsibilities for administrators and professors, enabling efficient management of users, subjects, codes, and question papers.

**Goals**
- Save time for educators
- Maintain consistency in exams
- Provide a structured, reusable question bank
- Enable easy generation and printing of question papers

**Security Notice:** Passwords are hashed with `password_hash()` (bcrypt). Always keep credentials secret and rotate in production.

**Quickstart:** See [QUICKSTART.md](QUICKSTART.md) for the fastest way to run AQPG locally with XAMPP.

## 2. Technology Stack
- **Backend:** PHP 8.2, MySQL 8.2
- **Frontend:** HTML5, CSS3, JavaScript (modern ES), Bootstrap 5
- **Environment:** XAMPP (Apache + MySQL), phpMyAdmin
- **Editor:** Visual Studio Code

**Project Access URLs**
- Main Site: `http://localhost/aqpg/`
- Admin Panel: `http://localhost/aqpg/admin/login.php`
- Professor Panel: `http://localhost/aqpg/users/login.php`

## 3. System Architecture
The application is organized into three main components:
1. **Public Interface:** Home, About, Contact, Reviews
2. **Admin Panel:** Manage professors, subjects, subject codes, and view generated papers
3. **Professor Panel:** Manage own subjects, subject codes, create question papers, add questions, generate final printable paper

## 4. Database Design
Database: `aqpg_db`

- **Admin**: Administrator credentials and login info
- **Registered Users**: Professor details, credentials, status
- **Subject List**: Subject names and descriptions
- **Subject Code**: Codes linked to subjects
- **Question Paper List**: Paper title, instructions, associated subject code
- **Question List**: Questions, marks, type (MCQ, Short, Long, etc.)
- **Choice List**: Options and correct answers for MCQs

## 5. Project Folder Structure
```
aqpg/
├── index.php
├── aqpg.sql
├── config/
├── admin/
├── users/
└── assets/
```
- **config/**: Database connection
- **admin/**: Administrator functionality
- **users/**: Professor functionality
- **assets/**: CSS, JS, images

## 6. Database Connection
- Centralized MySQLi connection file included in all pages
- UTF-8 encoding
- Handles connection errors

## 7. Public Home Page
Key sections: Navbar (dark theme), hero with CTA, about, team, reviews, contact (form, address, email, phone), footer. Designed for responsive display.

## 8. Admin Panel
- **Login:** Username + password with `password_verify`
- **Dashboard:** Totals for subjects, subject codes, question papers
- **User Management:** Add, edit, delete users
- **Subject Management:** Create, update, activate/deactivate subjects
- **Subject Code Management:** Assign and manage subject codes
- **Question Paper Viewing:** View papers created by professors

## 9. Professor Panel
- **Registration:** Full validation with password confirmation
- **Login:** Email-based authentication with session management
- **Dashboard:** Personalized statistics (subjects, codes, papers)

## 10. Subject Management (Professor)
Professors can add, edit, and delete subjects filtered by `pro_id = $_SESSION['pro_id']`.

## 11. Subject Code Management
Professors can create, link, update, and delete subject codes associated with their subjects.

## 12. Question Paper Management
Professors can create new papers, add instructions, edit or delete papers, and view detailed paper information.

## 13. Question Management (Core Feature)
- **Rich Text Editor:** Bold, italic, lists, images, tables
- **Question Types:** MCQ, Fill in the Blank, Short, Long
- **Dynamic Behavior:** MCQ shows choices; others hide choices
- **Choices System:** Add/remove options dynamically and select the correct answer

## 14. Question Paper Generation
Generated paper includes university header, subject details, instructions, and structured sections:
- MCQs
- Fill in the Blanks
- Short Questions
- Long Questions

Each section supports marks, course outcome (CO), and Bloom’s Taxonomy level.

## 15. Print Functionality
Uses `window.print()` for clean print layout; users can print directly or save as PDF.

## 16. Session Management
- Admin: `if (!isset($_SESSION['admin_id']))`
- Professor: `if (!isset($_SESSION['pro_id']))`

Ensures secure access and prevents unauthorized usage.

## 17. User Interface Design
- **Color Scheme:** Sidebar dark gray; buttons blue; success green; danger red
- **Components:** DataTables, Bootstrap modals, cards, badges
- **Responsive:** Bootstrap grid for multi-device support

## 18. Security Features
- Session-based authentication
- Password hashing with `password_hash`/`password_verify`
- Input validation and prepared statements

## 19. Installation Guide
1. Install XAMPP and start Apache & MySQL.
2. Copy this repository folder to `htdocs` as `aqpg/`.
3. Import `aqpg.sql` using phpMyAdmin (creates `aqpg_db` with tables and seed data).
4. Update `config/db.php` if your MySQL credentials differ from `root`/blank.
5. Visit `http://localhost/aqpg/` for the public page, `http://localhost/aqpg/admin/login.php` for admin, and `http://localhost/aqpg/users/login.php` for professors.

## 20. Default Credentials
- **Admin:** Username `admin`, Password `admin123`
- **Professor:** Email `professor@example.com`, Password `Avani@123`

## 21. Notes
- DataTables and Bootstrap 5 are loaded via CDN.
- Print view hides navigation and shows only paper content.
- Role-based access control
