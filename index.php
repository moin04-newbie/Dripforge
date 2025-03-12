<?php 
session_start();
include('./conn/conn.php');
include('./includes/session.php');

// Redirect if already logged in
if (isLoggedIn()) {
    header('Location: main/web.php');
    exit();
}

// Default error message
$loginError = '';
$registrationError = '';

// Check for error messages in URL
if (isset($_GET['login_error'])) {
    $loginError = urldecode($_GET['login_error']);
}

if (isset($_GET['reg_error'])) {
    $registrationError = urldecode($_GET['reg_error']);
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login & Registration - Dripforge</title>

    <!-- Style CSS -->
    <link rel="stylesheet" href="./assets/style.css">

    <!-- Bootstrap CSS -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@4.6.2/dist/css/bootstrap.min.css">
    <style>
        body {
            background-image: url('images/slider1.jpg');
            background-size: cover;
            background-position: center;
            background-repeat: no-repeat;
            position: relative;
        }
        
        body::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background-color: rgba(0, 0, 0, 0.6);
            z-index: -1;
        }
        
        .main {
            max-width: 900px;
            margin: 100px auto;
            background-color: rgba(255, 255, 255, 0.9);
            border-radius: 10px;
            box-shadow: 0 0 20px rgba(0, 0, 0, 0.3);
            overflow: hidden;
            display: flex;
            min-height: 500px;
        }
        
        .login, .registration {
            flex: 1;
            padding: 40px;
        }
        
        h1 {
            font-size: 2rem;
            margin-bottom: 30px;
            color: #333;
        }
        
        .form-group label {
            font-weight: 600;
            color: #555;
        }
        
        .login-btn, .login-register {
            background-color: #007bff;
            color: white;
            font-weight: 600;
            padding: 12px;
            border-radius: 5px;
            transition: all 0.3s ease;
        }
        
        .login-btn:hover, .login-register:hover {
            background-color: #0056b3;
        }
        
        .registrationForm {
            color: #007bff;
            margin-bottom: 10px;
            cursor: pointer;
            display: inline-block;
        }
        
        .registrationForm:hover {
            text-decoration: underline;
        }
        
        .error-message {
            background-color: #f8d7da;
            color: #721c24;
            padding: 10px;
            border-radius: 5px;
            margin-bottom: 20px;
        }
        
        .logo-link {
            display: block;
            text-align: center;
            margin-bottom: 20px;
        }
        
        .logo-text {
            font-size: 2.5rem;
            color: #333;
            font-weight: bold;
            text-decoration: none;
        }
        
        @media (max-width: 768px) {
            .main {
                flex-direction: column;
            }
        }
    </style>
</head>
<body>
    
    <div class="main">

        <!-- Login Area -->
        <div class="login" id="loginForm">
            <a href="main/web.php" class="logo-link">
                <span class="logo-text">Dripforge</span>
            </a>
            
            <h1 class="text-center">Login to Your Account</h1>
            
            <?php if (!empty($loginError)): ?>
                <div class="error-message">
                    <?= $loginError ?>
                </div>
            <?php endif; ?>
            
            <div class="login-form">
                <form action="endpoint/login.php" method="POST">
                    <div class="form-group">
                        <label for="username">Username:</label>
                        <input type="text" class="form-control" id="username" name="username" required>
                    </div>
                    <div class="form-group">
                        <label for="password">Password:</label>
                        <input type="password" class="form-control" id="password" name="password" required>
                    </div>
                    <p class="registrationForm" onclick="showRegistrationForm()">No Account? Register Here.</p>
                    <button type="submit" class="btn btn-dark login-btn form-control">Login</button>
                </form>
            </div>
        </div>


        <!-- Registration Area -->
        <div class="registration" id="registrationForm">
            <a href="main/web.php" class="logo-link">
                <span class="logo-text">Dripforge</span>
            </a>
            
            <h1 class="text-center">Create an Account</h1>
            
            <?php if (!empty($registrationError)): ?>
                <div class="error-message">
                    <?= $registrationError ?>
                </div>
            <?php endif; ?>
            
            <div class="registration-form">
                <form action="endpoint/add-user.php" method="POST">
                    <div class="form-group row">
                        <div class="col-6">
                            <label for="firstName">First Name:</label>
                            <input type="text" class="form-control" id="firstName" name="first_name" required>
                        </div>
                        <div class="col-6">
                            <label for="lastName">Last Name:</label>
                            <input type="text" class="form-control" id="lastName" name="last_name" required>
                        </div>
                    </div>
                    <div class="form-group row">
                        <div class="col-5">
                            <label for="contactNumber">Contact Number:</label>
                            <input type="number" class="form-control" id="contactNumber" name="contact_number" maxlength="11" required>
                        </div>
                        <div class="col-7">
                            <label for="email">Email:</label>
                            <input type="email" class="form-control" id="email" name="email" required>
                        </div>
                    </div>
                    <div class="form-group">
                        <label for="registerUsername">Username:</label>
                        <input type="text" class="form-control" id="registerUsername" name="username" required>
                    </div>
                    <div class="form-group">
                        <label for="registerPassword">Password:</label>
                        <input type="password" class="form-control" id="registerPassword" name="password" required>
                    </div>
                    <p class="registrationForm" onclick="showLoginForm()"><- Back to Login</p>
                    <button type="submit" class="btn btn-dark login-register form-control">Register</button>
                </form>
            </div>
        </div>
    </div>

    <script>
        // Constant variables
        const loginForm = document.getElementById('loginForm');
        const registrationForm = document.getElementById('registrationForm');

        // Hide registration form
        registrationForm.style.display = "none";

        function showRegistrationForm() {
            registrationForm.style.display = "";
            loginForm.style.display = "none";
        }

        function showLoginForm() {
            registrationForm.style.display = "none";
            loginForm.style.display = "";
        }
        
        <?php if (!empty($registrationError)): ?>
            // Show registration form if there's a registration error
            showRegistrationForm();
        <?php endif; ?>
    </script>

    <!-- Bootstrap Js -->
    <script src="https://cdn.jsdelivr.net/npm/jquery@3.5.1/dist/jquery.slim.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/popper.js@1.16.1/dist/umd/popper.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@4.6.2/dist/js/bootstrap.min.js"></script>
</body>
</html>