# Sanjivani University Placement & Alumni Tracking Portal, Kopargaon

A comprehensive full-stack EdTech web application for **Sanjivani University, Kopargaon** (Maharashtra) to manage campus placement drives, student recruitment applications, placement analytics, alumni directory, and 1-on-1 student-alumni mentorship.

---

## Technology Stack

- **Frontend**: HTML5, CSS3, JavaScript ES6, AngularJS 1.8.2, Bootstrap 5.3, Bootstrap Icons, Chart.js 4.4.
- **Backend**: PHP 8.1 RESTful JSON API using PDO with prepared statements, object-oriented database connection singleton, and session authentication.
- **Database**: MySQL / MariaDB (`placement_alumni_portal`) with relational constraints, foreign keys, and indexes.
- **Data Exchange**: JSON responses for REST APIs, with XML and CSV export engines for reports.
- **Architecture**: Responsive layout supporting Desktop, Tablet, and Mobile with role-based dashboard navigation.

---

## User Roles & Demo Login Credentials

All accounts are pre-seeded in the database and ready for immediate testing:

| Role | Name | Email | Password | Access Highlights |
| :--- | :--- | :--- | :--- | :--- |
| **Admin** | System Administrator | `admin@sanjivani.edu.in` | `Admin@123` | Full system CRUD, user accounts, security toggles |
| **Placement Officer** | Dr. Vinod P. Shinde (TPO) | `tpo@sanjivani.edu.in` | `Officer@123` | Create drives, review candidates, update statuses, reports |
| **Student** | Rohan Kulkarni | `rohan.kulkarni@sanjivani.edu.in` | `Student@123` | Eligible drives, 1-click apply, resume upload, track status |
| **Alumni Mentor** | Priya Nair (Sr. SDE @ Google) | `priya.nair@sanjivani.edu.in` | `Alumni@123` | Career profile, respond to mentorship requests |

> **Quick Demo Login:** On the Home and Login pages, convenient 1-click demo buttons are provided to instantly sign in as any of the 4 roles without typing credentials.

---

## Key Modules & Features

1. **Authentication & Security**
   - Role-based login (Student, Alumni, Placement Officer, Admin)
   - BCRYPT password hashing and session management
   - Student and Alumni registration with dynamic role-specific profile fields
   - Prepared statements for 100% SQL injection prevention

2. **Student Dashboard & Application Pipeline**
   - Real-time profile completion meter
   - Automatic drive eligibility checking (compares student CGPA and branch vs drive cutoffs)
   - Application status tracker with badges: `Applied`, `Shortlisted`, `Interview`, `Selected`, `Rejected`
   - Resume upload and storage (PDF/DOCX validation, 5MB limit)
   - Mentorship request initiation to alumni

3. **Drive Management**
   - Create, edit, list, and delete placement drives
   - Detailed parameters: Company, role, package (LPA), drive date, deadline, minimum CGPA cutoff, eligible engineering branches, required skills, and vacancies

4. **Alumni Directory & Mentorship System**
   - Searchable and filterable alumni network (by branch, graduation year, company, skills)
   - Direct mentorship request workflow with custom notes
   - Alumni request management: Accept, Reject, Mark Completed with feedback remarks

5. **Placement Statistics & Analytics**
   - Interactive KPI counters: Total students, placed students, placement percentage, highest/average/lowest CTC
   - 5 Dynamic Chart.js visualizations:
     1. Placement trends by year (with average package trendline)
     2. Placement distribution by engineering branch
     3. Company-wise placement volume
     4. Salary package bracket distribution (<6 LPA, 6-10 LPA, 10-15 LPA, 15-20 LPA, >20 LPA)
     5. Placed vs Unplaced student ratio (Donut chart)

6. **Placement Officer & Admin Consoles**
   - Application management with status modal and feedback notes
   - Automatic placement recording when a student is marked `Selected`
   - CRUD management for students, alumni, companies, drives, and user accounts
   - Multi-format report exports in CSV, JSON, and XML

---

## Project Structure

```
placement-alumni-portal/
├── api/
│   ├── auth/
│   │   ├── login.php              # Login & session creator
│   │   ├── register.php           # User registration
│   │   ├── logout.php             # Session terminator
│   │   ├── me.php                 # Current session profile
│   │   └── users.php              # Admin user management
│   ├── students/
│   │   ├── index.php              # Students CRUD
│   │   ├── profile.php            # Student profile get & update
│   │   └── upload_resume.php      # Resume file upload handler
│   ├── alumni/
│   │   ├── index.php              # Directory & Admin CRUD
│   │   └── profile.php            # Alumni profile update
│   ├── companies/
│   │   └── index.php              # Companies CRUD
│   ├── drives/
│   │   ├── index.php              # Drives CRUD & student eligibility filter
│   │   └── eligibility.php        # Detailed eligibility breakdown
│   ├── applications/
│   │   ├── index.php              # Applications list
│   │   ├── apply.php              # Drive application submission
│   │   └── status.php             # Status update & placement trigger
│   ├── placements/
│   │   └── index.php              # Placement offers CRUD
│   ├── mentorship/
│   │   ├── index.php              # Mentorship requests list
│   │   ├── request.php            # Mentorship request submit
│   │   └── status.php             # Mentorship response
│   ├── statistics/
│   │   └── index.php              # Analytics & chart aggregator
│   └── reports/
│       └── export.php             # CSV, JSON, and XML export engine
├── config/
│   ├── config.php                 # Application constants
│   ├── database.php               # PDO singleton
│   └── auth.php                   # Authentication & response helpers
├── database/
│   ├── schema.sql                 # Table schema definitions
│   └── sample_data.sql            # Seed data
├── uploads/
│   └── resumes/                   # Stored resume documents
├── assets/
│   ├── css/
│   │   ├── style.css              # Main portal stylesheet
│   │   └── dashboard.css          # Dashboard layout & sidebar CSS
│   └── js/
│       ├── app.js                 # Central application controller
│       └── charts.js              # Chart.js renderers
├── tests/
│   └── api_test.php               # Automated test suite
├── database.sql                   # Consolidated SQL file
├── install.php                    # Automated web/CLI database installer
├── run_server.bat                 # Helper to run MySQL and web server
├── index.html                     # Main portal single-page application
└── README.md                      # Documentation
```

