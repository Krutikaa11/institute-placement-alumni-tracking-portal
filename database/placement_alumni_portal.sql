-- MariaDB dump 10.19  Distrib 10.4.24-MariaDB, for Win64 (AMD64)
--
-- Host: localhost    Database: placement_alumni_portal
-- ------------------------------------------------------
-- Server version	10.4.24-MariaDB

/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;
/*!40103 SET @OLD_TIME_ZONE=@@TIME_ZONE */;
/*!40103 SET TIME_ZONE='+00:00' */;
/*!40014 SET @OLD_UNIQUE_CHECKS=@@UNIQUE_CHECKS, UNIQUE_CHECKS=0 */;
/*!40014 SET @OLD_FOREIGN_KEY_CHECKS=@@FOREIGN_KEY_CHECKS, FOREIGN_KEY_CHECKS=0 */;
/*!40101 SET @OLD_SQL_MODE=@@SQL_MODE, SQL_MODE='NO_AUTO_VALUE_ON_ZERO' */;
/*!40111 SET @OLD_SQL_NOTES=@@SQL_NOTES, SQL_NOTES=0 */;

--
-- Current Database: `placement_alumni_portal`
--

CREATE DATABASE /*!32312 IF NOT EXISTS*/ `placement_alumni_portal` /*!40100 DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci */;

USE `placement_alumni_portal`;

--
-- Table structure for table `alumni`
--

DROP TABLE IF EXISTS `alumni`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `alumni` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `user_id` int(11) NOT NULL,
  `graduation_year` int(11) NOT NULL,
  `branch` varchar(50) COLLATE utf8mb4_unicode_ci NOT NULL,
  `company` varchar(120) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `job_role` varchar(120) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `location` varchar(120) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `skills` text COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `linkedin` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `bio` text COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `available_for_mentorship` tinyint(1) DEFAULT 1,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  UNIQUE KEY `user_id` (`user_id`),
  KEY `idx_alumni_branch` (`branch`),
  KEY `idx_alumni_year` (`graduation_year`),
  KEY `idx_alumni_company` (`company`),
  CONSTRAINT `fk_alumni_user` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=6 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `alumni`
--

