<?php
require_once '../config/db_connection.php';

// Handle form submission
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $name = mysqli_real_escape_string($conn, $_POST['name']);
    $email = mysqli_real_escape_string($conn, $_POST['email']);
    $phone = mysqli_real_escape_string($conn, $_POST['phone']);
    $message = mysqli_real_escape_string($conn, $_POST['message']);

    $sql = "INSERT INTO contact_messages (name, email, phone , message)
            VALUES ('$name', '$email', '$phone', '$message')";

    if (mysqli_query($conn, $sql)) {
        $success_message = "Message sent successfully!";
    } else {
        $error_message = "Error: " . mysqli_error($conn);
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Contact Us - College Portal</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
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

        .contact-section {
            background-color: #f8f9fa;
            padding: 60px 0;
        }
        .contact-card {
            border: none;
            border-radius: 10px;
            box-shadow: 0 0 15px rgba(0,0,0,0.1);
            transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
            will-change: transform;
        }
        .info-card {
            border: none;
            border-radius: 10px;
            box-shadow: 0 0 15px rgba(0,0,0,0.1);
            transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
            will-change: transform;
        }
        .info-card:hover {
            transform: translateY(-5px) scale(1.02);
            box-shadow: 0 15px 30px rgba(0,0,0,0.15);
        }
        .form-control:focus {
            transform: scale(1.01);
            transition: all 0.3s ease;
        }
        .btn-primary {
            background: linear-gradient(120deg, #2980b9, #8e44ad);
            border: none;
            transition: all 0.3s ease;
        }
        .btn-primary:hover {
            transform: translateY(-2px);
            box-shadow: 0 5px 15px rgba(0,0,0,0.2);
        }
        .card-title i {
            animation: float 3s ease-in-out infinite;
        }

        /* Update submit button styles */
        .btn-submit {
            background: linear-gradient(120deg, var(--primary-color), var(--secondary-color));
            color: white;
            border: none;
            padding: 12px 30px;
            border-radius: 5px;
            font-weight: 500;
            transition: all 0.3s ease;
        }

        .btn-submit:hover {
            transform: translateY(-2px);
            box-shadow: 0 5px 15px rgba(220, 3, 18, 0.3);
        }

        .btn-submit:active {
            transform: translateY(0);
        }

        /* Map container styles */
        .map-container {
            margin-top: 3rem;
            padding: 20px 0;
            background-color: #fff;
        }
        
        .map-container .card {
            border: none;
            border-radius: 10px;
            overflow: hidden;
            box-shadow: 0 0 15px rgba(0,0,0,0.1);
        }
        
        .map-frame {
            width: 100%;
            height: 180px;  /* Reduced height */
            border: 0;
        }

        /* Add these new styles for height matching */
        .contact-row {
            display: flex;
            flex-wrap: wrap;
        }

        .contact-col {
            display: flex;
            flex-direction: column;
        }

        .contact-card {
            height: 100%;
            display: flex;
            flex-direction: column;
        }

        .contact-card .card-body {
            flex: 1;
        }

        .info-cards-container {
            height: 100%;
            display: flex;
            flex-direction: column;
            gap: 10px; /* Reduced gap between cards */
        }

        .info-card {
            flex: 1;
            margin-bottom: 0;
        }

        .info-card:last-child {
            margin-bottom: 0;
        }

        .map-frame {
            height: 250px;  /* Adjusted height */
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
                        <a class="nav-link" href="courses.php">
                            <i class="fas fa-graduation-cap me-1"></i>Courses
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="about.php">
                            <i class="fas fa-info-circle me-1"></i>About
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link active" href="contact.php">
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
    <div class="contact-section">
        <div class="container">
            <h2 class="text-center mb-5" data-aos="fade-down">Contact Us</h2>
            
            <div class="row contact-row">
                <div class="col-md-6 mb-4 contact-col" data-aos="fade-right">
                    <div class="card contact-card">
                        <div class="card-body">
                            <h4 class="card-title mb-4">Send us a Message</h4>
                            <form id="contactForm" method="POST" action="">
                                <?php if (isset($success_message)): ?>
                                    <div class="alert alert-success" role="alert"><?php echo $success_message; ?></div>
                                <?php endif; ?>
                                <?php if (isset($error_message)): ?>
                                    <div class="alert alert-danger" role="alert"><?php echo $error_message; ?></div>
                                <?php endif; ?>
                                <div class="mb-3">
                                    <label for="name" class="form-label">Your Name</label>
                                    <input type="text" class="form-control" id="name" name="name" required>
                                </div>
                                <div class="mb-3">
                                    <label for="email" class="form-label">Email Address</label>
                                    <input type="email" class="form-control" id="email" name="email" required>
                                </div>
                                <div class="mb-3">
                                    <label for="phone" class="form-label">Phone Number</label>
                                    <input type="text" class="form-control" id="phone" name="phone" required>
                                </div>
                                <div class="mb-3">
                                    <label for="message" class="form-label">Message</label>
                                    <textarea class="form-control" id="message" name="message" rows="5" required></textarea>
                                </div>
                                <div class="text-center mb-4">
                                    <button type="submit" class="btn btn-submit">
                                        <i class="fas fa-paper-plane me-2"></i>Send Message
                                    </button>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>

                <div class="col-md-6 contact-col" data-aos="fade-left">
                    <div class="info-cards-container">
                        <div class="col-12" data-aos="fade-up" data-aos-delay="100">
                            <div class="card info-card">
                                <div class="card-body">
                                    <h5 class="card-title"><i class="fas fa-map-marker-alt me-2"></i>Address</h5>
                                    <p class="card-text">Behind Kanksa BDO Office, Panagarh , Paschim Bardhaman, West Bengal, 713148<br>PinCode - 713148</p>
                                </div>
                            </div>
                        </div>

                        <div class="col-12" data-aos="fade-up" data-aos-delay="200">
                            <div class="card info-card">
                                <div class="card-body">
                                    <h5 class="card-title"><i class="fas fa-phone me-2"></i>Phone</h5>
                                    <p class="card-text">
                                        Main Office: +919126661234<br>
                                        Admission Cell: +91 85975793197 / +91 8597579326 / +91 9126661234
                                    </p>
                                </div>
                            </div>
                        </div>

                        <div class="col-12" data-aos="fade-up" data-aos-delay="300">
                            <div class="card info-card">
                                <div class="card-body">
                                    <h5 class="card-title"><i class="fas fa-envelope me-2"></i>Email</h5>
                                    <p class="card-text">
                                        General Inquiries: tip.durgapur@gmail.com<br>
                                        Admissions: tip.durgapur@gmail.com
                                    </p>
                                </div>
                            </div>
                        </div>

                        <!-- Map Card -->
                        <div class="col-12" data-aos="fade-up" data-aos-delay="400">
                            <div class="card info-card">
                                <div class="card-body p-0">
                                    <h5 class="card-title p-3 mb-0"><i class="fas fa-map me-2"></i>Find Us Here</h5>
                                    <iframe 
                                        class="map-frame"
                                        src="https://www.google.com/maps/embed?pb=!1m18!1m12!1m3!1d988.208331079634!2d87.44840759781565!3d23.46667957609776!2m3!1f0!2f0!3f0!3m2!1i1024!2i768!4f13.1!3m3!1m2!1s0x39f77be509e280fd%3A0x6acdd4adec827bf3!2sTechno%20Polytechnic%20Durgapur!5e0!3m2!1sen!2sin!4v1741366945926!5m2!1sen!2sin"
                                        allowfullscreen=""
                                        loading="lazy"
                                        referrerpolicy="no-referrer-when-downgrade">
                                    </iframe>
                                </div>
                            </div>
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

        // Form animation
        const form = document.getElementById('contactForm');
        form.addEventListener('submit', function(e) {
            e.preventDefault();
            this.classList.add('animate__animated', 'animate__pulse');
            setTimeout(() => this.submit(), 1000);
        });
    </script>
</body>
</html>
