<?php
require_once "db.php";
if(session_status() === PHP_SESSION_NONE) {session_start();}

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
<html lang ="en">
    <head>
        <meta charset = "utf-8">
        <title>Create Account</title>
    </head>

    <body>
        <h1>Create Account</h1>
        
        <?php if ($err): ?>
            <p style="color:red;"><?php echo htmlspecialchars($err); ?></p>
        <?php endif; ?>

        <form method="post" action="createuser.php">
            <label for="username">Username:</label><br>
            <input type="text" name="username" id="username" required><br><br>

            <label for="password">Password:</label><br>
            <input type="text" name="password" id="password" required><br><br>

            <button type="submit">Create Account</button>
        </form>

        <p>Already have an account? <a href="login.php">Login</a></php>

    </body>
</html>