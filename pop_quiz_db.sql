-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: Feb 12, 2026 at 11:50 AM
-- Server version: 10.4.32-MariaDB
-- PHP Version: 8.2.12

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Database: `pop_quiz_db`
--

-- --------------------------------------------------------

--
-- Table structure for table `active_sessions`
--

CREATE TABLE `active_sessions` (
  `id` int(11) NOT NULL,
  `student_number` varchar(50) NOT NULL,
  `session_token` varchar(255) NOT NULL,
  `user_agent` text DEFAULT NULL,
  `ip_address` varchar(45) DEFAULT NULL,
  `created_at` datetime DEFAULT current_timestamp(),
  `last_activity` datetime DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `answers`
--

CREATE TABLE `answers` (
  `id` int(11) NOT NULL,
  `student_number` varchar(50) NOT NULL,
  `question_id` int(11) NOT NULL,
  `selected_answer` enum('a','b','c','d') DEFAULT NULL,
  `submitted_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `attendance`
--

CREATE TABLE `attendance` (
  `id` int(11) NOT NULL,
  `student_number` varchar(50) NOT NULL,
  `quiz_id` int(11) NOT NULL,
  `status` enum('PRESENT','LATE','ABSENT') DEFAULT 'ABSENT',
  `time_in` time DEFAULT NULL,
  `date` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `daily_attendance`
--

CREATE TABLE `daily_attendance` (
  `id` int(11) NOT NULL,
  `student_number` varchar(50) NOT NULL,
  `attendance_date` date NOT NULL,
  `time_in` time NOT NULL,
  `time_out` time DEFAULT NULL,
  `status` enum('PRESENT','LATE','ABSENT') DEFAULT 'ABSENT',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `device_fingerprint` text DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `questions`
--

CREATE TABLE `questions` (
  `id` int(11) NOT NULL,
  `question_number` int(11) DEFAULT NULL,
  `unlock_time` time DEFAULT NULL,
  `quiz_id` int(11) NOT NULL,
  `question` text NOT NULL,
  `option_a` varchar(255) NOT NULL,
  `option_b` varchar(255) NOT NULL,
  `option_c` varchar(255) NOT NULL,
  `option_d` varchar(255) NOT NULL,
  `correct_answer` enum('a','b','c','d') NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `questions`
--

INSERT INTO `questions` (`id`, `question_number`, `unlock_time`, `quiz_id`, `question`, `option_a`, `option_b`, `option_c`, `option_d`, `correct_answer`) VALUES
(17, 1, '08:30:00', 6, 'Which HTML tag is used to create a hyperlink?', '<link>', '<a>', '<href>', '<url>', 'b'),
(18, 2, '09:00:00', 6, 'What does CSS stand for?', 'Computer Style Sheet', 'Creative Style System', 'Cascading Style Sheets', 'Colorful Style Sheets', 'c'),
(19, 3, '09:30:00', 6, 'Which language is mainly used to make a webpage interactive?', 'HTML', 'CSS', 'JavaScript', 'PHP', 'c'),
(20, 4, '10:00:00', 6, 'What device is used to connect multiple computers in a local network?', 'Router', 'Switch', 'Modem', 'Firewall', 'b'),
(21, 5, '10:30:00', 6, 'What does IP stand for?', 'Internet Program', 'Internet Protocol', 'Internal Process', 'Information Provider', 'b'),
(22, 6, '11:00:00', 6, 'Which cable is commonly used for Ethernet networking?', 'Coaxial cable', 'Fiber optic cable', 'Twisted pair cable', 'Power cable', 'c'),
(23, 7, '11:30:00', 6, 'Which symbol is used to end a statement in C?', ':', '.', ';', ',', 'c'),
(24, 8, '12:00:00', 6, 'Which function is used to display output in C?', 'print()', 'printf()', 'display()', 'output()', 'b'),
(25, 9, '13:30:00', 6, 'What is the correct file extension for C programs?', '.cp', '.c', '.cpp', '.exe', 'b'),
(26, 10, '14:00:00', 6, 'Which of the following is NOT a multimedia element?', 'Text', 'Audio', 'Video', 'Calculator', 'd'),
(27, 11, '14:30:00', 6, 'Which file format is commonly used for images?', '.pm3', '.pm4', '.jpg', '.exe', 'c'),
(28, 12, '15:00:00', 6, 'Which compression type permanently removes some data to reduce file size?', 'Lossless', 'Raw', 'Lossy', 'Vector', 'c'),
(29, 13, '15:30:00', 6, 'What is the output of an AND gate when inputs are 1 and 0?', '1', '0', 'Undefined', 'TRUE', 'b'),
(30, 14, '16:00:00', 6, 'Which Android component is used to display a single screen with a user interface?', 'Service', 'Broadcast Receiver', 'Activity', 'Content Provider', 'c'),
(31, 15, '16:30:00', 6, 'Which HTTP status code means “Unauthorized”?', '200 Error', '301 Error', '401 Error', '500 Error', 'c'),
(32, 16, '17:00:00', 6, 'Which architecture pattern is recommended by Google for Android apps?', 'MVC', 'MVP', 'MVVM', 'Monolithic', 'c'),
(33, 17, '17:30:00', 6, 'Which logic gate is called a universal gate?', 'AND', 'OR', 'XOR', 'NAND', 'd'),
(34, 18, '17:00:00', 6, 'What is the hexadecimal equivalent of binary 1111?', 'E', 'F', '10', '15', 'b');

-- --------------------------------------------------------

--
-- Table structure for table `quizzes`
--

CREATE TABLE `quizzes` (
  `id` int(11) NOT NULL,
  `title` varchar(255) NOT NULL,
  `schedule_date` date DEFAULT NULL,
  `time_limit` int(11) DEFAULT 0,
  `status` enum('ON','OFF') DEFAULT 'ON',
  `created_by` int(11) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `quizzes`
--

INSERT INTO `quizzes` (`id`, `title`, `schedule_date`, `time_limit`, `status`, `created_by`, `created_at`) VALUES
(6, 'DAY 1 - IT DAY\'S', '2026-03-04', 0, 'OFF', 1, '2026-02-02 00:25:31'),
(7, 'DAY 2 - IT DAY\'S', '2026-03-05', 0, 'OFF', 1, '2026-02-12 04:59:46'),
(8, 'DAY 3 - IT DAY\'S', '2026-03-06', 0, 'OFF', 1, '2026-02-12 05:00:14');

-- --------------------------------------------------------

--
-- Table structure for table `results`
--

CREATE TABLE `results` (
  `id` int(11) NOT NULL,
  `student_number` varchar(50) NOT NULL,
  `quiz_id` int(11) NOT NULL,
  `score` int(11) NOT NULL,
  `completion_time` time NOT NULL,
  `submitted_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `students`
--

CREATE TABLE `students` (
  `id` int(11) NOT NULL,
  `student_number` varchar(50) NOT NULL,
  `fname` varchar(100) NOT NULL,
  `lname` varchar(100) NOT NULL,
  `mname` varchar(100) DEFAULT NULL,
  `section` varchar(50) NOT NULL,
  `year_level` varchar(20) NOT NULL,
  `department` varchar(100) NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `students`
--

INSERT INTO `students` (`id`, `student_number`, `fname`, `lname`, `mname`, `section`, `year_level`, `department`, `created_at`) VALUES
(2699, '2019-0724', 'Desel', 'Boltron', 'Abello', '1E', '1st Year', 'BSIT', '2026-02-12 08:37:13'),
(2700, '2021-1172', 'Christian Neil', 'Pacilan', 'Batuhan', '2SE', '2nd Year', 'BSIT', '2026-02-12 08:37:13'),
(2701, '2023-0016', 'Rey Angelo', 'Aguirre', 'Abello', '2N', '2nd Year', 'BSIT', '2026-02-12 08:37:13'),
(2702, '2023-0484', 'Alsan Jay', 'Necesario', '', '2NE', '2nd Year', 'BSIT', '2026-02-12 08:37:13'),
(2703, '2023-0736', 'Reymart', 'Apawan', 'Hermoso', '2NE', '2nd Year', 'BSIT', '2026-02-12 08:37:13'),
(2704, '2023-0879', 'Aljon', 'Apawan', 'Villavito', '1N', '1st Year', 'BSIT', '2026-02-12 08:37:13'),
(2705, '2023-1293', 'BRYAN', 'DESCARTIN', 'DESPI', '3-SOUTHEAST', '3rd Year', 'BSIT', '2026-02-12 08:37:13'),
(2706, '2023-1304', 'John Paul', 'Batinda-an', 'Chavez', '2N', '2nd Year', 'BSIT', '2026-02-12 08:37:13'),
(2707, '2023-1338', 'ARNIEL', 'DELA PEÑA JR.', 'MANSUETO', '3-SOUTHEAST', '3rd Year', 'BSIT', '2026-02-12 08:37:13'),
(2708, '2023-1361', 'Raphael Jovanne', 'Caranzo', 'Alota', '2N', '2nd Year', 'BSIT', '2026-02-12 08:37:13'),
(2709, '2023-1374', 'John Ryan', 'Bacus', '', '2SE', '2nd Year', 'BSIT', '2026-02-12 08:37:13'),
(2710, '2023-1577', 'John Paul', 'Giducos', 'None', '2E', '2nd Year', 'BSIT', '2026-02-12 08:37:13'),
(2711, '2023-1593', 'Marlon James', 'Tinga', 'Locaylocay', '2NE', '2nd Year', 'BSIT', '2026-02-12 08:37:13'),
(2712, '2024-0026', 'Kane Harold', 'Garcia', 'Bayon-on', '2E', '2nd Year', 'BSIT', '2026-02-12 08:37:13'),
(2713, '2024-0027', 'Nazareno Jr.', 'Initan', 'Lasala', '2E', '2nd Year', 'BSIT', '2026-02-12 08:37:13'),
(2714, '2024-0034', 'Christian Bryle', 'Bueno', 'Aloyan', '2E', '2nd Year', 'BSIT', '2026-02-12 08:37:13'),
(2715, '2024-0044', 'Isandro', 'Batiancila', 'Escaran', '2E', '2nd Year', 'BSIT', '2026-02-12 08:37:13'),
(2716, '2024-0059', 'Wena', 'Hortelano', 'Ilustrisimo', '2S', '2nd Year', 'BSIT', '2026-02-12 08:37:13'),
(2717, '2024-0060', 'Bianca Nadine', 'Batiancila', 'Almodiel', '2W', '2nd Year', 'BSIT', '2026-02-12 08:37:13'),
(2718, '2024-0063', 'Anthonette Jane', 'Escaran', 'Batiancila', '2S', '2nd Year', 'BSIT', '2026-02-12 08:37:13'),
(2719, '2024-0064', 'Angelica', 'Batiancila', 'Mebato', '2W', '2nd Year', 'BSIT', '2026-02-12 08:37:13'),
(2720, '2024-0068', 'Karrel', 'Despabeladero', 'Catalu�?', '2S', '2nd Year', 'BSIT', '2026-02-12 08:37:13'),
(2721, '2024-0069', 'Myrnadel', 'Espina', 'Villarosa', '2S', '2nd Year', 'BSIT', '2026-02-12 08:37:13'),
(2722, '2024-0072', 'Japril', 'Mahilum', 'Cahutay', '2N', '2nd Year', 'BSIT', '2026-02-12 08:37:13'),
(2723, '2024-0075', 'Marny', 'Almuhallas', 'Rebusit', '2W', '2nd Year', 'BSIT', '2026-02-12 08:37:13'),
(2724, '2024-0079', 'Khent', 'Ompad', 'Maspara', '2W', '2nd Year', 'BSIT', '2026-02-12 08:37:13'),
(2725, '2024-0080', 'Jana Me', 'Ilustrisimo', 'Doble', '2W', '2nd Year', 'BSIT', '2026-02-12 08:37:13'),
(2726, '2024-0081', 'Jene Rose', 'Almohallas', 'Rebusit', '2W', '2nd Year', 'BSIT', '2026-02-12 08:37:13'),
(2727, '2024-0085', 'Mark Joseph', 'Casas', 'Aloba', '2W', '2nd Year', 'BSIT', '2026-02-12 08:37:13'),
(2728, '2024-0088', 'Rhea Jean', 'Umbao', 'Alolor', '2E', '2nd Year', 'BSIT', '2026-02-12 08:37:13'),
(2729, '2024-0089', 'Jeanel', 'Batarilan', 'Villanueva', '2W', '2nd Year', 'BSIT', '2026-02-12 08:37:13'),
(2730, '2024-0092', 'Julius', 'Monicillo', 'Noya', '2E', '2nd Year', 'BSIT', '2026-02-12 08:37:13'),
(2731, '2024-0116', 'John Paul', 'Espliguera', 'Lofranco', '2E', '2nd Year', 'BSIT', '2026-02-12 08:37:13'),
(2732, '2024-0120', 'John Dave', 'Batictic', 'Villacarlos', '2E', '2nd Year', 'BSIT', '2026-02-12 08:37:13'),
(2733, '2024-0121', 'Sherwin', 'Batictic', 'Rosales', '2E', '2nd Year', 'BSIT', '2026-02-12 08:37:13'),
(2734, '2024-0123', 'Janjan', 'Loredo', 'Israel', '2E', '2nd Year', 'BSIT', '2026-02-12 08:37:13'),
(2735, '2024-0170', 'Saturnina', 'Paragsa', 'Veliganio', '2N', '2nd Year', 'BSIT', '2026-02-12 08:37:13'),
(2736, '2024-0171', 'Novee', 'Batiancila', 'Jarina', '2N', '2nd Year', 'BSIT', '2026-02-12 08:37:13'),
(2737, '2024-0174', 'Emmanuel', 'Aniban', 'Caballero', '2W', '2nd Year', 'BSIT', '2026-02-12 08:37:13'),
(2738, '2024-0175', 'Christine', 'Ceniza', 'Enriquez', '2N', '2nd Year', 'BSIT', '2026-02-12 08:37:13'),
(2739, '2024-0178', 'Ma. Therese', 'Sedurifa', 'Esponilla', '2N', '2nd Year', 'BSIT', '2026-02-12 08:37:13'),
(2740, '2024-0179', 'Aisha Kay', 'Tayactac', 'Gelisanga', '2N', '2nd Year', 'BSIT', '2026-02-12 08:37:13'),
(2741, '2024-0190', 'Edrhond', 'Flores', 'Gilbuena', '2W', '2nd Year', 'BSIT', '2026-02-12 08:37:13'),
(2742, '2024-0191', 'James Anthony', 'Dela Cruz', 'Rodriguez', '2E', '2nd Year', 'BSIT', '2026-02-12 08:37:13'),
(2743, '2024-0199', 'Ni���', 'Cordova', 'Tayo', '2E', '2nd Year', 'BSIT', '2026-02-12 08:37:13'),
(2744, '2024-0210', 'Jasmine', 'Dela Pe�', 'Desamparado', '2E', '2nd Year', 'BSIT', '2026-02-12 08:37:13'),
(2745, '2024-0223', 'John Dareal', 'Da�', 'Ompad', '2N', '2nd Year', 'BSIT', '2026-02-12 08:37:13'),
(2746, '2024-0247', 'John Peterson', 'Tibay', 'Bawiin', '2W', '2nd Year', 'BSIT', '2026-02-12 08:37:13'),
(2747, '2024-0259', 'Erra Mae', 'Villabrille', 'Segovia', '2E', '2nd Year', 'BSIT', '2026-02-12 08:37:13'),
(2748, '2024-0286', 'Ronel', 'Lilo-an', 'Yba�?', '2W', '2nd Year', 'BSIT', '2026-02-12 08:37:13'),
(2749, '2024-0287', 'Dhimae Jane', 'Mansueto', 'Monta�', '2W', '2nd Year', 'BSIT', '2026-02-12 08:37:13'),
(2750, '2024-0295', 'Mheri Ninrea', 'Villacampa', 'Valendez', '2W', '2nd Year', 'BSIT', '2026-02-12 08:37:13'),
(2751, '2024-0297', 'Jenemie', 'Magdasal', 'Gimenez', '2W', '2nd Year', 'BSIT', '2026-02-12 08:37:13'),
(2752, '2024-0298', 'Cristina', 'Lawan', 'Daruca', '2W', '2nd Year', 'BSIT', '2026-02-12 08:37:13'),
(2753, '2024-0313', 'Verniel', 'Negre', 'Dela Pe�', '2W', '2nd Year', 'BSIT', '2026-02-12 08:37:13'),
(2754, '2024-0314', 'Angel', 'Maru', 'Cabasag', '2E', '2nd Year', 'BSIT', '2026-02-12 08:37:13'),
(2755, '2024-0322', 'Jesser', 'Forrosuelo', 'Yba�?', '2W', '2nd Year', 'BSIT', '2026-02-12 08:37:13'),
(2756, '2024-0323', 'Marjo', 'Andales', 'Esgana', '2W', '2nd Year', 'BSIT', '2026-02-12 08:37:13'),
(2757, '2024-0324', 'Brian', 'Lasala', 'Espinosa', '2W', '2nd Year', 'BSIT', '2026-02-12 08:37:13'),
(2758, '2024-0337', 'Ivy Mae', 'Villabrille', 'Anciano', '2E', '2nd Year', 'BSIT', '2026-02-12 08:37:13'),
(2759, '2024-0338', 'Neonel', 'Anciano', 'Velez', '2E', '2nd Year', 'BSIT', '2026-02-12 08:37:13'),
(2760, '2024-0339', 'Dexter', 'Monterola', 'Demoral', '2W', '2nd Year', 'BSIT', '2026-02-12 08:37:13'),
(2761, '2024-0341', 'James', 'Batolbatol', 'Ilustrisimo', '2W', '2nd Year', 'BSIT', '2026-02-12 08:37:13'),
(2762, '2024-0344', 'Shelou Mae', 'Layague', 'Escarro', '2E', '2nd Year', 'BSIT', '2026-02-12 08:37:13'),
(2763, '2024-0345', 'Jeslet Jay', 'Gahira', 'Tumabiene', '2W', '2nd Year', 'BSIT', '2026-02-12 08:37:13'),
(2764, '2024-0362', 'Elvera Jane', 'Ladi�', 'Taytayan', '2W', '2nd Year', 'BSIT', '2026-02-12 08:37:13'),
(2765, '2024-0378', 'Reymart', 'Detrago', 'None', '2W', '2nd Year', 'BSIT', '2026-02-12 08:37:13'),
(2766, '2024-0379', 'Clifford Mak', 'Aniban', '', '2W', '2nd Year', 'BSIT', '2026-02-12 08:37:13'),
(2767, '2024-0380', 'Roldan', 'Ducay', 'Bautro', '2E', '2nd Year', 'BSIT', '2026-02-12 08:37:13'),
(2768, '2024-0388', 'Miko', 'Orbeta', 'Espinosa', '2S', '2nd Year', 'BSIT', '2026-02-12 08:37:13'),
(2769, '2024-0389', 'Mark Vincent', 'Fariolen', 'Marabi', '2E', '2nd Year', 'BSIT', '2026-02-12 08:37:13'),
(2770, '2024-0390', 'Erwin', 'Mahipos', 'Milo', '2E', '2nd Year', 'BSIT', '2026-02-12 08:37:13'),
(2771, '2024-0414', 'Ashley', 'Legaspino', 'Capuras', '2SE', '2nd Year', 'BSIT', '2026-02-12 08:37:13'),
(2772, '2024-0417', 'Junjie', 'Mata', 'None', '2NE', '2nd Year', 'BSIT', '2026-02-12 08:37:13'),
(2773, '2024-0418', 'Jun Ferd', 'Mantal', 'Abello', '2W', '2nd Year', 'BSIT', '2026-02-12 08:37:13'),
(2774, '2024-0421', 'Troy', 'Villaruel', 'Paragsa', '2W', '2nd Year', 'BSIT', '2026-02-12 08:37:13'),
(2775, '2024-0422', 'Prince Jacob', 'Quijano', 'Apawan', '2W', '2nd Year', 'BSIT', '2026-02-12 08:37:13'),
(2776, '2024-0424', 'Wiljohn', 'Jumantoc', 'Delapieza', '2E', '2nd Year', 'BSIT', '2026-02-12 08:37:13'),
(2777, '2024-0431', 'John Carlo', 'Tayo', 'Pere', '2W', '2nd Year', 'BSIT', '2026-02-12 08:37:13'),
(2778, '2024-0436', 'Christelyn', 'Jimenez', 'Escote', '2E', '2nd Year', 'BSIT', '2026-02-12 08:37:13'),
(2779, '2024-0438', 'Jimuel', 'Mansueto', 'Villarino', '2NW', '2nd Year', 'BSIT', '2026-02-12 08:37:13'),
(2780, '2024-0449', 'Rolando Jr.', 'Tibay', 'Aloba', '2E', '2nd Year', 'BSIT', '2026-02-12 08:37:13'),
(2781, '2024-0450', 'John Clierk', 'Escaran', 'Cuambot', '2W', '2nd Year', 'BSIT', '2026-02-12 08:37:13'),
(2782, '2024-0451', 'Angelica', 'Bawi-in', 'Sarabia', '2W', '2nd Year', 'BSIT', '2026-02-12 08:37:13'),
(2783, '2024-0452', 'Rene Boy Jr', 'Sayson', 'Martus', '2W', '2nd Year', 'BSIT', '2026-02-12 08:37:13'),
(2784, '2024-0458', 'Dexter', 'Almohallas', 'Quezon', '2W', '2nd Year', 'BSIT', '2026-02-12 08:37:13'),
(2785, '2024-0461', 'Joy Marie', 'Yba�?', 'Esgana', '2E', '2nd Year', 'BSIT', '2026-02-12 08:37:13'),
(2786, '2024-0463', 'Milka', 'Alo', 'Yordan', '2E', '2nd Year', 'BSIT', '2026-02-12 08:37:13'),
(2787, '2024-0464', 'Julia', 'Yba�?', 'Esgana', '2NE', '2nd Year', 'BSIT', '2026-02-12 08:37:13'),
(2788, '2024-0469', 'Kiesha Marie', 'Caratao', 'Yhapon', '2N', '2nd Year', 'BSIT', '2026-02-12 08:37:13'),
(2789, '2024-0471', 'Catherine', 'Villacarlos', 'Negre', '2E', '2nd Year', 'BSIT', '2026-02-12 08:37:13'),
(2790, '2024-0473', 'Arjae', 'Calleno', 'Mata', '2W', '2nd Year', 'BSIT', '2026-02-12 08:37:13'),
(2791, '2024-0488', 'Edlyn', 'Cueva', 'Arriesgado', '2E', '2nd Year', 'BSIT', '2026-02-12 08:37:13'),
(2792, '2024-0498', 'Cj', 'Casas', 'None', '2NE', '2nd Year', 'BSIT', '2026-02-12 08:37:13'),
(2793, '2024-0499', 'Reymart', 'Placencia', 'Cueva', '2NE', '2nd Year', 'BSIT', '2026-02-12 08:37:13'),
(2794, '2024-0516', 'Prince Adam', 'Jumanguin', 'Ducay', '2S', '2nd Year', 'BSIT', '2026-02-12 08:37:13'),
(2795, '2024-0519', 'Exiel', 'Cahutay', 'Locaylocay', '2E', '2nd Year', 'BSIT', '2026-02-12 08:37:13'),
(2796, '2024-0538', 'Christian John', 'Chavez', 'Cajetas', '2NE', '2nd Year', 'BSIT', '2026-02-12 08:37:13'),
(2797, '2024-0554', 'Jude Mark', 'Sarzuelo', 'Rivera', '2NE', '2nd Year', 'BSIT', '2026-02-12 08:37:13'),
(2798, '2024-0557', 'Keybert', 'Supetran', 'Despi', '2NE', '2nd Year', 'BSIT', '2026-02-12 08:37:13'),
(2799, '2024-0558', 'Emmanuel', 'Honofre', 'Dawa', '2NE', '2nd Year', 'BSIT', '2026-02-12 08:37:13'),
(2800, '2024-0561', 'Kentrobert', 'Supetran', 'Despi', '2NE', '2nd Year', 'BSIT', '2026-02-12 08:37:13'),
(2801, '2024-0573', 'John Manuel', 'Paraguire', 'Estremera', '2N', '2nd Year', 'BSIT', '2026-02-12 08:37:13'),
(2802, '2024-0574', 'Lemuel', 'Sayson', 'Vargas', '2S', '2nd Year', 'BSIT', '2026-02-12 08:37:13'),
(2803, '2024-0587', 'Christian', 'Ilustrisimo', '', '2S', '2nd Year', 'BSIT', '2026-02-12 08:37:13'),
(2804, '2024-0592', 'Lucille', 'Maru', 'Villaceran', '2S', '2nd Year', 'BSIT', '2026-02-12 08:37:13'),
(2805, '2024-0600', 'Jade Lloyd', 'Gonzaga', 'Villacarlos', '2E', '2nd Year', 'BSIT', '2026-02-12 08:37:13'),
(2806, '2024-0608', 'Ni�', 'Ilustrisimo', 'Cahutay', '2NE', '2nd Year', 'BSIT', '2026-02-12 08:37:13'),
(2807, '2024-0622', 'Immaculate', 'Del Castillo', 'Jabido', '2NE', '2nd Year', 'BSIT', '2026-02-12 08:37:13'),
(2808, '2024-0623', 'Jerry', 'Tayad', 'Illustrisimo', '2S', '2nd Year', 'BSIT', '2026-02-12 08:37:13'),
(2809, '2024-0629', 'Adelene', 'Melitante', 'Villaceran', '2S', '2nd Year', 'BSIT', '2026-02-12 08:37:13'),
(2810, '2024-0633', 'Trixia May', 'Ungon', 'Oflas', '2S', '2nd Year', 'BSIT', '2026-02-12 08:37:13'),
(2811, '2024-0634', 'April Anne', 'Rosatace', 'Ungon', '2NE', '2nd Year', 'BSIT', '2026-02-12 08:37:13'),
(2812, '2024-0647', 'Klyde Care', 'Batiancila', 'Marabi', '2NE', '2nd Year', 'BSIT', '2026-02-12 08:37:13'),
(2813, '2024-0661', 'Erone', 'Fernadez', 'Desucatan', '2E', '2nd Year', 'BSIT', '2026-02-12 08:37:13'),
(2814, '2024-0663', 'Kenneth Kyle', 'Jangzon', 'Ofianga', '2S', '2nd Year', 'BSIT', '2026-02-12 08:37:13'),
(2815, '2024-0666', 'Ella Mae', 'Santiago', 'Jangzon', '2S', '2nd Year', 'BSIT', '2026-02-12 08:37:13'),
(2816, '2024-0667', 'Archie', 'Biatinggo', 'Fernandez', '2E', '2nd Year', 'BSIT', '2026-02-12 08:37:13'),
(2817, '2024-0691', 'Keren', 'Perolino', 'Jusayan', '2N', '2nd Year', 'BSIT', '2026-02-12 08:37:13'),
(2818, '2024-0692', 'Zea Margaret', 'Rayco', 'Garcia', '2N', '2nd Year', 'BSIT', '2026-02-12 08:37:13'),
(2819, '2024-0693', 'Joshua', 'Veliganio', 'Bawasanta', '2N', '2nd Year', 'BSIT', '2026-02-12 08:37:13'),
(2820, '2024-0703', 'Joshua', 'Silva', 'Camay', '2N', '2nd Year', 'BSIT', '2026-02-12 08:37:13'),
(2821, '2024-0715', 'Emjay', 'Solitario', 'Villacarlos', '2S', '2nd Year', 'BSIT', '2026-02-12 08:37:13'),
(2822, '2024-0719', 'James', 'Villacrucis', 'Villaceran', '2E', '2nd Year', 'BSIT', '2026-02-12 08:37:13'),
(2823, '2024-0720', 'Julius', 'Hingoyon', 'Mata', '2S', '2nd Year', 'BSIT', '2026-02-12 08:37:13'),
(2824, '2024-0721', 'Archie', 'Aniban', 'Caspe', '2S', '2nd Year', 'BSIT', '2026-02-12 08:37:13'),
(2825, '2024-0722', 'Ricklen Mark', 'Cena', 'Dela Pe�?', '2S', '2nd Year', 'BSIT', '2026-02-12 08:37:13'),
(2826, '2024-0724', 'Rodelyn Kim', 'Mahilum', '', '2NE', '2nd Year', 'BSIT', '2026-02-12 08:37:13'),
(2827, '2024-0727', 'Zoe Leigh', 'Balila', 'Gimenez', '2N', '2nd Year', 'BSIT', '2026-02-12 08:37:13'),
(2828, '2024-0730', 'Garrel', 'Maspara', 'Sevilleno', '2N', '2nd Year', 'BSIT', '2026-02-12 08:37:13'),
(2829, '2024-0734', 'Earl Laurence', 'Estrada', 'Vergara', '2S', '2nd Year', 'BSIT', '2026-02-12 08:37:13'),
(2830, '2024-0737', 'Noe', 'Alolod', 'Baterzal', '2N', '2nd Year', 'BSIT', '2026-02-12 08:37:13'),
(2831, '2024-0738', 'Angelo', 'Ilustrisimo', 'Desierto', '2S', '2nd Year', 'BSIT', '2026-02-12 08:37:13'),
(2832, '2024-0739', 'Jocelyn', 'Giducos', 'Almocera', '2NE', '2nd Year', 'BSIT', '2026-02-12 08:37:13'),
(2833, '2024-0740', 'Romel', 'Hinampas', 'Tidoso', '2S', '2nd Year', 'BSIT', '2026-02-12 08:37:13'),
(2834, '2024-0742', 'Ryan', 'Tayo', 'Labaho', '2N', '2nd Year', 'BSIT', '2026-02-12 08:37:13'),
(2835, '2024-0743', 'Dorryl Jade', 'Maguad', 'Espina', '2N', '2nd Year', 'BSIT', '2026-02-12 08:37:13'),
(2836, '2024-0747', 'Jeneva', 'Gilbuena', 'Illut', '2N', '2nd Year', 'BSIT', '2026-02-12 08:37:13'),
(2837, '2024-0749', 'Marlon Jr.', 'Dellera', 'Singuran', '2N', '2nd Year', 'BSIT', '2026-02-12 08:37:13'),
(2838, '2024-0750', 'Mark James', 'Avenido', 'Alob', '2N', '2nd Year', 'BSIT', '2026-02-12 08:37:13'),
(2839, '2024-0751', 'Myco', 'Babaylo', 'Mahilum', '2N', '2nd Year', 'BSIT', '2026-02-12 08:37:13'),
(2840, '2024-0762', 'Brian Earl', 'Almocera', '', '2S', '2nd Year', 'BSIT', '2026-02-12 08:37:13'),
(2841, '2024-0763', 'Gian Carlo', 'Sabaiton', 'Gilbuena', '2S', '2nd Year', 'BSIT', '2026-02-12 08:37:13'),
(2842, '2024-0765', 'Jerry', 'Pacinio', 'Seas', '2S', '2nd Year', 'BSIT', '2026-02-12 08:37:13'),
(2843, '2024-0766', 'Jade Kaizen', 'Batarilan', 'Briones', '2S', '2nd Year', 'BSIT', '2026-02-12 08:37:13'),
(2844, '2024-0769', 'Rubelyn', 'Sayam', 'Villaester', '2S', '2nd Year', 'BSIT', '2026-02-12 08:37:13'),
(2845, '2024-0770', 'Princess Jean', 'Villacampa', 'Batarilan', '2NE', '2nd Year', 'BSIT', '2026-02-12 08:37:13'),
(2846, '2024-0771', 'Razel Mae', 'Sedurifa', 'Ysulan', '2S', '2nd Year', 'BSIT', '2026-02-12 08:37:13'),
(2847, '2024-0773', 'Kenneth', 'Fariolen', 'Hijapon', '2NE', '2nd Year', 'BSIT', '2026-02-12 08:37:13'),
(2848, '2024-0774', 'Charisse Ira', 'Mahipos', 'Ilustrisimo', '2S', '2nd Year', 'BSIT', '2026-02-12 08:37:13'),
(2849, '2024-0775', 'Mark Aljade', 'Tibay', 'Entrampas', '2N', '2nd Year', 'BSIT', '2026-02-12 08:37:13'),
(2850, '2024-0779', 'Justine', 'Batuhan', 'Loar', '2E', '2nd Year', 'BSIT', '2026-02-12 08:37:13'),
(2851, '2024-0789', 'John Gregory', 'Bacolod', 'Ni�', '2S', '2nd Year', 'BSIT', '2026-02-12 08:37:13'),
(2852, '2024-0794', 'John Loyd', 'Pacinio', 'Turbanos', '2E', '2nd Year', 'BSIT', '2026-02-12 08:37:13'),
(2853, '2024-0795', 'Ronnar', 'Aguisanda', '', '2S', '2nd Year', 'BSIT', '2026-02-12 08:37:13'),
(2854, '2024-0796', 'Dheo John', 'Santillan', 'Bardinas', '2S', '2nd Year', 'BSIT', '2026-02-12 08:37:13'),
(2855, '2024-0797', 'Prince Jethro', 'Sumogat', 'Mingo', '2S', '2nd Year', 'BSIT', '2026-02-12 08:37:13'),
(2856, '2024-0800', 'Jeann', 'Mendoza', 'Lobeta�?', '2N', '2nd Year', 'BSIT', '2026-02-12 08:37:13'),
(2857, '2024-0801', 'Yhasmia', 'Layaog', 'Ilustrisimo', '2N', '2nd Year', 'BSIT', '2026-02-12 08:37:13'),
(2858, '2024-0815', 'Vincent', 'Marabi', 'Bacolod', '2E', '2nd Year', 'BSIT', '2026-02-12 08:37:13'),
(2859, '2024-0818', 'Allanie Marie', 'Destacamento', 'Aloyan', '2S', '2nd Year', 'BSIT', '2026-02-12 08:37:13'),
(2860, '2024-0819', 'Melo', 'Paragsa', '', '2N', '2nd Year', 'BSIT', '2026-02-12 08:37:13'),
(2861, '2024-0828', 'Bhenz', 'Mansueto', 'Dondon', '2E', '2nd Year', 'BSIT', '2026-02-12 08:37:13'),
(2862, '2024-0838', 'Darlyn May', 'Benignos', 'Villadolid', '2NE', '2nd Year', 'BSIT', '2026-02-12 08:37:13'),
(2863, '2024-0841', 'Joel', 'Ga', 'Batoy', '2E', '2nd Year', 'BSIT', '2026-02-12 08:37:13'),
(2864, '2024-0842', 'Oliver', 'Gidayawan', 'Pasasadaba', '2S', '2nd Year', 'BSIT', '2026-02-12 08:37:13'),
(2865, '2024-0843', 'Deansgamaliel', 'Mangubat', '', '2N', '2nd Year', 'BSIT', '2026-02-12 08:37:13'),
(2866, '2024-0845', 'Roel', 'Pastetio', 'Despi', '2E', '2nd Year', 'BSIT', '2026-02-12 08:37:13'),
(2867, '2024-0872', 'Jenrie', 'Ursal', 'Delos Reyes', '2E', '2nd Year', 'BSIT', '2026-02-12 08:37:13'),
(2868, '2024-0873', 'Kenneth', 'Rosales', 'Despi', '2N', '2nd Year', 'BSIT', '2026-02-12 08:37:13'),
(2869, '2024-0874', 'Orlando Jr.', 'Lauta', 'Magallanes', '2E', '2nd Year', 'BSIT', '2026-02-12 08:37:13'),
(2870, '2024-0888', 'Joan', 'Carabio', 'Veliganio', '2N', '2nd Year', 'BSIT', '2026-02-12 08:37:13'),
(2871, '2024-0890', 'Rachelle Kaye', 'Espinosa', 'Bausin', '2NE', '2nd Year', 'BSIT', '2026-02-12 08:37:13'),
(2872, '2024-0891', 'Len Len', 'Dillo', 'Loar', '2NE', '2nd Year', 'BSIT', '2026-02-12 08:37:13'),
(2873, '2024-0893', 'Raymart', 'Escaran', 'Godienes', '2N', '2nd Year', 'BSIT', '2026-02-12 08:37:13'),
(2874, '2024-0894', 'John Cyrus', 'Pescante', '', '2S', '2nd Year', 'BSIT', '2026-02-12 08:37:13'),
(2875, '2024-0895', 'Danilo', 'Ilustrisimo', 'Esgana', '2S', '2nd Year', 'BSIT', '2026-02-12 08:37:13'),
(2876, '2024-0896', 'Robin', 'Esgana', 'Zaspa', '2N', '2nd Year', 'BSIT', '2026-02-12 08:37:13'),
(2877, '2024-0897', 'Charles', 'Veliganilao', 'Batuhan', '2S', '2nd Year', 'BSIT', '2026-02-12 08:37:13'),
(2878, '2024-0904', 'John William', 'Cahutay', 'Jimenez', '2E', '2nd Year', 'BSIT', '2026-02-12 08:37:13'),
(2879, '2024-0906', 'Angelou', 'Escala', 'Mata', '2E', '2nd Year', 'BSIT', '2026-02-12 08:37:13'),
(2880, '2024-0911', 'Janus Keirk', 'Mahilum', 'Lacson', '2N', '2nd Year', 'BSIT', '2026-02-12 08:37:13'),
(2881, '2024-0912', 'Bryan', 'Galvan', '', '2N', '2nd Year', 'BSIT', '2026-02-12 08:37:13'),
(2882, '2024-0914', 'Gerry Salvi', 'Calunod', 'Gista', '2N', '2nd Year', 'BSIT', '2026-02-12 08:37:13'),
(2883, '2024-0919', 'Bea', 'Bautro', 'Getruelas', '2S', '2nd Year', 'BSIT', '2026-02-12 08:37:13'),
(2884, '2024-0935', 'Vernie', 'Esgana', 'Almocera', '2N', '2nd Year', 'BSIT', '2026-02-12 08:37:13'),
(2885, '2024-0955', 'Arabella', 'Villadolid', 'Quiamco', '2S', '2nd Year', 'BSIT', '2026-02-12 08:37:13'),
(2886, '2024-0956', 'Leibniz', 'Cueva', 'Caranzo', '2N', '2nd Year', 'BSIT', '2026-02-12 08:37:13'),
(2887, '2024-0979', 'Shovie Diane', 'Turbanos', 'Amorin', '2NE', '2nd Year', 'BSIT', '2026-02-12 08:37:13'),
(2888, '2024-0994', 'Jonas Michael', 'Verallo', 'Maru', '2E', '2nd Year', 'BSIT', '2026-02-12 08:37:13'),
(2889, '2024-1001', 'Carmi', 'De La Vega', 'Medallo', '2NE', '2nd Year', 'BSIT', '2026-02-12 08:37:13'),
(2890, '2024-1002', 'Jennilyn', 'Bernardo', 'Sonet', '2W', '2nd Year', 'BSIT', '2026-02-12 08:37:13'),
(2891, '2024-1003', 'Faith Loreen', 'Despi', 'Figuracion', '2NW', '2nd Year', 'BSIT', '2026-02-12 08:37:13'),
(2892, '2024-1004', 'Jhon Lennon', 'Ilusorio', 'Forrosuelo', '2W', '2nd Year', 'BSIT', '2026-02-12 08:37:13'),
(2893, '2024-1012', 'Kent Lloyd', 'Umbao', 'Quijano', '2N', '2nd Year', 'BSIT', '2026-02-12 08:37:13'),
(2894, '2024-1013', 'Alberto', 'Tonelete Ill', 'Babaylo', '2N', '2nd Year', 'BSIT', '2026-02-12 08:37:13'),
(2895, '2024-1015', 'John Carl', 'Pastrana', 'Baril', '2SE', '2nd Year', 'BSIT', '2026-02-12 08:37:13'),
(2896, '2024-1024', 'Jassmine', 'Dearca', 'Villaceran', '2NW', '2nd Year', 'BSIT', '2026-02-12 08:37:13'),
(2897, '2024-1025', 'Sherl Mae', 'Bahi-an', 'Despabeladero', '2NW', '2nd Year', 'BSIT', '2026-02-12 08:37:13'),
(2898, '2024-1036', 'Roxanne', 'Despi', 'Sevilleno', '2NW', '2nd Year', 'BSIT', '2026-02-12 08:37:13'),
(2899, '2024-1041', 'Mark Argie', 'Sarraga', 'Rebusit', '2NE', '2nd Year', 'BSIT', '2026-02-12 08:37:13'),
(2900, '2024-1042', 'June Lenard', 'Alberca', 'Bacolod', '2NE', '2nd Year', 'BSIT', '2026-02-12 08:37:13'),
(2901, '2024-1043', 'Kenieth', 'Navarro', 'Esparago', '2N', '2nd Year', 'BSIT', '2026-02-12 08:37:13'),
(2902, '2024-1066', 'John Lenard', 'Sinilong', 'Sopetran', '2NE', '2nd Year', 'BSIT', '2026-02-12 08:37:13'),
(2903, '2024-1092', 'Ryan', 'Alob', 'Ysulan', '2NE', '2nd Year', 'BSIT', '2026-02-12 08:37:13'),
(2904, '2024-1094', 'Raffy', 'Cervantes', 'Medallo', '2NW', '2nd Year', 'BSIT', '2026-02-12 08:37:13'),
(2905, '2024-1098', 'Ryll Niccol', 'Murira', 'Arriesgado', '2NE', '2nd Year', 'BSIT', '2026-02-12 08:37:13'),
(2906, '2024-1131', 'Christine Raylyza Mae', 'Isugan', 'Carin', '2N', '2nd Year', 'BSIT', '2026-02-12 08:37:13'),
(2907, '2024-1154', 'Darren', 'Gila', 'Tarde', '2SE', '2nd Year', 'BSIT', '2026-02-12 08:37:13'),
(2908, '2024-1155', 'Anthony James', 'Gigtenta', 'Illut', '2NW', '2nd Year', 'BSIT', '2026-02-12 08:37:13'),
(2909, '2024-1159', 'Jaspher', 'Villacampa', 'Bahandi', '2S', '2nd Year', 'BSIT', '2026-02-12 08:37:13'),
(2910, '2024-1190', 'Toneth', 'Batobalonos', 'Layao', '2NW', '2nd Year', 'BSIT', '2026-02-12 08:37:13'),
(2911, '2024-1191', 'Maritchu', 'Fernandez', 'Necesario', '2NW', '2nd Year', 'BSIT', '2026-02-12 08:37:13'),
(2912, '2024-1199', 'Dale', 'Marabi', 'Batiancila', '2NW', '2nd Year', 'BSIT', '2026-02-12 08:37:13'),
(2913, '2024-1201', 'Louis Angelo', 'Tibay', 'Bawiin', '2NW', '2nd Year', 'BSIT', '2026-02-12 08:37:13'),
(2914, '2024-1213', 'Chelse', 'Quezon', 'Arriesgado', '2SE', '2nd Year', 'BSIT', '2026-02-12 08:37:13'),
(2915, '2024-1214', 'Rafael', 'Rosalejos', 'Santillan', '2NW', '2nd Year', 'BSIT', '2026-02-12 08:37:13'),
(2916, '2024-1215', 'Jhonell', 'Ungon', 'Quiamco', '2NE', '2nd Year', 'BSIT', '2026-02-12 08:37:13'),
(2917, '2024-1219', 'Niel John', 'Ungon', 'Quiamco', '2NW', '2nd Year', 'BSIT', '2026-02-12 08:37:13'),
(2918, '2024-1231', 'Kent Ni�', 'Eyas', 'Ejorango', '2NW', '2nd Year', 'BSIT', '2026-02-12 08:37:13'),
(2919, '2024-1232', 'Richard Jr.', 'Alob', 'Desucatan', '2NW', '2nd Year', 'BSIT', '2026-02-12 08:37:13'),
(2920, '2024-1241', 'Jerlyn', 'Faburada', 'Baterzal', '2SE', '2nd Year', 'BSIT', '2026-02-12 08:37:13'),
(2921, '2024-1242', 'Ellaiza May', 'Sevillejo', 'Desamparado', '2SE', '2nd Year', 'BSIT', '2026-02-12 08:37:13'),
(2922, '2024-1243', 'Restitulo', 'Villacarlos Jr.', 'Lequigan', '2NW', '2nd Year', 'BSIT', '2026-02-12 08:37:13'),
(2923, '2024-1256', 'Hendel', 'Rosos', 'Pacinio', '2NW', '2nd Year', 'BSIT', '2026-02-12 08:37:13'),
(2924, '2024-1257', 'Kim David', 'Yaun', 'Tulio', '2NW', '2nd Year', 'BSIT', '2026-02-12 08:37:13'),
(2925, '2024-1281', 'Erwin', 'Alob', 'Pastiteo', '2NW', '2nd Year', 'BSIT', '2026-02-12 08:37:13'),
(2926, '2024-1282', 'Hanzavill Jean', 'Maupas', 'Doble', '2SE', '2nd Year', 'BSIT', '2026-02-12 08:37:13'),
(2927, '2024-1291', 'Jerry Jr.', 'Bantiling', 'Mahilum', '2NW', '2nd Year', 'BSIT', '2026-02-12 08:37:13'),
(2928, '2024-1293', 'Roland', 'Montecillo', 'Sala', '2NW', '2nd Year', 'BSIT', '2026-02-12 08:37:13'),
(2929, '2024-1317', 'Ge Franz', 'Tiongzon', 'Bernabe', '2NE', '2nd Year', 'BSIT', '2026-02-12 08:37:13'),
(2930, '2024-1318', 'Gilbert', 'Monato', 'Jalalon', '2NW', '2nd Year', 'BSIT', '2026-02-12 08:37:13'),
(2931, '2024-1327', 'Mitchell', 'Polloso', 'Gerbabuena', '2SE', '2nd Year', 'BSIT', '2026-02-12 08:37:13'),
(2932, '2024-1328', 'Elgen', 'Gulpany', 'Pasaylo', '2NW', '2nd Year', 'BSIT', '2026-02-12 08:37:13'),
(2933, '2024-1330', 'Jericha', 'Sedurifa', 'Apawan', '2SE', '2nd Year', 'BSIT', '2026-02-12 08:37:13'),
(2934, '2024-1331', 'Bleese', 'Novela', 'Apawan', '2SE', '2nd Year', 'BSIT', '2026-02-12 08:37:13'),
(2935, '2024-1333', 'Renante', 'Lirasan', 'Sarabia', '2NW', '2nd Year', 'BSIT', '2026-02-12 08:37:13'),
(2936, '2024-1343', 'John Louid', 'Chavez', 'Descartin', '2NE', '2nd Year', 'BSIT', '2026-02-12 08:37:13'),
(2937, '2024-1345', 'John', 'Ducanes', 'Sonet', '2W', '2nd Year', 'BSIT', '2026-02-12 08:37:13'),
(2938, '2024-1347', 'Nathaniel', 'De Jesus', 'Antipala', '2NE', '2nd Year', 'BSIT', '2026-02-12 08:37:13'),
(2939, '2024-1348', 'Chive', 'Despabeladero', 'Ondong', '2NW', '2nd Year', 'BSIT', '2026-02-12 08:37:13'),
(2940, '2024-1349', 'Romy', 'Alojacin', 'Derder', '2NW', '2nd Year', 'BSIT', '2026-02-12 08:37:13'),
(2941, '2024-1350', 'Angelou', 'Illut', 'Barok', '2NW', '2nd Year', 'BSIT', '2026-02-12 08:37:13'),
(2942, '2024-1351', 'Marvin', 'Layaog', 'Derder', '2NW', '2nd Year', 'BSIT', '2026-02-12 08:37:13'),
(2943, '2024-1361', 'Joven Chris', 'Cena', 'Arriesgado', '2NE', '2nd Year', 'BSIT', '2026-02-12 08:37:13'),
(2944, '2024-1362', 'Sonny', 'Juliane', 'Balili', '2NE', '2nd Year', 'BSIT', '2026-02-12 08:37:13'),
(2945, '2024-1363', 'Cleve Justin', 'Suan', 'Rasonable', '2NE', '2nd Year', 'BSIT', '2026-02-12 08:37:13'),
(2946, '2024-1382', 'Dilbeth - Jey', 'Aguilar', 'None', '2NW', '2nd Year', 'BSIT', '2026-02-12 08:37:13'),
(2947, '2024-1389', 'Mar\'s Jeff', 'Manayan', 'Illut', '2W', '2nd Year', 'BSIT', '2026-02-12 08:37:13'),
(2948, '2024-1396', 'Kert Ronil', 'Pasasadaba', 'Compuesto', '2SE', '2nd Year', 'BSIT', '2026-02-12 08:37:13'),
(2949, '2024-1397', 'Jacob Christ', 'Dalaygon', 'Gidayawan', '2SE', '2nd Year', 'BSIT', '2026-02-12 08:37:13'),
(2950, '2024-1409', 'Rhea Micaella', 'Badanoy', 'Zapa', '2W', '2nd Year', 'BSIT', '2026-02-12 08:37:13'),
(2951, '2024-1414', 'Joshua', 'Layao', 'Pacinio', '2SE', '2nd Year', 'BSIT', '2026-02-12 08:37:13'),
(2952, '2024-1431', 'Rey Andre', 'Robles', 'Gidayawan', '2SE', '2nd Year', 'BSIT', '2026-02-12 08:37:13'),
(2953, '2024-1432', 'Jenelle', 'Illut', 'Gilbuena', '2NW', '2nd Year', 'BSIT', '2026-02-12 08:37:13'),
(2954, '2024-1434', 'Jay-r', 'Orpeza', 'Elosorio', '2NW', '2nd Year', 'BSIT', '2026-02-12 08:37:13'),
(2955, '2024-1435', 'Pearl', 'Derrayal', 'Omangayon', '1SW', '1st Year', 'BSIT', '2026-02-12 08:37:13'),
(2956, '2024-1436', 'Jmar', 'Caraballe', 'Illut', '2NW', '2nd Year', 'BSIT', '2026-02-12 08:37:13'),
(2957, '2024-1437', 'Rey June', 'Illut', 'Yhapon', '2S', '2nd Year', 'BSIT', '2026-02-12 08:37:13'),
(2958, '2024-1442', 'Ken', 'Desamparado', 'Rebamonte', '2NW', '2nd Year', 'BSIT', '2026-02-12 08:37:13'),
(2959, '2024-1443', 'Bryan', 'Bacarizas', '', '1SE', '1st Year', 'BSIT', '2026-02-12 08:37:13'),
(2960, '2024-1444', 'Ni�', 'Espina', 'Bantimano', '2NW', '2nd Year', 'BSIT', '2026-02-12 08:37:13'),
(2961, '2024-1445', 'Reymar', 'Plasencia', 'Espina', '2NW', '2nd Year', 'BSIT', '2026-02-12 08:37:13'),
(2962, '2024-1462', 'John Rean', 'Cinco', 'Cernal', '2SE', '2nd Year', 'BSIT', '2026-02-12 08:37:13'),
(2963, '2024-1475', 'Irene', 'Villaceran', 'Destura', '2SE', '2nd Year', 'BSIT', '2026-02-12 08:37:13'),
(2964, '2024-1480', 'Shiela Lyn', 'Premacio', 'Escaran', '2W', '2nd Year', 'BSIT', '2026-02-12 08:37:13'),
(2965, '2024-1489', 'Christer Lee', 'Manzanares', '', '2S', '2nd Year', 'BSIT', '2026-02-12 08:37:13'),
(2966, '2024-1490', 'Michael Jay', 'Lagahid', 'Gonzales', '2SE', '2nd Year', 'BSIT', '2026-02-12 08:37:13'),
(2967, '2024-1494', 'Mark Louie', 'Villacarlos', 'Illut', '2SE', '2nd Year', 'BSIT', '2026-02-12 08:37:13'),
(2968, '2024-1495', 'Philip Martin', 'Batiancila', 'Batiancila', '2SE', '2nd Year', 'BSIT', '2026-02-12 08:37:13'),
(2969, '2024-1498', 'Janel Rose', 'Ilustrisimo', 'Moises', '2NE', '2nd Year', 'BSIT', '2026-02-12 08:37:13'),
(2970, '2024-1501', 'Arjun Mark', 'Andales', 'Perez', '2SE', '2nd Year', 'BSIT', '2026-02-12 08:37:13'),
(2971, '2024-1507', 'Frederick Prince', 'Derder', 'Veliganio', '2SE', '2nd Year', 'BSIT', '2026-02-12 08:37:13'),
(2972, '2024-1519', 'Aren Jhon', 'Giganto', 'Marfa', '2SE', '2nd Year', 'BSIT', '2026-02-12 08:37:13'),
(2973, '2024-1527', 'Romar', 'Sinday', 'Alolor', '2SE', '2nd Year', 'BSIT', '2026-02-12 08:37:13'),
(2974, '2024-1528', 'Sarah Jane', 'Ysulan', 'Batarilan', '2W', '2nd Year', 'BSIT', '2026-02-12 08:37:13'),
(2975, '2024-1535', 'Mark Angelo', 'Bautro', 'Mu�?', '2SE', '2nd Year', 'BSIT', '2026-02-12 08:37:13'),
(2976, '2024-1540', 'Vince', 'Rondina', 'Pacina', '2SE', '2nd Year', 'BSIT', '2026-02-12 08:37:13'),
(2977, '2024-1541', 'James Ryan', 'Paspie', 'None', '2SE', '2nd Year', 'BSIT', '2026-02-12 08:37:13'),
(2978, '2024-1542', 'Gesille', 'Villacin', 'Amante', '2W', '2nd Year', 'BSIT', '2026-02-12 08:37:13'),
(2979, '2024-1543', 'Delmar', 'Giltendez', 'Layon', '2S', '2nd Year', 'BSIT', '2026-02-12 08:37:13'),
(2980, '2024-1552', 'Mary Grace', 'Villacampa', 'Dela Fuente', '2NE', '2nd Year', 'BSIT', '2026-02-12 08:37:13'),
(2981, '2024-1555', 'Aljay', 'Desucatan', 'Dingding', '2SE', '2nd Year', 'BSIT', '2026-02-12 08:37:13'),
(2982, '2024-1558', 'Alemar', 'Almocera', 'Sepuesca', '2SE', '2nd Year', 'BSIT', '2026-02-12 08:37:13'),
(2983, '2024-1559', 'Melenio', 'Ilustrisimo', 'Monterola', '2SE', '2nd Year', 'BSIT', '2026-02-12 08:37:13'),
(2984, '2024-1564', 'John Rex', 'Forrosuelo', 'Maru', '2SE', '2nd Year', 'BSIT', '2026-02-12 08:37:13'),
(2985, '2024-1565', 'Emma', 'Nepangue', 'Ampusta', '2W', '2nd Year', 'BSIT', '2026-02-12 08:37:13'),
(2986, '2024-1567', 'Jefferson John', 'Pacifico', 'Marande', '2SE', '2nd Year', 'BSIT', '2026-02-12 08:37:13'),
(2987, '2024-1581', 'Justine Mark', 'Ilustrisimo', 'Maru', '2SE', '2nd Year', 'BSIT', '2026-02-12 08:37:13'),
(2988, '2024-1587', 'Nico', 'Layese', 'Plaza', '2N', '2nd Year', 'BSIT', '2026-02-12 08:37:13'),
(2989, '2024-1588', 'Jamaica', 'Arriesgado', '', '2W', '2nd Year', 'BSIT', '2026-02-12 08:37:13'),
(2990, '2024-1589', 'Richard', 'Dela Pe�?', 'Demoral', '2W', '2nd Year', 'BSIT', '2026-02-12 08:37:13'),
(2991, '2024-1607', 'Roljost', 'Alba�?', 'Bautro', '2W', '2nd Year', 'BSIT', '2026-02-12 08:37:13'),
(2992, '2024-1608', 'Lance Rio', 'Dela Pena', 'Mansueto', '2SE', '2nd Year', 'BSIT', '2026-02-12 08:37:13'),
(2993, '2024-1633', 'Severino Jake', 'Caramales', 'Ofaga', '2NE', '2nd Year', 'BSIT', '2026-02-12 08:37:13'),
(2994, '2024-1638', 'Noe', 'Despi', '', '2W', '2nd Year', 'BSIT', '2026-02-12 08:37:13'),
(2995, '2024-1639', 'Danny Boy', 'Ida�', 'Bandalan', '2W', '2nd Year', 'BSIT', '2026-02-12 08:37:13'),
(2996, '2024-1653', 'John Paul', 'Dela Pe�', 'Mirabel', '2SE', '2nd Year', 'BSIT', '2026-02-12 08:37:13'),
(2997, '2024-1666', 'Chrishan', 'Quijano', 'Hiba', '2SE', '2nd Year', 'BSIT', '2026-02-12 08:37:13'),
(2998, '2024-1672', 'Ronald', 'Caraba��a', 'Ge��', '2SE', '2nd Year', 'BSIT', '2026-02-12 08:37:13'),
(2999, '2024-1675', 'Manny', 'Katipunan', 'Despi', '2W', '2nd Year', 'BSIT', '2026-02-12 08:37:13'),
(3000, '2024-1677', 'Kenth David', 'Alegre', 'Layao', '2NW', '2nd Year', 'BSIT', '2026-02-12 08:37:13'),
(3001, '2024-1678', 'Kent Harvey', 'Chavez', 'Escalla', '2NW', '2nd Year', 'BSIT', '2026-02-12 08:37:13'),
(3002, '2024-1687', 'Evelyn', 'Ypil', 'Desucatan', '2W', '2nd Year', 'BSIT', '2026-02-12 08:37:13'),
(3003, '2024-1700', 'Edwin', 'Manto', 'Dublin', '2N', '2nd Year', 'BSIT', '2026-02-12 08:37:13'),
(3004, '2024-1701', 'John Rey', 'Pepito', '', '2NW', '2nd Year', 'BSIT', '2026-02-12 08:37:13'),
(3005, '2024-1704', 'Luther', 'Manalo', 'None', '2SE', '2nd Year', 'BSIT', '2026-02-12 08:37:13'),
(3006, '2024-1705', 'Kirby Luis', 'Ofianga', 'Santillan', '2SE', '2nd Year', 'BSIT', '2026-02-12 08:37:13'),
(3007, '2024-1713', 'Mark', 'Arreglado', 'Compania', '2SE', '2nd Year', 'BSIT', '2026-02-12 08:37:13'),
(3008, '2024-1716', 'Richjun', 'Colambot', 'Yba�??', '2W', '2nd Year', 'BSIT', '2026-02-12 08:37:13'),
(3009, '2024-1728', 'Charles Jupit', 'Corona', 'Mulle', '2W', '2nd Year', 'BSIT', '2026-02-12 08:37:13'),
(3010, '2024-1735', 'Keith Justinne', 'Yba�?', 'Villaceran', '2NW', '2nd Year', 'BSIT', '2026-02-12 08:37:13'),
(3011, '2024-1745', 'Riza Mae', 'Gilbuena', 'Rosales', '2W', '2nd Year', 'BSIT', '2026-02-12 08:37:13'),
(3012, '2024-1748', 'Lovely Faith', 'Yangao', 'Bilbao', '2W', '2nd Year', 'BSIT', '2026-02-12 08:37:13'),
(3013, '2024-1749', 'Jhon Lloyd', 'Monta�', 'Villacarlos', '2NE', '2nd Year', 'BSIT', '2026-02-12 08:37:13'),
(3014, '2024-1756', 'Jiah', 'Quijano', 'Oftana', '2W', '2nd Year', 'BSIT', '2026-02-12 08:37:13'),
(3015, '2024-1759', 'Claude', 'Quezon', 'Genova', '2NE', '2nd Year', 'BSIT', '2026-02-12 08:37:13'),
(3016, '2024-1760', 'Eduardo', 'Villaester Jr.', 'Fariolen', '2NE', '2nd Year', 'BSIT', '2026-02-12 08:37:13'),
(3017, '2024-1767', 'Edilberto', 'Talatayod Jr.', 'Ivardolaza', '2S', '2nd Year', 'BSIT', '2026-02-12 08:37:13'),
(3018, '2024-1768', 'Jerry', 'Giduquio Jr.', 'Anciano', '2S', '2nd Year', 'BSIT', '2026-02-12 08:37:13'),
(3019, '2024-1784', 'John Mark', 'Grande', 'Rosales', '2N', '2nd Year', 'BSIT', '2026-02-12 08:37:13'),
(3020, '2024-1785', 'Brex John', 'Caramelo', 'Niedo', '2N', '2nd Year', 'BSIT', '2026-02-12 08:37:13'),
(3021, '2024-1790', 'Anthony', 'Gilbuena', 'Carabio', '2NE', '2nd Year', 'BSIT', '2026-02-12 08:37:13'),
(3022, '2024-1791', 'Reynaldo', 'Placencia', 'Buncal', '2N', '2nd Year', 'BSIT', '2026-02-12 08:37:13'),
(3023, '2024-1792', 'Kierth', 'Bautista', 'Moncada', '2N', '2nd Year', 'BSIT', '2026-02-12 08:37:13'),
(3024, '2024-1793', 'Denmark', 'Vergara', 'Monterola', '2N', '2nd Year', 'BSIT', '2026-02-12 08:37:13'),
(3025, '2024-1794', 'Dionimar', 'Tumabini', 'Batiancila', '2E', '2nd Year', 'BSIT', '2026-02-12 08:37:13'),
(3026, '2024-1795', 'Carlo', 'Batobalonos', 'Canoy', '2SE', '2nd Year', 'BSIT', '2026-02-12 08:37:13'),
(3027, '2024-1819', 'Ella Mae', 'Ga�?', 'Sevillino', '2NE', '2nd Year', 'BSIT', '2026-02-12 08:37:13'),
(3028, '2024-1844', 'Ryggor Joseph', 'Velasco', 'Santillan', '2S', '2nd Year', 'BSIT', '2026-02-12 08:37:13'),
(3029, '2024-1866', 'Allen Din', 'Cabrera', 'Paspie', '2W', '2nd Year', 'BSIT', '2026-02-12 08:37:13'),
(3030, '2024-1867', 'Lance Terrence', 'Marabi', '', '2W', '2nd Year', 'BSIT', '2026-02-12 08:37:13'),
(3031, '2024-1874', 'Ryan', 'Esgana', 'Dablo', '2S', '2nd Year', 'BSIT', '2026-02-12 08:37:13'),
(3032, '2024-1877', 'Ralph', 'Escala', 'Almo', '2S', '2nd Year', 'BSIT', '2026-02-12 08:37:13'),
(3033, '2024-1890', 'Dave', 'Vidad', 'Barrios', '2N', '2nd Year', 'BSIT', '2026-02-12 08:37:13'),
(3034, '2024-1894', 'JOEL', 'BAYON-ON', 'MENDOZA', '3-SOUTH', '3rd Year', 'BSIT', '2026-02-12 08:37:13'),
(3035, '2024-1901', 'John Mark', 'Hortelano', 'Desabille', '2S', '2nd Year', 'BSIT', '2026-02-12 08:37:13'),
(3036, '2024-1908', 'Kienth Dian', 'Sumampong', 'Ilustrisimo', '2S', '2nd Year', 'BSIT', '2026-02-12 08:37:13'),
(3037, '2024-1916', 'Ceasar', 'Forrosuelo', 'Marabe', '2NE', '2nd Year', 'BSIT', '2026-02-12 08:37:13'),
(3038, '2024-1918', 'Junielle', 'Villacarlos', 'Gigante', '1E', '1st Year', 'BSIT', '2026-02-12 08:37:13'),
(3039, '2024-1920', 'Lea', 'Alo', 'Desabille', '2SE', '2nd Year', 'BSIT', '2026-02-12 08:37:13'),
(3040, '2024-1938', 'Jaspher', 'Derder', 'Ilustrisimo', '2W', '2nd Year', 'BSIT', '2026-02-12 08:37:13'),
(3041, '2024-1942', 'Carkiven', 'Isagana', 'Igloria', '2SE', '2nd Year', 'BSIT', '2026-02-12 08:37:13'),
(3042, '2024-1943', 'Emmanuel Joseph', 'Lim', 'Pasasadaba', '2W', '2nd Year', 'BSIT', '2026-02-12 08:37:13'),
(3043, '2024-1944', 'July', 'Pescante', 'Bacolod', '2NW', '2nd Year', 'BSIT', '2026-02-12 08:37:13'),
(3044, '2024-1947', 'Alexander', 'Negapatan', 'Ca��', '2N', '2nd Year', 'BSIT', '2026-02-12 08:37:13'),
(3045, '2024-1950', 'Juren', 'Desamparado', 'Garcia', '2N', '2nd Year', 'BSIT', '2026-02-12 08:37:13'),
(3046, '2024-7045', 'Vincent', 'Hesalta', 'Fernandez', '2E', '2nd Year', 'BSIT', '2026-02-12 08:37:13'),
(3047, '2025-0108', 'Kristyl', 'Mahinay', 'Roxas', '1SW', '1st Year', 'BSIT', '2026-02-12 08:37:13'),
(3048, '2025-0112', 'Aaron Moss', 'Ara�?', 'Rosales', '1NE', '1st Year', 'BSIT', '2026-02-12 08:37:13'),
(3049, '2025-0127', 'Christian Mark', 'Giducos', 'Estellore', '1SW', '1st Year', 'BSIT', '2026-02-12 08:37:13'),
(3050, '2025-0128', 'Jobert', 'Mondarte', 'Pradilla', '1SW', '1st Year', 'BSIT', '2026-02-12 08:37:13'),
(3051, '2025-0129', 'Janverlie', 'Delgado', 'Caballero', '1NE', '1st Year', 'BSIT', '2026-02-12 08:37:13'),
(3052, '2025-0130', 'Rheana Mekylla', 'Peritos', 'Tumabini', '1S', '1st Year', 'BSIT', '2026-02-12 08:37:13'),
(3053, '2025-0131', 'Gary', 'Almonia', 'Batain', '1NE', '1st Year', 'BSIT', '2026-02-12 08:37:13'),
(3054, '2025-0132', 'Ferli', 'Andraque', 'Aloyan', '1NE', '1st Year', 'BSIT', '2026-02-12 08:37:13'),
(3055, '2025-0133', 'Hanz Matthew', 'Tancinco', 'Batirzal', '1SW', '1st Year', 'BSIT', '2026-02-12 08:37:13'),
(3056, '2025-0134', 'Jienlee', 'Boyore', 'Alolor', '1NE', '1st Year', 'BSIT', '2026-02-12 08:37:13'),
(3057, '2025-0135', 'Jasper', 'Martus', 'Dela Pe�', '1SW', '1st Year', 'BSIT', '2026-02-12 08:37:13'),
(3058, '2025-0136', 'Waren', 'Ducay', 'Villaceran', '1NE', '1st Year', 'BSIT', '2026-02-12 08:37:13'),
(3059, '2025-0137', 'Beverly Jane', 'Bernabat', 'Garrido', '1NE', '1st Year', 'BSIT', '2026-02-12 08:37:13'),
(3060, '2025-0138', 'Angel', 'Guadamor', 'Alburo', '1SW', '1st Year', 'BSIT', '2026-02-12 08:37:13'),
(3061, '2025-0139', 'Lynel', 'Quiamco', 'Ca�??', '1SW', '1st Year', 'BSIT', '2026-02-12 08:37:13'),
(3062, '2025-0140', 'John Mark', 'Cabiling', '', '1NE', '1st Year', 'BSIT', '2026-02-12 08:37:13'),
(3063, '2025-0141', 'Joshua Lordy', 'Garcia', 'Sayson', '1NE', '1st Year', 'BSIT', '2026-02-12 08:37:13'),
(3064, '2025-0142', 'Jimms', 'Amadeo', 'Almodiel', '1SW', '1st Year', 'BSIT', '2026-02-12 08:37:13'),
(3065, '2025-0143', 'John Owen', 'Flores', 'Magsalay', '1NE', '1st Year', 'BSIT', '2026-02-12 08:37:13'),
(3066, '2025-0144', 'Ronalyn', 'Batiancila', 'Baruc', '1NE', '1st Year', 'BSIT', '2026-02-12 08:37:13'),
(3067, '2025-0145', 'Eunice', 'Ablao', 'Escala', '1NE', '1st Year', 'BSIT', '2026-02-12 08:37:13'),
(3068, '2025-0146', 'Erich', 'Sevillejo', 'Deanon', '1SW', '1st Year', 'BSIT', '2026-02-12 08:37:13'),
(3069, '2025-0150', 'Joshua', 'Villa', 'Ablao', '1SW', '1st Year', 'BSIT', '2026-02-12 08:37:13'),
(3070, '2025-0151', 'Jovan', 'Albaciete', 'Halichic', '1NE', '1st Year', 'BSIT', '2026-02-12 08:37:13'),
(3071, '2025-0153', 'Kimberly Mae', 'Jumanguin', 'Fariolen', '1S', '1st Year', 'BSIT', '2026-02-12 08:37:13'),
(3072, '2025-0154', 'Fermin', 'Desamparado Iii', 'Continedo', '1NE', '1st Year', 'BSIT', '2026-02-12 08:37:13'),
(3073, '2025-0155', 'Saquio', 'Mancio', 'Lawan', '1SW', '1st Year', 'BSIT', '2026-02-12 08:37:13'),
(3074, '2025-0156', 'Alvin', 'Maru', 'Piamonte', '1SW', '1st Year', 'BSIT', '2026-02-12 08:37:13'),
(3075, '2025-0157', 'Alexander', 'Fernandez', 'Castillo', '1NE', '1st Year', 'BSIT', '2026-02-12 08:37:13'),
(3076, '2025-0158', 'Lylanie', 'Caramonte', 'Capuras', '1NE', '1st Year', 'BSIT', '2026-02-12 08:37:13'),
(3077, '2025-0159', 'Eliel', 'Taes', 'Arriola', '1SW', '1st Year', 'BSIT', '2026-02-12 08:37:13'),
(3078, '2025-0161', 'Gracie Ann', 'Tidoso', 'Villacarlos', '1SW', '1st Year', 'BSIT', '2026-02-12 08:37:13'),
(3079, '2025-0162', 'Ezrylle Diane', 'Masula', 'Batiancila', '1SW', '1st Year', 'BSIT', '2026-02-12 08:37:13'),
(3080, '2025-0163', 'Margie', 'Giducos', 'Escala', '1NE', '1st Year', 'BSIT', '2026-02-12 08:37:13'),
(3081, '2025-0165', 'Azur Jay', 'Esgana', 'Marinduque', '1NE', '1st Year', 'BSIT', '2026-02-12 08:37:13'),
(3082, '2025-0166', 'John Miguel', 'Rustia', '', '1SW', '1st Year', 'BSIT', '2026-02-12 08:37:13'),
(3083, '2025-0170', 'Marc Bernard', 'Derrayal', 'Silvero', '1NE', '1st Year', 'BSIT', '2026-02-12 08:37:13'),
(3084, '2025-0171', 'Janry', 'Laguialam', 'Albaciete', '1SW', '1st Year', 'BSIT', '2026-02-12 08:37:13'),
(3085, '2025-0172', 'Angel Mae', 'Quezon', 'Ducay', '1SW', '1st Year', 'BSIT', '2026-02-12 08:37:13'),
(3086, '2025-0174', 'John Rey', 'Guarisma', 'Bayarong', '1E', '1st Year', 'BSIT', '2026-02-12 08:37:13'),
(3087, '2025-0175', 'Barry', 'Laurente', 'Carallas', '1SW', '1st Year', 'BSIT', '2026-02-12 08:37:13'),
(3088, '2025-0189', 'Aj Clarck', 'Cahutay', 'Maru', '1NE', '1st Year', 'BSIT', '2026-02-12 08:37:13'),
(3089, '2025-0190', 'Rowella', 'Maru', 'Aniban', '1E', '1st Year', 'BSIT', '2026-02-12 08:37:13'),
(3090, '2025-0191', 'Jay Boy', 'Enopia', 'Parochel', '1NE', '1st Year', 'BSIT', '2026-02-12 08:37:13'),
(3091, '2025-0194', 'Frank', 'Ysulan', 'Banquisio', '1SW', '1st Year', 'BSIT', '2026-02-12 08:37:13'),
(3092, '2025-0196', 'Christian', 'Cahutay', 'M.', '1NE', '1st Year', 'BSIT', '2026-02-12 08:37:13'),
(3093, '2025-0197', 'Gabriel', 'Escario', '', '1NE', '1st Year', 'BSIT', '2026-02-12 08:37:13'),
(3094, '2025-0198', 'Jiann Kenn', 'Abello', '', '1NE', '1st Year', 'BSIT', '2026-02-12 08:37:13'),
(3095, '2025-0200', 'Clydelle', 'Toquero', 'Niedo', '1SW', '1st Year', 'BSIT', '2026-02-12 08:37:13'),
(3096, '2025-0201', 'Princess Jewel', 'Mancio', 'Amadeo', '1SW', '1st Year', 'BSIT', '2026-02-12 08:37:13'),
(3097, '2025-0202', 'Jhonriel', 'Tendido', 'Villaceran', '1SW', '1st Year', 'BSIT', '2026-02-12 08:37:13'),
(3098, '2025-0206', 'Fatima', 'Pogoy', 'Juntilla', '1SW', '1st Year', 'BSIT', '2026-02-12 08:37:13'),
(3099, '2025-0211', 'John Ryan', 'Puerto', 'Mansueto', '1SW', '1st Year', 'BSIT', '2026-02-12 08:37:13'),
(3100, '2025-0212', 'Zernan', 'Villaester', 'Fernandez', '1SW', '1st Year', 'BSIT', '2026-02-12 08:37:13'),
(3101, '2025-0213', 'Trisha Anne', 'Avelda', 'Bonghanoy', '1SE', '1st Year', 'BSIT', '2026-02-12 08:37:13'),
(3102, '2025-0223', 'Zairah', 'Desamparado', 'Almonicar', '1NE', '1st Year', 'BSIT', '2026-02-12 08:37:13'),
(3103, '2025-0224', 'Ni��o Cris', 'Villacarlos', 'Cena', '1SW', '1st Year', 'BSIT', '2026-02-12 08:37:13'),
(3104, '2025-0228', 'Dave', 'Villacrusis', 'Hermogila', '1SW', '1st Year', 'BSIT', '2026-02-12 08:37:13'),
(3105, '2025-0230', 'Adrian', 'Bolivar', 'Barco', '1NE', '1st Year', 'BSIT', '2026-02-12 08:37:13'),
(3106, '2025-0231', 'Jose Roy', 'Olinares', 'Villaester', '1E', '1st Year', 'BSIT', '2026-02-12 08:37:13'),
(3107, '2025-0233', 'Mark', 'Baring', 'Barnuevo', '1E', '1st Year', 'BSIT', '2026-02-12 08:37:13'),
(3108, '2025-0234', 'John Nivin', 'Versoza', 'Ilustrisimo', '1E', '1st Year', 'BSIT', '2026-02-12 08:37:13'),
(3109, '2025-0235', 'Renato Jr.', 'Bawiin', 'Ducay', '1NE', '1st Year', 'BSIT', '2026-02-12 08:37:13'),
(3110, '2025-0236', 'Gian Kyra', 'Duante', 'Cueva', '1NE', '1st Year', 'BSIT', '2026-02-12 08:37:13'),
(3111, '2025-0237', 'Roselie', 'Almonicar', 'Layao', '1NE', '1st Year', 'BSIT', '2026-02-12 08:37:13'),
(3112, '2025-0239', 'Robert', 'Espina', 'Lepiten', '1N', '1st Year', 'BSIT', '2026-02-12 08:37:13'),
(3113, '2025-0240', 'Chan Zerg', 'Pogoy', 'Labores', '1SW', '1st Year', 'BSIT', '2026-02-12 08:37:13'),
(3114, '2025-0242', 'Aj', 'Almodiel', 'Bautista', '1NE', '1st Year', 'BSIT', '2026-02-12 08:37:13'),
(3115, '2025-0244', 'Juanna Mae', 'Almocera', 'Anciano', '1NE', '1st Year', 'BSIT', '2026-02-12 08:37:13'),
(3116, '2025-0245', 'Kent', 'Taber', 'Alo', '1SW', '1st Year', 'BSIT', '2026-02-12 08:37:13'),
(3117, '2025-0246', 'Shannelyn', 'Vegafria', 'Santillan', '1SW', '1st Year', 'BSIT', '2026-02-12 08:37:13'),
(3118, '2025-0247', 'Christian Jhay', 'Morales', 'Santillan', '1SW', '1st Year', 'BSIT', '2026-02-12 08:37:13'),
(3119, '2025-0248', 'Juliana Marie', 'Traya', 'Delape�', '1SW', '1st Year', 'BSIT', '2026-02-12 08:37:13'),
(3120, '2025-0254', 'Roleo Jay', 'Villaceran', 'Santillan', '1N', '1st Year', 'BSIT', '2026-02-12 08:37:13'),
(3121, '2025-0261', 'Decemary', 'Escaran', 'Giducos', '1NE', '1st Year', 'BSIT', '2026-02-12 08:37:13'),
(3122, '2025-0262', 'Alfrix', 'Dayon', 'Escala', '1NE', '1st Year', 'BSIT', '2026-02-12 08:37:13'),
(3123, '2025-0263', 'Kim', 'Batindaan', 'Illut', '1NE', '1st Year', 'BSIT', '2026-02-12 08:37:13'),
(3124, '2025-0266', 'Junly', 'Pacilan', 'Baluarte', '1S', '1st Year', 'BSIT', '2026-02-12 08:37:13'),
(3125, '2025-0267', 'Jhon Paul', 'Gregorio', 'Tome', '1N', '1st Year', 'BSIT', '2026-02-12 08:37:13'),
(3126, '2025-0270', 'James', 'Tonelete', 'Babaylo', '1SW', '1st Year', 'BSIT', '2026-02-12 08:37:13'),
(3127, '2025-0273', 'Nestor', 'Flores Jr.', 'Derder', '1SE', '1st Year', 'BSIT', '2026-02-12 08:37:13'),
(3128, '2025-0275', 'Leonardo', 'Jumanguin Jr.', 'Gidayawan', '1SW', '1st Year', 'BSIT', '2026-02-12 08:37:13'),
(3129, '2025-0276', 'Vincent', 'Pacilan', 'Cahutay', '1SW', '1st Year', 'BSIT', '2026-02-12 08:37:13'),
(3130, '2025-0277', 'Rexan', 'Doble', 'Rubio', '1NE', '1st Year', 'BSIT', '2026-02-12 08:37:13'),
(3131, '2025-0278', 'George', 'Maranga', 'Magallanes', '1SE', '1st Year', 'BSIT', '2026-02-12 08:37:13'),
(3132, '2025-0279', 'Aaron Paul', 'Unggon', 'Delabajan', '1SW', '1st Year', 'BSIT', '2026-02-12 08:37:13'),
(3133, '2025-0280', 'Marvin', 'Villaceran', 'Santillan', '1E', '1st Year', 'BSIT', '2026-02-12 08:37:13'),
(3134, '2025-0281', 'Dhen Mark', 'Medallo', 'Almasco', '1SE', '1st Year', 'BSIT', '2026-02-12 08:37:13'),
(3135, '2025-0286', 'Sarah Mae', 'Layaog', 'Derder', '1S', '1st Year', 'BSIT', '2026-02-12 08:37:13'),
(3136, '2025-0302', 'Jundel', 'Bacalso', 'Marfa', '1SE', '1st Year', 'BSIT', '2026-02-12 08:37:13'),
(3137, '2025-0305', 'Jhon Paul', 'Caballero', 'Alota', '1N', '1st Year', 'BSIT', '2026-02-12 08:37:13'),
(3138, '2025-0306', 'Brint', 'Villaceran', 'Ducay', '1E', '1st Year', 'BSIT', '2026-02-12 08:37:13'),
(3139, '2025-0307', 'John Lloyd', 'Namanas', 'Quiamco', '1E', '1st Year', 'BSIT', '2026-02-12 08:37:13'),
(3140, '2025-0308', 'Julian', 'Chin', 'Desabille', '1SE', '1st Year', 'BSIT', '2026-02-12 08:37:13'),
(3141, '2025-0323', 'Angel', 'Arranguez', 'Roxas', '1SE', '1st Year', 'BSIT', '2026-02-12 08:37:13'),
(3142, '2025-0335', 'Yuki', 'Valencia', 'Layos', '1E', '1st Year', 'BSIT', '2026-02-12 08:37:13'),
(3143, '2025-0352', 'Fermine Joy', 'Cernal', 'Sinadjan', '1SE', '1st Year', 'BSIT', '2026-02-12 08:37:13'),
(3144, '2025-0361', 'Alana Grace', 'Pasinio', 'Magsipoc', '1E', '1st Year', 'BSIT', '2026-02-12 08:37:13'),
(3145, '2025-0365', 'Jamaica', 'Mangubat', 'O.', '1SE', '1st Year', 'BSIT', '2026-02-12 08:37:13'),
(3146, '2025-0389', 'Franz Micheal', 'Tidoso', 'Cueva', '1S', '1st Year', 'BSIT', '2026-02-12 08:37:13'),
(3147, '2025-0392', 'Zedrich', 'Malibago', 'Gonzales', '1S', '1st Year', 'BSIT', '2026-02-12 08:37:13'),
(3148, '2025-0393', 'Yohanne', 'Bautro', 'Ducay', '1S', '1st Year', 'BSIT', '2026-02-12 08:37:13'),
(3149, '2025-0396', 'Laurence', 'Mulle', 'Batiancila', '1S', '1st Year', 'BSIT', '2026-02-12 08:37:13'),
(3150, '2025-0399', 'John Gabriel', 'Borce', 'Maru', '1S', '1st Year', 'BSIT', '2026-02-12 08:37:13'),
(3151, '2025-0400', 'Larhenz', 'Sulla', 'Bautro', '1S', '1st Year', 'BSIT', '2026-02-12 08:37:13'),
(3152, '2025-0435', 'Johna Mae', 'Decierdo', 'Despi', '1SE', '1st Year', 'BSIT', '2026-02-12 08:37:13'),
(3153, '2025-0437', 'Aniana', 'To�?', 'Saplad', '1SE', '1st Year', 'BSIT', '2026-02-12 08:37:13'),
(3154, '2025-0438', 'Ronilyn', 'Sardido', 'Camotes', '1SE', '1st Year', 'BSIT', '2026-02-12 08:37:13'),
(3155, '2025-0439', 'Rain', 'Gaid', 'Torion', '1SE', '1st Year', 'BSIT', '2026-02-12 08:37:13'),
(3156, '2025-0446', 'Blenjie', 'Bacolod', 'Garcia', '1E', '1st Year', 'BSIT', '2026-02-12 08:37:13'),
(3157, '2025-0447', 'Ruslyn May', 'Umbao', 'Despi', '1S', '1st Year', 'BSIT', '2026-02-12 08:37:13'),
(3158, '2025-0448', 'Mary Flor Jane', 'Caseres', 'Repel', '1S', '1st Year', 'BSIT', '2026-02-12 08:37:13'),
(3159, '2025-0461', 'Jayvin', 'Alob', 'Leones', '1E', '1st Year', 'BSIT', '2026-02-12 08:37:13'),
(3160, '2025-0462', 'Jay Nathanael', 'Carpiso', 'Sedurifa', '1E', '1st Year', 'BSIT', '2026-02-12 08:37:13'),
(3161, '2025-0463', 'Renante', 'Rosalejos', 'Velez', '1SE', '1st Year', 'BSIT', '2026-02-12 08:37:13'),
(3162, '2025-0464', 'Isagani', 'Batolbatol', 'Batuhan', '1SE', '1st Year', 'BSIT', '2026-02-12 08:37:13'),
(3163, '2025-0465', 'Roger', 'Gilbuena', 'Abello', '1SE', '1st Year', 'BSIT', '2026-02-12 08:37:13'),
(3164, '2025-0486', 'Enjie', 'Pacilan', 'Marande', '1SE', '1st Year', 'BSIT', '2026-02-12 08:37:13'),
(3165, '2025-0487', 'John Loyd', 'Ilustrisimo', 'Batolbatol', '1N', '1st Year', 'BSIT', '2026-02-12 08:37:13'),
(3166, '2025-0488', 'Rogen', 'Sarraga', 'Baruc', '1N', '1st Year', 'BSIT', '2026-02-12 08:37:13'),
(3167, '2025-0493', 'Christer', 'Paglinawan', 'Cinco', '1S', '1st Year', 'BSIT', '2026-02-12 08:37:13'),
(3168, '2025-0503', 'Mathew Miles', 'Lobiogo', 'Kaquilala', '1S', '1st Year', 'BSIT', '2026-02-12 08:37:13'),
(3169, '2025-0504', 'John Stephen', 'Escarro', 'De La Pena', '1S', '1st Year', 'BSIT', '2026-02-12 08:37:13'),
(3170, '2025-0505', 'Lloyd Stephen', 'Clavis', 'Laurente', '1S', '1st Year', 'BSIT', '2026-02-12 08:37:13'),
(3171, '2025-0508', 'Babie Jean', 'Patoc', 'Alingasa', '1E', '1st Year', 'BSIT', '2026-02-12 08:37:13'),
(3172, '2025-0510', 'Nicole', 'Villacastin', 'Rondina', '1N', '1st Year', 'BSIT', '2026-02-12 08:37:13'),
(3173, '2025-0515', 'Francine', 'Lorca', 'Nepangque', '1N', '1st Year', 'BSIT', '2026-02-12 08:37:13'),
(3174, '2025-0517', 'Jeff Andre', 'Pacilan', 'Zaspa', '1N', '1st Year', 'BSIT', '2026-02-12 08:37:13'),
(3175, '2025-0522', 'Ryan Jay', 'Obamos', '', '1N', '1st Year', 'BSIT', '2026-02-12 08:37:13'),
(3176, '2025-0529', 'John Loyd', 'Espliguera', 'Lofranco', '1N', '1st Year', 'BSIT', '2026-02-12 08:37:13'),
(3177, '2025-0535', 'Earl Robert', 'Gilbuena', 'Batobalonos', '1SE', '1st Year', 'BSIT', '2026-02-12 08:37:13'),
(3178, '2025-0539', 'Niko', 'Bacaro', 'Pamaybay', '1N', '1st Year', 'BSIT', '2026-02-12 08:37:13'),
(3179, '2025-0540', 'Krish Jeevan', 'Escarro', 'Sabandal', '1N', '1st Year', 'BSIT', '2026-02-12 08:37:13'),
(3180, '2025-0549', 'Kert Jil', 'Cesa', 'Dosdos', '1S', '1st Year', 'BSIT', '2026-02-12 08:37:13'),
(3181, '2025-0553', 'John Estefano', 'Ofqueria', 'Batobalonos', '1S', '1st Year', 'BSIT', '2026-02-12 08:37:13'),
(3182, '2025-0554', 'Jumark', 'Mendoza', 'Batuhan', '1N', '1st Year', 'BSIT', '2026-02-12 08:37:13'),
(3183, '2025-0556', 'Jenberly', 'Matinao', 'Desabille', '1N', '1st Year', 'BSIT', '2026-02-12 08:37:13'),
(3184, '2025-0560', 'John Kenneth', 'Sevillejo', '', '1E', '1st Year', 'BSIT', '2026-02-12 08:37:13');
INSERT INTO `students` (`id`, `student_number`, `fname`, `lname`, `mname`, `section`, `year_level`, `department`, `created_at`) VALUES
(3185, '2025-0562', 'Mark Fidel', 'Salazar', 'Gilbuena', '1S', '1st Year', 'BSIT', '2026-02-12 08:37:13'),
(3186, '2025-0564', 'Rogelio', 'Esgana', 'Ilustrisimo', '1S', '1st Year', 'BSIT', '2026-02-12 08:37:13'),
(3187, '2025-0565', 'France Andrew', 'Tero', 'Abad', '1S', '1st Year', 'BSIT', '2026-02-12 08:37:13'),
(3188, '2025-0569', 'Jealen', 'Rosalejos', 'Esgana', '1E', '1st Year', 'BSIT', '2026-02-12 08:37:13'),
(3189, '2025-0573', 'Ephraim', 'Dela Cruz', 'Cueva', '1N', '1st Year', 'BSIT', '2026-02-12 08:37:13'),
(3190, '2025-0575', 'Sean Andres', 'Quebec', 'Enriquez', '1E', '1st Year', 'BSIT', '2026-02-12 08:37:13'),
(3191, '2025-0584', 'Mariel', 'Quijano', 'Pastor', '1S', '1st Year', 'BSIT', '2026-02-12 08:37:13'),
(3192, '2025-0587', 'John Rendel', 'Metante', 'Bayon-on', '1S', '1st Year', 'BSIT', '2026-02-12 08:37:13'),
(3193, '2025-0589', 'Limar', 'Esgana', 'Gilbuena', '1S', '1st Year', 'BSIT', '2026-02-12 08:37:13'),
(3194, '2025-0590', 'Steve Austin', 'Alolor', 'Marabulas', '1S', '1st Year', 'BSIT', '2026-02-12 08:37:13'),
(3195, '2025-0596', 'Julie Ann', 'Verana', '', '1E', '1st Year', 'BSIT', '2026-02-12 08:37:13'),
(3196, '2025-0598', 'Jerwin', 'Almohallas', '', '1E', '1st Year', 'BSIT', '2026-02-12 08:37:13'),
(3197, '2025-0600', 'Jake', 'Quijano', 'Yhapon', '1E', '1st Year', 'BSIT', '2026-02-12 08:37:13'),
(3198, '2025-0602', 'Jonathan', 'Bulandres', 'Almonicar', '1S', '1st Year', 'BSIT', '2026-02-12 08:37:13'),
(3199, '2025-0612', 'John Rex', 'Ilustrisimo', 'Jabal', '1N', '1st Year', 'BSIT', '2026-02-12 08:37:13'),
(3200, '2025-0623', 'Mark James', 'Deniega', 'Baruc', '1S', '1st Year', 'BSIT', '2026-02-12 08:37:13'),
(3201, '2025-0629', 'Luis', 'Cueva Jr', 'Quimno', '1S', '1st Year', 'BSIT', '2026-02-12 08:37:13'),
(3202, '2025-0631', 'John Dave', 'Bayon-on', 'Cueva', '1S', '1st Year', 'BSIT', '2026-02-12 08:37:13'),
(3203, '2025-0634', 'Christian Ni�', 'Lugnasin', 'Baldapan', '1E', '1st Year', 'BSIT', '2026-02-12 08:37:13'),
(3204, '2025-0635', 'Jullian Jhay', 'Recto', '', '1E', '1st Year', 'BSIT', '2026-02-12 08:37:13'),
(3205, '2025-0636', 'Jamlee', 'Alob', 'Monta�', '1S', '1st Year', 'BSIT', '2026-02-12 08:37:13'),
(3206, '2025-0637', 'Andrie', 'Tiongzon', 'Santillan', '1E', '1st Year', 'BSIT', '2026-02-12 08:37:13'),
(3207, '2025-0638', 'Gezal', 'Desamparado', 'Rebamonte', '1S', '1st Year', 'BSIT', '2026-02-12 08:37:13'),
(3208, '2025-0639', 'Anjo', 'Siblero', 'Bacolod', '1S', '1st Year', 'BSIT', '2026-02-12 08:37:13'),
(3209, '2025-0640', 'James Michael', 'Maspara', 'Aguisanda', '1S', '1st Year', 'BSIT', '2026-02-12 08:37:13'),
(3210, '2025-0641', 'Johnrey', 'Gigante', 'Lawan', '1E', '1st Year', 'BSIT', '2026-02-12 08:37:13'),
(3211, '2025-0642', 'Mecayiela Jean', 'Bajo', 'Nicor', '1N', '1st Year', 'BSIT', '2026-02-12 08:37:13'),
(3212, '2025-0643', 'Carmela', 'Espina', 'Illusorio', '1N', '1st Year', 'BSIT', '2026-02-12 08:37:13'),
(3213, '2025-0645', 'Daniela', 'Gidayawan', 'Pasasadaba', '1N', '1st Year', 'BSIT', '2026-02-12 08:37:13'),
(3214, '2025-0646', 'Reniel', 'Ofril', 'Maru', '1N', '1st Year', 'BSIT', '2026-02-12 08:37:13'),
(3215, '2025-0647', 'Jirah Lee', 'Mata', 'Dela Pe�', '1N', '1st Year', 'BSIT', '2026-02-12 08:37:13'),
(3216, '2025-0648', 'Shane Kylee', 'Bawi-in', 'Maspara', '1N', '1st Year', 'BSIT', '2026-02-12 08:37:13'),
(3217, '2025-0655', 'Jose Ma.', 'Ilustrisimo', 'Gilbuena', '1E', '1st Year', 'BSIT', '2026-02-12 08:37:13'),
(3218, '2025-0672', 'Marina', 'Ribo', 'Pacilan', '1N', '1st Year', 'BSIT', '2026-02-12 08:37:13'),
(3219, '2025-0686', 'Rolando', 'Pepito', 'Sevillano', '1E', '1st Year', 'BSIT', '2026-02-12 08:37:13'),
(3220, '2025-0689', 'Catalino', 'Forrosuelo', 'Sevillejo', '1E', '1st Year', 'BSIT', '2026-02-12 08:37:13'),
(3221, '2025-0690', 'Peter John', 'Daruca', 'Bayon-on', '1E', '1st Year', 'BSIT', '2026-02-12 08:37:13'),
(3222, '2025-0702', 'Emmanuel', 'Tayactac', 'Escala', '1E', '1st Year', 'BSIT', '2026-02-12 08:37:13'),
(3223, '2025-0704', 'Ronalyn', 'Santiago', 'Pasicaran', '1E', '1st Year', 'BSIT', '2026-02-12 08:37:13'),
(3224, '2025-0709', 'Engel Mark', 'Bohol', 'Cahutay', '1E', '1st Year', 'BSIT', '2026-02-12 08:37:13'),
(3225, '2025-0711', 'Jhon Rhod', 'Layaog', 'Maspara', '1E', '1st Year', 'BSIT', '2026-02-12 08:37:13'),
(3226, '2025-0712', 'Ma. Lorcely Champagne', 'Carabio', 'Giducos', '1E', '1st Year', 'BSIT', '2026-02-12 08:37:13'),
(3227, '2025-0713', 'Chelshaye Rose', 'Hubahib', 'Ysulan', '1E', '1st Year', 'BSIT', '2026-02-12 08:37:13'),
(3228, '2025-0721', 'Jebert', 'Ramos', 'Almocera', '1E', '1st Year', 'BSIT', '2026-02-12 08:37:13'),
(3229, '2025-0724', 'Ni�', 'Dela Pe�', '', '1SE', '1st Year', 'BSIT', '2026-02-12 08:37:13'),
(3230, '2025-0731', 'Stephanie', 'Sumil', 'De Los Reyes', '1E', '1st Year', 'BSIT', '2026-02-12 08:37:13'),
(3231, '2025-0735', 'Negel', 'Escarlan', 'Giducos', '1N', '1st Year', 'BSIT', '2026-02-12 08:37:13'),
(3232, '2025-0755', 'Shanielyn', 'Pahuriray', 'Sisbre�', '1S', '1st Year', 'BSIT', '2026-02-12 08:37:13'),
(3233, '2025-0759', 'Gerald', 'Villaceran', 'Daruca', '1S', '1st Year', 'BSIT', '2026-02-12 08:37:13'),
(3234, '2025-0762', 'Jethro James', 'Ca�??', 'Basa', '1W', '1st Year', 'BSIT', '2026-02-12 08:37:13'),
(3235, '2025-0764', 'Mark', 'Oftana', 'Illustrisimo', '1N', '1st Year', 'BSIT', '2026-02-12 08:37:13'),
(3236, '2025-0765', 'John Kenneth', 'Baterna', 'Pepito', '1S', '1st Year', 'BSIT', '2026-02-12 08:37:13'),
(3237, '2025-0766', 'Jessalyn', 'Mulle', 'Requeron', '1S', '1st Year', 'BSIT', '2026-02-12 08:37:13'),
(3238, '2025-0769', 'Shehera', 'Desuyo', 'Santillan', '1SE', '1st Year', 'BSIT', '2026-02-12 08:37:13'),
(3239, '2025-0818', 'Rachell Lee', 'Catajay', 'Obsequias', '1S', '1st Year', 'BSIT', '2026-02-12 08:37:13'),
(3240, '2025-0820', 'Johncris', 'Mari�?', 'Maru', '1W', '1st Year', 'BSIT', '2026-02-12 08:37:13'),
(3241, '2025-0824', 'Joshwin', 'Palay', 'Gidayawan', '1SE', '1st Year', 'BSIT', '2026-02-12 08:37:13'),
(3242, '2025-0835', 'Jefferson', 'Batasin-in', '', '1SE', '1st Year', 'BSIT', '2026-02-12 08:37:13'),
(3243, '2025-0846', 'Dexter', 'Valencia', 'Layos', '1E', '1st Year', 'BSIT', '2026-02-12 08:37:13'),
(3244, '2025-0849', 'Eric', 'Rayco', 'Escobar', '1W', '1st Year', 'BSIT', '2026-02-12 08:37:13'),
(3245, '2025-0884', 'Erryl Jean', 'Alamo', 'Escaran', '1W', '1st Year', 'BSIT', '2026-02-12 08:37:13'),
(3246, '2025-0885', 'Cherry Mae', 'Segurado', 'Tamboboy', '1E', '1st Year', 'BSIT', '2026-02-12 08:37:13'),
(3247, '2025-0886', 'Klyx', 'Ara�', 'Bautista', '1SE', '1st Year', 'BSIT', '2026-02-12 08:37:13'),
(3248, '2025-0903', 'Arcelyn', 'Mondejar', 'Ilustrisimo', '1S', '1st Year', 'BSIT', '2026-02-12 08:37:13'),
(3249, '2025-0904', 'Jam Marvin', 'Desabille', 'Niedo', '1SE', '1st Year', 'BSIT', '2026-02-12 08:37:13'),
(3250, '2025-0933', 'John Ryan', 'Flora', 'Siblero', '1SE', '1st Year', 'BSIT', '2026-02-12 08:37:13'),
(3251, '2025-0934', 'Mark San', 'Paragsa', 'Alolor', '1SE', '1st Year', 'BSIT', '2026-02-12 08:37:13'),
(3252, '2025-0935', 'Jems Edward', 'Diongson', 'Forrosuelo', '1SE', '1st Year', 'BSIT', '2026-02-12 08:37:13'),
(3253, '2025-0936', 'Jhulia', 'Ofianga', 'Santillan', '1SE', '1st Year', 'BSIT', '2026-02-12 08:37:13'),
(3254, '2025-0937', 'Teodulo Ill', 'Inso', 'Peraan', '1SE', '1st Year', 'BSIT', '2026-02-12 08:37:13'),
(3255, '2025-0940', 'Emilio', 'Gomez', 'Vosotros', '1NW', '1st Year', 'BSIT', '2026-02-12 08:37:13'),
(3256, '2025-0948', 'Richmond', 'Ofianga', 'Parochel', '1SE', '1st Year', 'BSIT', '2026-02-12 08:37:13'),
(3257, '2025-0949', 'Belle Joe', 'Paspie', 'Desoyu', '1SE', '1st Year', 'BSIT', '2026-02-12 08:37:13'),
(3258, '2025-0950', 'Rico Jr.', 'Santillan', 'Desuyo', '1SE', '1st Year', 'BSIT', '2026-02-12 08:37:13'),
(3259, '2025-0953', 'Joshua', 'Ofianga', 'Parochel', '1SE', '1st Year', 'BSIT', '2026-02-12 08:37:13'),
(3260, '2025-0957', 'Bjay', 'Loon', 'M.', '1SE', '1st Year', 'BSIT', '2026-02-12 08:37:13'),
(3261, '2025-0973', 'Jennilyn', 'Faburada', 'Baterzal', '1W', '1st Year', 'BSIT', '2026-02-12 08:37:13'),
(3262, '2025-0974', 'Ayumi', 'Derayal', 'P.', '1W', '1st Year', 'BSIT', '2026-02-12 08:37:13'),
(3263, '2025-0982', 'Eliezel', 'Giducos', 'Estellore', '1W', '1st Year', 'BSIT', '2026-02-12 08:37:13'),
(3264, '2025-0986', 'Bernadeth Grace', 'Duran', 'Ca�??', '1W', '1st Year', 'BSIT', '2026-02-12 08:37:13'),
(3265, '2025-0991', 'Julius Ceasar', 'Espinosa', '', '1W', '1st Year', 'BSIT', '2026-02-12 08:37:13'),
(3266, '2025-0994', 'Christopher', 'Labayen', 'M.', '1W', '1st Year', 'BSIT', '2026-02-12 08:37:13'),
(3267, '2025-0998', 'Rovince', 'Santillan', 'Romero', '1W', '1st Year', 'BSIT', '2026-02-12 08:37:13'),
(3268, '2025-1003', 'Nino Kent', 'Deniega', 'Templado', '1W', '1st Year', 'BSIT', '2026-02-12 08:37:13'),
(3269, '2025-1008', 'Jofel John', 'Fariolen', 'Flores', '1W', '1st Year', 'BSIT', '2026-02-12 08:37:13'),
(3270, '2025-1011', 'Crispin John', 'Negredo', 'Veliganio', '1N', '1st Year', 'BSIT', '2026-02-12 08:37:13'),
(3271, '2025-1013', 'Fernando Jr.', 'Mata', 'Rebusit', '1SE', '1st Year', 'BSIT', '2026-02-12 08:37:13'),
(3272, '2025-1018', 'Kheyziel Mae', 'Malacad', 'Chavez', '1S', '1st Year', 'BSIT', '2026-02-12 08:37:13'),
(3273, '2025-1022', 'Rhean Paul', 'Zaballa', 'Medallo', '2E', '2nd Year', 'BSIT', '2026-02-12 08:37:13'),
(3274, '2025-1025', 'Art Adrian', 'Oftana', 'Villarin', '1W', '1st Year', 'BSIT', '2026-02-12 08:37:13'),
(3275, '2025-1032', 'Ramilo', 'Palad', 'Ochea', '1W', '1st Year', 'BSIT', '2026-02-12 08:37:13'),
(3276, '2025-1033', 'Crisabel', 'Bacolod', 'Caramelo', '1NW', '1st Year', 'BSIT', '2026-02-12 08:37:13'),
(3277, '2025-1035', 'Vriljun', 'Santillan', 'Layao', '1W', '1st Year', 'BSIT', '2026-02-12 08:37:13'),
(3278, '2025-1036', 'John Mark', 'Lagarit', 'Escalecas', '1SE', '1st Year', 'BSIT', '2026-02-12 08:37:13'),
(3279, '2025-1037', 'Jebby', 'Mulle', 'Delavega', '1N', '1st Year', 'BSIT', '2026-02-12 08:37:13'),
(3280, '2025-1043', 'Rosemarie', 'Cabaltera', 'Canoos', '1W', '1st Year', 'BSIT', '2026-02-12 08:37:13'),
(3281, '2025-1045', 'Crizan Glare', 'Gomos', 'Montolo', '1S', '1st Year', 'BSIT', '2026-02-12 08:37:13'),
(3282, '2025-1046', 'Vonne Karl', 'Espelita', 'Seronda', '1W', '1st Year', 'BSIT', '2026-02-12 08:37:13'),
(3283, '2025-1047', 'Jaica Rose', 'Villaester', 'Pacifico', '1E', '1st Year', 'BSIT', '2026-02-12 08:37:13'),
(3284, '2025-1048', 'Isra', 'Burlasa', 'Giganto', '1S', '1st Year', 'BSIT', '2026-02-12 08:37:13'),
(3285, '2025-1050', 'Maria Mae', 'Esgana', 'Sabal', '1W', '1st Year', 'BSIT', '2026-02-12 08:37:13'),
(3286, '2025-1051', 'Renalyn', 'Landao', 'Desabille', '1W', '1st Year', 'BSIT', '2026-02-12 08:37:13'),
(3287, '2025-1074', 'Martus', 'Elizamae', 'Saraga', '1SE', '1st Year', 'BSIT', '2026-02-12 08:37:13'),
(3288, '2025-1083', 'Eduardo', 'Canoy Jr', 'Ribo', '2E', '2nd Year', 'BSIT', '2026-02-12 08:37:13'),
(3289, '2025-1084', 'Jovencio Jr.', 'Santillan', 'Oflas', '2N', '2nd Year', 'BSIT', '2026-02-12 08:37:13'),
(3290, '2025-1085', 'Baldwin', 'Gilo', 'Mansueto', '2SE', '2nd Year', 'BSIT', '2026-02-12 08:37:13'),
(3291, '2025-1088', 'Gabriel Dave', 'Arcillas', 'Seville', '2E', '2nd Year', 'BSIT', '2026-02-12 08:37:13'),
(3292, '2025-1092', 'Danica', 'Anciano', '', '1N', '1st Year', 'BSIT', '2026-02-12 08:37:13'),
(3293, '2025-1093', 'Anna Mae', 'Cioco', 'Cantiller', '1N', '1st Year', 'BSIT', '2026-02-12 08:37:13'),
(3294, '2025-1094', 'Mark Angelou', 'Sabal', '', '1NE', '1st Year', 'BSIT', '2026-02-12 08:37:13'),
(3295, '2025-1095', 'Eraldjun', 'Santillan', 'Hintapa', '1N', '1st Year', 'BSIT', '2026-02-12 08:37:13'),
(3296, '2025-1098', 'Michel', 'Bayon-on', 'Giducos', '1N', '1st Year', 'BSIT', '2026-02-12 08:37:13'),
(3297, '2025-1104', 'Akemi', 'Tidoso', 'Paras', '1S', '1st Year', 'BSIT', '2026-02-12 08:37:13'),
(3298, '2025-1108', 'Nealvin', 'Doble', 'Almohallas', '1N', '1st Year', 'BSIT', '2026-02-12 08:37:13'),
(3299, '2025-1112', 'John Mark', 'Daruca', 'Pacanza', '1N', '1st Year', 'BSIT', '2026-02-12 08:37:13'),
(3300, '2025-1115', 'John Paul', 'Mata', 'Batain', '1N', '1st Year', 'BSIT', '2026-02-12 08:37:13'),
(3301, '2025-1121', 'John Alteo', 'Locaylocay', 'Asintista', '1N', '1st Year', 'BSIT', '2026-02-12 08:37:13'),
(3302, '2025-1123', 'Antonio Luis', 'Panganiban', '', '1E', '1st Year', 'BSIT', '2026-02-12 08:37:13'),
(3303, '2025-1124', 'Mark Steven', 'Gui��', 'Jumola', '1SE', '1st Year', 'BSIT', '2026-02-12 08:37:13'),
(3304, '2025-1125', 'Hernando', 'Loar', 'Abong', '1SE', '1st Year', 'BSIT', '2026-02-12 08:37:13'),
(3305, '2025-1126', 'James Dave', 'Batayola', 'Punay', '1SE', '1st Year', 'BSIT', '2026-02-12 08:37:13'),
(3306, '2025-1127', 'Triztan', 'Javier', 'Jay E.', '1SW', '1st Year', 'BSIT', '2026-02-12 08:37:13'),
(3307, '2025-1129', 'Jahzerah Mae', 'Ocso', 'Salinas', '1SE', '1st Year', 'BSIT', '2026-02-12 08:37:13'),
(3308, '2025-1136', 'Angel Reese', 'Lauron', 'Cordero', '1NE', '1st Year', 'BSIT', '2026-02-12 08:37:13'),
(3309, '2022-2029', 'LEONISIS', 'ASIS', 'MAGALLANES', '4-NORTH', '4th Year', 'BSIT', '2026-02-12 08:37:13'),
(3310, '2022-1924', 'NELSON JR.', 'PROJO', 'ARCO', '4-NORTH', '4th Year', 'BSIT', '2026-02-12 08:37:13'),
(3311, '2022-1887', 'HENRYL', 'PUNAY', 'UMBAO', '4-NORTH', '4th Year', 'BSIT', '2026-02-12 08:37:13'),
(3312, '2022-2032', 'NIÑO MARK', 'ZASPA', 'SETINTA', '4-NORTH', '4th Year', 'BSIT', '2026-02-12 08:37:13'),
(3313, '2022-1986', 'CRISTINE JANE', 'PAGHUBASAN', 'UMBAO', '4-NORTH', '4th Year', 'BSIT', '2026-02-12 08:37:13'),
(3314, '2022-1319', 'MARJANNY', 'ALOB', 'CASIPLE', '4-NORTH', '4th Year', 'BSIT', '2026-02-12 08:37:13'),
(3315, '2022-1754', 'KYLA MARIE', 'TEVES', 'ESPINOSA', '4-EAST', '4th Year', 'BSIT', '2026-02-12 08:37:13'),
(3316, '2022-1897', 'JOMAR', 'SETENTA', '', '4-NORTH', '4th Year', 'BSIT', '2026-02-12 08:37:13'),
(3317, '2022-1764', 'MARIAN', 'BACOLOD', 'CARANZO', '4-EAST', '4th Year', 'BSIT', '2026-02-12 08:37:13'),
(3318, '2022-1847', 'KC JOY', 'VELIGANILAO', 'SANTILLAN', '4-EAST', '4th Year', 'BSIT', '2026-02-12 08:37:13'),
(3319, '2022-1761', 'RONA', 'TAYACTAC', 'CORDERO', '4-EAST', '4th Year', 'BSIT', '2026-02-12 08:37:13'),
(3320, '2022-5090', 'ROSELYN', 'ROSALES', 'ANGCLA', '4-NORTH', '4th Year', 'BSIT', '2026-02-12 08:37:13'),
(3321, '2022-5023', 'LANNY', 'MORADAS', 'DOCDOCAN', '4-EAST', '4th Year', 'BSIT', '2026-02-12 08:37:13'),
(3322, '2022-3060', 'NIÑO MIKE', 'ZASPA', 'SETINTA', '4-WEST', '4th Year', 'BSIT', '2026-02-12 08:37:13'),
(3323, '2022-2065', 'CLAIRE', 'ILUSORIO', 'BATUSBATUSAN', '4-NORTH', '4th Year', 'BSIT', '2026-02-12 08:37:13'),
(3324, '2022-1768', 'MARYCRIS', 'PESCANTE', 'BATIANCILA', '4-EAST', '4th Year', 'BSIT', '2026-02-12 08:37:13'),
(3325, '2022-2073', 'RONALYN', 'LANUTAN', 'VILLARUEL', '4-NORTH', '4th Year', 'BSIT', '2026-02-12 08:37:13'),
(3326, '2022-1750', 'EMMA', 'GILTENDEZ', 'PASTOR', '4-EAST', '4th Year', 'BSIT', '2026-02-12 08:37:13'),
(3327, '2022-2004', 'JENNEFER', 'ESCARLAN', 'CARABALLE', '4-EAST', '4th Year', 'BSIT', '2026-02-12 08:37:13'),
(3328, '2022-1747', 'TRESHA MAE', 'DESUCATAN', '', '4-EAST', '4th Year', 'BSIT', '2026-02-12 08:37:13'),
(3329, '2022-7005', 'AMETIZ', 'BATIANCILA', 'CERNAL', '4-NORTH', '4th Year', 'BSIT', '2026-02-12 08:37:13'),
(3330, '2022-3057', 'FATIMA', 'ARNASAN', 'PERPIÑAN', '4-NORTH', '4th Year', 'BSIT', '2026-02-12 08:37:13'),
(3331, '2022-7006', 'FRANCISCO', 'FERNANDEZ JR.,', 'SANTIAGO', '4-EAST', '4th Year', 'BSIT', '2026-02-12 08:37:13'),
(3332, '2022-1709', 'LINALYN', 'ALOB', 'YSULAN', '4-NORTH', '4th Year', 'BSIT', '2026-02-12 08:37:13'),
(3333, '2022-5004', 'KHAMYR', 'ARAÑO', 'BAUTISTA', '4-WEST', '4th Year', 'BSIT', '2026-02-12 08:37:13'),
(3334, '2022-1780', 'MARY ANN', 'DUCAY', 'MULLE', '4-EAST', '4th Year', 'BSIT', '2026-02-12 08:37:13'),
(3335, '2022-5080', 'LEE', 'GILBUELA', 'SANTILLAN', '4-WEST', '4th Year', 'BSIT', '2026-02-12 08:37:13'),
(3336, '2022-3025', 'ELMER', 'ESPINOSA', 'DESCARTIN', '4-WEST', '4th Year', 'BSIT', '2026-02-12 08:37:13'),
(3337, '2022-3030', 'JOSHUA', 'PASTORPIDE', '', '4-WEST', '4th Year', 'BSIT', '2026-02-12 08:37:13'),
(3338, '2023-1237', 'MIRIAM', 'BATAIN', 'FARIOLEN', '4-WEST', '4th Year', 'BSIT', '2026-02-12 08:37:13'),
(3339, '2022-6030', 'JESSA MAE', 'ESCARAN', 'SALUDAR', '4-WEST', '4th Year', 'BSIT', '2026-02-12 08:37:13'),
(3340, '2022-4066', 'AIAN', 'DESUCATAN', 'CARALLAS', '4-WEST', '4th Year', 'BSIT', '2026-02-12 08:37:13'),
(3341, '2022-4001', 'JULINETTE', 'BATIRZAL', 'SALUDAR', '4-WEST', '4th Year', 'BSIT', '2026-02-12 08:37:13'),
(3342, '2022-1836', 'VINCENT', 'PESCANTE', 'ESCARAN', '4-EAST', '4th Year', 'BSIT', '2026-02-12 08:37:13'),
(3343, '2022-1981', 'JOSHUA', 'ALBURO', 'OFTANA', '4-EAST', '4th Year', 'BSIT', '2026-02-12 08:37:13'),
(3344, '2022-3031', 'JEV', 'BAUTRO', 'JARINA', '4-WEST', '4th Year', 'BSIT', '2026-02-12 08:37:13'),
(3345, '2022-2013', 'MARK GERALD', 'QUEZON', 'GIPIALA', '4-NORTH', '4th Year', 'BSIT', '2026-02-12 08:37:13'),
(3346, '2022-6039', 'LEIRA JADE', 'REYES', '', '4-WEST', '4th Year', 'BSIT', '2026-02-12 08:37:13'),
(3347, '2022-6029', 'RILY', 'REYES', 'ALMOCERA', '4-WEST', '4th Year', 'BSIT', '2026-02-12 08:37:13'),
(3348, '2021-1112', 'ANNA MAE', 'EBEN', 'ALOLOR', '4-WEST', '4th Year', 'BSIT', '2026-02-12 08:37:13'),
(3349, '2022-5088', 'SHESHAN', 'BOLIVAR', 'UNGON', '4-WEST', '4th Year', 'BSIT', '2026-02-12 08:37:13'),
(3350, '2022-2003', 'RUDELYN', 'ILLUT', 'OFQUERIA', '4-EAST', '4th Year', 'BSIT', '2026-02-12 08:37:13'),
(3351, '2022-2006', 'JONA MAE', 'ILLUT', 'GIDUCOS', '4-EAST', '4th Year', 'BSIT', '2026-02-12 08:37:13'),
(3352, '2022-5068', 'CARL JASSON', 'ILUSTRISIMO', 'OFIASA', '4-EAST', '4th Year', 'BSIT', '2026-02-12 08:37:13'),
(3353, '2022-1969', 'WARREN', 'ILUSTRISIMO', 'JUMANTOC', '4-EAST', '4th Year', 'BSIT', '2026-02-12 08:37:13'),
(3354, '2022-1970', 'CRISTINA', 'ILUSTRISIMO', 'JUMANTOC', '4-EAST', '4th Year', 'BSIT', '2026-02-12 08:37:13'),
(3355, '2022-2005', 'CHUNLY JANE', 'GILBUENA', 'GIDUCOS', '4-EAST', '4th Year', 'BSIT', '2026-02-12 08:37:13'),
(3356, '2022-2050', 'JOSHUA', 'DESUYO', 'MARABI', '4-NORTH', '4th Year', 'BSIT', '2026-02-12 08:37:13'),
(3357, '2022-4045', 'JOERGE', 'YANGAO', 'BILBAO', '4-WEST', '4th Year', 'BSIT', '2026-02-12 08:37:13'),
(3358, '2022-5059', 'GENILYN', 'DESABILLE', 'VELIGANIO', '4-WEST', '4th Year', 'BSIT', '2026-02-12 08:37:13'),
(3359, '2022-5007', 'TERESA', 'CORDOVA', 'MATA', '4-WEST', '4th Year', 'BSIT', '2026-02-12 08:37:13'),
(3360, '2022-7039', 'JOHN PATRICK', 'SELLE', '', '4-WEST', '4th Year', 'BSIT', '2026-02-12 08:37:13'),
(3361, '2022-3073', 'JOHN PAUL', 'RAZONABLE', 'MARU', '4-WEST', '4th Year', 'BSIT', '2026-02-12 08:37:13'),
(3362, '2022-3018', 'JENFORD', 'ALBACIETE', '', '4-EAST', '4th Year', 'BSIT', '2026-02-12 08:37:13'),
(3363, '2022-7007', 'JOYCE', 'COSTA', 'MATA', '4-WEST', '4th Year', 'BSIT', '2026-02-12 08:37:13'),
(3364, '2022-4038', 'JESSAMEN', 'ILUSTRISIMO', 'ILLUT', '4-WEST', '4th Year', 'BSIT', '2026-02-12 08:37:13'),
(3365, '2022-5037', 'ARNEL', 'REBAMONTE', 'ILUSTRISIMO', '4-WEST', '4th Year', 'BSIT', '2026-02-12 08:37:13'),
(3366, '2022-4056', 'ANGELO', 'DERDER', 'GIDUCOS', '4-WEST', '4th Year', 'BSIT', '2026-02-12 08:37:13'),
(3367, '2021-1510', 'JONALYN', 'GIDUCOS', '', '4-WEST', '4th Year', 'BSIT', '2026-02-12 08:37:13'),
(3368, '2021-1406', 'ZENNY', 'CERVANTES', 'MEDALLO', '4-WEST', '4th Year', 'BSIT', '2026-02-12 08:37:13'),
(3369, '2022-4099', 'DEN JOHN', 'DESPI', 'ISABEL', '4-WEST', '4th Year', 'BSIT', '2026-02-12 08:37:13'),
(3370, '2022-2096', 'CHARLES', 'DUMABOK', 'SEGOVIA', '4-NORTH', '4th Year', 'BSIT', '2026-02-12 08:37:13'),
(3371, '2022-2027', 'PRINCESS MARTENE', 'BATAYOLA', '', '4-NORTH', '4th Year', 'BSIT', '2026-02-12 08:37:13'),
(3372, '2022-1950', 'EVRANCE', 'CARACAS', 'GILBUENA', '4-NORTH', '4th Year', 'BSIT', '2026-02-12 08:37:13'),
(3373, '2022-1743', 'JOHN NILO', 'VERSOZA', 'ILUSTRISIMO', '4-NORTH', '4th Year', 'BSIT', '2026-02-12 08:37:13'),
(3374, '2022-2095', 'EDDIE JR.', 'SEGOBIA', 'LAYAOG', '4-NORTH', '4th Year', 'BSIT', '2026-02-12 08:37:13'),
(3375, '2021-1691', 'PEARL ANNE', 'ARSOLON', 'BULADO', '4-SOUTH', '4th Year', 'BSIT', '2026-02-12 08:37:13'),
(3376, '2022-5000', 'JUNNICK', 'BAUTRO', 'VILLACERAN', '4-WEST', '4th Year', 'BSIT', '2026-02-12 08:37:13'),
(3377, '2022-4097', 'CRISTINA', 'BILBAO', 'REBAMONTE', '4-WEST', '4th Year', 'BSIT', '2026-02-12 08:37:13'),
(3378, '2022-7056', 'AMRAFEL', 'ALENSONORIN', 'ANIBAN', '4-WEST', '4th Year', 'BSIT', '2026-02-12 08:37:13'),
(3379, '2022-7029', 'ARA MAE', 'DEMORAL', 'RELATOS', '4-WEST', '4th Year', 'BSIT', '2026-02-12 08:37:13'),
(3380, '2021-1254', 'ANGELYN', 'BACOLOD', 'CORDOVA', '4-WEST', '4th Year', 'BSIT', '2026-02-12 08:37:13'),
(3381, '2022-1813', 'DANIELLE ACE', 'SEVILLEJO', '', '4-EAST', '4th Year', 'BSIT', '2026-02-12 08:37:13'),
(3382, '2022-5084', 'JERRY', 'ESCARIO', 'LALICAN', '4-EAST', '4th Year', 'BSIT', '2026-02-12 08:37:13'),
(3383, '2022-1956', 'SAMANTHA', 'CENA', 'DELA PEÑA', '4-EAST', '4th Year', 'BSIT', '2026-02-12 08:37:13'),
(3384, '2022-3083', 'RODGE', 'LAURENTE', 'FLORES', '4-EAST', '4th Year', 'BSIT', '2026-02-12 08:37:13'),
(3385, '2022-6078', 'MARK JOHN', 'LAWAN', 'SILVA', '4-EAST', '4th Year', 'BSIT', '2026-02-12 08:37:13'),
(3386, '2022-1758', 'HARELINE', 'JIMENEZ', '', '4-EAST', '4th Year', 'BSIT', '2026-02-12 08:37:13'),
(3387, '2022-1777', 'RICAMAE JANE', 'SANTILLAN', 'MULLE', '4-EAST', '4th Year', 'BSIT', '2026-02-12 08:37:13'),
(3388, '2022-5026', 'JAN ROBERT', 'FRANCISCO', 'QUEZON', '4-NORTH', '4th Year', 'BSIT', '2026-02-12 08:37:13'),
(3389, '2022-1907', 'JUNEL', 'DABALOS', 'REBAMONTE', '4-EAST', '4th Year', 'BSIT', '2026-02-12 08:37:13'),
(3390, '2022-1831', 'JUNEL', 'DOLINO', '', '4-EAST', '4th Year', 'BSIT', '2026-02-12 08:37:13'),
(3391, '2022-6076', 'JOHN REY', 'ANGO', 'BAUTRO', '4-EAST', '4th Year', 'BSIT', '2026-02-12 08:37:13'),
(3392, '2022-1896', 'JOHN LOYD', 'SARABIA', '', '4-EAST', '4th Year', 'BSIT', '2026-02-12 08:37:13'),
(3393, '2022-1876', 'JOHN RALPH', 'ALMODIEL', 'JARINA', '4-EAST', '4th Year', 'BSIT', '2026-02-12 08:37:13'),
(3394, '2022-5021', 'ROYCE VINCENT', 'LANDERO', 'SANTILLAN', '4-EAST', '4th Year', 'BSIT', '2026-02-12 08:37:13'),
(3395, '2022-1909', 'KEITH IAN', 'DESAMPARADO', 'MANSUETO', '4-EAST', '4th Year', 'BSIT', '2026-02-12 08:37:13'),
(3396, '2022-1783', 'JAKE', 'RODRIGUEZ', 'BALLANO', '4-EAST', '4th Year', 'BSIT', '2026-02-12 08:37:13'),
(3397, '2022-5089', 'CHRISTINE', 'FORROSUELO', 'SINAMBONG', '4-WEST', '4th Year', 'BSIT', '2026-02-12 08:37:13'),
(3398, '2022-4028', 'JERRY', 'NASOL', 'MATA', '4-EAST', '4th Year', 'BSIT', '2026-02-12 08:37:13'),
(3399, '2022-1922', 'KYLE', 'GADIANO', 'DESABILLE', '4-EAST', '4th Year', 'BSIT', '2026-02-12 08:37:13'),
(3400, '2022-1936', 'MELCHADES', 'MANSUETO', 'CAPUS', '4-EAST', '4th Year', 'BSIT', '2026-02-12 08:37:13'),
(3401, '2022-6008', 'DAVE', 'JANGZON', 'OFIANGA', '4-EAST', '4th Year', 'BSIT', '2026-02-12 08:37:13'),
(3402, '2022-3076', 'JOHN ERLOU', 'BALIJADO', 'ALVERIO', '4-WEST', '4th Year', 'BSIT', '2026-02-12 08:37:13'),
(3403, '2021-1562', 'HAZEL MAE', 'BATUHAN', 'LAYAOG', '4-NORTH', '4th Year', 'BSIT', '2026-02-12 08:37:13'),
(3404, '2021-1344', 'BRIAN NICK', 'ACORDA', 'MULLE', '4-NORTH', '4th Year', 'BSIT', '2026-02-12 08:37:13'),
(3405, '2022-7009', 'NIÑO', 'BACOLOD', 'SEVILLA', '4-NORTH', '4th Year', 'BSIT', '2026-02-12 08:37:13'),
(3406, '2022-7015', 'JOHN', 'GRANDE', 'KENNETH R.', '4-WEST', '4th Year', 'BSIT', '2026-02-12 08:37:13'),
(3407, '2022-5060', 'MARK GENO', 'INSO', 'COLAMBOT', '4-WEST', '4th Year', 'BSIT', '2026-02-12 08:37:13'),
(3408, '2022-1789', 'JIMELYN', 'CAPURAS', 'CUEVA', '4-EAST', '4th Year', 'BSIT', '2026-02-12 08:37:13'),
(3409, '2021-0748', 'NANCY', 'LOREDO', 'SANTILLAN', '4-EAST', '4th Year', 'BSIT', '2026-02-12 08:37:13'),
(3410, '2022-7086', 'NIÑA JANE', 'ALOLOR', 'ESPINA', '4-EAST', '4th Year', 'BSIT', '2026-02-12 08:37:13'),
(3411, '2021-7026', 'DESIREE', 'APAWAN', 'ILLUSTRISIMO', '4-EAST', '4th Year', 'BSIT', '2026-02-12 08:37:13'),
(3412, '2022-1843', 'KERBIE', 'VILLACERAN', 'FLORES', '4-EAST', '4th Year', 'BSIT', '2026-02-12 08:37:13'),
(3413, '2022-2000', 'HASAN AHMAD', 'SYED', 'MANGUBAT', '4-NORTH', '4th Year', 'BSIT', '2026-02-12 08:37:13'),
(3414, '2022-2008', 'JOVIE NORIELLE', 'ILUSTRISIMO', '', '4-NORTH', '4th Year', 'BSIT', '2026-02-12 08:37:13'),
(3415, '2020-0438', 'EDDIESON', 'ARELLANO', 'MARU', '4-NORTH', '4th Year', 'BSIT', '2026-02-12 08:37:13'),
(3416, '2022-3010', 'IAN JAY', 'FARIOLEN', '', '4-NORTH', '4th Year', 'BSIT', '2026-02-12 08:37:13'),
(3417, '2022-3011', 'KIM ROD', 'VELIGANIO', 'OFQUERIA', '4-NORTH', '4th Year', 'BSIT', '2026-02-12 08:37:13'),
(3418, '2022-7028', 'AGIE', 'VERALLO', 'BATASIN-IN', '4-WEST', '4th Year', 'BSIT', '2026-02-12 08:37:13'),
(3419, '2022-7026', 'FLORDILIZE', 'SEVILLENO', 'ESCALICAS', '4-EAST', '4th Year', 'BSIT', '2026-02-12 08:37:13'),
(3420, '2022-5085', 'JENNIFER', 'DESUYO', 'DE LA PEÑA', '4-WEST', '4th Year', 'BSIT', '2026-02-12 08:37:13'),
(3421, '2022-2093', 'SHANE LEMUEL', 'OLINARES', 'CAYOTE', '4-NORTH', '4th Year', 'BSIT', '2026-02-12 08:37:13'),
(3422, '2022-2086', 'DEXTER', 'VILLACRUCIS', 'HERMOGILA', '4-NORTH', '4th Year', 'BSIT', '2026-02-12 08:37:13'),
(3423, '2022-2035', 'JULIUS', 'ROSALES', 'PEREZ', '4-NORTH', '4th Year', 'BSIT', '2026-02-12 08:37:13'),
(3424, '2022-6095', 'JONATHAN', 'ESGANA', 'VERONIO', '4-WEST', '4th Year', 'BSIT', '2026-02-12 08:37:13'),
(3425, '2021-6095', 'JECELO', 'ILUSTRISIMO', 'DESCARTIN', '4-WEST', '4th Year', 'BSIT', '2026-02-12 08:37:13'),
(3426, '2022-7117', 'ELBERT', 'MANSUETO', 'OFIANGA', '4-WEST', '4th Year', 'BSIT', '2026-02-12 08:37:13'),
(3427, '2021-0866', 'ISHIE MYCA', 'JAYME', '', '4-EAST', '4th Year', 'BSIT', '2026-02-12 08:37:13'),
(3428, '2022-3041', 'SEAN KEVIN', 'CHAVEZ', 'HERMOSO', '4-WEST', '4th Year', 'BSIT', '2026-02-12 08:37:13'),
(3429, '2022-4050', 'KEVIN ADRIAN', 'PACINIO', 'TURBANOS', '4-NORTH', '4th Year', 'BSIT', '2026-02-12 08:37:13'),
(3430, '2022-4073', 'SHERY MAE', 'DORIN', 'MAROLLANO', '4-WEST', '4th Year', 'BSIT', '2026-02-12 08:37:13'),
(3431, '2022-7018', 'IGNATIUS BARON', 'RIO', 'PANGILINAN', '4-WEST', '4th Year', 'BSIT', '2026-02-12 08:37:13'),
(3432, '2022-5043', 'JUSTINE', 'BELMONTE', '', '4-WEST', '4th Year', 'BSIT', '2026-02-12 08:37:13'),
(3433, '2022-5057', 'JUSTIN MARK', 'KAQUILALA', 'OLINARES', '4-EAST', '4th Year', 'BSIT', '2026-02-12 08:37:13'),
(3434, '2020-0130', 'ROMMEL', 'DESABILLE', 'RELOS', '4-EAST', '4th Year', 'BSIT', '2026-02-12 08:37:13'),
(3435, '2022-5032', 'MICHAEL JOSH', 'KAQUILALA', 'OLINARES', '4-WEST', '4th Year', 'BSIT', '2026-02-12 08:37:13'),
(3436, '2021-1594', 'CHARLES CHRISTIAN', 'LUGNASIN', '', '4-WEST', '4th Year', 'BSIT', '2026-02-12 08:37:13'),
(3437, '2022-2045', 'JOHN PAUL', 'GILBUELA', 'CENA', '4-NORTH', '4th Year', 'BSIT', '2026-02-12 08:37:13'),
(3438, '2022-6087', 'SHERWIN', 'SUMBI', 'ALOLOR', '4-NORTH', '4th Year', 'BSIT', '2026-02-12 08:37:13'),
(3439, '2022-5092', 'CHARLIE', 'NEGAPATAN', 'TORRECAMPO', '4-NORTH', '4th Year', 'BSIT', '2026-02-12 08:37:13'),
(3440, '2022-5002', 'KARL DENRALF', 'NEGAPATAN', 'PASTITEO', '4-NORTH', '4th Year', 'BSIT', '2026-02-12 08:37:13'),
(3441, '2022-5093', 'ARWIN', 'LORCA', 'ILUSTRISIMO', '4-NORTH', '4th Year', 'BSIT', '2026-02-12 08:37:13'),
(3442, '2022-4058', 'DOMINEC', 'LAYA-OG', '', '4-WEST', '4th Year', 'BSIT', '2026-02-12 08:37:13'),
(3443, '2020-0664', 'ROLDAN', 'LOZADA, JR.', 'CARABALLE', '4-NORTH', '4th Year', 'BSIT', '2026-02-12 08:37:13'),
(3444, '2022-4069', 'KENNETH', 'GIDUCOS', 'ESCARAN', '4-WEST', '4th Year', 'BSIT', '2026-02-12 08:37:13'),
(3445, '2022-1798', 'ARGIE', 'GILBUENA', 'CERVANTES', '4-WEST', '4th Year', 'BSIT', '2026-02-12 08:37:13'),
(3446, '2022-4070', 'PAUL MARK', 'DESUYO', 'VILLACERAN', '4-WEST', '4th Year', 'BSIT', '2026-02-12 08:37:13'),
(3447, '2022-4067', 'JOHN MARQUE', 'VILLACERAN', '', '4-WEST', '4th Year', 'BSIT', '2026-02-12 08:37:13'),
(3448, '2022-5020', 'REMAR', 'MARABI', 'GIDUCOS', '4-NORTH', '4th Year', 'BSIT', '2026-02-12 08:37:13'),
(3449, '2021-0869', 'JERICHO', 'DESPI', 'N/A', '4-WEST', '4th Year', 'BSIT', '2026-02-12 08:37:13'),
(3450, '2020-0272', 'FRED MARK', 'EMNACIN', 'N/A', '4-WEST', '4th Year', 'BSIT', '2026-02-12 08:37:13'),
(3451, '2020-0314', 'RACKY JAY', 'JUMANTOC', 'ESCALA', '4-WEST', '4th Year', 'BSIT', '2026-02-12 08:37:13'),
(3452, '2018-0421', 'JOHN HUMPHREY', 'CERVANTES', 'CARANZO', '4-NORTH', '4th Year', 'BSIT', '2026-02-12 08:37:13'),
(3453, '2018-0477', 'JAZER', 'DELA CRUZ', 'AREVALO', '4-NORTH', '4th Year', 'BSIT', '2026-02-12 08:37:13'),
(3454, '2021-1718', 'REJULES', 'HISTORIA', 'BACOLOD', '4-WEST', '4th Year', 'BSIT', '2026-02-12 08:37:13'),
(3455, '2020-0129', 'RADEN JOSHUA', 'HIBA', 'MARU', '4-WEST', '4th Year', 'BSIT', '2026-02-12 08:37:13'),
(3456, '2020-0332', 'SHEENA MAE', 'LAWAN', 'SILVA', '4-WEST', '4th Year', 'BSIT', '2026-02-12 08:37:13'),
(3457, '2019-0198', 'ANTHONY', 'ABING', 'APAWAN', '4-WEST', '4th Year', 'BSIT', '2026-02-12 08:37:13'),
(3458, '2021-1675', 'KEITH ANGELO', 'DEO', 'SEARES', '4-WEST', '4th Year', 'BSIT', '2026-02-12 08:37:13'),
(3459, '2021-1617', 'JULIAN', 'SEVILLENO JR.', 'LIBRE', '4-WEST', '4th Year', 'BSIT', '2026-02-12 08:37:13'),
(3460, '2021-1266', 'JOHNIEL', 'AMADEO', 'GIDAWAYAN', '4-WEST', '4th Year', 'BSIT', '2026-02-12 08:37:13'),
(3461, '2021-1654', 'ANGELITO', 'REBAMONTE', 'N/A', '4-NORTH', '4th Year', 'BSIT', '2026-02-12 08:37:13'),
(3462, '2021-1221', 'JANREY', 'CARANO-O', 'QUIJANO', '4-NORTH', '4th Year', 'BSIT', '2026-02-12 08:37:13'),
(3463, '2021-1736', 'JOHN MIGUEL', 'ANACAN', 'NAVARETTE', '4-NORTH', '4th Year', 'BSIT', '2026-02-12 08:37:13'),
(3464, '2021-1052', 'RONA JEAN', 'UMBAO', 'EACALA', '4-EAST', '4th Year', 'BSIT', '2026-02-12 08:37:13'),
(3465, '2021-1001', 'CLAVELLE', 'APAWAN', 'OROPESA', '4-EAST', '4th Year', 'BSIT', '2026-02-12 08:37:13'),
(3466, '2019-0347', 'GERALYN', 'GARCIA', 'SARABIA', '4-EAST', '4th Year', 'BSIT', '2026-02-12 08:37:13'),
(3467, '2022-2028', 'JAY ANN', 'ESCALICAS', 'GIDUQUIO', '4-NORTH', '4th Year', 'BSIT', '2026-02-12 08:37:13'),
(3468, '2023-1228', 'MEAH JEAN', 'CAMIGUING', 'PANCHO', '4-NORTH', '4th Year', 'BSIT', '2026-02-12 08:37:13'),
(3469, '2023-0475', 'JOHN LOUISE', 'ESPINOSA', 'SAPALECIO', '3-NORTHEAST', '3rd Year', 'BSIT', '2026-02-12 08:37:13'),
(3470, '2023-0617', 'JESS VENCENT', 'VELLAESTER', '', '3-NORTHEAST', '3rd Year', 'BSIT', '2026-02-12 08:37:13'),
(3471, '2023-1144', 'RUCHIELEE', 'MARU', 'SANTILLAN', '3-NORTHEAST', '3rd Year', 'BSIT', '2026-02-12 08:37:13'),
(3472, '2023-1079', 'RONALIE', 'AQUE', 'BORNALES', '3-NORTHEAST', '3rd Year', 'BSIT', '2026-02-12 08:37:13'),
(3473, '2023-0913', 'RONALD', 'FERNANDEZ JR.', 'MATA', '3-WEST', '3rd Year', 'BSIT', '2026-02-12 08:37:13'),
(3474, '2023-0451', 'JAMES LORENCE', 'CERNAL', 'VALLEJOS', '3-NORTHEAST', '3rd Year', 'BSIT', '2026-02-12 08:37:13'),
(3475, '2023-1080', 'REMLAN', 'JAMILI', 'JUVAYAN', '3-SOUTHWEST', '3rd Year', 'BSIT', '2026-02-12 08:37:13'),
(3476, '2023-0635', 'JUDEN CRIS', 'ABELLO', 'DESCARTIN', '3-WEST', '3rd Year', 'BSIT', '2026-02-12 08:37:13'),
(3477, '2023-0685', 'RIZA', 'SALINAS', 'YBANEZ', '3-SOUTH', '3rd Year', 'BSIT', '2026-02-12 08:37:13'),
(3478, '2023-0904', 'MA.JOELETTE MAY', 'OFQUERIA', 'FARIOLEN', '3-SOUTH', '3rd Year', 'BSIT', '2026-02-12 08:37:13'),
(3479, '2023-1132', 'JOHN PHILIP', 'PARILLA', 'VELIGAÑO', '3-SOUTHWEST', '3rd Year', 'BSIT', '2026-02-12 08:37:13'),
(3480, '2023-0400', 'JOSHUA', 'SEGOVIA', 'SOLITARIO', '3-SOUTHWEST', '3rd Year', 'BSIT', '2026-02-12 08:37:13'),
(3481, '2023-1497', 'KEILORD', 'ALMOCERA', 'BACOLOD', '3-NORTHWEST', '3rd Year', 'BSIT', '2026-02-12 08:37:13'),
(3482, '2023-1296', 'FRANCIS NICOLE', 'PASTOR', 'MARTUS', '3-WEST', '3rd Year', 'BSIT', '2026-02-12 08:37:13'),
(3483, '2023-1297', 'CHRISTIAN DY', 'REBUSIT', 'TAPAO', '3-NORTHEAST', '3rd Year', 'BSIT', '2026-02-12 08:37:13'),
(3484, '2023-0926', 'JEREMIAS, JR.', 'CABAHUG', 'BATIANCILA', '3-NORTHEAST', '3rd Year', 'BSIT', '2026-02-12 08:37:13'),
(3485, '2023-0363', 'MYLA', 'MATA', 'DESUCATAN', '3-NORTHEAST', '3rd Year', 'BSIT', '2026-02-12 08:37:13'),
(3486, '2023-1234', 'JISEN', 'YASE', 'ARRANCHADO', '3-WEST', '3rd Year', 'BSIT', '2026-02-12 08:37:13'),
(3487, '2023-0009', 'MIGUEL', 'PLASENCIA', 'CARABUENA', '3-WEST', '3rd Year', 'BSIT', '2026-02-12 08:37:13'),
(3488, '2023-0369', 'ANNA MAREI', 'MANZANARES', 'DILAO', '3-NORTHEAST', '3rd Year', 'BSIT', '2026-02-12 08:37:13'),
(3489, '2023-1000', 'MELJOY', 'FARIOLEN', 'MIAO', '3-NORTHEAST', '3rd Year', 'BSIT', '2026-02-12 08:37:13'),
(3490, '2023-0478', 'REWEL JR.', 'BATIANCILA', 'VELIGANIO', '3-SOUTHWEST', '3rd Year', 'BSIT', '2026-02-12 08:37:13'),
(3491, '2023-0341', 'JENNY', 'GILBUENA', 'SALINAS', '3-SOUTHWEST', '3rd Year', 'BSIT', '2026-02-12 08:37:13'),
(3492, '2023-0129', 'MAY', 'MANATAD', 'CAHUTAY', '3-SOUTHWEST', '3rd Year', 'BSIT', '2026-02-12 08:37:13'),
(3493, '2023-0326', 'REYMART', 'MALINAO', 'MEDALLO', '3-WEST', '3rd Year', 'BSIT', '2026-02-12 08:37:13'),
(3494, '2023-1045', 'ISABEL', 'VERALLO', 'LAYESE', '3-SOUTH', '3rd Year', 'BSIT', '2026-02-12 08:37:13'),
(3495, '2023-0937', 'LAVIGN', 'ALOYAN', 'HOMENA', '3-SOUTHWEST', '3rd Year', 'BSIT', '2026-02-12 08:37:13'),
(3496, '2023-0927', 'AIRENE', 'HIJAPON', 'BATIANCILA', '3-SOUTHWEST', '3rd Year', 'BSIT', '2026-02-12 08:37:13'),
(3497, '2023-0264', 'SHANE LOU', 'NECESARIO', 'SEAS', '3-SOUTHWEST', '3rd Year', 'BSIT', '2026-02-12 08:37:13'),
(3498, '2023-0766', 'KYZEL', 'ALEVIADO', 'FERNANDEZ', '3-SOUTH', '3rd Year', 'BSIT', '2026-02-12 08:37:13'),
(3499, '2023-1081', 'MATEO JR', 'ALMONICAR', 'AGUILAR', '3-SOUTHWEST', '3rd Year', 'BSIT', '2026-02-12 08:37:13'),
(3500, '2023-0340', 'MERIAM', 'SALINAS', 'ESCALA', '3-WEST', '3rd Year', 'BSIT', '2026-02-12 08:37:13'),
(3501, '2023-0956', 'MARY GRACE', 'MULLE', 'DESPI', '3-WEST', '3rd Year', 'BSIT', '2026-02-12 08:37:13'),
(3502, '2023-1398', 'KRISTEL', 'RAYCO', 'LAYAO', '3-WEST', '3rd Year', 'BSIT', '2026-02-12 08:37:13'),
(3503, '2023-1155', 'CHERRY ROSE', 'OFTANA', 'OFQUERIA', '3-SOUTHWEST', '3rd Year', 'BSIT', '2026-02-12 08:37:13'),
(3504, '2023-0102', 'JUVIELYN', 'DELA PEÑA', 'SEAS', '3-NORTHEAST', '3rd Year', 'BSIT', '2026-02-12 08:37:13'),
(3505, '2023-0229', 'CHRISTINE JOY', 'REBOSIT', 'ABAO', '3-NORTHEAST', '3rd Year', 'BSIT', '2026-02-12 08:37:13'),
(3506, '2023-0961', 'SHERYL', 'TAYACTAC', 'DESPI', '3-SOUTH', '3rd Year', 'BSIT', '2026-02-12 08:37:13'),
(3507, '2023-0396', 'KATE ROSEL', 'ALONTAGA', 'BATIANCILA', '3-NORTHEAST', '3rd Year', 'BSIT', '2026-02-12 08:37:13'),
(3508, '2023-0529', 'MYLEN', 'OMANGAYON', 'BAUTRO', '3-NORTH', '3rd Year', 'BSIT', '2026-02-12 08:37:13'),
(3509, '2023-1303', 'REYJANE', 'YBAÑEZ', 'MONAHAN', '3-WEST', '3rd Year', 'BSIT', '2026-02-12 08:37:13'),
(3510, '2023-1054', 'JUNMARK', 'GIDUCOS', 'BATIANCILA', '3-NORTHEAST', '3rd Year', 'BSIT', '2026-02-12 08:37:13'),
(3511, '2023-0830', 'JENNY', 'ABING', 'OMBING', '3-SOUTH', '3rd Year', 'BSIT', '2026-02-12 08:37:13'),
(3512, '2023-1299', 'JUDY ANN', 'ABALLE', 'LEQUIN', '3-NORTHEAST', '3rd Year', 'BSIT', '2026-02-12 08:37:13'),
(3513, '2023-1001', 'MELJEAN', 'FARIOLEN', 'MIAO', '3-NORTHEAST', '3rd Year', 'BSIT', '2026-02-12 08:37:13'),
(3514, '2023-0034', 'JUPITER', 'BOTILLA', 'ESTENZO', '3-WEST', '3rd Year', 'BSIT', '2026-02-12 08:37:13'),
(3515, '2023-0520', 'RENALYN', 'CARACENA', 'BAYON-ON', '3-NORTHEAST', '3rd Year', 'BSIT', '2026-02-12 08:37:13'),
(3516, '2023-0120', 'LAWRENCE', 'ACOSTA', 'TARA', '3-WEST', '3rd Year', 'BSIT', '2026-02-12 08:37:13'),
(3517, '2023-1310', 'JOHN GABRIEL', 'TOYCO', 'HEQUILAN', '3-WEST', '3rd Year', 'BSIT', '2026-02-12 08:37:13'),
(3518, '2023-0231', 'MARY JANE', 'BATINDAAN', 'PASTORITE', '3-WEST', '3rd Year', 'BSIT', '2026-02-12 08:37:13'),
(3519, '2023-0636', 'ELJOY', 'PASIGAY', 'POSTERO', '3-WEST', '3rd Year', 'BSIT', '2026-02-12 08:37:13'),
(3520, '2023-0144', 'VENZ HARRY', 'MACELLONES', '', '3-NORTHEAST', '3rd Year', 'BSIT', '2026-02-12 08:37:13'),
(3521, '2023-0393', 'ROMNICK', 'BELAIS', 'CORNEA', '3-NORTHEAST', '3rd Year', 'BSIT', '2026-02-12 08:37:13'),
(3522, '2023-1432', 'JAN ANTHONY', 'CASIPONG', 'JUANICO', '3-NORTHWEST', '3rd Year', 'BSIT', '2026-02-12 08:37:13'),
(3523, '2023-0482', 'EMILINDA', 'PACINIO', 'LAYAOG', '3-NORTH', '3rd Year', 'BSIT', '2026-02-12 08:37:13'),
(3524, '2023-0160', 'JOYCE', 'VILLACARLOS', 'SISDOYRO', '3-NORTH', '3rd Year', 'BSIT', '2026-02-12 08:37:13'),
(3525, '2023-0473', 'ALOUWIN', 'PACILAN', 'BATUHAN', '3-NORTH', '3rd Year', 'BSIT', '2026-02-12 08:37:13'),
(3526, '2023-0480', 'KAREL ANN', 'VILLACERAN', 'CUEVA', '3-NORTH', '3rd Year', 'BSIT', '2026-02-12 08:37:13'),
(3527, '2023-0370', 'LYNLYN', 'MANSANAREZ', 'BATIANCILA', '3-NORTH', '3rd Year', 'BSIT', '2026-02-12 08:37:13'),
(3528, '2023-0679', 'MICHELLE', 'DERDER', 'BANQUE', '3-NORTH', '3rd Year', 'BSIT', '2026-02-12 08:37:13'),
(3529, '2023-0452', 'JOSHUA', 'TAYO', 'RIBAMONTE', '3-SOUTH', '3rd Year', 'BSIT', '2026-02-12 08:37:13'),
(3530, '2023-0303', 'ERICKA', 'BATINDAAN', 'SARABIA', '3-NORTHWEST', '3rd Year', 'BSIT', '2026-02-12 08:37:13'),
(3531, '2023-0995', 'CLEA STEPHANIE', 'LOBO', 'PAGHINAYAN', '3-SOUTHWEST', '3rd Year', 'BSIT', '2026-02-12 08:37:13'),
(3532, '2023-0698', 'RAPHY', 'ANCIANO', 'ESPLIGUERA', '3-SOUTHWEST', '3rd Year', 'BSIT', '2026-02-12 08:37:13'),
(3533, '2023-0680', 'ARVIN', 'SEVILLENO', 'ALMOJALLAS', '3-WEST', '3rd Year', 'BSIT', '2026-02-12 08:37:13'),
(3534, '2023-1368', 'MARK JOHNRYL', 'ENOY', 'GIGANTO', '3-NORTHWEST', '3rd Year', 'BSIT', '2026-02-12 08:37:13'),
(3535, '2023-0281', 'ANALYN', 'SOLITARIO', 'ALOYAN', '3-NORTH', '3rd Year', 'BSIT', '2026-02-12 08:37:13'),
(3536, '2023-0402', 'HEZEL', 'PASICARAN', 'DESCARTIN', '3-NORTH', '3rd Year', 'BSIT', '2026-02-12 08:37:13'),
(3537, '2023-1267', 'WILSON', 'TUMABIENE', 'IGPAS', '3-SOUTHWEST', '3rd Year', 'BSIT', '2026-02-12 08:37:13'),
(3538, '2023-0321', 'LESLIE KAY', 'ESPINA', 'CARMELOTES', '3-NORTH', '3rd Year', 'BSIT', '2026-02-12 08:37:13'),
(3539, '2023-0474', 'RYAN JAKE', 'MURIRA', 'ARRIESGADO', '3-NORTH', '3rd Year', 'BSIT', '2026-02-12 08:37:13'),
(3540, '2023-0965', 'AIZA', 'MORADAS', 'ESGANA', '3-WEST', '3rd Year', 'BSIT', '2026-02-12 08:37:13'),
(3541, '2023-0556', 'ROSE CLARIZ', 'SIACOR', 'GINATADCAN', '3-NORTHEAST', '3rd Year', 'BSIT', '2026-02-12 08:37:13'),
(3542, '2023-0421', 'MARKNEL', 'UMBAO', 'GRANDE', '3-WEST', '3rd Year', 'BSIT', '2026-02-12 08:37:13'),
(3543, '2023-0338', 'KHRISNA MAE', 'SACNAHON', 'ARIOLA', '3-WEST', '3rd Year', 'BSIT', '2026-02-12 08:37:13'),
(3544, '2023-0269', 'VINCENT', 'AQUE', '', '3-NORTHEAST', '3rd Year', 'BSIT', '2026-02-12 08:37:13'),
(3545, '2023-0288', 'MARY ANN', 'DESCARTIN', 'OFRIL', '3-NORTH', '3rd Year', 'BSIT', '2026-02-12 08:37:13'),
(3546, '2023-1099', 'MITCH LOURENCE', 'SANTILLAN', 'ABELLO', '3-WEST', '3rd Year', 'BSIT', '2026-02-12 08:37:13'),
(3547, '2023-0479', 'ELCID', 'JUMAWAN', 'MONTEBON', '3-WEST', '3rd Year', 'BSIT', '2026-02-12 08:37:13'),
(3548, '2023-0737', 'PRINCE CEDRICK', 'MANSUETO', 'DEO', '3-WEST', '3rd Year', 'BSIT', '2026-02-12 08:37:13'),
(3549, '2023-0268', 'HILBERT', 'VILLACARLOS', 'CANOY', '3-WEST', '3rd Year', 'BSIT', '2026-02-12 08:37:13'),
(3550, '2023-0306', 'JENAMIE', 'PARAGSA', 'BULA', '3-WEST', '3rd Year', 'BSIT', '2026-02-12 08:37:13'),
(3551, '2023-0329', 'JOHN FAVE', 'ARRANGUEZ', 'MARFA', '3-NORTH', '3rd Year', 'BSIT', '2026-02-12 08:37:13'),
(3552, '2023-0637', 'NICOLE', 'SANTILLAN', 'DE OCAMPO', '3-SOUTH', '3rd Year', 'BSIT', '2026-02-12 08:37:13'),
(3553, '2023-1395', 'RUBEN', 'ALMODIEL', 'LAYAO', '3-WEST', '3rd Year', 'BSIT', '2026-02-12 08:37:13'),
(3554, '2023-0981', 'ELEUTERIO', 'PACALDO', 'ILLUT', '3-WEST', '3rd Year', 'BSIT', '2026-02-12 08:37:13'),
(3555, '2023-1135', 'CHRIS JOHN', 'CORDOVA', 'VILLACASTIN', '3-NORTHEAST', '3rd Year', 'BSIT', '2026-02-12 08:37:13'),
(3556, '2020-0311', 'ANTHONY', 'SEPUESCA', 'ALOLOR', '3-NORTHEAST', '3rd Year', 'BSIT', '2026-02-12 08:37:13'),
(3557, '2023-0508', 'ELIEZER', 'FORROSUELO', 'MANSUETO', '3-SOUTH', '3rd Year', 'BSIT', '2026-02-12 08:37:13'),
(3558, '2023-0001', 'NESMARC', 'LUNOD', 'AGAN', '3-WEST', '3rd Year', 'BSIT', '2026-02-12 08:37:13'),
(3559, '2022-4022', 'RICA', 'RAQUIZA', 'UMBAO', '3-WEST', '3rd Year', 'BSIT', '2026-02-12 08:37:13'),
(3560, '2023-1233', 'JOHN PATRICK', 'PARAGSA', '', '3-SOUTH', '3rd Year', 'BSIT', '2026-02-12 08:37:13'),
(3561, '2023-0531', 'CLARK HARRIS', 'ABRIL', 'CARTAJENA', '3-SOUTH', '3rd Year', 'BSIT', '2026-02-12 08:37:13'),
(3562, '2023-0914', 'LOVELY', 'DUCAY', 'MATA', '3-SOUTH', '3rd Year', 'BSIT', '2026-02-12 08:37:13'),
(3563, '2023-1393', 'ARVEY BRYLLE', 'CAPURAS', '', '3-NORTHWEST', '3rd Year', 'BSIT', '2026-02-12 08:37:13'),
(3564, '2023-1363', 'KIM LOYD', 'ZASPA', 'SETINTA', '3-NORTHWEST', '3rd Year', 'BSIT', '2026-02-12 08:37:13'),
(3565, '2023-1476', 'MARK JOHN', 'CABASE', 'CAPURAS', '3-NORTHWEST', '3rd Year', 'BSIT', '2026-02-12 08:37:13'),
(3566, '2023-1365', 'EUGENE', 'VELIGANIO', 'PUTONG', '3-NORTHWEST', '3rd Year', 'BSIT', '2026-02-12 08:37:13'),
(3567, '2023-1145', 'JOHN PAUL', 'DARUCA', 'SARABIA', '3-NORTH', '3rd Year', 'BSIT', '2026-02-12 08:37:13'),
(3568, '2023-1433', 'CLARK', 'CALZADA', 'BURLAOS', '3-NORTHWEST', '3rd Year', 'BSIT', '2026-02-12 08:37:13'),
(3569, '2023-1097', 'JOHN PAUL', 'SANTILLAN', '', '3-NORTHEAST', '3rd Year', 'BSIT', '2026-02-12 08:37:13'),
(3570, '2023-1129', 'ANGELO', 'ANCIANO', 'YBANEZ', '3-NORTHEAST', '3rd Year', 'BSIT', '2026-02-12 08:37:13'),
(3571, '2023-0132', 'JEALOU', 'ABELLANOSA', 'LAPE', '3-NORTH', '3rd Year', 'BSIT', '2026-02-12 08:37:13'),
(3572, '2023-1389', 'RAFAEL', 'GILBUENA', 'DERDER', '3-SOUTHWEST', '3rd Year', 'BSIT', '2026-02-12 08:37:13'),
(3573, '2023-0292', 'ANGELINE', 'PALAY', 'GIGANTO', '3-NORTH', '3rd Year', 'BSIT', '2026-02-12 08:37:13'),
(3574, '2023-0447', 'JOSHUA', 'MATA', 'BATU-IGAS', '3-NORTH', '3rd Year', 'BSIT', '2026-02-12 08:37:13'),
(3575, '2023-1130', 'EUBERTO', 'CABILING JR.', 'ILUSTRISIMO', '3-NORTHWEST', '3rd Year', 'BSIT', '2026-02-12 08:37:13'),
(3576, '2023-1246', 'JESSA', 'PLACENCIA', 'MARANDE', '3-NORTH', '3rd Year', 'BSIT', '2026-02-12 08:37:13'),
(3577, '2022-7021', 'JUNEL', 'GAMAO', '', '3-WEST', '3rd Year', 'BSIT', '2026-02-12 08:37:13'),
(3578, '2023-1224', 'RAFAEL', 'VILLACERAN', 'PACELAN', '3-SOUTH', '3rd Year', 'BSIT', '2026-02-12 08:37:13'),
(3579, '2023-0985', 'JUNARD', 'DELOS REYES', 'ESPINOSA', '3-SOUTHWEST', '3rd Year', 'BSIT', '2026-02-12 08:37:13'),
(3580, '2023-0450', 'MARIO JR.', 'CENA', 'DESPABELADERO', '3-NORTH', '3rd Year', 'BSIT', '2026-02-12 08:37:13'),
(3581, '2023-1518', 'ELBERT', 'BULANDRES', 'ALMONICAR', '3-NORTHEAST', '3rd Year', 'BSIT', '2026-02-12 08:37:13'),
(3582, '2023-0020', 'RYNALD REE', 'PONCE', 'NEPANGUE', '3-NORTH', '3rd Year', 'BSIT', '2026-02-12 08:37:13'),
(3583, '2023-1122', 'EDMUND', 'DERDER', 'MORADAS', '3-NORTH', '3rd Year', 'BSIT', '2026-02-12 08:37:13'),
(3584, '2023-0568', 'CARL DEXTER', 'MASULA', 'BATIANCILA', '3-WEST', '3rd Year', 'BSIT', '2026-02-12 08:37:13'),
(3585, '2023-1095', 'SHAMIE', 'ALMONICAR', 'CARANZO', '3-SOUTH', '3rd Year', 'BSIT', '2026-02-12 08:37:13'),
(3586, '2023-0692', 'EDUARDO JR.', 'ESCALA', 'QUEZON', '3-SOUTH', '3rd Year', 'BSIT', '2026-02-12 08:37:13'),
(3587, '2023-1035', 'CATHERINE MAE', 'SANTILLAN', 'VILLACERAN', '3-SOUTH', '3rd Year', 'BSIT', '2026-02-12 08:37:13'),
(3588, '2023-1009', 'RAMEL', 'LAWAN', 'SUAN', '3-SOUTH', '3rd Year', 'BSIT', '2026-02-12 08:37:13'),
(3589, '2023-0962', 'MARJUN', 'TAYACTAC', 'DESPI', '3-SOUTH', '3rd Year', 'BSIT', '2026-02-12 08:37:13'),
(3590, '2023-1010', 'JEHU', 'TAES', 'MANSUETO', '3-SOUTH', '3rd Year', 'BSIT', '2026-02-12 08:37:13'),
(3591, '2023-1101', 'JULIENNE MAR', 'DESCARTIN', '', '3-SOUTHWEST', '3rd Year', 'BSIT', '2026-02-12 08:37:13'),
(3592, '2023-1042', 'JOREX', 'SARRAGA', 'NEPANGUE', '3-SOUTH', '3rd Year', 'BSIT', '2026-02-12 08:37:13'),
(3593, '2023-1041', 'KEN MICHAEL', 'ARRANCHADO', 'DUCAY', '3-NORTHEAST', '3rd Year', 'BSIT', '2026-02-12 08:37:13'),
(3594, '2023-0910', 'BREYLAN', 'BAYON-ON', 'LURA', '3-SOUTH', '3rd Year', 'BSIT', '2026-02-12 08:37:13'),
(3595, '2023-1595', 'RACHEL ANN', 'UY', 'DELOS REYES', '3-NORTH', '3rd Year', 'BSIT', '2026-02-12 08:37:13'),
(3596, '2023-0161', 'RAIZA MAE', 'GIDACAN', 'VILLACAMPA', '3-NORTH', '3rd Year', 'BSIT', '2026-02-12 08:37:13'),
(3597, '2023-0008', 'JOHN DAVE', 'MARU', '', '3-NORTH', '3rd Year', 'BSIT', '2026-02-12 08:37:13'),
(3598, '2023-0287', 'MARY GRACE', 'ILOSORIO', 'ILLUT', '3-NORTH', '3rd Year', 'BSIT', '2026-02-12 08:37:13'),
(3599, '2023-1417', 'REAN', 'MATA', 'MAGAPAN', '3-NORTH', '3rd Year', 'BSIT', '2026-02-12 08:37:13'),
(3600, '2023-1472', 'NIÑO JHOEVANN', 'VILLACERAN', 'AMADEO', '3-SOUTHWEST', '3rd Year', 'BSIT', '2026-02-12 08:37:13'),
(3601, '2023-0772', 'JOHN LESTER', 'DUCAY', 'SANTILLAN', '3-NORTHWEST', '3rd Year', 'BSIT', '2026-02-12 08:37:13'),
(3602, '2023-0078', 'RENGEL', 'NIEDO', 'ATIENZA', '3-NORTH', '3rd Year', 'BSIT', '2026-02-12 08:37:13'),
(3603, '2023-1280', 'MIGUEL CEDRICK', 'GUARISMA', 'BAYARONG', '3-NORTHEAST', '3rd Year', 'BSIT', '2026-02-12 08:37:13'),
(3604, '2023-0134', 'LOUIE JAY', 'ILUSTRISIMO', 'OFIASA', '3-NORTH', '3rd Year', 'BSIT', '2026-02-12 08:37:13'),
(3605, '2023-0770', 'ERICH', 'LAYONES', 'CABULLO', '3-NORTHWEST', '3rd Year', 'BSIT', '2026-02-12 08:37:13'),
(3606, '2023-0131', 'ZHAENA MAE', 'OFIASA', 'PANDACAN', '3-NORTH', '3rd Year', 'BSIT', '2026-02-12 08:37:13'),
(3607, '2023-1154', 'ANN MARIE', 'SULLA', '', '3-NORTH', '3rd Year', 'BSIT', '2026-02-12 08:37:13'),
(3608, '2023-0929', 'JANEY', 'BOLJORAN', 'OLIVA', '3-SOUTH', '3rd Year', 'BSIT', '2026-02-12 08:37:13'),
(3609, '2023-0963', 'MARK VINCENT', 'RABUSA', 'ILUSTRISIMO', '3-WEST', '3rd Year', 'BSIT', '2026-02-12 08:37:13'),
(3610, '2023-1396', 'DERZA JEAN', 'GILBUENA', '', '3-NORTHWEST', '3rd Year', 'BSIT', '2026-02-12 08:37:13'),
(3611, '2023-0360', 'LORENCE', 'CARATAO', 'DELA CRUZ', '3-NORTH', '3rd Year', 'BSIT', '2026-02-12 08:37:13'),
(3612, '2023-1314', 'JENNY', 'CABRERA', 'BAGONOC', '3-NORTHWEST', '3rd Year', 'BSIT', '2026-02-12 08:37:13'),
(3613, '2023-1038', 'ALIMAR', 'ESCALA', 'ALON', '3-SOUTH', '3rd Year', 'BSIT', '2026-02-12 08:37:13'),
(3614, '2023-0984', 'ROSE MARIE', 'DESPI', 'DESUCATAN', '3-SOUTH', '3rd Year', 'BSIT', '2026-02-12 08:37:13'),
(3615, '2023-1225', 'JOHN ANTHONY', 'ABELLO', 'BAYON-ON', '3-SOUTH', '3rd Year', 'BSIT', '2026-02-12 08:37:13'),
(3616, '2023-1227', 'JOEY', 'MARCE', 'ABELLO', '3-SOUTH', '3rd Year', 'BSIT', '2026-02-12 08:37:13'),
(3617, '2023-0603', 'CLINTON JAY', 'VILLACIN', 'JUBAY', '3-SOUTH', '3rd Year', 'BSIT', '2026-02-12 08:37:13'),
(3618, '2023-1036', 'CRISTEL JOY', 'BATOBALONOS', 'UGBAMIN', '3-SOUTH', '3rd Year', 'BSIT', '2026-02-12 08:37:13'),
(3619, '2023-0975', 'RAFFY', 'BASILAN', 'YONGSON', '3-WEST', '3rd Year', 'BSIT', '2026-02-12 08:37:13'),
(3620, '2023-0930', 'PAUL', 'GERCAN', 'BOLJORAN', '3-SOUTH', '3rd Year', 'BSIT', '2026-02-12 08:37:13'),
(3621, '2023-0980', 'RONEL', 'ALOLOD', 'CHAVEZ', '3-SOUTH', '3rd Year', 'BSIT', '2026-02-12 08:37:13'),
(3622, '2023-0567', 'JAYLIAN', 'BACOLOD', 'CAHUTAY', '3-SOUTHWEST', '3rd Year', 'BSIT', '2026-02-12 08:37:13'),
(3623, '2023-1474', 'CRISANTO', 'VILLACERAN JR', 'DESPI', '3-NORTHWEST', '3rd Year', 'BSIT', '2026-02-12 08:37:13'),
(3624, '2023-1513', 'JOHN REY NIÑO', 'DESCOTIDO', 'TAMPARONG', '3-NORTHWEST', '3rd Year', 'BSIT', '2026-02-12 08:37:13'),
(3625, '2023-0560', 'NATHALY PEARL', 'ESPINOSA', 'ESPINA', '3-SOUTHWEST', '3rd Year', 'BSIT', '2026-02-12 08:37:13'),
(3626, '2023-0958', 'REYMART', 'SANGUTAN', 'VILLAESTER', '3-SOUTHWEST', '3rd Year', 'BSIT', '2026-02-12 08:37:13'),
(3627, '2023-1484', 'CHRISTIAN', 'POTAYRE', 'LECCIONES', '3-NORTHWEST', '3rd Year', 'BSIT', '2026-02-12 08:37:13'),
(3628, '2023-0644', 'KEVIN', 'ILUSTRISIMO', 'RIVERA', '3-SOUTHWEST', '3rd Year', 'BSIT', '2026-02-12 08:37:13'),
(3629, '2023-0291', 'MICHELLE', 'DENILA', '', '3-NORTH', '3rd Year', 'BSIT', '2026-02-12 08:37:13'),
(3630, '2023-1430', 'LIBERATO', 'FERNANDEZ JR.', 'GILA', '3-NORTHWEST', '3rd Year', 'BSIT', '2026-02-12 08:37:13'),
(3631, '2023-1189', 'NIHL JOSHUA', 'VILLARINO', 'LOCAY-LOCAY', '3-SOUTHWEST', '3rd Year', 'BSIT', '2026-02-12 08:37:13'),
(3632, '2023-0717', 'RONNIE', 'BATALION', 'CABALLERO', '3-SOUTH', '3rd Year', 'BSIT', '2026-02-12 08:37:13'),
(3633, '2023-1158', 'LOUIE', 'BLASA', 'PEPITO', '3-SOUTH', '3rd Year', 'BSIT', '2026-02-12 08:37:13'),
(3634, '2023-0368', 'MICHELLE', 'MARTUS', 'BAYNOSA', '3-NORTHEAST', '3rd Year', 'BSIT', '2026-02-12 08:37:13'),
(3635, '2023-0014', 'MHEL ROSE', 'PASTRANA', 'SALVE', '3-NORTHEAST', '3rd Year', 'BSIT', '2026-02-12 08:37:13'),
(3636, '2023-0908', 'CLIFFORD', 'VILEGANO', '', '3-NORTHEAST', '3rd Year', 'BSIT', '2026-02-12 08:37:13'),
(3637, '2023-0487', 'LEO', 'ALOLOD', 'PAVIA', '3-SOUTH', '3rd Year', 'BSIT', '2026-02-12 08:37:13'),
(3638, '2023-1482', 'JOHN FRANCIS', 'QUIJANO', 'DESCARTIN', '3-SOUTH', '3rd Year', 'BSIT', '2026-02-12 08:37:13'),
(3639, '2023-1481', 'KRISTINE MAE', 'MANGGIRAN', '', '3-SOUTH', '3rd Year', 'BSIT', '2026-02-12 08:37:13'),
(3640, '2023-0960', 'DEJAY', 'PARDILLO', 'BARRO', '3-WEST', '3rd Year', 'BSIT', '2026-02-12 08:37:13'),
(3641, '2023-1047', 'ANTONIO', 'OFLAS JR.', 'COSE', '3-SOUTH', '3rd Year', 'BSIT', '2026-02-12 08:37:13'),
(3642, '2023-1378', 'CARLO', 'ILUSTRISIMO', '', '3-NORTHWEST', '3rd Year', 'BSIT', '2026-02-12 08:37:13'),
(3643, '2024-1216', 'ELIVER GIO', 'MAQUILANG', 'GRAVINO', '3-NORTHWEST', '3rd Year', 'BSIT', '2026-02-12 08:37:13'),
(3644, '2023-0273', 'MERCY MARIE', 'DIONGSON', 'MAGALLANES', '3-NORTHWEST', '3rd Year', 'BSIT', '2026-02-12 08:37:13'),
(3645, '2023-0414', 'ROGEL NIÑO', 'UGBAMIN', 'OFTANA', '3-SOUTHEAST', '3rd Year', 'BSIT', '2026-02-12 08:37:13'),
(3646, '2023-1401', 'RJAY', 'FORROSUELO', 'DECAPE', '3-SOUTHEAST', '3rd Year', 'BSIT', '2026-02-12 08:37:13'),
(3647, '2023-0408', 'REIL DAVE', 'BAWIIN', 'DUCAY', '3-SOUTHEAST', '3rd Year', 'BSIT', '2026-02-12 08:37:13'),
(3648, '2023-1441', 'JOHN MATT', 'ALOB', 'MONTAÑO', '3-NORTHWEST', '3rd Year', 'BSIT', '2026-02-12 08:37:13'),
(3649, '2023-1548', 'ELDON', 'LAYAGUE', 'BAUTRO', '3-NORTHWEST', '3rd Year', 'BSIT', '2026-02-12 08:37:13'),
(3650, '2023-1446', 'ERIC JHON', 'SEVILLANO', '', '3-NORTHWEST', '3rd Year', 'BSIT', '2026-02-12 08:37:13'),
(3651, '2023-0510', 'JOHN DEE', 'VILLARINO', 'TECSON', '3-NORTH', '3rd Year', 'BSIT', '2026-02-12 08:37:13'),
(3652, '2023-1402', 'IAN JAY', 'SILVA', '', '3-NORTHWEST', '3rd Year', 'BSIT', '2026-02-12 08:37:13'),
(3653, '2023-1416', 'JOHN DAVID', 'PARADERO', 'OFTANA', '3-SOUTHEAST', '3rd Year', 'BSIT', '2026-02-12 08:37:13'),
(3654, '2023-0135', 'JAMES', 'NAPALYA', 'ILUSTRISIMO', '3-SOUTHEAST', '3rd Year', 'BSIT', '2026-02-12 08:37:13'),
(3655, '2023-0771', 'JANELLE', 'ESCOBILLO', 'BOLTRON', '3-SOUTHEAST', '3rd Year', 'BSIT', '2026-02-12 08:37:13');
INSERT INTO `students` (`id`, `student_number`, `fname`, `lname`, `mname`, `section`, `year_level`, `department`, `created_at`) VALUES
(3656, '2023-0342', 'DANIEL', 'GIDUCOS', 'ESCARAN', '3-SOUTHEAST', '3rd Year', 'BSIT', '2026-02-12 08:37:13'),
(3657, '2023-0230', 'SHANE ARA', 'LUCHAVEZ', 'ABRIL', '3-SOUTHEAST', '3rd Year', 'BSIT', '2026-02-12 08:37:13'),
(3658, '2023-1592', 'JOHN NINO', 'ESPLIGUERA', 'ILUSTRISIMO', '3-SOUTHEAST', '3rd Year', 'BSIT', '2026-02-12 08:37:13'),
(3659, '2023-0643', 'JOVANIE', 'RAYCO JR.', 'CABRERA', '3-SOUTHEAST', '3rd Year', 'BSIT', '2026-02-12 08:37:13'),
(3660, '2023-0907', 'LOVELY', 'CORNEA', 'LAWAN', '3-SOUTHEAST', '3rd Year', 'BSIT', '2026-02-12 08:37:13'),
(3661, '2023-1458', 'WINVER', 'CALAWAGAN', 'ASIS', '3-SOUTHEAST', '3rd Year', 'BSIT', '2026-02-12 08:37:13'),
(3662, '2023-1538', 'SENANDO', 'DELA FUENTE JR.', 'ESCALICAS', '3-SOUTHEAST', '3rd Year', 'BSIT', '2026-02-12 08:37:13'),
(3663, '2023-1011', 'SALMER DAVE', 'TAIES', 'ILLUT', '3-SOUTH', '3rd Year', 'BSIT', '2026-02-12 08:37:13'),
(3664, '2023-0874', 'JONATHAN', 'RICO', 'ROSATASE', '3-SOUTHEAST', '3rd Year', 'BSIT', '2026-02-12 08:37:13'),
(3665, '2023-1543', 'JEFFREY', 'GIDUQUIO', '', '3-SOUTHEAST', '3rd Year', 'BSIT', '2026-02-12 08:37:13'),
(3666, '2023-0262', 'JEAM', 'VILLAESTER', 'LEGASPINO', '3-NORTHWEST', '3rd Year', 'BSIT', '2026-02-12 08:37:13'),
(3667, '2023-0166', 'FRENZY FAYA', 'CUEVA', 'CARMELO', '3-NORTH', '3rd Year', 'BSIT', '2026-02-12 08:37:13'),
(3668, '2024-1706', 'JUREN', 'DESAMPARADO', 'GARCIA', '3-SOUTHEAST', '3rd Year', 'BSIT', '2026-02-12 08:37:13'),
(3669, '2023-0517', 'IRENE JOYCE', 'SEVILLEJO', 'VILLACARLOS', '3-WEST', '3rd Year', 'BSIT', '2026-02-12 08:37:13'),
(3670, '2023-1108', 'SHAQUIEL DANIEL', 'UMANDAP', 'GANELO', '3-NORTH', '3rd Year', 'BSIT', '2026-02-12 08:37:13'),
(3671, '2023-0398', 'JOVEN', 'CENA', 'SEVILLANO', '3-SOUTHWEST', '3rd Year', 'BSIT', '2026-02-12 08:37:13'),
(3672, '2023-1473', 'JHANNA JOY', 'DURIAS', 'VILLACERAN', '3-SOUTHWEST', '3rd Year', 'BSIT', '2026-02-12 08:37:13'),
(3673, '2023-1242', 'HARLEY JADE', 'LIM', 'MORINO', '3-SOUTHWEST', '3rd Year', 'BSIT', '2026-02-12 08:37:13'),
(3674, '2023-0734', 'ANGELINE', 'LAPI', 'SEBUYANA', '3-SOUTHWEST', '3rd Year', 'BSIT', '2026-02-12 08:37:13'),
(3675, '2023-1221', 'JEROME', 'ALTAR', 'GARCIA', '3-SOUTHWEST', '3rd Year', 'BSIT', '2026-02-12 08:37:13'),
(3676, '2023-1229', 'ROWELL JAY', 'PLACENCIA', 'ANCIANO', '3-SOUTHWEST', '3rd Year', 'BSIT', '2026-02-12 08:37:13'),
(3677, '2023-1272', 'NOGIE', 'GILBUENA', 'ESGANA', '3-NORTHEAST', '3rd Year', 'BSIT', '2026-02-12 08:37:13'),
(3678, '2023-1415', 'SYDRICK', 'ALONTE', 'ILUSTRISIMO', '3-NORTHEAST', '3rd Year', 'BSIT', '2026-02-12 08:37:13'),
(3679, '2023-0875', 'RONYL', 'PAROCHEL', 'MATA', '3-SOUTH', '3rd Year', 'BSIT', '2026-02-12 08:37:13'),
(3680, '2023-0397', 'ROYET', 'PEPITO', 'PELAYRE', '3-NORTH', '3rd Year', 'BSIT', '2026-02-12 08:37:13'),
(3681, '2023-0305', 'BERNADETTE', 'BAULITA', 'PELAYO', '3-SOUTHWEST', '3rd Year', 'BSIT', '2026-02-12 08:37:13'),
(3682, '2023-1100', 'AARON JOHN', 'MONTAÑO', 'TIPONTIPON', '3-SOUTHWEST', '3rd Year', 'BSIT', '2026-02-12 08:37:13'),
(3683, '2023-0574', 'JEREMIA', 'ADOLFO', 'BACASMAS', '3-SOUTHWEST', '3rd Year', 'BSIT', '2026-02-12 08:37:13'),
(3684, '2023-0756', 'MARY JANE', 'LAWAS', 'CABRERA', '3-WEST', '3rd Year', 'BSIT', '2026-02-12 08:37:13'),
(3685, '2023-0391', 'EDGAR', 'ESPINA', '', '3-NORTH', '3rd Year', 'BSIT', '2026-02-12 08:37:13'),
(3686, '2023-1105', 'ARCHIE', 'BILLONES', 'ROQUE', '3-WEST', '3rd Year', 'BSIT', '2026-02-12 08:37:13'),
(3687, '2023-1311', 'GABRIMAR', 'MASPARA', 'SEVILLENO', '3-WEST', '3rd Year', 'BSIT', '2026-02-12 08:37:13'),
(3688, '2023-1421', 'JOVAN', 'MAHUSAY', 'BATIRZAL', '3-SOUTHEAST', '3rd Year', 'BSIT', '2026-02-12 08:37:13'),
(3689, '2023-0538', 'REYMART', 'DESUCATAN', 'SATERA', '3-NORTHEAST', '3rd Year', 'BSIT', '2026-02-12 08:37:13'),
(3690, '2023-1390', 'MHARJORIE', 'RIBO', 'ESPINA', '3-NORTHWEST', '3rd Year', 'BSIT', '2026-02-12 08:37:13'),
(3691, '2023-1371', 'MARY ROSENETTE', 'ESGANA', 'CUIZON', '3-NORTHWEST', '3rd Year', 'BSIT', '2026-02-12 08:37:13'),
(3692, '2023-1230', 'JOSEPH', 'ENERO', 'CATANPATAN', '3-WEST', '3rd Year', 'BSIT', '2026-02-12 08:37:13'),
(3693, '2023-1367', 'JHADE', 'ALOBA', 'PRIAS', '3-SOUTHEAST', '3rd Year', 'BSIT', '2026-02-12 08:37:13'),
(3694, '2023-1424', 'ELISEO', 'SEVILLA', 'BATAYOLA', '3-SOUTHEAST', '3rd Year', 'BSIT', '2026-02-12 08:37:13'),
(3695, '2023-0512', 'DAVE', 'ALAGBAN', 'ALMOCERA', '3-EAST', '3rd Year', 'BSIT', '2026-02-12 08:37:13'),
(3696, '2023-0293', 'JEROW', 'SANTILLAN', 'ALOBA', '3-EAST', '3rd Year', 'BSIT', '2026-02-12 08:37:13'),
(3697, '2023-0628', 'MENGIE', 'PATALINGHOG', 'VILLACERAN', '3-EAST', '3rd Year', 'BSIT', '2026-02-12 08:37:13'),
(3698, '2023-0909', 'NOVA MAE', 'RESUENA', 'BATUIGAS', '3-EAST', '3rd Year', 'BSIT', '2026-02-12 08:37:13'),
(3699, '2023-0486', 'JOHN VINCENT', 'RECREO', 'BACOMO', '3-EAST', '3rd Year', 'BSIT', '2026-02-12 08:37:13'),
(3700, '2023-0483', 'JHANA RICA', 'VILLACERAN', 'SEDURIFA', '3-EAST', '3rd Year', 'BSIT', '2026-02-12 08:37:13'),
(3701, '2023-0089', 'ROSE ANN', 'FORROSUELO', 'VILLAESTER', '3-EAST', '3rd Year', 'BSIT', '2026-02-12 08:37:13'),
(3702, '2023-0642', 'MEAKYLA', 'TORRES', 'ROBIATO', '3-EAST', '3rd Year', 'BSIT', '2026-02-12 08:37:13'),
(3703, '2023-0611', 'GIAN CHRISTOPHER', 'ABANID', 'CENA', '3-EAST', '3rd Year', 'BSIT', '2026-02-12 08:37:13'),
(3704, '2023-0509', 'RJ', 'ILOSORIO', 'BALIBAGOSO', '3-EAST', '3rd Year', 'BSIT', '2026-02-12 08:37:13'),
(3705, '2023-0893', 'GIOVERT JOHN', 'GIDAYAWAN', 'KAQUILALA', '3-EAST', '3rd Year', 'BSIT', '2026-02-12 08:37:13'),
(3706, '2023-0524', 'JOHN LLOYD', 'ALAMO', 'ESCARAN', '3-EAST', '3rd Year', 'BSIT', '2026-02-12 08:37:13'),
(3707, '2023-0615', 'ALJUN', 'TAMPOS', 'BAYON-ON', '3-EAST', '3rd Year', 'BSIT', '2026-02-12 08:37:13'),
(3708, '2023-0616', 'ALVIN PAUL', 'TAMPOS', 'BAYON-ON', '3-EAST', '3rd Year', 'BSIT', '2026-02-12 08:37:13'),
(3709, '2023-1550', 'ROGER', 'GIDUQUIO', 'BATIANCILA', '3-EAST', '3rd Year', 'BSIT', '2026-02-12 08:37:13'),
(3710, '2023-1545', 'REMARK', 'TAMPOS', '', '3-EAST', '3rd Year', 'BSIT', '2026-02-12 08:37:13'),
(3711, '2023-0472', 'OLIVER', 'OFIASA', 'RAYCO', '3-EAST', '3rd Year', 'BSIT', '2026-02-12 08:37:13'),
(3712, '2023-0885', 'DOMINIC GABRIEL', 'TAYONG', 'VILLACARLOS', '3-EAST', '3rd Year', 'BSIT', '2026-02-12 08:37:13'),
(3713, '2023-0519', 'JESIEREE', 'LAWAAN', 'ILLUT', '3-EAST', '3rd Year', 'BSIT', '2026-02-12 08:37:13'),
(3714, '2023-0298', 'RICHARD', 'DARUCA', 'HERMOCILLA', '3-EAST', '3rd Year', 'BSIT', '2026-02-12 08:37:13'),
(3715, '2023-1300', 'KYLE ANTHONY', 'BANTILAN', 'CAHUTAY', '3-EAST', '3rd Year', 'BSIT', '2026-02-12 08:37:13'),
(3716, '2023-0387', 'ALJUN', 'CARATAO', 'ALMOHALLAS', '3-EAST', '3rd Year', 'BSIT', '2026-02-12 08:37:13'),
(3717, '2023-0388', 'ADAN', 'VISAGAR', 'CALVARIO', '3-EAST', '3rd Year', 'BSIT', '2026-02-12 08:37:13'),
(3718, '2023-0518', 'RECASIS ANN', 'ENGUITO', 'GILBUENA', '3-EAST', '3rd Year', 'BSIT', '2026-02-12 08:37:13'),
(3719, '2023-1004', 'ALMA FE', 'BATUHAN', '', '3-EAST', '3rd Year', 'BSIT', '2026-02-12 08:37:13'),
(3720, '2023-0601', 'JAKE BRYLLE', 'PANTALEON', 'DOMENICE', '3-EAST', '3rd Year', 'BSIT', '2026-02-12 08:37:13'),
(3721, '2023-0880', 'ROWEL', 'COMESSION', 'SOLLANO', '3-EAST', '3rd Year', 'BSIT', '2026-02-12 08:37:13'),
(3722, '2023-0481', 'WENIFREDO', 'ALO', 'BORJA', '3-EAST', '3rd Year', 'BSIT', '2026-02-12 08:37:13'),
(3723, '2023-1295', 'MARY KHATE ANTONINA', 'SEVILLA', 'BOLTRON', '3-EAST', '3rd Year', 'BSIT', '2026-02-12 08:37:13'),
(3724, '2023-0285', 'ROGELIO', 'TRADIO', 'DOBLE', '3-EAST', '3rd Year', 'BSIT', '2026-02-12 08:37:13'),
(3725, '2023-0359', 'MIKE STEPHEN', 'TEDOSO', 'DELOS SANTOS', '3-EAST', '3rd Year', 'BSIT', '2026-02-12 08:37:13'),
(3726, '2023-0901', 'JIMBOY', 'MARABE', 'ADORNA', '3-EAST', '3rd Year', 'BSIT', '2026-02-12 08:37:13'),
(3727, '2023-0871', 'KENT JAMEL', 'REBUSIT', 'GIDUCOS', '3-EAST', '3rd Year', 'BSIT', '2026-02-12 08:37:13'),
(3728, '2023-0413', 'CARL JAY', 'CENA', 'BALONGOY', '3-EAST', '3rd Year', 'BSIT', '2026-02-12 08:37:13'),
(3729, '2023-0763', 'ADRIAN', 'FORROSUELO', 'FERNANDEZ', '3-EAST', '3rd Year', 'BSIT', '2026-02-12 08:37:13'),
(3730, '2023-1190', 'RALF', 'CUEVA', 'FRANCISCO', '3-EAST', '3rd Year', 'BSIT', '2026-02-12 08:37:13'),
(3731, '2023-0604', 'MYROE', 'BAYON-ON', 'FERNANDEZ', '3-EAST', '3rd Year', 'BSIT', '2026-02-12 08:37:13'),
(3732, '2023-1418', 'ZED LAURENCE', 'LLAGOSO', 'CANALES', '3-EAST', '3rd Year', 'BSIT', '2026-02-12 08:37:13'),
(3733, '2023-0976', 'JAHAZIEL REY', 'OCSO', 'SALINAS', '3-EAST', '3rd Year', 'BSIT', '2026-02-12 08:37:13'),
(3734, '2022-6052', 'SERAPIN', 'SANTILLAN JR.,', 'CHAVEZ', '3-NORTH', '3rd Year', 'BSIT', '2026-02-12 08:37:13'),
(3735, '2023-1240', 'ERIC', 'GILLANA', 'DACAY', '3-NORTH', '3rd Year', 'BSIT', '2026-02-12 08:37:13'),
(3736, '2021-1601', 'JEFF', 'GIGANTO', 'SAYSON', '3-SOUTH', '3rd Year', 'BSIT', '2026-02-12 08:37:13');

-- --------------------------------------------------------

--
-- Table structure for table `users`
--

CREATE TABLE `users` (
  `id` int(11) NOT NULL,
  `name` varchar(100) NOT NULL,
  `role` enum('admin','student') NOT NULL,
  `email` varchar(100) NOT NULL,
  `password` varchar(255) NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `users`
--

INSERT INTO `users` (`id`, `name`, `role`, `email`, `password`, `created_at`) VALUES
(1, 'KENT PETER MEDALLO', 'admin', 'hapersounds@gmail.com', '$2y$10$xed/Vf.kqWYBDUS4F6q0y.QNd6mpV3q/FTdstdCGMqvACjDdWaNeu', '2026-01-30 12:53:36');

--
-- Indexes for dumped tables
--

--
-- Indexes for table `active_sessions`
--
ALTER TABLE `active_sessions`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `unique_student` (`student_number`);

--
-- Indexes for table `answers`
--
ALTER TABLE `answers`
  ADD PRIMARY KEY (`id`),
  ADD KEY `student_id` (`student_number`),
  ADD KEY `question_id` (`question_id`);

--
-- Indexes for table `attendance`
--
ALTER TABLE `attendance`
  ADD PRIMARY KEY (`id`),
  ADD KEY `student_id` (`student_number`),
  ADD KEY `quiz_id` (`quiz_id`);

--
-- Indexes for table `daily_attendance`
--
ALTER TABLE `daily_attendance`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `unique_daily_attendance` (`student_number`,`attendance_date`);

--
-- Indexes for table `questions`
--
ALTER TABLE `questions`
  ADD PRIMARY KEY (`id`),
  ADD KEY `quiz_id` (`quiz_id`);

--
-- Indexes for table `quizzes`
--
ALTER TABLE `quizzes`
  ADD PRIMARY KEY (`id`),
  ADD KEY `created_by` (`created_by`);

--
-- Indexes for table `results`
--
ALTER TABLE `results`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `unique_attempt` (`student_number`,`quiz_id`),
  ADD KEY `quiz_id` (`quiz_id`);

--
-- Indexes for table `students`
--
ALTER TABLE `students`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `student_number` (`student_number`);

--
-- Indexes for table `users`
--
ALTER TABLE `users`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `email` (`email`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `active_sessions`
--
ALTER TABLE `active_sessions`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=20;

--
-- AUTO_INCREMENT for table `answers`
--
ALTER TABLE `answers`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=211;

--
-- AUTO_INCREMENT for table `attendance`
--
ALTER TABLE `attendance`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=20;

--
-- AUTO_INCREMENT for table `daily_attendance`
--
ALTER TABLE `daily_attendance`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=58;

--
-- AUTO_INCREMENT for table `questions`
--
ALTER TABLE `questions`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=35;

--
-- AUTO_INCREMENT for table `quizzes`
--
ALTER TABLE `quizzes`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=9;

--
-- AUTO_INCREMENT for table `results`
--
ALTER TABLE `results`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=20;

--
-- AUTO_INCREMENT for table `students`
--
ALTER TABLE `students`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3737;

--
-- AUTO_INCREMENT for table `users`
--
ALTER TABLE `users`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `active_sessions`
--
ALTER TABLE `active_sessions`
  ADD CONSTRAINT `active_sessions_ibfk_1` FOREIGN KEY (`student_number`) REFERENCES `students` (`student_number`) ON DELETE CASCADE;

--
-- Constraints for table `answers`
--
ALTER TABLE `answers`
  ADD CONSTRAINT `answers_ibfk_2` FOREIGN KEY (`question_id`) REFERENCES `questions` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `attendance`
--
ALTER TABLE `attendance`
  ADD CONSTRAINT `attendance_ibfk_2` FOREIGN KEY (`quiz_id`) REFERENCES `quizzes` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `questions`
--
ALTER TABLE `questions`
  ADD CONSTRAINT `questions_ibfk_1` FOREIGN KEY (`quiz_id`) REFERENCES `quizzes` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `quizzes`
--
ALTER TABLE `quizzes`
  ADD CONSTRAINT `quizzes_ibfk_1` FOREIGN KEY (`created_by`) REFERENCES `users` (`id`) ON DELETE SET NULL;

--
-- Constraints for table `results`
--
ALTER TABLE `results`
  ADD CONSTRAINT `results_ibfk_2` FOREIGN KEY (`quiz_id`) REFERENCES `quizzes` (`id`) ON DELETE CASCADE;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
