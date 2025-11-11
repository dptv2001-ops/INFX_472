<?php
/**
 * WIKI MAIN PAGE
 * 
 * This page displays the wiki articles. It can show either:
 * 1. A list of all articles (default view)
 * 2. A single article when requested by short_title parameter
 */

// Include required files for authentication and database connection
require_once "auth.php";
require_once "db.php";

// AUTHENTICATION CHECK
// Ensure the user is logged in before viewing articles
require_auth();

// Get the current logged-in user's username for display
$user = current_user();

// SINGLE ARTICLE VIEW HANDLING
// Check if a specific article was requested via URL parameter
// Example: wiki.php?short_title=my-article-title
if (isset($_GET['short_title'])) {
    // Escape the short_title to prevent SQL injection
    $short_title = $mysqli->real_escape_string($_GET['short_title']);
    
    // Build SQL query to fetch article with author information
    // LEFT JOIN ensures we get the article even if author_id is NULL
    // The WHERE clause handles both:
    // - New articles with proper short_title
    // - Old articles without short_title (backward compatibility)
    $sql = "SELECT a.*, u.username AS author
            FROM article a
            LEFT JOIN users u ON a.author_id = u.id
            WHERE a.short_title = '$short_title' OR (a.short_title IS NULL AND LOWER(REPLACE(REPLACE(REPLACE(a.title, ' ', '-'), '.', ''), ',', '')) = '$short_title')";
    
    // Execute query and fetch article data
    $result = $mysqli->query($sql);
    $article = $result->fetch_assoc();
}
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
            background: linear-gradient(135deg, #e3f2fd 0%, #bbdefb 100%);
            min-height: 100vh;
            margin: 0;
            padding: 20px;
        }

        header {
            text-align: center;
            margin-bottom: 30px;
            padding: 20px;
            background: rgba(255, 255, 255, 0.9);
            border-radius: 15px;
            box-shadow: 0 4px 15px rgba(74, 144, 226, 0.2);
        }

        nav {
            display: flex;
            justify-content: center;
            gap: 15px;
            flex-wrap: wrap;
            margin-top: 20px;
        }

        nav a {
            margin: 0;
        }

        main {
            background: rgba(255, 255, 255, 0.9);
            border-radius: 15px;
            padding: 30px;
            box-shadow: 0 4px 15px rgba(74, 144, 226, 0.2);
            max-width: 900px;
            margin: 0 auto;
        }

        article, section {
            background: transparent;
        }

        article h2, section h2 {
            color: #4a90e2;
            border-bottom: 2px solid #e3f2fd;
            padding-bottom: 10px;
        }

        article div, section div {
            border-bottom: 1px solid #e3f2fd;
            padding: 15px 0;
            transition: background-color 0.3s ease;
        }

        article div:hover, section div:hover {
            background-color: #f5f9ff;
            border-radius: 8px;
            padding-left: 10px;
        }

        footer {
            text-align: center;
            margin-top: 30px;
            color: #666;
            padding: 20px;
        }
    </style>
