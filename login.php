<?php
require_once "db.php";
if (session_status() === PHP_SESSION_NONE) { session_start(); }

$err = "";

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $username = trim($_POST["username"] ?? "");
    $password = $_POST["password"] ?? "";

    if ($username === "" || $password === "") {
        $err = "Username and password are required.";
    } else {
        // Look up user
        $stmt = mysqli_prepare($mysqli, "SELECT id, username, password_hash FROM users WHERE username = ?");
        mysqli_stmt_bind_param($stmt, "s", $username);
        mysqli_stmt_execute($stmt);
        $result = mysqli_stmt_get_result($stmt);

        if ($row = mysqli_fetch_assoc($result)) {
            // Pepper the incoming password the same way we did at signup
            $peppered = hash_hmac("sha256", $password, PEPPER);

            if (password_verify($peppered, $row["password_hash"])) {
                // Success: start session
                $_SESSION["username"] = $row["username"];
                $_SESSION["user_id"] = $row["id"];
                header("Location: index.php?msg=Welcome+" . urlencode($row["username"]));
                exit();
            } else {
                $err = "Invalid username or password.";
            }
        } else {
            $err = "Account not found. Create one?";
        }
    }
}
?>

<!doctype html>
<html lang="en">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <title>Login - The INFX Wiki</title>
        <link rel="stylesheet" href="style.css">
        <style>
            /* Login Page Specific Styles */
            body.login-page {
                margin: 0;
                padding: 0;
                min-height: 100vh;
                display: flex;
                align-items: center;
                justify-content: center;
                background: linear-gradient(135deg, #6bb6ff 0%, #4a90e2 100%);
                position: relative;
                overflow: hidden;
            }

            /* Animated background elements */
            body.login-page::before {
                content: '';
                position: absolute;
                width: 200%;
                height: 200%;
                background: radial-gradient(circle, rgba(255,255,255,0.1) 1px, transparent 1px);
                background-size: 50px 50px;
                animation: backgroundMove 20s linear infinite;
                opacity: 0.3;
            }

            @keyframes backgroundMove {
                0% { transform: translate(0, 0); }
                100% { transform: translate(50px, 50px); }
            }

            /* Enhanced floating bubbles animation */
            .bubble {
                position: absolute;
                border-radius: 50%;
                background: rgba(255, 255, 255, 0.15);
                animation: float 8s ease-in-out infinite;
                backdrop-filter: blur(2px);
            }

            .bubble:nth-child(1) {
                width: 80px;
                height: 80px;
                left: 10%;
                top: 20%;
                animation-delay: 0s;
                animation-duration: 6s;
            }

            .bubble:nth-child(2) {
                width: 60px;
                height: 60px;
                left: 20%;
                top: 60%;
                animation-delay: 2s;
                animation-duration: 8s;
            }

            .bubble:nth-child(3) {
                width: 100px;
                height: 100px;
                left: 80%;
                top: 30%;
                animation-delay: 4s;
                animation-duration: 7s;
            }

            .bubble:nth-child(4) {
                width: 50px;
                height: 50px;
                left: 70%;
                top: 70%;
                animation-delay: 1s;
                animation-duration: 9s;
            }

            .bubble:nth-child(5) {
                width: 90px;
                height: 90px;
                left: 5%;
                top: 50%;
                animation-delay: 3s;
                animation-duration: 10s;
            }

            @keyframes float {
                0%, 100% { 
                    transform: translateY(0) translateX(0) rotate(0deg) scale(1);
                    opacity: 0.7;
                }
                25% { 
                    transform: translateY(-30px) translateX(10px) rotate(90deg) scale(1.1);
                    opacity: 1;
                }
                50% { 
                    transform: translateY(-60px) translateX(-10px) rotate(180deg) scale(0.9);
                    opacity: 0.8;
                }
                75% { 
                    transform: translateY(-30px) translateX(15px) rotate(270deg) scale(1.05);
                    opacity: 0.9;
                }
            }

            /* Particle effect */
            .particle {
                position: absolute;
                width: 4px;
                height: 4px;
                background: rgba(255, 255, 255, 0.6);
                border-radius: 50%;
                animation: particleFloat 15s infinite;
            }

            @keyframes particleFloat {
                0% {
                    transform: translateY(100vh) translateX(0) rotate(0deg);
                    opacity: 0;
                }
                10% {
                    opacity: 1;
                }
                90% {
                    opacity: 1;
                }
                100% {
                    transform: translateY(-100px) translateX(100px) rotate(360deg);
                    opacity: 0;
                }
            }

            .login-container {
                position: relative;
                z-index: 1;
                width: 100%;
                max-width: 420px;
                padding: 20px;
            }

            .login-card {
                background: rgba(255, 255, 255, 0.95);
                backdrop-filter: blur(10px);
                border-radius: 20px;
                padding: 40px;
                box-shadow: 0 20px 60px rgba(0, 0, 0, 0.3);
                animation: slideIn 0.6s ease-out, pulse 3s ease-in-out infinite;
                transform: translateY(0);
                transition: all 0.3s ease;
            }

            .login-card:hover {
                box-shadow: 0 25px 70px rgba(0, 0, 0, 0.35);
                transform: translateY(-2px);
            }

            @keyframes slideIn {
                from {
                    opacity: 0;
                    transform: translateY(-30px) scale(0.95);
                }
                to {
                    opacity: 1;
                    transform: translateY(0) scale(1);
                }
            }

            @keyframes pulse {
                0%, 100% {
                    box-shadow: 0 20px 60px rgba(0, 0, 0, 0.3);
                }
                50% {
                    box-shadow: 0 20px 60px rgba(74, 144, 226, 0.4);
                }
            }

            .login-card h1 {
                color: #4a90e2;
                margin-bottom: 10px;
                font-size: 2.5em;
                text-align: center;
                animation: fadeIn 0.8s ease-out 0.2s both;
            }

            @keyframes fadeIn {
                from { opacity: 0; }
                to { opacity: 1; }
            }

            .login-subtitle {
                text-align: center;
                color: #666;
                margin-bottom: 30px;
                font-size: 0.9em;
                animation: fadeIn 0.8s ease-out 0.4s both;
            }

            .message {
                padding: 12px 20px;
                border-radius: 8px;
                margin-bottom: 20px;
                text-align: center;
                animation: slideDown 0.5s ease-out;
                font-weight: 500;
            }

            @keyframes slideDown {
                from {
                    opacity: 0;
                    transform: translateY(-10px);
                }
                to {
                    opacity: 1;
                    transform: translateY(0);
                }
            }

            .message.success {
                background-color: #d4edda;
                color: #155724;
                border: 1px solid #c3e6cb;
            }

            .message.error {
                background-color: #f8d7da;
                color: #721c24;
                border: 1px solid #f5c6cb;
            }

            .login-form {
                animation: fadeIn 0.8s ease-out 0.6s both;
            }

            .form-group {
                margin-bottom: 25px;
                position: relative;
            }

            .form-group label {
                display: block;
                margin-bottom: 8px;
                color: #333;
                font-weight: 600;
                font-size: 0.95em;
                transition: all 0.3s ease;
                transform-origin: left;
            }

            .form-group input {
                width: 100%;
                padding: 14px 16px;
                padding-right: 45px;
                border: 2px solid #e0e0e0;
                border-radius: 10px;
                font-size: 1em;
                transition: all 0.3s ease;
                box-sizing: border-box;
                background: #f8f9fa;
                position: relative;
            }

            .form-group input:focus {
                outline: none;
                border-color: #4a90e2;
                background: #fff;
                box-shadow: 0 0 0 3px rgba(74, 144, 226, 0.1), 0 4px 12px rgba(74, 144, 226, 0.15);
                transform: translateY(-2px);
            }

            .form-group input:focus + .input-icon {
                color: #4a90e2;
                transform: scale(1.1);
            }

            .form-group input:valid {
                border-color: #28a745;
            }

            .form-group input:invalid:not(:placeholder-shown) {
                border-color: #dc3545;
                animation: shake 0.5s;
            }

            @keyframes shake {
                0%, 100% { transform: translateX(0); }
                25% { transform: translateX(-5px); }
                75% { transform: translateX(5px); }
            }

            .form-group input::placeholder {
                color: #999;
                transition: opacity 0.3s ease;
            }

            .form-group input:focus::placeholder {
                opacity: 0.5;
            }

            .input-wrapper {
                position: relative;
            }

            .input-icon {
                position: absolute;
                right: 15px;
                top: 50%;
                transform: translateY(-50%);
                color: #999;
                transition: all 0.3s ease;
                pointer-events: none;
                font-size: 1.1em;
            }

            .password-toggle {
                position: absolute;
                right: 15px;
                top: 50%;
                transform: translateY(-50%);
                background: none;
                border: none;
                cursor: pointer;
                padding: 5px;
                transition: all 0.3s ease;
                z-index: 2;
                width: 24px;
                height: 24px;
                display: flex;
                align-items: center;
                justify-content: center;
            }

            .password-toggle svg {
                width: 20px;
                height: 20px;
                fill: #999;
                transition: all 0.3s ease;
            }

            .password-toggle:hover svg {
                fill: #4a90e2;
                transform: scale(1.1);
            }

            .password-toggle:active svg {
                transform: scale(0.95);
            }

            .login-button {
                width: 100%;
                padding: 14px;
                background: linear-gradient(135deg, #6bb6ff 0%, #4a90e2 100%);
                color: white;
                border: none;
                border-radius: 10px;
                font-size: 1.1em;
                font-weight: 600;
                cursor: pointer;
                transition: all 0.3s ease;
                margin-top: 10px;
                box-shadow: 0 4px 15px rgba(74, 144, 226, 0.4);
                position: relative;
                overflow: hidden;
            }

            .login-button::before {
                content: '';
                position: absolute;
                top: 50%;
                left: 50%;
                width: 0;
                height: 0;
                border-radius: 50%;
                background: rgba(255, 255, 255, 0.3);
                transform: translate(-50%, -50%);
                transition: width 0.6s, height 0.6s;
            }

            .login-button:hover {
                transform: translateY(-2px);
                box-shadow: 0 6px 20px rgba(74, 144, 226, 0.6);
            }

            .login-button:hover::before {
                width: 300px;
                height: 300px;
            }

            .login-button:active {
                transform: translateY(0);
            }

            .login-button.loading {
                pointer-events: none;
                opacity: 0.8;
            }

            .login-button.loading::after {
                content: '';
                position: absolute;
                width: 20px;
                height: 20px;
                top: 50%;
                left: 50%;
                margin-left: -10px;
                margin-top: -10px;
                border: 3px solid rgba(255, 255, 255, 0.3);
                border-top-color: white;
                border-radius: 50%;
                animation: spin 0.8s linear infinite;
            }

            @keyframes spin {
                to { transform: rotate(360deg); }
            }

            .ripple {
                position: absolute;
                border-radius: 50%;
                background: rgba(255, 255, 255, 0.6);
                transform: scale(0);
                animation: ripple-animation 0.6s ease-out;
                pointer-events: none;
            }

            @keyframes ripple-animation {
                to {
                    transform: scale(4);
                    opacity: 0;
                }
            }

            .signup-link {
                text-align: center;
                margin-top: 25px;
                padding-top: 25px;
                border-top: 1px solid #e0e0e0;
                animation: fadeIn 0.8s ease-out 0.8s both;
            }

            .signup-link p {
                color: #666;
                margin: 0;
                font-size: 0.95em;
            }

            .signup-link a {
                color: #4a90e2;
                text-decoration: none;
                font-weight: 600;
                transition: all 0.3s ease;
                position: relative;
            }

            .signup-link a::after {
                content: '';
                position: absolute;
                width: 0;
                height: 2px;
                bottom: -2px;
                left: 0;
                background: #4a90e2;
                transition: width 0.3s ease;
            }

            .signup-link a:hover::after {
                width: 100%;
            }

            .signup-link a:hover {
                color: #6bb6ff;
            }
        </style>
    </head>

    <body class="login-page">
        <div class="bubble"></div>
        <div class="bubble"></div>
        <div class="bubble"></div>
        <div class="bubble"></div>
        <div class="bubble"></div>
        
        <div class="login-container">
            <div class="login-card">
                <h1>Welcome Back</h1>
                <p class="login-subtitle">Sign in to continue to The INFX Wiki</p>

                <?php if (isset($_GET['msg'])): ?>
                    <div class="message success">
                        <?php echo htmlspecialchars($_GET['msg']); ?>
                    </div>
                <?php endif; ?>

                <?php if ($err): ?>
                    <div class="message error">
                        <?php echo htmlspecialchars($err); ?>
                    </div>
                <?php endif; ?>

                <form method="post" action="login.php" class="login-form" id="loginForm">
                    <div class="form-group">
                        <label for="username">Username</label>
                        <div class="input-wrapper">
                            <input type="text" name="username" id="username" placeholder="Enter your username" required autofocus>
                            <span class="input-icon">👤</span>
                        </div>
                    </div>

                    <div class="form-group">
                        <label for="password">Password</label>
                        <div class="input-wrapper">
                            <input type="password" name="password" id="password" placeholder="Enter your password" required>
                            <button type="button" class="password-toggle" id="passwordToggle" aria-label="Toggle password visibility">
                                <svg id="eyeIcon" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24">
                                    <path d="M12 4.5C7 4.5 2.73 7.61 1 12c1.73 4.39 6 7.5 11 7.5s9.27-3.11 11-7.5c-1.73-4.39-6-7.5-11-7.5zM12 17c-2.76 0-5-2.24-5-5s2.24-5 5-5 5 2.24 5 5-2.24 5-5 5zm0-8c-1.66 0-3 1.34-3 3s1.34 3 3 3 3-1.34 3-3-1.34-3-3-3z"/>
                                </svg>
                                <svg id="eyeOffIcon" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" style="display: none;">
                                    <path d="M12 7c2.76 0 5 2.24 5 5 0 .65-.13 1.26-.36 1.83l2.92 2.92c1.51-1.26 2.7-2.89 3.43-4.75-1.73-4.39-6-7.5-11-7.5-1.4 0-2.74.25-3.98.7l2.16 2.16C10.74 7.13 11.35 7 12 7zM2 4.27l2.28 2.28.46.46C3.08 8.3 1.78 10.02 1 12c1.73 4.39 6 7.5 11 7.5 1.55 0 3.03-.3 4.38-.84l.42.42L19.73 22 21 20.73 3.27 3 2 4.27zM7.53 9.8l1.55 1.55c-.05.21-.08.43-.08.65 0 1.66 1.34 3 3 3 .22 0 .44-.03.65-.08l1.55 1.55c-.67.33-1.41.53-2.2.53-2.76 0-5-2.24-5-5 0-.79.2-1.53.53-2.2zm4.31-.78l3.15 3.15.02-.16c0-1.66-1.34-3-3-3l-.17.01z"/>
                                </svg>
                            </button>
                        </div>
                    </div>

                    <button type="submit" class="login-button" id="loginButton">Sign In</button>
                </form>

                <div class="signup-link">
                    <p>Don't have an account? <a href="createuser.php">Create one</a></p>
                </div>
            </div>
        </div>

        <script>
            /**
             * INTERACTIVE LOGIN FORM FUNCTIONALITY
             * 
             * This script adds interactive features to the login form including:
             * - Password visibility toggle
             * - Ripple effects on button click
             * - Form submission loading states
             * - Input validation feedback
             * - Floating particle effects
             * - Typing animation for title
             */
            
            // PASSWORD TOGGLE FUNCTIONALITY
            // Allow users to show/hide password by clicking the eye icon
            const passwordToggle = document.getElementById('passwordToggle');
            const passwordInput = document.getElementById('password');
            const eyeIcon = document.getElementById('eyeIcon');
            const eyeOffIcon = document.getElementById('eyeOffIcon');
            
            // Toggle password visibility when eye icon is clicked
            passwordToggle.addEventListener('click', function() {
                // Switch between 'password' and 'text' input types
                const type = passwordInput.getAttribute('type') === 'password' ? 'text' : 'password';
                passwordInput.setAttribute('type', type);
                
                // Show appropriate icon based on visibility state
                if (type === 'password') {
                    // Password is hidden - show open eye icon
                    eyeIcon.style.display = 'block';
                    eyeOffIcon.style.display = 'none';
                } else {
                    // Password is visible - show closed eye icon
                    eyeIcon.style.display = 'none';
                    eyeOffIcon.style.display = 'block';
                }
                
                // Add visual feedback with scale animation
                this.style.transform = 'translateY(-50%) scale(1.2)';
                setTimeout(() => {
                    this.style.transform = 'translateY(-50%) scale(1)';
                }, 200);
            });

            // BUTTON RIPPLE EFFECT
            // Create a ripple animation effect at the click location
            const loginButton = document.getElementById('loginButton');
            loginButton.addEventListener('click', function(e) {
                // Get the button's position relative to the viewport
                const rect = this.getBoundingClientRect();
                
                // Calculate click position relative to the button
                const x = e.clientX - rect.left;
                const y = e.clientY - rect.top;
                
                // Create ripple element and position it at click location
                const ripple = document.createElement('span');
                ripple.classList.add('ripple');
                ripple.style.left = x + 'px';
                ripple.style.top = y + 'px';
                
                // Add ripple to button and remove it after animation completes
                this.appendChild(ripple);
                
                setTimeout(() => {
                    ripple.remove();
                }, 600);
            });

            // FORM SUBMISSION LOADING STATE
            // Show loading indicator when form is submitted
            const loginForm = document.getElementById('loginForm');
            loginForm.addEventListener('submit', function(e) {
                const button = document.getElementById('loginButton');
                // Add loading class which triggers CSS spinner animation
                button.classList.add('loading');
                // Change button text to indicate processing
                button.textContent = 'Signing In...';
            });

            // INPUT FIELD INTERACTIONS
            // Add visual feedback for input field interactions
            const inputs = document.querySelectorAll('input');
            inputs.forEach(input => {
                // Scale up input wrapper slightly when field is focused
                input.addEventListener('focus', function() {
                    this.parentElement.style.transform = 'scale(1.02)';
                });
                
                // Return to normal size when field loses focus
                input.addEventListener('blur', function() {
                    this.parentElement.style.transform = 'scale(1)';
                });

                // REAL-TIME VALIDATION FEEDBACK
                // Change border color based on input validity
                input.addEventListener('input', function() {
                    if (this.checkValidity()) {
                        // Valid input - green border
                        this.style.borderColor = '#28a745';
                        this.style.boxShadow = '0 0 0 3px rgba(40, 167, 69, 0.1)';
                    } else if (this.value.length > 0) {
                        // Invalid input with content - red border
                        this.style.borderColor = '#dc3545';
                        this.style.boxShadow = '0 0 0 3px rgba(220, 53, 69, 0.1)';
                    }
                });
            });

            // FLOATING PARTICLE EFFECTS
            // Create animated particles that float up the screen for visual appeal
            function createParticle() {
                // Create a new particle element
                const particle = document.createElement('div');
                particle.classList.add('particle');
                
                // Randomize particle position and animation properties
                particle.style.left = Math.random() * 100 + '%';           // Random horizontal position
                particle.style.animationDelay = Math.random() * 2 + 's';  // Random delay before animation starts
                particle.style.animationDuration = (10 + Math.random() * 10) + 's'; // Random animation duration (10-20s)
                
                // Add particle to the page
                document.body.appendChild(particle);
                
                // Remove particle after animation completes (20 seconds max)
                setTimeout(() => {
                    particle.remove();
                }, 20000);
            }

            // Generate new particles every 300ms for continuous effect
            setInterval(createParticle, 300);

            // TYPING ANIMATION FOR TITLE
            // Animate the "Welcome Back" title to appear character by character
            const title = document.querySelector('.login-card h1');
            const originalText = title.textContent;
            
            // Clear the title initially
            title.textContent = '';
            let i = 0;
            
            // Function to type out the title one character at a time
            function typeTitle() {
                if (i < originalText.length) {
                    // Add next character
                    title.textContent += originalText.charAt(i);
                    i++;
                    // Continue typing after 100ms delay
                    setTimeout(typeTitle, 100);
                }
            }
            
            // Start typing animation after 500ms delay (page load)
            setTimeout(typeTitle, 500);
        </script>
    </body>
</html>