LOCK TABLES `alumni` WRITE;
/*!40000 ALTER TABLE `alumni` DISABLE KEYS */;
INSERT INTO `alumni` VALUES (1,13,2021,'Computer Engineering','Google','Senior Software Engineer','Pune / Bangalore','Distributed Systems, Go, Kubernetes, Cloud Architecture, DSA','https://linkedin.com/in/priya-nair-sanjivani','Sanjivani Kopargaon 2021 batch graduate. Mentoring juniors for Tier-1 product tech interviews and competitive coding.',1,'2026-10-29 11:02:00'),(2,14,2020,'Information Technology','Microsoft','Cloud Solutions Architect','Hyderabad / Pune','Azure, DevOps, Terraform, Microservices, C# .NET','https://linkedin.com/in/arjun-borse-cloud','Proud Sanjivani alumnus. Helping engineering students build production-grade cloud architectures and DevOps pipelines.',1,'2026-10-29 11:02:00'),(3,15,2019,'Computer Engineering','NVIDIA','Lead AI Scientist','Pune, India','Deep Learning, CUDA, PyTorch, Computer Vision, Generative AI','https://linkedin.com/in/dr-nilesh-kulkarni-ai','Sanjivani Kopargaon alumnus. Guiding young researchers in AI, Machine Learning, and High Performance Computing.',1,'2026-10-29 11:02:00'),(4,16,2018,'Mechanical Engineering','Tata Consultancy Services','Technical Delivery Lead','Pune, Maharashtra','Enterprise Java, PLM Systems, Cloud Migration, Agile Leadership','https://linkedin.com/in/manish-joshi-tcs','Transitioned from core mechanical to digital enterprise solutions at TCS. Happy to guide cross-branch students.',1,'2026-10-29 11:02:00'),(5,17,2022,'Electronics & Telecomm','L&T Technology Services','Cybersecurity Specialist','Nashik / Mumbai','Network Security, Cloud Threat Defense, Ethical Hacking, Python','https://linkedin.com/in/divya-sonawane-sec','Sanjivani Kopargaon 2022 alumna. Guiding female engineers in cybersecurity and embedded security.',1,'2026-10-29 11:02:00');
/*!40000 ALTER TABLE `alumni` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `applications`
--

DROP TABLE IF EXISTS `applications`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `applications` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `student_id` int(11) NOT NULL,
  `drive_id` int(11) NOT NULL,
  `applied_date` timestamp NOT NULL DEFAULT current_timestamp(),
  `status` enum('Applied','Shortlisted','Interview','Selected','Rejected') COLLATE utf8mb4_unicode_ci DEFAULT 'Applied',
  `notes` text COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `resume_path` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`id`),
  UNIQUE KEY `unique_student_drive` (`student_id`,`drive_id`),
  KEY `fk_app_drive` (`drive_id`),
  KEY `idx_app_status` (`status`),
  CONSTRAINT `fk_app_drive` FOREIGN KEY (`drive_id`) REFERENCES `drives` (`id`) ON DELETE CASCADE,
  CONSTRAINT `fk_app_student` FOREIGN KEY (`student_id`) REFERENCES `students` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=12 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `applications`
--

LOCK TABLES `applications` WRITE;
/*!40000 ALTER TABLE `applications` DISABLE KEYS */;
INSERT INTO `applications` VALUES (1,1,1,'2026-10-15 04:45:00','Interview','Cleared Google Online Coding Challenge with 100% score. Technical system round scheduled.','uploads/resumes/sample_resume_rahul.pdf','2026-10-29 11:02:00'),(2,1,3,'2026-10-16 06:00:00','Applied','Application received by TCS Digital and undergoing technical portfolio review.','uploads/resumes/sample_resume_rahul.pdf','2026-10-29 11:02:00'),(3,2,1,'2026-10-15 08:30:00','Selected','Outstanding performance in technical and system design rounds at Google. Offer letter extended (24.5 LPA).','uploads/resumes/sample_resume_sneha.pdf','2026-10-29 11:02:00'),(4,2,3,'2026-10-16 11:15:00','Shortlisted','Shortlisted for TCS Digital Advanced Coding Round based on PyTorch and AWS projects.','uploads/resumes/sample_resume_sneha.pdf','2026-10-29 11:02:00'),(5,3,1,'2026-10-17 03:50:00','Rejected','Did not meet the algorithmic cutoff in Round 1.','uploads/resumes/sample_resume_amit.pdf','2026-10-29 11:02:00'),(6,3,4,'2026-10-17 09:40:00','Interview','Passed round 1. Technical discussion on Microsoft Azure cloud architecture scheduled.','uploads/resumes/sample_resume_amit.pdf','2026-10-29 11:02:00'),(7,4,2,'2026-10-18 05:30:00','Shortlisted','Resume verified by TPO cell. Amazon Online Assessment link dispatched.','uploads/resumes/sample_resume_priya.pdf','2026-10-29 11:02:00'),(8,4,4,'2026-10-18 07:00:00','Selected','Top scorer in Microsoft Cloud & Full Stack coding round. Offer extended (21.0 LPA).','uploads/resumes/sample_resume_priya.pdf','2026-10-29 11:02:00'),(9,6,5,'2026-10-19 04:30:00','Selected','Top ranked candidate in NVIDIA AI & CUDA acceleration interview. Highest campus offer (28.0 LPA).','uploads/resumes/sample_resume_ananya.pdf','2026-10-29 11:02:00'),(10,8,6,'2026-10-19 08:50:00','Interview','Interview slot confirmed for Infosys Specialist Programmer evaluation.','uploads/resumes/sample_resume_pooja.pdf','2026-10-29 11:02:00'),(11,1,2,'2026-10-29 11:17:08','Shortlisted','Status updated to Shortlisted by Dr. Vinod P. Shinde','uploads/resumes/resume_1_1793272628.pdf','2026-10-29 11:18:36');
/*!40000 ALTER TABLE `applications` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `companies`
--

DROP TABLE IF EXISTS `companies`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `companies` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `company_name` varchar(120) COLLATE utf8mb4_unicode_ci NOT NULL,
  `industry` varchar(100) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `location` varchar(100) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `website` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `description` text COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `logo` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=9 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `companies`
--

LOCK TABLES `companies` WRITE;
/*!40000 ALTER TABLE `companies` DISABLE KEYS */;
INSERT INTO `companies` VALUES (1,'Google','Global Cloud, AI & Search Technologies','Bangalore / Hyderabad / Pune','https://careers.google.com','World-leading multinational technology enterprise pioneering artificial intelligence, cloud computing, and consumer digital ecosystems.','google.png','2026-10-29 11:02:00'),(2,'Microsoft','Enterprise Cloud, OS & Artificial Intelligence','Hyderabad / Bangalore / Pune','https://careers.microsoft.com','Global Fortune 50 multinational empowering digital transformation through Azure, AI Copilot, GitHub, and enterprise cloud software.','microsoft.png','2026-10-29 11:02:00'),(3,'Tata Consultancy Services (TCS)','Global IT, Consulting & Digital Transformation','Pune / Mumbai / Nashik Hub','https://tcs.com/careers','Flagship multinational IT conglomerate of the Tata Group and leading campus recruiter for Sanjivani University graduates.','tcs.png','2026-10-29 11:02:00'),(4,'Amazon (AWS)','Cloud Infrastructure, AI & Global E-Commerce','Hyderabad / Bangalore / Pune','https://amazon.jobs','Pioneering multinational technology company specializing in AWS cloud infrastructure, large-scale distributed systems, and robotics.','amazon.png','2026-10-29 11:02:00'),(5,'NVIDIA','Accelerated Computing, GPU & Generative AI','Pune / Bangalore, India','https://nvidia.com/careers','World leader in accelerated computing, autonomous machines, graphics processing, and foundational AI supercomputing hardware.','nvidia.png','2026-10-29 11:02:00'),(6,'Infosys','Global Digital Services, Cloud & AI Systems','Pune / Bangalore Campus','https://infosys.com/careers','Renowned multinational information technology company providing business consulting, next-gen cloud automation, and digital engineering.','infosys.png','2026-10-29 11:02:00'),(7,'LTIMindtree (Larsen & Toubro)','Global Digital Transformation & IT Consulting','Mumbai / Pune / Navi Mumbai','https://ltimindtree.com','Premier multinational technology consulting enterprise of the Larsen & Toubro Group driving engineering excellence worldwide.','lti.png','2026-10-29 11:02:00'),(8,'Cognizant Technology Solutions','Digital Engineering, Cloud & Enterprise AI','Pune / Chennai / Hyderabad','https://cognizant.com','Global multinational powerhouse modernizing technology infrastructure, industry workflows, and data-driven customer experiences.','cognizant.png','2026-10-29 11:02:00');
/*!40000 ALTER TABLE `companies` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `drives`
--

DROP TABLE IF EXISTS `drives`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `drives` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `company_id` int(11) NOT NULL,
  `job_role` varchar(120) COLLATE utf8mb4_unicode_ci NOT NULL,
  `package` decimal(10,2) NOT NULL COMMENT 'Package in LPA (Lakhs Per Annum)',
  `location` varchar(120) COLLATE utf8mb4_unicode_ci NOT NULL,
  `drive_date` date NOT NULL,
  `deadline` date NOT NULL,
  `eligibility_criteria` text COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `skills` text COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `vacancies` int(11) DEFAULT 1,
  `min_cgpa` decimal(3,2) DEFAULT 0.00,
  `eligible_branches` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT 'All Branches',
  `status` enum('upcoming','active','completed','cancelled') COLLATE utf8mb4_unicode_ci DEFAULT 'active',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  KEY `fk_drives_company` (`company_id`),
  KEY `idx_drives_deadline` (`deadline`),
  KEY `idx_drives_status` (`status`),
  CONSTRAINT `fk_drives_company` FOREIGN KEY (`company_id`) REFERENCES `companies` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=7 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `drives`
--

LOCK TABLES `drives` WRITE;
/*!40000 ALTER TABLE `drives` DISABLE KEYS */;
INSERT INTO `drives` VALUES (1,1,'Software Development Engineer - I (SDE-1)',24.50,'Bangalore / Pune','2026-11-15','2026-11-05','Minimum 8.00 CGPA, Sanjivani engineering students with exceptional data structures, algorithms, and distributed systems fundamentals.','Data Structures, Algorithms, Go / C++, System Design, Git',15,8.00,'Computer Engineering, Information Technology, AI & Data Science','active','2026-10-29 11:02:00'),(2,4,'Cloud DevOps & Systems Engineer',18.00,'Hyderabad / Pune','2026-11-20','2026-11-10','Minimum 7.50 CGPA, hands-on Linux administration, Docker containerization, AWS architecture, and networking aptitude.','Linux, Docker, AWS Architecture, Python, CI/CD, Kubernetes',20,7.50,'Computer Engineering, Information Technology, AI & Data Science','active','2026-10-29 11:02:00'),(3,3,'Systems Software & AI Researcher (TCS Digital)',15.50,'Pune Campus, Maharashtra','2026-11-28','2026-11-18','Minimum 7.50 CGPA, portfolio of Machine Learning, Deep Learning, or high-concurrency systems projects.','Python, PyTorch, C++, Machine Learning, Algorithms',30,7.50,'Computer Engineering, Information Technology, AI & Data Science, Electronics & Telecomm','active','2026-10-29 11:02:00'),(4,2,'Cloud Software Engineer (Azure)',21.00,'Bangalore / Pune','2026-12-05','2026-11-25','Minimum 8.00 CGPA, proficient in object-oriented programming, modern cloud microservices, and web technologies.','C# .NET, Azure, React, TypeScript, Microservices, SQL',18,8.00,'Computer Engineering, Information Technology, AI & Data Science','active','2026-10-29 11:02:00'),(5,5,'AI Algorithms & Deep Learning Engineer',28.00,'Pune / Bangalore','2026-12-12','2026-12-02','Minimum 8.50 CGPA, strong mathematical rigor, proficiency in CUDA, C++, and deep learning framework internals.','CUDA, C++, Python, Deep Learning, TensorRT, Linux',10,8.50,'Computer Engineering, AI & Data Science, Electronics & Telecomm, Electronics & Computer','active','2026-10-29 11:02:00'),(6,6,'Specialist Programmer & Cloud Developer',9.50,'Pune / Bangalore','2026-12-18','2026-12-08','Minimum 6.50 CGPA, open to all Sanjivani engineering disciplines with demonstrated programming aptitude.','Java, Python, SQL, Cloud Platforms, Spring Boot, Web APIs',40,6.50,'All Branches','active','2026-10-29 11:02:00');
/*!40000 ALTER TABLE `drives` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `mentorship_requests`
--

DROP TABLE IF EXISTS `mentorship_requests`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `mentorship_requests` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `student_id` int(11) NOT NULL,
  `alumni_id` int(11) NOT NULL,
  `message` text COLLATE utf8mb4_unicode_ci NOT NULL,
  `request_date` timestamp NOT NULL DEFAULT current_timestamp(),
  `status` enum('Pending','Accepted','Rejected','Completed') COLLATE utf8mb4_unicode_ci DEFAULT 'Pending',
  `response_message` text COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`id`),
  KEY `fk_mentorship_student` (`student_id`),
  KEY `fk_mentorship_alumni` (`alumni_id`),
  CONSTRAINT `fk_mentorship_alumni` FOREIGN KEY (`alumni_id`) REFERENCES `alumni` (`id`) ON DELETE CASCADE,
  CONSTRAINT `fk_mentorship_student` FOREIGN KEY (`student_id`) REFERENCES `students` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=7 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `mentorship_requests`
--

LOCK TABLES `mentorship_requests` WRITE;
/*!40000 ALTER TABLE `mentorship_requests` DISABLE KEYS */;
INSERT INTO `mentorship_requests` VALUES (1,1,1,'Namaste Priya maam, I am a final year Computer Engineering student at Sanjivani University, Kopargaon. Would love your guidance on system design and FAANG interview preparation.','2026-10-01 05:00:00','Accepted','Happy to connect with a fellow Sanjivani engineer! Let us set up a weekend Google Meet session to review your core concepts.','2026-10-29 11:02:00'),(2,2,3,'Hello Dr. Nilesh sir, I am researching deep learning pipelines at Sanjivani Kopargaon and aiming for AI roles. Looking forward to your mentorship.','2026-10-05 08:45:00','Accepted','Glad to see the AI momentum at Sanjivani University! Let us connect on LinkedIn and discuss your project portfolio.','2026-10-29 11:02:00'),(3,3,2,'Respected Arjun sir, I am passionate about Azure cloud infrastructure and DevOps. Could you please share tips on certifications for Sanjivani undergraduates?','2026-10-08 05:30:00','Pending',NULL,'2026-10-29 11:02:00'),(4,5,4,'Hello Manish sir, I am in Electronics & Computer at Sanjivani and want to explore enterprise Java and cloud systems at TCS. How should I structure my preparation?','2026-10-10 10:50:00','Rejected','Currently mentoring maximum student capacity for this quarter. Please reach out in the next semester cycle.','2026-10-29 11:02:00'),(5,6,1,'Hi Priya maam, thank you for your interview preparation guidance. I have received an offer from Google (24.5 LPA)! Sharing my gratitude.','2026-10-12 04:10:00','Completed','Heartiest congratulations Tanvi! Sanjivani University is proud of your achievements!','2026-10-29 11:02:00'),(6,1,1,'hii','2026-10-29 11:14:22','Accepted','Happy to connect! Let us arrange a call.','2026-10-29 11:15:13');
/*!40000 ALTER TABLE `mentorship_requests` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `placements`
--

DROP TABLE IF EXISTS `placements`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `placements` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `student_id` int(11) NOT NULL,
  `company_id` int(11) NOT NULL,
  `drive_id` int(11) DEFAULT NULL,
  `package` decimal(10,2) NOT NULL,
  `placement_year` int(11) NOT NULL,
  `offer_letter` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  KEY `fk_placements_student` (`student_id`),
  KEY `fk_placements_company` (`company_id`),
  KEY `fk_placements_drive` (`drive_id`),
  CONSTRAINT `fk_placements_company` FOREIGN KEY (`company_id`) REFERENCES `companies` (`id`) ON DELETE CASCADE,
  CONSTRAINT `fk_placements_drive` FOREIGN KEY (`drive_id`) REFERENCES `drives` (`id`) ON DELETE SET NULL,
  CONSTRAINT `fk_placements_student` FOREIGN KEY (`student_id`) REFERENCES `students` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=6 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `placements`
--

LOCK TABLES `placements` WRITE;
/*!40000 ALTER TABLE `placements` DISABLE KEYS */;
INSERT INTO `placements` VALUES (1,2,1,1,24.50,2026,'offer_letters/offer_snehal_google.pdf','2026-10-29 11:02:00'),(2,4,2,4,21.00,2026,'offer_letters/offer_priya_microsoft.pdf','2026-10-29 11:02:00'),(3,6,5,5,28.00,2026,'offer_letters/offer_ananya_nvidia.pdf','2026-10-29 11:02:00'),(4,10,3,3,15.50,2026,'offer_letters/offer_neha_tcsdigital.pdf','2026-10-29 11:02:00'),(5,1,4,2,18.00,2025,'offer_letters/offer_rohan_amazon.pdf','2026-10-29 11:02:00');
/*!40000 ALTER TABLE `placements` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `students`
--

DROP TABLE IF EXISTS `students`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `students` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `user_id` int(11) NOT NULL,
  `roll_no` varchar(50) COLLATE utf8mb4_unicode_ci NOT NULL,
  `branch` varchar(50) COLLATE utf8mb4_unicode_ci NOT NULL,
  `year` int(11) NOT NULL DEFAULT 4,
  `cgpa` decimal(3,2) NOT NULL DEFAULT 0.00,
  `phone` varchar(25) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `resume` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `skills` text COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  UNIQUE KEY `user_id` (`user_id`),
  UNIQUE KEY `roll_no` (`roll_no`),
  KEY `idx_student_branch` (`branch`),
  KEY `idx_student_cgpa` (`cgpa`),
  CONSTRAINT `fk_students_user` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=11 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `students`
--

LOCK TABLES `students` WRITE;
/*!40000 ALTER TABLE `students` DISABLE KEYS */;
INSERT INTO `students` VALUES (1,3,'SU2023001','Computer Engineering',4,8.90,'+91 98220 11221','resume_1_1793272628.pdf','Java, Spring Boot, MySQL, React, Microservices, Git','2026-10-29 11:02:00'),(2,4,'SU2023015','Information Technology',4,9.30,'+91 98220 22332','sample_resume_sneha.pdf','Python, Django, AWS, AngularJS, Docker, Kubernetes','2026-10-29 11:02:00'),(3,5,'SU2023009','Mechanical Engineering',4,8.10,'+91 98220 33443','sample_resume_amit.pdf','AutoCAD, SolidWorks, Ansys, Industrial Robotics, Python','2026-10-29 11:02:00'),(4,6,'SU2023028','Computer Engineering',4,8.60,'+91 98220 44554','sample_resume_priya.pdf','Node.js, Express, MongoDB, Vue.js, REST APIs, TypeScript','2026-10-29 11:02:00'),(5,7,'SU2023042','Electronics & Computer',4,7.80,'+91 98220 55665','sample_resume_rohit.pdf','Embedded C++, IoT, Raspberry Pi, Python, Circuit Design','2026-10-29 11:02:00'),(6,8,'SU2023055','AI & Data Science',4,9.60,'+91 98220 66776','sample_resume_ananya.pdf','Machine Learning, PyTorch, Deep Learning, NLP, Data Analytics','2026-10-29 11:02:00'),(7,9,'SU2023033','Information Technology',4,8.20,'+91 98220 77887','sample_resume_vikram.pdf','PHP 8, Laravel, JavaScript ES6, MySQL, Linux DevOps','2026-10-29 11:02:00'),(8,10,'SU2023019','Electrical Engineering',4,7.90,'+91 98220 88998','sample_resume_pooja.pdf','Power Electronics, MATLAB, PLC & SCADA, Industrial Automation','2026-10-29 11:02:00'),(9,11,'SU2023089','Computer Engineering',4,7.40,'+91 98220 99009','sample_resume_karthik.pdf','HTML5, CSS3, JavaScript, PHP, MySQL, Bootstrap 5','2026-10-29 11:02:00'),(10,12,'SU2023071','Electronics & Telecomm',4,8.70,'+91 98220 12345','sample_resume_neha.pdf','VLSI, Digital Signal Processing, Python, SQL, Edge AI','2026-10-29 11:02:00');
/*!40000 ALTER TABLE `students` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `users`
--

DROP TABLE IF EXISTS `users`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `users` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `name` varchar(100) COLLATE utf8mb4_unicode_ci NOT NULL,
  `email` varchar(120) COLLATE utf8mb4_unicode_ci NOT NULL,
  `password` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `role` enum('admin','officer','student','alumni') COLLATE utf8mb4_unicode_ci NOT NULL,
  `avatar` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `is_active` tinyint(1) DEFAULT 1,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  UNIQUE KEY `email` (`email`)
) ENGINE=InnoDB AUTO_INCREMENT=18 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `users`
--

LOCK TABLES `users` WRITE;
/*!40000 ALTER TABLE `users` DISABLE KEYS */;
INSERT INTO `users` VALUES (1,'System Administrator','admin@sanjivani.edu.in','$2y$10$UMgCH3f0lHavrnl7npxOp.EiGWABl7hmTHuiw43eDU5t.nq95aqTG','admin','admin_avatar.png',1,'2026-10-29 11:02:00'),(2,'Dr. Vinod P. Shinde','tpo@sanjivani.edu.in','$2y$10$eiSaCgqt3PivqIUzK.mbb.j9K7KEMoknP9lbIVixLe4UBzWhJiGA6','officer','officer_avatar.png',1,'2026-10-29 11:02:00'),(3,'Rohan Kulkarni','rohan.kulkarni@sanjivani.edu.in','$2y$10$0tyY5bKqLl8f3RVpH/ROFeJM.iAiL4XJ.zLbPaSOrdaiEuT0DRVbO','student','student1.png',1,'2026-10-29 11:02:00'),(4,'Snehal Tambe','snehal.tambe@sanjivani.edu.in','$2y$10$0tyY5bKqLl8f3RVpH/ROFeJM.iAiL4XJ.zLbPaSOrdaiEuT0DRVbO','student','student2.png',1,'2026-10-29 11:02:00'),(5,'Aditya Shinde','aditya.shinde@sanjivani.edu.in','$2y$10$0tyY5bKqLl8f3RVpH/ROFeJM.iAiL4XJ.zLbPaSOrdaiEuT0DRVbO','student','student3.png',1,'2026-10-29 11:02:00'),(6,'Pooja Gholap','pooja.gholap@sanjivani.edu.in','$2y$10$0tyY5bKqLl8f3RVpH/ROFeJM.iAiL4XJ.zLbPaSOrdaiEuT0DRVbO','student','student4.png',1,'2026-10-29 11:02:00'),(7,'Sanket Pawar','sanket.pawar@sanjivani.edu.in','$2y$10$0tyY5bKqLl8f3RVpH/ROFeJM.iAiL4XJ.zLbPaSOrdaiEuT0DRVbO','student','student5.png',1,'2026-10-29 11:02:00'),(8,'Tanvi Jadhav','tanvi.jadhav@sanjivani.edu.in','$2y$10$0tyY5bKqLl8f3RVpH/ROFeJM.iAiL4XJ.zLbPaSOrdaiEuT0DRVbO','student','student6.png',1,'2026-10-29 11:02:00'),(9,'Tejas Patil','tejas.patil@sanjivani.edu.in','$2y$10$0tyY5bKqLl8f3RVpH/ROFeJM.iAiL4XJ.zLbPaSOrdaiEuT0DRVbO','student','student7.png',1,'2026-10-29 11:02:00'),(10,'Rutuja Darekar','rutuja.darekar@sanjivani.edu.in','$2y$10$0tyY5bKqLl8f3RVpH/ROFeJM.iAiL4XJ.zLbPaSOrdaiEuT0DRVbO','student','student8.png',1,'2026-10-29 11:02:00'),(11,'Shubham More','shubham.more@sanjivani.edu.in','$2y$10$0tyY5bKqLl8f3RVpH/ROFeJM.iAiL4XJ.zLbPaSOrdaiEuT0DRVbO','student','student9.png',1,'2026-10-29 11:02:00'),(12,'Pratik Deshmukh','pratik.deshmukh@sanjivani.edu.in','$2y$10$0tyY5bKqLl8f3RVpH/ROFeJM.iAiL4XJ.zLbPaSOrdaiEuT0DRVbO','student','student10.png',1,'2026-10-29 11:02:00'),(13,'Priya Nair-Patil','priya.nair@sanjivani.edu.in','$2y$10$VuwK5OCgtQ3N894.eWCuSuTmD0Cx5vJxuieD23Tif6OWn8RCqvaaO','alumni','alumni1.png',1,'2026-10-29 11:02:00'),(14,'Arjun Reddy-Borse','arjun.reddy@sanjivani.edu.in','$2y$10$VuwK5OCgtQ3N894.eWCuSuTmD0Cx5vJxuieD23Tif6OWn8RCqvaaO','alumni','alumni2.png',1,'2026-10-29 11:02:00'),(15,'Dr. Nilesh Kulkarni','nilesh.kulkarni@sanjivani.edu.in','$2y$10$VuwK5OCgtQ3N894.eWCuSuTmD0Cx5vJxuieD23Tif6OWn8RCqvaaO','alumni','alumni3.png',1,'2026-10-29 11:02:00'),(16,'Manish Joshi','manish.joshi@sanjivani.edu.in','$2y$10$VuwK5OCgtQ3N894.eWCuSuTmD0Cx5vJxuieD23Tif6OWn8RCqvaaO','alumni','alumni4.png',1,'2026-10-29 11:02:00'),(17,'Divya Iyer-Sonawane','divya.iyer@sanjivani.edu.in','$2y$10$VuwK5OCgtQ3N894.eWCuSuTmD0Cx5vJxuieD23Tif6OWn8RCqvaaO','alumni','alumni5.png',1,'2026-10-29 11:02:00');
/*!40000 ALTER TABLE `users` ENABLE KEYS */;
UNLOCK TABLES;
/*!40103 SET TIME_ZONE=@OLD_TIME_ZONE */;

/*!40101 SET SQL_MODE=@OLD_SQL_MODE */;
/*!40014 SET FOREIGN_KEY_CHECKS=@OLD_FOREIGN_KEY_CHECKS */;
/*!40014 SET UNIQUE_CHECKS=@OLD_UNIQUE_CHECKS */;
/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
/*!40111 SET SQL_NOTES=@OLD_SQL_NOTES */;

-- Dump completed on 2026-10-29 16:54:37
