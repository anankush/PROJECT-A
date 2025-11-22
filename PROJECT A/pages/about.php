<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>About Us - College Portal</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <link href="https://unpkg.com/aos@2.3.1/dist/aos.css" rel="stylesheet">
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

        .about-section {
            background-color: #f8f9fa;
            padding: 60px 0;
        }
        .feature-card {
            border: none;
            border-radius: 10px;
            box-shadow: 0 0 15px rgba(0,0,0,0.1);
            transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
            will-change: transform;
        }
        .feature-card:hover {
            transform: translateY(-10px) scale(1.02);
            box-shadow: 0 15px 30px rgba(0,0,0,0.15);
        }
        .feature-icon {
            animation: float 3s ease-in-out infinite;
        }
        @keyframes float {
            0% { transform: translateY(0px); }
            50% { transform: translateY(-10px); }
            100% { transform: translateY(0px); }
        }
        .lead {
            transition: all 0.3s ease;
        }
        .lead:hover {
            transform: scale(1.02);
            color: var(--primary-color);
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

        /* Fix card alignment and hover effects */
        .row.g-4 {
            display: flex;
            flex-wrap: wrap;
            margin: 0 -1rem;
        }

        .row.g-4 > .col-md-4 {
            padding: 1rem;
            display: flex;
        }

        .feature-card {
            width: 100%;
            height: 100%;
            display: flex;
            flex-direction: column;
            border: none;
            border-radius: 10px;
            box-shadow: 0 0 15px rgba(0,0,0,0.1);
            transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
            overflow: hidden;
        }

        .feature-card .card-body {
            flex: 1;
            display: flex;
            flex-direction: column;
            justify-content: center;
            align-items: center;
            padding: 2rem 1.5rem;
        }

        .feature-card:hover {
            transform: translateY(-10px);
            box-shadow: 0 15px 30px rgba(0,0,0,0.15);
        }

        /* Update feature card and icon styles */
        .feature-icon {
            color: rgb(220, 3, 18) !important;  /* matches --primary-color */
            font-size: 3em;
            margin-bottom: 1rem;
            transition: all 0.3s ease;
        }

        .feature-card:hover .feature-icon {
            color: rgb(247, 12, 19) !important;  /* matches --secondary-color */
            transform: scale(1.1) translateY(-10px);
        }

        /* Remove any conflicting icon styles */
        .fa-graduation-cap.feature-icon,
        .fa-flask.feature-icon,
        .fa-users.feature-icon {
            color: rgb(220, 3, 18) !important;
        }

        .feature-card:hover .fa-graduation-cap.feature-icon,
        .feature-card:hover .fa-flask.feature-icon,
        .feature-card:hover .fa-users.feature-icon {
            color: rgb(247, 12, 19) !important;
        }

        @keyframes float {
            0% { transform: translateY(0px); }
            50% { transform: translateY(-10px); }
            100% { transform: translateY(0px); }
        }

        

    </style>
</head>
<body>
    <!-- Updated Navbar -->
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
                        <a class="nav-link" href="courses.php">
                            <i class="fas fa-graduation-cap me-1"></i>Courses
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link active" href="about.php">
                            <i class="fas fa-info-circle me-1"></i>About
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="contact.php">
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
    <div class="about-section">
        <div class="container">
            <h2 class="text-center mb-5" data-aos="fade-down">About Our College</h2>
            
            <div class="row mb-5">
                <div class="col-md-6" data-aos="fade-right" data-aos-delay="100">
                    <h3>Our Vision</h3>
                    <p class="lead">To be a leading institution in technical education, fostering innovation and creating future leaders in technology.</p>
                </div>
                <div class="col-md-6" data-aos="fade-left" data-aos-delay="200">
                    <h3>Our Mission</h3>
                    <p class="lead">To provide quality education, promote research and development, and prepare students for the challenges of tomorrow.</p>
                </div>
            </div>

            <div class="row g-4">
                <div class="col-md-4" data-aos="fade-up" data-aos-delay="100">
                    <div class="card feature-card h-100">
                        <div class="card-body text-center">
                            <i class="fas fa-graduation-cap feature-icon"></i>
                            <h4 class="card-title">Excellence in Education</h4>
                            <p class="card-text">Our institution maintains high academic standards with experienced faculty and modern facilities.</p>
                        </div>
                    </div>
                </div>

                <div class="col-md-4" data-aos="fade-up" data-aos-delay="200">
                    <div class="card feature-card h-100">
                        <div class="card-body text-center">
                            <i class="fas fa-flask feature-icon"></i>
                            <h4 class="card-title">Research Focus</h4>
                            <p class="card-text">We encourage research and innovation through the practical and real life things.</p>
                        </div>
                    </div>
                </div>

                <div class="col-md-4" data-aos="fade-up" data-aos-delay="300">
                    <div class="card feature-card h-100">
                        <div class="card-body text-center">
                            <i class="fas fa-users feature-icon"></i>
                            <h4 class="card-title">Student Success</h4>
                            <p class="card-text">Our placement cell ensures excellent career opportunities for our students.</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Footer -->
    <footer class="bg-dark text-white py-4 mt-5">
        <div class="container">
            <div class="text-center">
                <p class="mb-0">© PROJECT A . All rights reserved.</p>
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
            offset: 100
        });
    </script>
</body>
</html>
