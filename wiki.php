<?php
require_once "auth.php";
require_once "db.php";

// Require user authentication
require_auth();

// Get current logged-in user
$user = current_user();

// If a specific article is requested by short_title
if (isset($_GET['short_title'])) {
    $short_title = $mysqli->real_escape_string($_GET['short_title']);
    $sql = "SELECT a.*, u.username AS author
            FROM article a
            LEFT JOIN users u ON a.author_id = u.id
            WHERE a.short_title = '$short_title'";
    $result = $mysqli->query($sql);
    $article = $result->fetch_assoc();
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>The INFX Wiki</title>
    <link rel="stylesheet" href="style.css">
</head>
<body>
    <header>
        <nav class="nav-bar">
            <ul>
                <li><a class="active" href="wiki.php">Home</a></li>
                <li><a href="addarticle.php">Add Article</a></li>
                <li><a href="logout.php">Logout</a></li>
                <li><?php echo htmlspecialchars($user); ?></li>
            </ul>
        </nav>
        <h1>The INFX Wiki</h1>
        <p class="subheading">Welcome, <?php echo htmlspecialchars($user); ?>!</p>
    </header>

    <main>
    <?php if (isset($article)): ?>
        <!-- Single Article View -->
        <article style="max-width:800px; margin:auto;">
            <h2><?php echo htmlspecialchars($article['title']); ?></h2>
            <?php if ($article['image']): ?>
                <img src="uploads/<?php echo htmlspecialchars($article['image']); ?>" alt="Article Image" style="max-width:100%; border-radius:10px;">
            <?php endif; ?>
            <p><strong>Intro:</strong> <?php echo nl2br(htmlspecialchars($article['intro'])); ?></p>
            <p><?php echo nl2br(htmlspecialchars($article['body'])); ?></p>

            <?php if ($article['reference']): ?>
                <p><strong>References:</strong><br><?php echo nl2br(htmlspecialchars($article['reference'])); ?></p>
            <?php endif; ?>

            <p style="font-style:italic; color:gray;">
                Posted by <?php echo htmlspecialchars($article['author'] ?? 'Unknown'); ?> 
                on <?php echo htmlspecialchars($article['created_at']); ?>
            </p>

            <p><a href="wiki.php" id="return">← Back to all articles</a></p>
        </article>

    <?php else: ?>
        <!-- Article List View -->
        <section style="max-width:800px; margin:auto;">
            <h2>All Articles</h2>
            <?php
            $result = $mysqli->query("SELECT short_title, title, intro, created_at FROM article ORDER BY created_at DESC");
            if ($result && $result->num_rows > 0):
                while ($row = $result->fetch_assoc()):
                    $short = htmlspecialchars($row['short_title']);
            ?>
                <div style="border-bottom:1px solid #ccc; padding:10px 0;">
                    <h3><a href="wiki.php?short_title=<?php echo $short; ?>"><?php echo htmlspecialchars($row['title']); ?></a></h3>
                    <p><?php echo nl2br(htmlspecialchars($row['intro'])); ?></p>
                    <small>Published on <?php echo htmlspecialchars($row['created_at']); ?></small>
                </div>
            <?php
                endwhile;
            else:
                echo "<p>No articles yet. <a href='addarticle.php' style='background-color: var(--primary-color);
                color: white; padding: 8px 12px; text-decoration: none; border-radius: 5px;
                transition: background-color 0.3s ease'>Add one here</a></p>";
            endif;
            ?>
        </section>
    <?php endif; ?>
    </main>

    <footer>
        <p>&copy; 2025 INFX Wiki Project</p>
    </footer>
</body>
</html>