---

## Database Files & Import Instructions

The zip archive includes complete, ready-to-import database files:
- `placement_alumni_portal.sql` (Root): Full live MariaDB / MySQL export with database creation, table structure, and all MNC records.
- `database.sql` (Root): Single unified installation script.
- `database/schema.sql`: 8 relational tables with foreign keys and indexes.
- `database/sample_data.sql`: Seed data for Sanjivani University, Kopargaon (8 MNCs, 10 students, 5 alumni, 6 drives, 10 applications, 5 placements).
- `install.php`: Automated installer script with PDO multi-statement execution.

### Method 1: Automated 1-Click Installer (Fastest)
Run in terminal or visit in browser:
- **CLI**: `php install.php`
- **Browser**: `http://localhost/placement-alumni-portal/install.php` or `http://127.0.0.1:8000/install.php`

### Method 2: Import via phpMyAdmin (XAMPP)
1. Open phpMyAdmin at `http://localhost/phpmyadmin/`.
2. Click the **Import** tab at the top.
3. Choose file: select `placement_alumni_portal.sql` (or `database.sql`).
4. Click **Import** at the bottom. Database `placement_alumni_portal` and all tables will be created and populated.

### Method 3: Import via MySQL Command Line
```cmd
mysql -u root -p < placement_alumni_portal.sql
```

---

## Setup & Running Instructions

### Option A: Using the Automated Batch Runner (Windows)
Double-click `run_server.bat` or run in terminal:
```cmd
run_server.bat
```
This script automatically:
1. Detects your PHP installation (XAMPP or system PATH).
2. Verifies that MySQL is running (starts `mysqld` if needed).
3. Executes `install.php` to set up the database and seed records.
4. Starts the PHP server at `http://127.0.0.1:8000` and opens your browser.

---

### Option B: Manual Setup via CLI

1. **Start MySQL**: Ensure your MySQL server is running on `127.0.0.1:3306`.
2. **Run Installer**:
   ```cmd
   php install.php
   ```
   *(Or import `placement_alumni_portal.sql` directly into phpMyAdmin).*
3. **Start the PHP Built-in Server**:
   ```cmd
   php -S 127.0.0.1:8000
   ```
4. **Open in Browser**: Navigate to `http://127.0.0.1:8000`.

---

### Option C: Using XAMPP Apache

1. Copy or extract the folder `placement-alumni-portal` to `C:\xampp\htdocs\placement-alumni-portal`.
2. Open your browser to `http://localhost/placement-alumni-portal/install.php` to initialize the database.
3. Access the portal at `http://localhost/placement-alumni-portal/index.html`.

---

## REST API Documentation

| Endpoint | Method | Role | Description |
| :--- | :---: | :--- | :--- |
| `/api/auth/login.php` | `POST` | Public | Login with email and password |
| `/api/auth/register.php` | `POST` | Public | Register student or alumni |
| `/api/auth/logout.php` | `POST` | Any | Destroy session and sign out |
| `/api/auth/me.php` | `GET` | Any | Check current user session & profile |
| `/api/drives/index.php` | `GET` | Public/Auth | List drives with filters and auto-eligibility |
| `/api/drives/index.php` | `POST`/`PUT`/`DELETE` | Officer/Admin | Create, edit, or delete placement drives |
| `/api/drives/eligibility.php` | `GET` | Student | Detailed breakdown of CGPA and branch requirements |
| `/api/applications/apply.php` | `POST` | Student | Submit drive application with resume |
| `/api/applications/index.php` | `GET` | Student/Officer | List student applications or drive applicants |
| `/api/applications/status.php` | `POST`/`PUT` | Officer/Admin | Update candidate status (Applied, Interview, Selected, etc.) |
| `/api/alumni/index.php` | `GET` | Public | Search and filter alumni directory |
| `/api/mentorship/request.php` | `POST` | Student | Send mentorship request to alumni |
| `/api/mentorship/status.php` | `POST`/`PUT` | Alumni | Accept, reject, or complete mentorship request |
| `/api/statistics/index.php` | `GET` | Public | Aggregate KPIs and charts data |
| `/api/reports/export.php` | `GET` | Officer/Admin | Export data in `csv`, `json`, or `xml` |

---

## Automated Verification Suite

Run the included verification suite anytime using:
```cmd
php tests/api_test.php
```
This tests:
- Database table integrity and record counts
- BCRYPT password hashes for all 4 roles
- Automatic eligibility engine logic
- Placement statistics aggregations
