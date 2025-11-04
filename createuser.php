<?php
require_once "db.php";
if (session_status() === PHP_SESSION_NONE) { session_start(); }

$err = "";

if ($_SERVER["REQUEST_METHOD"] === "POST"){
    $username = trim($_POST["username"] ?? "");
    $password = $_POST["password"] ?? "";

    if ($username === "" || $password === "") {
        $err = "Username and password are required.";
    }else{
        //check if user exists
        $stmt = mysqli_prepare($mysqli, "SELECT id FROM users WHERE username = ?");
        mysqli_stmt_bind_param($stmt, "s", $username);
        mysqli_stmt_execute($stmt);
        $res = mysqli_stmt_get_result($stmt);

        if (mysqli_fetch_assoc($res)) {
            $err = "Username already exists. Please choose another.";
        } else {
            // Hash password with pepper and salt (bcrypt)
            $peppered = hash_hmac("sha256", $password, PEPPER);
            $hash = password_hash($peppered, PASSWORD_BCRYPT);

            $stmt2 = mysqli_prepare($mysqli, "INSERT INTO users (username, password_hash) VALUES (?, ?)");
            mysqli_stmt_bind_param($stmt2, "ss", $username, $hash);

            if (mysqli_stmt_execute($stmt2)) {
                $_SESSION["username"] = $username;
                $_SESSION["user_id"] = mysqli_insert_id($mysqli);
                header("Location: index.php?msg=Account+created");
                exit();
            } else {
                $err = "Failed to create account: " . htmlspecialchars(mysqli_error($mysqli));
            }
        }
    }
}
?>

<!doctype html>
<html lang="en">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <title>Create Account - The INFX Wiki</title>
        <link rel="stylesheet" href="style.css">
        <style>
            body.create-page {
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

            body.create-page::before {
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

            .bubble {
                position: absolute;
                border-radius: 50%;
                background: rgba(255, 255, 255, 0.1);
                animation: float 6s ease-in-out infinite;
            }

            .bubble:nth-child(1) {
                width: 80px;
                height: 80px;
                left: 10%;
                animation-delay: 0s;
            }

            .bubble:nth-child(2) {
                width: 60px;
                height: 60px;
                left: 20%;
                animation-delay: 2s;
            }

            .bubble:nth-child(3) {
                width: 100px;
                height: 100px;
                left: 80%;
                animation-delay: 4s;
            }

            @keyframes float {
                0%, 100% { transform: translateY(0) rotate(0deg); }
                50% { transform: translateY(-20px) rotate(180deg); }
            }

            .create-container {
                position: relative;
                z-index: 1;
                width: 100%;
                max-width: 420px;
                padding: 20px;
            }

            .create-card {
                background: rgba(255, 255, 255, 0.95);
                backdrop-filter: blur(10px);
                border-radius: 20px;
                padding: 40px;
                box-shadow: 0 20px 60px rgba(0, 0, 0, 0.3);
                animation: slideIn 0.6s ease-out;
            }

            @keyframes slideIn {
                from {
                    opacity: 0;
                    transform: translateY(-30px);
                }
                to {
                    opacity: 1;
                    transform: translateY(0);
                }
            }

            .create-card h1 {
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

            .create-subtitle {
                text-align: center;
                color: #666;
                margin-bottom: 30px;
                font-size: 0.9em;
                animation: fadeIn 0.8s ease-out 0.4s both;
            }

            .message.error {
                padding: 12px 20px;
                border-radius: 8px;
                margin-bottom: 20px;
                text-align: center;
                animation: slideDown 0.5s ease-out;
                font-weight: 500;
                background-color: #f8d7da;
                color: #721c24;
                border: 1px solid #f5c6cb;
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

            .create-form {
                animation: fadeIn 0.8s ease-out 0.6s both;
            }

            .form-group {
                margin-bottom: 25px;
            }

            .form-group label {
                display: block;
                margin-bottom: 8px;
                color: #333;
                font-weight: 600;
                font-size: 0.95em;
            }

            .form-group input {
                width: 100%;
                padding: 14px 16px;
                border: 2px solid #e0e0e0;
                border-radius: 10px;
                font-size: 1em;
                transition: all 0.3s ease;
                box-sizing: border-box;
                background: #f8f9fa;
            }

            .form-group input:focus {
                outline: none;
                border-color: #4a90e2;
                background: #fff;
                box-shadow: 0 0 0 3px rgba(74, 144, 226, 0.1);
                transform: translateY(-2px);
            }

            .create-button {
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
            }

            .create-button:hover {
                transform: translateY(-2px);
                box-shadow: 0 6px 20px rgba(74, 144, 226, 0.6);
            }

            .login-link {
                text-align: center;
                margin-top: 25px;
                padding-top: 25px;
                border-top: 1px solid #e0e0e0;
                animation: fadeIn 0.8s ease-out 0.8s both;
            }

            .login-link p {
                color: #666;
                margin: 0;
                font-size: 0.95em;
            }

            .login-link a {
                color: #4a90e2;
                text-decoration: none;
                font-weight: 600;
                transition: all 0.3s ease;
                position: relative;
            }

            .login-link a::after {
                content: '';
                position: absolute;
                width: 0;
                height: 2px;
                bottom: -2px;
                left: 0;
                background: #4a90e2;
                transition: width 0.3s ease;
            }

            .login-link a:hover::after {
                width: 100%;
            }

            .login-link a:hover {
                color: #6bb6ff;
            }
        </style>
    </head>

    <body class="create-page">
        <div class="bubble"></div>
        <div class="bubble"></div>
        <div class="bubble"></div>
        
        <div class="create-container">
            <div class="create-card">
                <h1>Create Account</h1>
                <p class="create-subtitle">Join The INFX Wiki community</p>

                <?php if ($err): ?>
                    <div class="message error">
                        <?php echo htmlspecialchars($err); ?>
                    </div>
                <?php endif; ?>

                <form method="post" action="createuser.php" class="create-form">
                    <div class="form-group">
                        <label for="username">Username</label>
                        <input type="text" name="username" id="username" placeholder="Choose a username" required autofocus>
                    </div>

                    <div class="form-group">
                        <label for="password">Password</label>
                        <input type="password" name="password" id="password" placeholder="Choose a password" required>
                    </div>

                    <button type="submit" class="create-button">Create Account</button>
                </form>

                <div class="login-link">
                    <p>Already have an account? <a href="login.php">Login</a></p>
                </div>
            </div>
        </div>
    </body>
</html>