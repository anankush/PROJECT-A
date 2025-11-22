<?php
session_start();
require_once '../config/db_connection.php';

// Fetch courses from database
$sql = "SELECT * FROM courses ORDER BY id ASC";
$result = mysqli_query($conn, $sql);

$courses = array();
if ($result && mysqli_num_rows($result) > 0) {
    while ($row = mysqli_fetch_assoc($result)) {
        $courses[] = $row;
    }
}

$isLoggedIn = isset($_SESSION['student_id']) || isset($_SESSION['admin_id']);
$dashboardLink = isset($_SESSION['student_id']) ? '../student/dashboard.php' : 
                (isset($_SESSION['admin_id']) ? '../admin/dashboard.php' : '');
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Courses - College Portal</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <link href="https://unpkg.com/aos@2.3.1/dist/aos.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/animate.css/4.1.1/animate.min.css" rel="stylesheet">
    <style>
        :root {
            --primary-color: rgb(220, 3, 18);
            --secondary-color: rgb(247, 12, 19);
        }
        
        .navbar {
            background: linear-gradient(120deg, var(--primary-color), var(--secondary-color));
            padding: 1rem 0;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
        }

        .navbar-brand {
            font-size: 1.5rem;
            font-weight: 600;
            padding: 0.5rem 1rem;
            border-radius: 20px;
            transition: all 0.3s ease;
        }

        .navbar-brand:hover {
            background: rgba(255, 255, 255, 0.2);
            transform: translateY(-2px);
        }

        .navbar-nav .nav-item .nav-link {
            position: relative;
            overflow: hidden;
            color: white !important;
            font-weight: 500;
            padding: 0.5rem 1.2rem;
            margin: 0 0.2rem;
            border-radius: 20px;
            transition: all 0.3s ease;
        }

        .navbar-nav .nav-item .nav-link:hover,
        .navbar-nav .nav-item .nav-link.active {
            background: rgba(255, 255, 255, 0.2);
            transform: translateY(-2px);
        }

        .navbar-nav .nav-link::after {
            content: '';
            position: absolute;
            width: 0;
            height: 2px;
            background: white;
            left: 50%;
            bottom: 0;
            transition: all 0.3s ease;
            transform: translateX(-50%);
        }

        .navbar-nav .nav-link:hover::after {
            width: 100%;
        }

        @media (max-width: 991px) {
            .navbar-collapse {
                background: linear-gradient(120deg, var(--primary-color), var(--secondary-color));
                position: absolute;
                top: 100%;
                left: 0;
                right: 0;
                padding: 1rem;
                box-shadow: 0 2px 10px rgba(0,0,0,0.1);
                z-index: 1000;
            }

            .navbar-nav {
                margin: 0;
                padding: 0;
            }

            .navbar-nav .nav-item {
                width: 100%;
                margin: 0.25rem 0;
            }

            .navbar-nav .nav-link {
                padding: 0.75rem 1rem !important;
                border-radius: 8px;
                margin: 0 !important;
                display: flex;
                align-items: center;
            }

            .navbar-nav.me-auto {
                margin-bottom: 1rem !important;
                border-bottom: 1px solid rgba(255,255,255,0.1);
                padding-bottom: 1rem;
            }
        }

        .dropdown-header {
            color: var(--primary-color);
            font-weight: bold;
            padding: 0.5rem 1rem;
        }
        
        .dropdown-item {
            padding: 0.5rem 1rem;
            transition: all 0.3s ease;
        }
        
        .dropdown-item:hover {
            background-color: rgba(41, 128, 185, 0.1);
            transform: translateX(5px);
        }
        
        .dropdown-menu {
            border: none;
            box-shadow: 0 0 15px rgba(0,0,0,0.1);
            border-radius: 0.5rem;
        }

        .course-card {
            border: none;
            border-radius: 10px;
            box-shadow: 0 0 15px rgba(0,0,0,0.1);
            transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
            will-change: transform;
            opacity: 0;
            animation: fadeIn 0.8s ease-in forwards;
        }
        .course-card:hover {
            transform: translateY(-10px) scale(1.02);
            box-shadow: 0 15px 30px rgba(0,0,0,0.15);
        }
        .card-title {
            position: relative;
            display: inline-block;
        }
        .card-title::after {
            content: '';
            position: absolute;
            width: 0;
            height: 2px;
            bottom: -2px;
            left: 0;
            background: linear-gradient(120deg, #2980b9, #8e44ad);
            transition: width 0.3s ease;
        }
        .course-card:hover .card-title::after {
            width: 100%;
        }
        .fas {
            transition: all 0.3s ease;
        }
        .course-card:hover .fas {
            transform: scale(1.2);
            color: #2980b9;
        }

        @keyframes fadeIn {
            from { opacity: 0; transform: translateY(20px); }
            to { opacity: 1; transform: translateY(0); }
        }

        @keyframes float {
            0% { transform: translateY(0); }
            50% { transform: translateY(-10px); }
            100% { transform: translateY(0); }
        }

        .card-title i {
            animation: float 3s ease-in-out infinite;
        }

        .course-icon {
            color: var(--primary-color);
            font-size: 2.5rem;
            margin-bottom: 1rem;
            animation: float 3s ease-in-out infinite;
        }

        .animate-hover {
            transition: all 0.3s ease;
        }

        .animate-hover:hover {
            transform: scale(1.05);
        }

        .list-unstyled li {
            transition: all 0.3s ease;
            padding: 0.5rem 0;
        }

        .list-unstyled li:hover {
            transform: translateX(10px);
            color: var(--primary-color);
        }

        .fas {
            transition: all 0.3s ease;
        }

        .course-card:hover .fas {
            transform: scale(1.2);
            color: var(--primary-color);
        }

        /* Footer Styles */
        .footer {
            background-color: #333;
            color: white;
            padding: 40px 0;
        }

        .social-icons a {
            color: white;
            margin: 0 10px;
            font-size: 1.5rem;
            transition: color 0.3s;
        }

        .social-icons a:hover {
            color: var(--primary-color);
        }

        @media (max-width: 768px) {
            .footer {
                text-align: center;
            }

            .footer .col-md-4 {
                margin-bottom: 30px;
            }

            .social-icons {
                margin-top: 15px;
            }

            .social-icons a {
                margin: 0 15px;
            }
        }
    </style>
</head>
<body>
    <!-- Navbar -->
    <nav class="navbar navbar-expand-lg navbar-dark mb-4">
        <div class="container">
            <a class="navbar-brand" href="../index.php">TPD College Portal</a>
            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
                <span class="navbar-toggler-icon"></span>
            </button>
            <div class="collapse navbar-collapse" id="navbarNav">
                <ul class="navbar-nav me-auto">
                    <li class="nav-item">
                        <a class="nav-link" href="../index.php">
                            <i class="fas fa-home me-1"></i>Home
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link active" href="courses.php">
                            <i class="fas fa-graduation-cap me-1"></i>Courses
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="about.php">
                            <i class="fas fa-info-circle me-1"></i>About
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="contact.php">
                            <i class="fas fa-envelope me-1"></i>Contact
                        </a>
                    </li>
                </ul>
                <!-- Login/Register Dropdown -->
                <ul class="navbar-nav">
                    <li class="nav-item dropdown">
                        <a class="nav-link dropdown-toggle" href="#" id="loginDropdown" role="button" data-bs-toggle="dropdown" aria-expanded="false">
                            <i class="fas fa-user me-2"></i>Login/Register
                        </a>
                        <ul class="dropdown-menu dropdown-menu-end">
                            <li><h6 class="dropdown-header">Student</h6></li>
                            <li><a class="dropdown-item" href="../student/login.php"><i class="fas fa-sign-in-alt me-2"></i>Student Login</a></li>
                            <li><a class="dropdown-item" href="../student/register.php"><i class="fas fa-user-plus me-2"></i>Student Register</a></li>
                            <li><hr class="dropdown-divider"></li>
                            <li><h6 class="dropdown-header">Administrator</h6></li>
                            <li><a class="dropdown-item" href="../admin/login.php"><i class="fas fa-user-shield me-2"></i>Admin Login</a></li>
                            <li><a class="dropdown-item" href="../admin/register.php"><i class="fas fa-user-plus me-2"></i>Admin Register</a></li>
                        </ul>
                    </li>
                </ul>
            </div>
        </div>
    </nav>

    <!-- Main Content -->
    <div class="container py-4">
        <h2 class="text-center mb-4 animate__animated animate__fadeInDown">Our Courses</h2>
        
        <div class="row g-4">
            <?php if (!empty($courses)): ?>
                <?php foreach($courses as $index => $course): ?>
                    <div class="col-md-6" data-aos="fade-up" data-aos-delay="<?php echo ($index + 1) * 100; ?>">
                        <div class="card course-card h-100">
                            <div class="card-body">
                                <div class="text-center mb-3">
                                    <i class="fas fa-graduation-cap course-icon"></i>
                                </div>
                                <h5 class="card-title animate-hover"><?php echo htmlspecialchars($course['course_name']); ?></h5>
                                <p class="card-text"><?php echo htmlspecialchars($course['description']); ?></p>
                                <ul class="list-unstyled">
                                    <li><i class="fas fa-clock me-2"></i>Duration: <?php echo htmlspecialchars($course['duration']); ?></li>
                                    <li><i class="fas fa-book me-2"></i><?php echo htmlspecialchars($course['specialization']); ?></li>
                                </ul>
                            </div>
                        </div>
                    </div>
                <?php endforeach; ?>
            <?php else: ?>
                <!-- Computer Science -->
                <div class="col-md-6" data-aos="fade-up" data-aos-delay="100">
                    <div class="card course-card h-100">
                        <div class="card-body">
                            <div class="text-center mb-3">
                                <i class="fas fa-laptop-code course-icon"></i>
                            </div>
                            <h5 class="card-title animate-hover">Computer Science & Engineering</h5>
                            <p class="card-text">Learn about computer systems, software development, and cutting-edge technologies. This program prepares you for a career in the ever-growing tech industry.</p>
                            <ul class="list-unstyled">
                                <li><i class="fas fa-clock me-2"></i>Duration: 2-3 Years</li>
                                <li><i class="fas fa-book me-2"></i>Specializations Available</li>
                            </ul>
                        </div>
                    </div>
                </div>

                <!-- Civil Engineering -->
                <div class="col-md-6" data-aos="fade-up" data-aos-delay="200">
                    <div class="card course-card h-100">
                        <div class="card-body">
                            <div class="text-center mb-3">
                                <i class="fas fa-building course-icon"></i>
                            </div>
                            <h5 class="card-title animate-hover">Civil Engineering</h5>
                            <p class="card-text">Study structural engineering, construction management, and environmental systems. Build the infrastructure that shapes our world.</p>
                            <ul class="list-unstyled">
                                <li><i class="fas fa-clock me-2"></i>Duration: 2-3 Years</li>
                                <li><i class="fas fa-book me-2"></i>Specializations Available</li>
                            </ul>
                        </div>
                    </div>
                </div>

                <!-- Mechanical Engineering -->
                <div class="col-md-6" data-aos="fade-up" data-aos-delay="300">
                    <div class="card course-card h-100">
                        <div class="card-body">
                            <div class="text-center mb-3">
                                <i class="fas fa-cog course-icon"></i>
                            </div>
                            <h5 class="card-title animate-hover">Mechanical Engineering</h5>
                            <p class="card-text">Master the principles of mechanics, thermodynamics, and machine design. Create innovative solutions for industrial challenges.</p>
                            <ul class="list-unstyled">
                                <li><i class="fas fa-clock me-2"></i>Duration: 2-3 Years</li>
                                <li><i class="fas fa-book me-2"></i>Specializations Available</li>
                            </ul>
                        </div>
                    </div>
                </div>

                <!-- Electrical Engineering -->
                <div class="col-md-6" data-aos="fade-up" data-aos-delay="400">
                    <div class="card course-card h-100">
                        <div class="card-body">
                            <div class="text-center mb-3">
                                <i class="fas fa-bolt course-icon"></i>
                            </div>
                            <h5 class="card-title animate-hover">Electrical Engineering</h5>
                            <p class="card-text">Study power systems, electronics, and control systems. Power the future with sustainable energy solutions.</p>
                            <ul class="list-unstyled">
                                <li><i class="fas fa-clock me-2"></i>Duration: 2-3 Years</li>
                                <li><i class="fas fa-book me-2"></i>Specializations Available</li>
                            </ul>
                        </div>
                    </div>
                </div>
            <?php endif; ?>
        </div>
    </div>

    <!-- Footer -->
    <footer class="footer">
        <div class="container">
            <div class="row g-4">
                <div class="col-md-4">
                    <h5 class="mb-3">Contact Us</h5>
                    <div class="d-flex align-items-center mb-2">
                        <i class="fas fa-map-marker-alt me-2"></i>
                        <p class="mb-0">Behind Kanksa BDO Office, Panagarh , Paschim Bardhaman, West Bengal, 713148</p>
                    </div>
                    <div class="d-flex align-items-center mb-2">
                        <i class="fas fa-phone me-2"></i>
                        <p class="mb-0">+91 1234567890</p>
                    </div>
                    <div class="d-flex align-items-center">
                        <i class="fas fa-envelope me-2"></i>
                        <p class="mb-0">tpd.academics@gmail.com</p>
                    </div>
                </div>
                <div class="col-md-4">
                    <h5 class="mb-3">Quick Links</h5>
                    <ul class="list-unstyled">
                        <li class="mb-2"><a href="courses.php" class="text-white text-decoration-none">Courses</a></li>
                        <li class="mb-2"><a href="about.php" class="text-white text-decoration-none">About Us</a></li>
                        <li class="mb-2"><a href="contact.php" class="text-white text-decoration-none">Contact</a></li>
                    </ul>
                </div>
                <div class="col-md-4">
                    <h5 class="mb-3">Follow Us</h5>
                    <div class="social-icons">
                        <a href="#" aria-label="Facebook"><i class="fab fa-facebook"></i></a>
                        <a href="#" aria-label="Twitter"><i class="fab fa-twitter"></i></a>
                        <a href="#" aria-label="Instagram"><i class="fab fa-instagram"></i></a>
                        <a href="#" aria-label="LinkedIn"><i class="fab fa-linkedin"></i></a>
                    </div>
                </div>
            </div>
            <hr class="bg-white my-4">
            <div class="text-center">
                <p class="mb-0">&copy; PROJECT A . All rights reserved.</p>
            </div>
        </div>
    </footer>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/js/all.min.js"></script>
    <script src="https://unpkg.com/aos@2.3.1/dist/aos.js"></script>
    <script>
        AOS.init({
            duration: 800,
            once: true,
            offset: 100,
            mirror: true,
            anchorPlacement: 'top-bottom'
        });

        // Add smooth scroll behavior
        document.querySelectorAll('a[href^="#"]').forEach(anchor => {
            anchor.addEventListener('click', function (e) {
                e.preventDefault();
                document.querySelector(this.getAttribute('href')).scrollIntoView({
                    behavior: 'smooth'
                });
            });
        });
    </script>
</body>
</html>
