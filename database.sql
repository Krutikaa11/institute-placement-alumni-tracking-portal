-- Institute Placement & Alumni Tracking Portal
-- Database Schema: placement_alumni_portal

CREATE DATABASE IF NOT EXISTS `placement_alumni_portal` DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE `placement_alumni_portal`;

-- Drop existing tables in reverse dependency order
SET FOREIGN_KEY_CHECKS = 0;
DROP TABLE IF EXISTS `mentorship_requests`;
DROP TABLE IF EXISTS `placements`;
DROP TABLE IF EXISTS `applications`;
DROP TABLE IF EXISTS `drives`;
DROP TABLE IF EXISTS `companies`;
DROP TABLE IF EXISTS `alumni`;
DROP TABLE IF EXISTS `students`;
DROP TABLE IF EXISTS `users`;
SET FOREIGN_KEY_CHECKS = 1;

-- 1. Users Table (Core Auth & Roles)
CREATE TABLE `users` (
    `id` INT AUTO_INCREMENT PRIMARY KEY,
    `name` VARCHAR(100) NOT NULL,
    `email` VARCHAR(120) NOT NULL UNIQUE,
    `password` VARCHAR(255) NOT NULL,
    `role` ENUM('admin', 'officer', 'student', 'alumni') NOT NULL,
    `avatar` VARCHAR(255) DEFAULT NULL,
    `is_active` TINYINT(1) DEFAULT 1,
    `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 2. Students Table
CREATE TABLE `students` (
    `id` INT AUTO_INCREMENT PRIMARY KEY,
    `user_id` INT NOT NULL UNIQUE,
    `roll_no` VARCHAR(50) NOT NULL UNIQUE,
    `branch` VARCHAR(50) NOT NULL,
    `year` INT NOT NULL DEFAULT 4,
    `cgpa` DECIMAL(3,2) NOT NULL DEFAULT 0.00,
    `phone` VARCHAR(25) DEFAULT NULL,
    `resume` VARCHAR(255) DEFAULT NULL,
    `skills` TEXT DEFAULT NULL,
    `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    CONSTRAINT `fk_students_user` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 3. Alumni Table
CREATE TABLE `alumni` (
    `id` INT AUTO_INCREMENT PRIMARY KEY,
    `user_id` INT NOT NULL UNIQUE,
    `graduation_year` INT NOT NULL,
    `branch` VARCHAR(50) NOT NULL,
    `company` VARCHAR(120) DEFAULT NULL,
    `job_role` VARCHAR(120) DEFAULT NULL,
    `location` VARCHAR(120) DEFAULT NULL,
    `skills` TEXT DEFAULT NULL,
    `linkedin` VARCHAR(255) DEFAULT NULL,
    `bio` TEXT DEFAULT NULL,
    `available_for_mentorship` TINYINT(1) DEFAULT 1,
    `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    CONSTRAINT `fk_alumni_user` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 4. Companies Table
CREATE TABLE `companies` (
    `id` INT AUTO_INCREMENT PRIMARY KEY,
    `company_name` VARCHAR(120) NOT NULL,
    `industry` VARCHAR(100) DEFAULT NULL,
    `location` VARCHAR(100) DEFAULT NULL,
    `website` VARCHAR(255) DEFAULT NULL,
    `description` TEXT DEFAULT NULL,
    `logo` VARCHAR(255) DEFAULT NULL,
    `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 5. Drives Table
CREATE TABLE `drives` (
    `id` INT AUTO_INCREMENT PRIMARY KEY,
    `company_id` INT NOT NULL,
    `job_role` VARCHAR(120) NOT NULL,
    `package` DECIMAL(10,2) NOT NULL COMMENT 'Package in LPA (Lakhs Per Annum)',
    `location` VARCHAR(120) NOT NULL,
    `drive_date` DATE NOT NULL,
    `deadline` DATE NOT NULL,
    `eligibility_criteria` TEXT DEFAULT NULL,
    `skills` TEXT DEFAULT NULL,
    `vacancies` INT DEFAULT 1,
    `min_cgpa` DECIMAL(3,2) DEFAULT 0.00,
    `eligible_branches` VARCHAR(255) DEFAULT 'All Branches',
    `status` ENUM('upcoming', 'active', 'completed', 'cancelled') DEFAULT 'active',
    `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    CONSTRAINT `fk_drives_company` FOREIGN KEY (`company_id`) REFERENCES `companies` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 6. Applications Table
CREATE TABLE `applications` (
    `id` INT AUTO_INCREMENT PRIMARY KEY,
    `student_id` INT NOT NULL,
    `drive_id` INT NOT NULL,
    `applied_date` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    `status` ENUM('Applied', 'Shortlisted', 'Interview', 'Selected', 'Rejected') DEFAULT 'Applied',
    `notes` TEXT DEFAULT NULL,
    `resume_path` VARCHAR(255) DEFAULT NULL,
    `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    CONSTRAINT `fk_app_student` FOREIGN KEY (`student_id`) REFERENCES `students` (`id`) ON DELETE CASCADE,
    CONSTRAINT `fk_app_drive` FOREIGN KEY (`drive_id`) REFERENCES `drives` (`id`) ON DELETE CASCADE,
    CONSTRAINT `unique_student_drive` UNIQUE (`student_id`, `drive_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 7. Placements Table
CREATE TABLE `placements` (
    `id` INT AUTO_INCREMENT PRIMARY KEY,
    `student_id` INT NOT NULL,
    `company_id` INT NOT NULL,
    `drive_id` INT DEFAULT NULL,
    `package` DECIMAL(10,2) NOT NULL,
    `placement_year` INT NOT NULL,
    `offer_letter` VARCHAR(255) DEFAULT NULL,
    `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    CONSTRAINT `fk_placements_student` FOREIGN KEY (`student_id`) REFERENCES `students` (`id`) ON DELETE CASCADE,
    CONSTRAINT `fk_placements_company` FOREIGN KEY (`company_id`) REFERENCES `companies` (`id`) ON DELETE CASCADE,
    CONSTRAINT `fk_placements_drive` FOREIGN KEY (`drive_id`) REFERENCES `drives` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 8. Mentorship Requests Table
CREATE TABLE `mentorship_requests` (
    `id` INT AUTO_INCREMENT PRIMARY KEY,
    `student_id` INT NOT NULL,
    `alumni_id` INT NOT NULL,
    `message` TEXT NOT NULL,
    `request_date` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    `status` ENUM('Pending', 'Accepted', 'Rejected', 'Completed') DEFAULT 'Pending',
    `response_message` TEXT DEFAULT NULL,
    `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    CONSTRAINT `fk_mentorship_student` FOREIGN KEY (`student_id`) REFERENCES `students` (`id`) ON DELETE CASCADE,
    CONSTRAINT `fk_mentorship_alumni` FOREIGN KEY (`alumni_id`) REFERENCES `alumni` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Indexes for search & filter optimization
CREATE INDEX idx_student_branch ON `students`(`branch`);
CREATE INDEX idx_student_cgpa ON `students`(`cgpa`);
CREATE INDEX idx_alumni_branch ON `alumni`(`branch`);
CREATE INDEX idx_alumni_year ON `alumni`(`graduation_year`);
CREATE INDEX idx_alumni_company ON `alumni`(`company`);
CREATE INDEX idx_drives_deadline ON `drives`(`deadline`);
CREATE INDEX idx_drives_status ON `drives`(`status`);
CREATE INDEX idx_app_status ON `applications`(`status`);
-- Sanjivani University, Kopargaon
-- Training & Placement Cell Portal
-- Authentic Sample Seed Data for Kopargaon Campus

USE `placement_alumni_portal`;

-- 1. Insert Users
-- Passwords:
-- Admin@123:    $2y$10$UMgCH3f0lHavrnl7npxOp.EiGWABl7hmTHuiw43eDU5t.nq95aqTG
-- Officer@123:  $2y$10$eiSaCgqt3PivqIUzK.mbb.j9K7KEMoknP9lbIVixLe4UBzWhJiGA6
-- Student@123:  $2y$10$0tyY5bKqLl8f3RVpH/ROFeJM.iAiL4XJ.zLbPaSOrdaiEuT0DRVbO
-- Alumni@123:   $2y$10$VuwK5OCgtQ3N894.eWCuSuTmD0Cx5vJxuieD23Tif6OWn8RCqvaaO

INSERT INTO `users` (`id`, `name`, `email`, `password`, `role`, `avatar`) VALUES
(1, 'System Administrator', 'admin@sanjivani.edu.in', '$2y$10$UMgCH3f0lHavrnl7npxOp.EiGWABl7hmTHuiw43eDU5t.nq95aqTG', 'admin', 'admin_avatar.png'),
(2, 'Dr. Vinod P. Shinde', 'tpo@sanjivani.edu.in', '$2y$10$eiSaCgqt3PivqIUzK.mbb.j9K7KEMoknP9lbIVixLe4UBzWhJiGA6', 'officer', 'officer_avatar.png'),
-- 10 Sanjivani University Students (IDs 3 to 12)
(3, 'Rohan Kulkarni', 'rohan.kulkarni@sanjivani.edu.in', '$2y$10$0tyY5bKqLl8f3RVpH/ROFeJM.iAiL4XJ.zLbPaSOrdaiEuT0DRVbO', 'student', 'student1.png'),
(4, 'Snehal Tambe', 'snehal.tambe@sanjivani.edu.in', '$2y$10$0tyY5bKqLl8f3RVpH/ROFeJM.iAiL4XJ.zLbPaSOrdaiEuT0DRVbO', 'student', 'student2.png'),
(5, 'Aditya Shinde', 'aditya.shinde@sanjivani.edu.in', '$2y$10$0tyY5bKqLl8f3RVpH/ROFeJM.iAiL4XJ.zLbPaSOrdaiEuT0DRVbO', 'student', 'student3.png'),
(6, 'Pooja Gholap', 'pooja.gholap@sanjivani.edu.in', '$2y$10$0tyY5bKqLl8f3RVpH/ROFeJM.iAiL4XJ.zLbPaSOrdaiEuT0DRVbO', 'student', 'student4.png'),
(7, 'Sanket Pawar', 'sanket.pawar@sanjivani.edu.in', '$2y$10$0tyY5bKqLl8f3RVpH/ROFeJM.iAiL4XJ.zLbPaSOrdaiEuT0DRVbO', 'student', 'student5.png'),
(8, 'Tanvi Jadhav', 'tanvi.jadhav@sanjivani.edu.in', '$2y$10$0tyY5bKqLl8f3RVpH/ROFeJM.iAiL4XJ.zLbPaSOrdaiEuT0DRVbO', 'student', 'student6.png'),
(9, 'Tejas Patil', 'tejas.patil@sanjivani.edu.in', '$2y$10$0tyY5bKqLl8f3RVpH/ROFeJM.iAiL4XJ.zLbPaSOrdaiEuT0DRVbO', 'student', 'student7.png'),
(10, 'Rutuja Darekar', 'rutuja.darekar@sanjivani.edu.in', '$2y$10$0tyY5bKqLl8f3RVpH/ROFeJM.iAiL4XJ.zLbPaSOrdaiEuT0DRVbO', 'student', 'student8.png'),
(11, 'Shubham More', 'shubham.more@sanjivani.edu.in', '$2y$10$0tyY5bKqLl8f3RVpH/ROFeJM.iAiL4XJ.zLbPaSOrdaiEuT0DRVbO', 'student', 'student9.png'),
(12, 'Pratik Deshmukh', 'pratik.deshmukh@sanjivani.edu.in', '$2y$10$0tyY5bKqLl8f3RVpH/ROFeJM.iAiL4XJ.zLbPaSOrdaiEuT0DRVbO', 'student', 'student10.png'),
-- 5 Sanjivani Alumni (IDs 13 to 17)
(13, 'Priya Nair-Patil', 'priya.nair@sanjivani.edu.in', '$2y$10$VuwK5OCgtQ3N894.eWCuSuTmD0Cx5vJxuieD23Tif6OWn8RCqvaaO', 'alumni', 'alumni1.png'),
(14, 'Arjun Reddy-Borse', 'arjun.reddy@sanjivani.edu.in', '$2y$10$VuwK5OCgtQ3N894.eWCuSuTmD0Cx5vJxuieD23Tif6OWn8RCqvaaO', 'alumni', 'alumni2.png'),
(15, 'Dr. Nilesh Kulkarni', 'nilesh.kulkarni@sanjivani.edu.in', '$2y$10$VuwK5OCgtQ3N894.eWCuSuTmD0Cx5vJxuieD23Tif6OWn8RCqvaaO', 'alumni', 'alumni3.png'),
(16, 'Manish Joshi', 'manish.joshi@sanjivani.edu.in', '$2y$10$VuwK5OCgtQ3N894.eWCuSuTmD0Cx5vJxuieD23Tif6OWn8RCqvaaO', 'alumni', 'alumni4.png'),
(17, 'Divya Iyer-Sonawane', 'divya.iyer@sanjivani.edu.in', '$2y$10$VuwK5OCgtQ3N894.eWCuSuTmD0Cx5vJxuieD23Tif6OWn8RCqvaaO', 'alumni', 'alumni5.png');

-- 2. Insert 10 Sanjivani Students Details
INSERT INTO `students` (`id`, `user_id`, `roll_no`, `branch`, `year`, `cgpa`, `phone`, `resume`, `skills`) VALUES
(1, 3, 'SU2023001', 'Computer Engineering', 4, 8.90, '+91 98220 11221', 'sample_resume_rahul.pdf', 'Java, Spring Boot, MySQL, React, Microservices, Git'),
(2, 4, 'SU2023015', 'Information Technology', 4, 9.30, '+91 98220 22332', 'sample_resume_sneha.pdf', 'Python, Django, AWS, AngularJS, Docker, Kubernetes'),
(3, 5, 'SU2023009', 'Mechanical Engineering', 4, 8.10, '+91 98220 33443', 'sample_resume_amit.pdf', 'AutoCAD, SolidWorks, Ansys, Industrial Robotics, Python'),
(4, 6, 'SU2023028', 'Computer Engineering', 4, 8.60, '+91 98220 44554', 'sample_resume_priya.pdf', 'Node.js, Express, MongoDB, Vue.js, REST APIs, TypeScript'),
(5, 7, 'SU2023042', 'Electronics & Computer', 4, 7.80, '+91 98220 55665', 'sample_resume_rohit.pdf', 'Embedded C++, IoT, Raspberry Pi, Python, Circuit Design'),
(6, 8, 'SU2023055', 'AI & Data Science', 4, 9.60, '+91 98220 66776', 'sample_resume_ananya.pdf', 'Machine Learning, PyTorch, Deep Learning, NLP, Data Analytics'),
(7, 9, 'SU2023033', 'Information Technology', 4, 8.20, '+91 98220 77887', 'sample_resume_vikram.pdf', 'PHP 8, Laravel, JavaScript ES6, MySQL, Linux DevOps'),
(8, 10, 'SU2023019', 'Electrical Engineering', 4, 7.90, '+91 98220 88998', 'sample_resume_pooja.pdf', 'Power Electronics, MATLAB, PLC & SCADA, Industrial Automation'),
(9, 11, 'SU2023089', 'Computer Engineering', 4, 7.40, '+91 98220 99009', 'sample_resume_karthik.pdf', 'HTML5, CSS3, JavaScript, PHP, MySQL, Bootstrap 5'),
(10, 12, 'SU2023071', 'Electronics & Telecomm', 4, 8.70, '+91 98220 12345', 'sample_resume_neha.pdf', 'VLSI, Digital Signal Processing, Python, SQL, Edge AI');

-- 3. Insert 5 Sanjivani Alumni Details
INSERT INTO `alumni` (`id`, `user_id`, `graduation_year`, `branch`, `company`, `job_role`, `location`, `skills`, `linkedin`, `bio`, `available_for_mentorship`) VALUES
(1, 13, 2021, 'Computer Engineering', 'Google', 'Senior Software Engineer', 'Pune / Bangalore', 'Distributed Systems, Go, Kubernetes, Cloud Architecture, DSA', 'https://linkedin.com/in/priya-nair-sanjivani', 'Sanjivani Kopargaon 2021 batch graduate. Mentoring juniors for Tier-1 product tech interviews and competitive coding.', 1),
(2, 14, 2020, 'Information Technology', 'Microsoft', 'Cloud Solutions Architect', 'Hyderabad / Pune', 'Azure, DevOps, Terraform, Microservices, C# .NET', 'https://linkedin.com/in/arjun-borse-cloud', 'Proud Sanjivani alumnus. Helping engineering students build production-grade cloud architectures and DevOps pipelines.', 1),
(3, 15, 2019, 'Computer Engineering', 'NVIDIA', 'Lead AI Scientist', 'Pune, India', 'Deep Learning, CUDA, PyTorch, Computer Vision, Generative AI', 'https://linkedin.com/in/dr-nilesh-kulkarni-ai', 'Sanjivani Kopargaon alumnus. Guiding young researchers in AI, Machine Learning, and High Performance Computing.', 1),
(4, 16, 2018, 'Mechanical Engineering', 'Tata Consultancy Services', 'Technical Delivery Lead', 'Pune, Maharashtra', 'Enterprise Java, PLM Systems, Cloud Migration, Agile Leadership', 'https://linkedin.com/in/manish-joshi-tcs', 'Transitioned from core mechanical to digital enterprise solutions at TCS. Happy to guide cross-branch students.', 1),
(5, 17, 2022, 'Electronics & Telecomm', 'L&T Technology Services', 'Cybersecurity Specialist', 'Nashik / Mumbai', 'Network Security, Cloud Threat Defense, Ethical Hacking, Python', 'https://linkedin.com/in/divya-sonawane-sec', 'Sanjivani Kopargaon 2022 alumna. Guiding female engineers in cybersecurity and embedded security.', 1);

-- 4. Insert 8 Premier Multinational Corporations (MNCs Recruiting at Sanjivani University, Kopargaon)
INSERT INTO `companies` (`id`, `company_name`, `industry`, `location`, `website`, `description`, `logo`) VALUES
(1, 'Google', 'Global Cloud, AI & Search Technologies', 'Bangalore / Hyderabad / Pune', 'https://careers.google.com', 'World-leading multinational technology enterprise pioneering artificial intelligence, cloud computing, and consumer digital ecosystems.', 'google.png'),
(2, 'Microsoft', 'Enterprise Cloud, OS & Artificial Intelligence', 'Hyderabad / Bangalore / Pune', 'https://careers.microsoft.com', 'Global Fortune 50 multinational empowering digital transformation through Azure, AI Copilot, GitHub, and enterprise cloud software.', 'microsoft.png'),
(3, 'Tata Consultancy Services (TCS)', 'Global IT, Consulting & Digital Transformation', 'Pune / Mumbai / Nashik Hub', 'https://tcs.com/careers', 'Flagship multinational IT conglomerate of the Tata Group and leading campus recruiter for Sanjivani University graduates.', 'tcs.png'),
(4, 'Amazon (AWS)', 'Cloud Infrastructure, AI & Global E-Commerce', 'Hyderabad / Bangalore / Pune', 'https://amazon.jobs', 'Pioneering multinational technology company specializing in AWS cloud infrastructure, large-scale distributed systems, and robotics.', 'amazon.png'),
(5, 'NVIDIA', 'Accelerated Computing, GPU & Generative AI', 'Pune / Bangalore, India', 'https://nvidia.com/careers', 'World leader in accelerated computing, autonomous machines, graphics processing, and foundational AI supercomputing hardware.', 'nvidia.png'),
(6, 'Infosys', 'Global Digital Services, Cloud & AI Systems', 'Pune / Bangalore Campus', 'https://infosys.com/careers', 'Renowned multinational information technology company providing business consulting, next-gen cloud automation, and digital engineering.', 'infosys.png'),
(7, 'LTIMindtree (Larsen & Toubro)', 'Global Digital Transformation & IT Consulting', 'Mumbai / Pune / Navi Mumbai', 'https://ltimindtree.com', 'Premier multinational technology consulting enterprise of the Larsen & Toubro Group driving engineering excellence worldwide.', 'lti.png'),
(8, 'Cognizant Technology Solutions', 'Digital Engineering, Cloud & Enterprise AI', 'Pune / Chennai / Hyderabad', 'https://cognizant.com', 'Global multinational powerhouse modernizing technology infrastructure, industry workflows, and data-driven customer experiences.', 'cognizant.png');

-- 5. Insert 6 Placement Drives from Premier MNCs
INSERT INTO `drives` (`id`, `company_id`, `job_role`, `package`, `location`, `drive_date`, `deadline`, `eligibility_criteria`, `skills`, `vacancies`, `min_cgpa`, `eligible_branches`, `status`) VALUES
(1, 1, 'Software Development Engineer - I (SDE-1)', 24.50, 'Bangalore / Pune', '2026-11-15', '2026-11-05', 'Minimum 8.00 CGPA, Sanjivani engineering students with exceptional data structures, algorithms, and distributed systems fundamentals.', 'Data Structures, Algorithms, Go / C++, System Design, Git', 15, 8.00, 'Computer Engineering, Information Technology, AI & Data Science', 'active'),
(2, 4, 'Cloud DevOps & Systems Engineer', 18.00, 'Hyderabad / Pune', '2026-11-20', '2026-11-10', 'Minimum 7.50 CGPA, hands-on Linux administration, Docker containerization, AWS architecture, and networking aptitude.', 'Linux, Docker, AWS Architecture, Python, CI/CD, Kubernetes', 20, 7.50, 'Computer Engineering, Information Technology, AI & Data Science', 'active'),
(3, 3, 'Systems Software & AI Researcher (TCS Digital)', 15.50, 'Pune Campus, Maharashtra', '2026-11-28', '2026-11-18', 'Minimum 7.50 CGPA, portfolio of Machine Learning, Deep Learning, or high-concurrency systems projects.', 'Python, PyTorch, C++, Machine Learning, Algorithms', 30, 7.50, 'Computer Engineering, Information Technology, AI & Data Science, Electronics & Telecomm', 'active'),
(4, 2, 'Cloud Software Engineer (Azure)', 21.00, 'Bangalore / Pune', '2026-12-05', '2026-11-25', 'Minimum 8.00 CGPA, proficient in object-oriented programming, modern cloud microservices, and web technologies.', 'C# .NET, Azure, React, TypeScript, Microservices, SQL', 18, 8.00, 'Computer Engineering, Information Technology, AI & Data Science', 'active'),
(5, 5, 'AI Algorithms & Deep Learning Engineer', 28.00, 'Pune / Bangalore', '2026-12-12', '2026-12-02', 'Minimum 8.50 CGPA, strong mathematical rigor, proficiency in CUDA, C++, and deep learning framework internals.', 'CUDA, C++, Python, Deep Learning, TensorRT, Linux', 10, 8.50, 'Computer Engineering, AI & Data Science, Electronics & Telecomm, Electronics & Computer', 'active'),
(6, 6, 'Specialist Programmer & Cloud Developer', 9.50, 'Pune / Bangalore', '2026-12-18', '2026-12-08', 'Minimum 6.50 CGPA, open to all Sanjivani engineering disciplines with demonstrated programming aptitude.', 'Java, Python, SQL, Cloud Platforms, Spring Boot, Web APIs', 40, 6.50, 'All Branches', 'active');

-- 6. Insert 10 Applications (Sanjivani Students to MNC Drives)
INSERT INTO `applications` (`id`, `student_id`, `drive_id`, `applied_date`, `status`, `notes`, `resume_path`) VALUES
(1, 1, 1, '2026-10-15 10:15:00', 'Interview', 'Cleared Google Online Coding Challenge with 100% score. Technical system round scheduled.', 'uploads/resumes/sample_resume_rahul.pdf'),
(2, 1, 3, '2026-10-16 11:30:00', 'Applied', 'Application received by TCS Digital and undergoing technical portfolio review.', 'uploads/resumes/sample_resume_rahul.pdf'),
(3, 2, 1, '2026-10-15 14:00:00', 'Selected', 'Outstanding performance in technical and system design rounds at Google. Offer letter extended (24.5 LPA).', 'uploads/resumes/sample_resume_sneha.pdf'),
(4, 2, 3, '2026-10-16 16:45:00', 'Shortlisted', 'Shortlisted for TCS Digital Advanced Coding Round based on PyTorch and AWS projects.', 'uploads/resumes/sample_resume_sneha.pdf'),
(5, 3, 1, '2026-10-17 09:20:00', 'Rejected', 'Did not meet the algorithmic cutoff in Round 1.', 'uploads/resumes/sample_resume_amit.pdf'),
(6, 3, 4, '2026-10-17 15:10:00', 'Interview', 'Passed round 1. Technical discussion on Microsoft Azure cloud architecture scheduled.', 'uploads/resumes/sample_resume_amit.pdf'),
(7, 4, 2, '2026-10-18 11:00:00', 'Shortlisted', 'Resume verified by TPO cell. Amazon Online Assessment link dispatched.', 'uploads/resumes/sample_resume_priya.pdf'),
(8, 4, 4, '2026-10-18 12:30:00', 'Selected', 'Top scorer in Microsoft Cloud & Full Stack coding round. Offer extended (21.0 LPA).', 'uploads/resumes/sample_resume_priya.pdf'),
(9, 6, 5, '2026-10-19 10:00:00', 'Selected', 'Top ranked candidate in NVIDIA AI & CUDA acceleration interview. Highest campus offer (28.0 LPA).', 'uploads/resumes/sample_resume_ananya.pdf'),
(10, 8, 6, '2026-10-19 14:20:00', 'Interview', 'Interview slot confirmed for Infosys Specialist Programmer evaluation.', 'uploads/resumes/sample_resume_pooja.pdf');

-- 7. Insert 5 Placements at Top MNCs
INSERT INTO `placements` (`id`, `student_id`, `company_id`, `drive_id`, `package`, `placement_year`, `offer_letter`) VALUES
(1, 2, 1, 1, 24.50, 2026, 'offer_letters/offer_snehal_google.pdf'),
(2, 4, 2, 4, 21.00, 2026, 'offer_letters/offer_priya_microsoft.pdf'),
(3, 6, 5, 5, 28.00, 2026, 'offer_letters/offer_ananya_nvidia.pdf'),
(4, 10, 3, 3, 15.50, 2026, 'offer_letters/offer_neha_tcsdigital.pdf'),
(5, 1, 4, 2, 18.00, 2025, 'offer_letters/offer_rohan_amazon.pdf');

-- 8. Insert 5 Mentorship Requests
INSERT INTO `mentorship_requests` (`id`, `student_id`, `alumni_id`, `message`, `request_date`, `status`, `response_message`) VALUES
(1, 1, 1, 'Namaste Priya maam, I am a final year Computer Engineering student at Sanjivani University, Kopargaon. Would love your guidance on system design and FAANG interview preparation.', '2026-10-01 10:30:00', 'Accepted', 'Happy to connect with a fellow Sanjivani engineer! Let us set up a weekend Google Meet session to review your core concepts.'),
(2, 2, 3, 'Hello Dr. Nilesh sir, I am researching deep learning pipelines at Sanjivani Kopargaon and aiming for AI roles. Looking forward to your mentorship.', '2026-10-05 14:15:00', 'Accepted', 'Glad to see the AI momentum at Sanjivani University! Let us connect on LinkedIn and discuss your project portfolio.'),
(3, 3, 2, 'Respected Arjun sir, I am passionate about Azure cloud infrastructure and DevOps. Could you please share tips on certifications for Sanjivani undergraduates?', '2026-10-08 11:00:00', 'Pending', NULL),
(4, 5, 4, 'Hello Manish sir, I am in Electronics & Computer at Sanjivani and want to explore enterprise Java and cloud systems at TCS. How should I structure my preparation?', '2026-10-10 16:20:00', 'Rejected', 'Currently mentoring maximum student capacity for this quarter. Please reach out in the next semester cycle.'),
(5, 6, 1, 'Hi Priya maam, thank you for your interview preparation guidance. I have received an offer from Google (24.5 LPA)! Sharing my gratitude.', '2026-10-12 09:40:00', 'Completed', 'Heartiest congratulations Tanvi! Sanjivani University is proud of your achievements!');