</head>
<body>
    <header>
        <img src="images/infx_logo.png" alt="INFX Wiki logo" style="width: 120px; height: auto; display: block; margin: 0 auto 15px auto;">
        <h1>The INFX Wiki</h1>
        <p class="subheading">Welcome, <?php echo htmlspecialchars($user); ?>!</p>
        <nav>
            <a href="wiki.php" id="return">Home</a>
            <a href="addarticle.php" id="return">Add Article</a>
            <a href="logout.php" id="return">Logout</a>
        </nav>
    </header>

    <main>
    <!-- SUCCESS MESSAGE DISPLAY -->
    <!-- Display success messages passed via URL parameter (e.g., after adding article) -->
    <?php if (isset($_GET['msg'])): ?>
        <div style="background-color: #d4edda; color: #155724; padding: 15px; border-radius: 10px; margin-bottom: 20px; border: 1px solid #c3e6cb; max-width: 800px; margin: 0 auto;">
            <?php echo htmlspecialchars($_GET['msg']); ?>
        </div>
    <?php endif; ?>
    
    <!-- CONTENT DISPLAY LOGIC -->
    <?php if (isset($article)): ?>
        <!-- SINGLE ARTICLE VIEW -->
        <!-- Display a single article when short_title is provided in URL -->
        <article style="max-width:800px; margin:auto;">
            <!-- Article Title -->
            <h2><?php echo htmlspecialchars($article['title']); ?></h2>
            
            <!-- Article Image (if provided) -->
            <?php if (!empty($article['image'])): ?>
                <?php 
                $image_filename = htmlspecialchars($article['image']);
                $image_path_relative = "uploads/" . $image_filename;
                $image_path_absolute = __DIR__ . "/uploads/" . $image_filename;
                // Check if file exists using absolute path
                if (file_exists($image_path_absolute)): ?>
                    <img src="<?php echo $image_path_relative; ?>" alt="Article Image" style="max-width:100%; border-radius:10px; margin: 20px 0;">
                <?php else: ?>
                    <p style="color: #999; font-style: italic;">Image not found: <?php echo $image_filename; ?></p>
                <?php endif; ?>
            <?php endif; ?>
            
            <!-- Article Intro Text -->
            <!-- nl2br() converts newlines to <br> tags for proper display -->
            <p><strong>Intro:</strong> <?php echo nl2br(htmlspecialchars($article['intro'])); ?></p>
            
            <!-- Article Body Content -->
            <p><?php echo nl2br(htmlspecialchars($article['body'])); ?></p>

            <!-- Article References (if provided) -->
            <?php if ($article['reference']): ?>
                <p><strong>References:</strong><br><?php echo nl2br(htmlspecialchars($article['reference'])); ?></p>
            <?php endif; ?>

            <!-- Article Metadata -->
            <!-- Display author and creation date -->
            <p style="font-style:italic; color:gray;">
                Posted by <?php echo htmlspecialchars($article['author'] ?? 'Unknown'); ?> 
                on <?php echo htmlspecialchars($article['created_at']); ?>
            </p>

            <!-- Navigation link back to article list -->
            <p><a href="wiki.php" id="return">← Back to all articles</a></p>
        </article>

    <?php else: ?>
        <!-- ARTICLE LIST VIEW -->
        <!-- Display list of all articles (default view) -->
        <section style="max-width:800px; margin:auto;">
            <h2>All Articles</h2>
            <?php
            // FETCH ALL ARTICLES FROM DATABASE
            // Query to get all articles, ordered by creation date (newest first)
            // Include id for fallback URL generation if short_title is missing
            $result = $mysqli->query("SELECT id, short_title, title, intro, created_at FROM article ORDER BY created_at DESC");
            
            // Check if articles exist
            if ($result && $result->num_rows > 0):
                // Loop through each article
                while ($row = $result->fetch_assoc()):
                    // URL GENERATION FOR ARTICLE LINKS
                    // Generate proper URL for each article
                    // Handle both new articles (with short_title) and old articles (without)
                    if (!empty($row['short_title'])) {
                        // Use existing short_title if available
                        $short = htmlspecialchars($row['short_title']);
                        $link = "wiki.php?short_title=" . urlencode($short);
                    } else {
                        // BACKWARD COMPATIBILITY
                        // For old articles without short_title, generate one from title
                        // Convert title to URL-friendly format
                        $title_for_url = strtolower($row['title']);
                        $title_for_url = preg_replace('/[^a-z0-9]+/', '-', $title_for_url);
                        $title_for_url = trim($title_for_url, '-');
                        
                        // If title conversion fails, use article ID as fallback
                        if (empty($title_for_url)) {
                            $title_for_url = 'article-' . $row['id'];
                        }
                        $link = "wiki.php?short_title=" . urlencode($title_for_url);
                        $short = $title_for_url;
                    }
            ?>
                <!-- Individual Article Entry in List -->
                <div style="border-bottom:1px solid #ccc; padding:10px 0;">
                    <!-- Article Title as Clickable Link -->
                    <h3><a href="<?php echo $link; ?>"><?php echo htmlspecialchars($row['title']); ?></a></h3>
                    
                    <!-- Article Intro Text Preview -->
                    <p><?php echo nl2br(htmlspecialchars($row['intro'])); ?></p>
                    
                    <!-- Article Publication Date -->
                    <small>Published on <?php echo htmlspecialchars($row['created_at']); ?></small>
                </div>
            <?php
                endwhile;
            else:
                // EMPTY STATE
                // Display message when no articles exist yet
                echo "<p>No articles yet. <a href='addarticle.php'>Add one here</a>.</p>";
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
