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
        <title>Login</title>
        <link rel="stylesheet" href="style.css">
    </head>

    <body>
        <h1>Login</h1>

        <?php if (isset($_GET['msg'])): ?>
            <p style="color:green;"><?php echo htmlspecialchars($_GET['msg']); ?></p>
        <?php endif; ?>

        <?php if ($err): ?>
            <p style="color:red;"><?php echo htmlspecialchars($err); ?></p>
        <?php endif; ?>

        <form method="post" action="login.php">
            <label for="username">Username:</label><br>
            <input type="text" name="username" id="username" required><br><br>

            <label for="password">Password:</label><br>
            <input type="password" name="password" id="password" required><br><br>

            <button type="submit">Login</button>
        </form>

        <p>Don't have an account? <a href="createuser.php">Create one</a></p>
    </body>
</html>