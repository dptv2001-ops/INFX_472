
<?php 
require_once "auth.php"; 
require_once "db.php"; 

// Check if the user is authenticated 
if (isset($_SESSION['username'])) {
    // User is logged in, redirect them to the main wiki page
    header('Location: wiki.php'); 
    exit();
}

// Check for status messages from login.php or createuser.php
$message = isset($_GET['msg']) ? htmlspecialchars($_GET['msg']) : '';
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>The INFX Wiki</title>
    <link rel="stylesheet" href="style.css">
    <style>
        body {
            background: linear-gradient(135deg, #6bb6ff 0%, #4a90e2 100%);
            min-height: 100vh;
            margin: 0;
            padding: 20px;
            display: flex;
            flex-direction: column;
            justify-content: center;
            align-items: center;
        }

        .welcome-container {
            background: rgba(255, 255, 255, 0.95);
            backdrop-filter: blur(10px);
            border-radius: 20px;
            padding: 50px;
            box-shadow: 0 20px 60px rgba(0, 0, 0, 0.3);
            max-width: 600px;
            text-align: center;
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

        .main-heading {
            color: #4a90e2;
            font-size: 2.8em;
            margin-bottom: 15px;
        }

        .subheading {
            color: #666;
            font-size: 1.2em;
            margin-bottom: 30px;
        }

        .message {
            background-color: #d4edda;
            color: #155724;
            padding: 15px;
            border-radius: 10px;
            margin-bottom: 20px;
            border: 1px solid #c3e6cb;
        }

        .nav-links {
            margin-top: 30px;
            display: flex;
            gap: 20px;
            justify-content: center;
            flex-wrap: wrap;
        }

        .nav-links a {
            padding: 15px 30px;
            background: linear-gradient(135deg, #6bb6ff 0%, #4a90e2 100%);
            color: white;
            text-decoration: none;
            border-radius: 10px;
            font-weight: 600;
            transition: all 0.3s ease;
            box-shadow: 0 4px 15px rgba(74, 144, 226, 0.4);
        }

        .nav-links a:hover {
            transform: translateY(-2px);
            box-shadow: 0 6px 20px rgba(74, 144, 226, 0.6);
        }

        footer {
            margin-top: 40px;
            color: white;
            text-align: center;
        }
    </style>
</head>
<body>
    <div class="welcome-container">
        <header>
            <h1 class="main-heading">Welcome to The INFX Wiki</h1>
            <p class="subheading">Your Knowledge Hub for INFX 472</p>
        </header>

        <main>
            <?php if ($message): ?>
                <div class="message">
                    <?php echo $message; ?>
                </div>
            <?php endif; ?>

            <p style="color: #666; margin-bottom: 20px;">You must log in to view or add articles and navigate through the website.</p>
            
            <div class="nav-links">
                <a href="login.php">Log In</a>
                <a href="createuser.php">Create New Account</a>
            </div>
        </main>
    </div>
    
    <footer>
        <p>&copy; 2025 INFX Wiki Project</p>
    </footer>
</body>
</html>