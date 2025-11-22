<?php
require_once 'config/db_connection.php';
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Welcome to College Portal</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <link href="https://unpkg.com/aos@2.3.1/dist/aos.css" rel="stylesheet">
    <style>
        :root {
            --primary-color:rgb(220, 3, 18);
            --secondary-color:rgb(247, 12, 19);
        }
        
        .navbar {
            background: linear-gradient(120deg, var(--primary-color), var(--secondary-color));
            padding: 1rem 0;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
        }

        .navbar-brand {
            display: flex;
            align-items: center;
            gap: 10px;
            font-size: 1.5rem;
            font-weight: 600;
            padding: 0.5rem 1rem;
            border-radius: 20px;
            transition: all 0.3s ease;
        }

        .navbar-brand img {
            height: 90px;
            width: auto;
            transition: transform 0.3s ease;
        }

        .navbar-brand:hover {
            background: rgba(255, 255, 255, 0.2);
        }

        .navbar-brand:hover img {
            transform: scale(1.1);
        }

        .hero-section {
            background: linear-gradient(rgba(0, 0, 0, 0.7), rgba(0, 0, 0, 0.7)), url('images/college.png');
            background-size: cover;
            background-position: center;
            color: white;
            padding: 100px 0;
            text-align: center;
        }

        .feature-card {
            border: none;
            border-radius: 10px;
            box-shadow: 0 0 15px rgba(0,0,0,0.1);
            transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
            height: 100%;
            will-change: transform;
        }

        .feature-card:hover {
            transform: translateY(-10px) scale(1.02);
            box-shadow: 0 10px 20px rgba(0,0,0,0.15);
        }

        .feature-icon {
            font-size: 2.5rem;
            color: var(--primary-color);
            margin-bottom: 1rem;
            animation: float 3s ease-in-out infinite;
        }

        .news-section {
            background-color: #f8f9fa;
            padding: 60px 0;
        }

        .news-card {
            border: none;
            border-radius: 10px;
            box-shadow: 0 0 15px rgba(0,0,0,0.1);
            transition: transform 0.3s;
            margin-bottom: 20px;
        }

        .news-card:hover {
            transform: translateY(-5px);
        }

        .cta-section {
            background: linear-gradient(120deg, var(--primary-color), var(--secondary-color));
            color: white;
            padding: 60px 0;
        }

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

        .btn-custom {
            background: linear-gradient(120deg, var(--primary-color), var(--secondary-color));
            color: white;
            border: none;
            padding: 10px 30px;
            border-radius: 25px;
            transition: all 0.3s ease;
            position: relative;
            overflow: hidden;
            z-index: 1;
        }

        .btn-custom:hover {
            transform: translateY(-2px);
            color: white;
            box-shadow: 0 5px 15px rgba(0,0,0,0.3);
        }

        .btn-custom::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            width: 0;
            height: 100%;
            background: rgba(255,255,255,0.1);
            transition: all 0.3s ease;
            z-index: -1;
        }

        .btn-custom:hover::before {
            width: 100%;
        }

        .navbar-nav .nav-link {
            position: relative;
            overflow: hidden;
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

        .navbar-nav .nav-item .nav-link {
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

        @media (max-width: 991px) {
            .navbar-nav .nav-item .nav-link {
                padding: 0.5rem 1rem;
                margin: 0.2rem 0;
            }
            
            .navbar-nav .nav-item .nav-link:hover {
                transform: translateX(5px);
            }
        }

        /* Add animation keyframes */
        @keyframes fadeInUp {
            from {
                opacity: 0;
                transform: translateY(20px);
            }
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }

        @keyframes float {
            0% { transform: translateY(0px); }
            50% { transform: translateY(-10px); }
            100% { transform: translateY(0px); }
        }

        /* Add animation classes */
        .animate-fadeInUp {
            animation: fadeInUp 1s ease-out;
        }

        /* Mobile Responsive Styles */
        @media (max-width: 768px) {
            .navbar-brand {
                font-size: 1.2rem;
            }

            .hero-section {
                padding: 60px 0;
            }

            .hero-section h1 {
                font-size: 2rem;
            }

            .hero-section .lead {
                font-size: 1.1rem;
            }

            .hero-section .btn {
                margin-bottom: 10px;
                width: 100%;
                max-width: 200px;
            }

            .feature-card {
                margin-bottom: 20px;
            }

            .feature-card h4 {
                font-size: 1.3rem;
            }

            .news-section h2, 
            .cta-section h2 {
                font-size: 1.8rem;
            }

            .news-card {
                margin-bottom: 20px;
            }

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

        /* Small Mobile Devices */
        @media (max-width: 576px) {
            .hero-section h1 {
                font-size: 1.8rem;
            }

            .btn-custom {
                padding: 8px 20px;
                font-size: 0.9rem;
            }

            .feature-icon {
                font-size: 2rem;
            }

            .news-section, 
            .cta-section {
                padding: 40px 0;
            }

            .footer {
                padding: 30px 0;
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

        /* Mobile Navigation Optimization */
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

            .dropdown-menu {
                position: static !important;
                float: none;
                width: 100%;
                margin: 0.5rem 0;
                background: rgba(255,255,255,0.95);
                transform: none !important;
            }

            .dropdown-menu.show {
                display: block;
                opacity: 1;
                visibility: visible;
                margin-top: 0.5rem;
            }

            .navbar-toggler {
                border: none;
                padding: 0.5rem;
            }

            .navbar-toggler:focus {
                box-shadow: none;
                outline: none;
            }

            .navbar-brand {
                margin-right: auto;
            }

            /* Fix for login dropdown on mobile */
            .navbar-nav .nav-item.dropdown {
                position: static;
            }

            .dropdown-menu-end {
                right: auto;
                width: 100%;
            }

            /* Improve touch targets */
            .dropdown-item {
                padding: 0.75rem 1rem;
            }

            /* Add spacing between icons and text */
            .nav-link i,
            .dropdown-item i {
                margin-right: 0.75rem;
                width: 20px;
                text-align: center;
            }
        }

        /* Extra small devices */
        @media (max-width: 575px) {
            .navbar-brand {
                font-size: 1.1rem;
                padding: 0.4rem 0.8rem;
            }

            .navbar-toggler {
                padding: 0.4rem;
            }

            .dropdown-menu {
                margin: 0;
            }
        }
    </style>
</head>
<body>
    <!-- Navbar -->
    <nav class="navbar navbar-expand-lg navbar-dark">
        <div class="container position-relative">
            <a class="navbar-brand" href="index.php">
                <img src="images/logo1.png" alt="College Logo">
                TPD College Portal 
            </a>
            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
                <span class="navbar-toggler-icon"></span>
            </button>
            <div class="collapse navbar-collapse" id="navbarNav">
                <ul class="navbar-nav me-auto">
                    <li class="nav-item">
                        <a class="nav-link active" href="index.php">
                            <i class="fas fa-home me-1"></i>Home
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="pages/courses.php">
                            <i class="fas fa-graduation-cap me-1"></i>Courses
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="pages/about.php">
                            <i class="fas fa-info-circle me-1"></i>About
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="pages/contact.php">
                            <i class="fas fa-envelope me-1"></i>Contact
                        </a>
                    </li>
                </ul>
                <ul class="navbar-nav">
                    <li class="nav-item dropdown">
                        <a class="nav-link dropdown-toggle" href="#" id="loginDropdown" role="button" data-bs-toggle="dropdown" aria-expanded="false">
                            <i class="fas fa-user me-2"></i>Login/Register
                        </a>
                        <ul class="dropdown-menu dropdown-menu-end">
                            <li><h6 class="dropdown-header">Student</h6></li>
                            <li><a class="dropdown-item" href="student/login.php"><i class="fas fa-sign-in-alt me-2"></i>Student Login</a></li>
                            <li><a class="dropdown-item" href="student/register.php"><i class="fas fa-user-plus me-2"></i>Student Register</a></li>
                            <li><hr class="dropdown-divider"></li>
                            <li><h6 class="dropdown-header">Administrator</h6></li>
                            <li><a class="dropdown-item" href="admin/login.php"><i class="fas fa-user-shield me-2"></i>Admin Login</a></li>
                            <li><a class="dropdown-item" href="admin/register.php"><i class="fas fa-user-plus me-2"></i>Admin Register</a></li>
                        </ul>
                    </li>
                </ul>
            </div>
        </div>
    </nav>

    <!-- Hero Section -->
    <section class="hero-section">
        <div class="container">
            <h1 class="display-4 mb-4" data-aos="fade-down" data-aos-delay="100">Welcome to Techno Polytechnic Durgapur</h1>
            <p class="lead mb-4" data-aos="fade-up" data-aos-delay="200">Empowering Education Through Technology</p>
            <div class="d-flex flex-column flex-sm-row align-items-center justify-content-center gap-3" data-aos="zoom-in" data-aos-delay="300">
                <a href="student/register.php" class="btn btn-custom btn-lg">
                    <i class="fas fa-user-plus me-2"></i>Enroll Now
                </a>
                <a href="pages/courses.php" class="btn btn-outline-light btn-lg">
                    <i class="fas fa-book me-2"></i>Explore Courses
                </a>
            </div>
        </div>
    </section>

    <!-- Features Section -->
    <section class="py-5">
        <div class="container">
            <h2 class="text-center mb-5" data-aos="fade-up">Why Choose Us?</h2>
            <div class="row g-4 justify-content-center">
                <div class="col-sm-6 col-lg-4" data-aos="fade-up" data-aos-delay="100">
                    <div class="card feature-card">
                        <div class="card-body text-center">
                            <i class="fas fa-graduation-cap feature-icon"></i>
                            <h4>Quality Education</h4>
                            <p>Experience world-class education with our expert faculty and modern facilities.</p>
                        </div>
                    </div>
                </div>
                <div class="col-sm-6 col-lg-4" data-aos="fade-up" data-aos-delay="200">
                    <div class="card feature-card">
                        <div class="card-body text-center">
                            <i class="fas fa-laptop-code feature-icon"></i>
                            <h4>Modern Technology</h4>
                            <p>Access to state-of-the-art labs and learning resources.</p>
                        </div>
                    </div>
                </div>
                <div class="col-sm-6 col-lg-4" data-aos="fade-up" data-aos-delay="300">
                    <div class="card feature-card">
                        <div class="card-body text-center">
                            <i class="fas fa-users feature-icon"></i>
                            <h4>Career Support</h4>
                            <p>Dedicated placement cell to help you achieve your career goals.</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- News Section -->
    <section class="news-section">
        <div class="container">
            <h2 class="text-center mb-5" data-aos="fade-up">Latest Updates</h2>
            <div class="row g-4 justify-content-center">
                <div class="col-sm-6 col-lg-4" data-aos="fade-up" data-aos-delay="100">
                    <div class="card news-card h-100">
                        <div class="card-body d-flex flex-column">
                            <h5 class="card-title">Admission Open 2025</h5>
                            <p class="card-text flex-grow-1">Applications are now open for the academic year 2025. Apply early to secure your seat.</p>
                            <a href="student/register.php" class="btn btn-custom mt-3">Apply Now</a>
                        </div>
                    </div>
                </div>
                <div class="col-sm-6 col-lg-4" data-aos="fade-up" data-aos-delay="200">
                    <div class="card news-card h-100">
                        <div class="card-body d-flex flex-column">
                            <h5 class="card-title">New Courses Added</h5>
                            <p class="card-text flex-grow-1">Explore our new specialized courses in emerging technologies.</p>
                            <a href="pages/courses.php" class="btn btn-custom mt-3">Learn More</a>
                        </div>
                    </div>
                </div>
                <div class="col-sm-6 col-lg-4" data-aos="fade-up" data-aos-delay="300">
                    <div class="card news-card h-100">
                        <div class="card-body d-flex flex-column">
                            <h5 class="card-title">Campus Placements</h5>
                            <p class="card-text flex-grow-1">Outstanding placement records with top companies visiting our campus.</p>
                            <a href="pages/about.php" class="btn btn-custom mt-3">View Details</a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- CTA Section -->
    <section class="cta-section">
        <div class="container text-center">
            <h2 class="mb-4">Ready to Start Your Journey?</h2>
            <p class="lead mb-4">Join us and be a part of our growing academic community.</p>
            <a href="student/register.php" class="btn btn-light btn-lg">Get Started Today</a>
        </div>
    </section>

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
                        <li class="mb-2"><a href="pages/courses.php" class="text-white text-decoration-none">Courses</a></li>
                        <li class="mb-2"><a href="pages/about.php" class="text-white text-decoration-none">About Us</a></li>
                        <li class="mb-2"><a href="pages/contact.php" class="text-white text-decoration-none">Contact</a></li>
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
    <script src="https://unpkg.com/aos@2.3.1/dist/aos.js"></script>
    <script>
        // Initialize AOS
        AOS.init({
            duration: 800,
            once: true,
            offset: 100,
            delay: 100,
            mirror: false,
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

        // Performance optimization
        document.addEventListener('DOMContentLoaded', function() {
            // Lazy load images
            const images = document.querySelectorAll('img[data-src]');
            const imageObserver = new IntersectionObserver((entries, observer) => {
                entries.forEach(entry => {
                    if (entry.isIntersecting) {
                        const img = entry.target;
                        img.src = img.getAttribute('data-src');
                        img.removeAttribute('data-src');
                        observer.unobserve(img);
                    }
                });
            });

            images.forEach(img => imageObserver.observe(img));
        });
    </script>
</body>
</html>
