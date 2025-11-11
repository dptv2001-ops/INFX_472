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

// hides edit form
$editmode_title = "style='display:none;'";
$editmode_intro = "style='display:none;'";
$editmode_body = "style='display:none;'";
$editmode_ref = "style='display:none;'";

// if edit icon is pressed, show form - based on which icon label is pressed
if(isset($_POST['title_form'])) {
    $editmode_title = "style='inline-block;'";
}
elseif(isset($_POST['intro_form'])) {
    $editmode_intro = "style='inline-block;'";
}
elseif(isset($_POST['body_form'])) {
    $editmode_body = "style='inline-block;'";
}
elseif(isset($_POST['ref_form'])) {
    $editmode_ref = "style='inline-block;'";
}

// upload change to database - based on which edit form was submitted
if(isset($_POST['confirm_title'])) {
    $short_title = $mysqli->real_escape_string($_GET['short_title']);
    $newtitle = $mysqli->real_escape_string($_POST['title_input']);
    $sql = "UPDATE article SET title='$newtitle' WHERE short_title='$short_title'";
    $result = $mysqli->query($sql);
}
elseif(isset($_POST['confirm_intro'])) {
    $short_title = $mysqli->real_escape_string($_GET['short_title']);
    $intro = $mysqli->real_escape_string($_POST['intro_input']);
    $sql = "UPDATE article SET intro='$intro' WHERE short_title='$short_title'";
    $result = $mysqli->query($sql);
}
elseif(isset($_POST['confirm_body'])) {
    $short_title = $mysqli->real_escape_string($_GET['short_title']);
    $body = $mysqli->real_escape_string($_POST['body_input']);
    $sql = "UPDATE article SET body='$body' WHERE short_title='$short_title'";
    $result = $mysqli->query($sql);
}
elseif(isset($_POST['confirm_references'])) {
    $short_title = $mysqli->real_escape_string($_GET['short_title']);
    $ref = $mysqli->real_escape_string($_POST['intro_input']);
    $sql = "UPDATE article SET reference='$ref' WHERE short_title='$short_title'";
    $result = $mysqli->query($sql);
}


?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>The INFX Wiki</title>
    <link rel="stylesheet" href="style.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/7.0.1/css/all.min.css" integrity="sha512-2SwdPD6INVrV/lHTZbO2nodKhrnDdJK9/kg2XD1r9uGqPo1cUbujc+IYdlYdEErWNu69gVcYgdxlmVmzTWnetw==" crossorigin="anonymous" referrerpolicy="no-referrer" />
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
   
            <h2 class="can_edit"><?php echo htmlspecialchars($article['title']); ?> <form class="style-strip" 
            method="POST" action="wiki.php?short_title=<?php echo urlencode($article['short_title']); ?>">
            <!-- triggers edit form -->
            <button type="submit" name="title_form" style="all:unset;"><i class="fa-regular fa-pen-to-square hidden"></button></i></form></h2>
            <!-- edit form -->
            <div class="form-input" <?php echo $editmode_title; ?>>
                <form method="POST" action="wiki.php?short_title=<?php echo urlencode($article['short_title']); ?>">
                    <label for="title_input">Edit title:</label><br>
                    <input type="text" id="title_input" name="title_input" placeholder="Enter text here">
                    <input type="submit" value="Confirm change" name="confirm_title">
                </form>
            </div>
            <!-- Article Image (if provided) -->
            <?php if (!empty($article['image'])): ?>
                <?php 
                $image_filename = htmlspecialchars($article['image']);
                $image_path_relative = "images/" . $image_filename;
                $image_path_absolute = __DIR__ . "/images/" . $image_filename;
                // Check if file exists using absolute path
                if (file_exists($image_path_absolute)): ?>
                    <img src="<?php echo $image_path_relative; ?>" alt="Article Image" style="max-width:100%; border-radius:10px; margin: 20px 0;">
                <?php else: ?>
                    <p style="color: #999; font-style: italic;">Image not found: <?php echo $image_filename; ?></p>
                <?php endif; ?>
            <?php endif; ?>
            
            <!-- Article Intro Text -->
            <!-- nl2br() converts newlines to <br> tags for proper display -->
            <h3 class="can_edit">
                    Intro
                    <form class="style-strip" method="POST" action="wiki.php?short_title=<?php echo urlencode($article['short_title']); ?>">
                        <!-- triggers edit form -->
                        <button type="submit" name="intro_form" style="all:unset;">
                            <i class="fa-regular fa-pen-to-square"></i>
                        </button>
                    </form>:
            </h3>
            <!-- edit form -->
            <div class="form-input" <?php echo $editmode_intro; ?>>
                <form method="POST" action="wiki.php?short_title=<?php echo urlencode($article['short_title']); ?>">
                    <label for="intro_input">Edit intro:</label><br>
                    <input type="text" id="intro_input" name="intro_input" placeholder="Enter text here">
                    <input type="submit" value="Confirm change" name="confirm_intro">
                </form>
            </div>
            <p><?php echo nl2br(htmlspecialchars($article['intro'])); ?></p>
            
            <!-- Article Body Content -->
            <h4 class="can_edit">
                Body
                    <form class="style-strip" method="POST" action="wiki.php?short_title=<?php echo urlencode($article['short_title']); ?>">
                        <!-- triggers edit form -->
                        <button type="submit" name="body_form" style="all:unset;">
                            <i class="fa-regular fa-pen-to-square"></i>
                        </button>
                    </form>:
            </h4>
            <div class="body-input" <?php echo $editmode_body; ?>>
                <form method="POST" action="wiki.php?short_title=<?php echo urlencode($article['short_title']); ?>">
                    <label for="body_input">Edit intro:</label><br>
                    <input type="text" id="body_input" name="body_input" placeholder="Enter text here">
                    <input type="submit" value="Confirm change" name="confirm_body">
                </form>
            </div>
            <p><?php echo nl2br(htmlspecialchars($article['body'])); ?></p>

            <!-- Article References (if provided) -->
            <?php if ($article['reference']): ?>
                <h3 class="can_edit">
                    References
                    <form class="style-strip" method="POST" action="wiki.php?short_title=<?php echo urlencode($article['short_title']); ?>">
                        <!-- triggers edit form -->
                        <button type="submit" name="ref_form" style="all:unset;">
                            <i class="fa-regular fa-pen-to-square"></i>
                        </button>
                    </form>:
                </h3>
                <!-- edit form -->
                <div class="form-input" <?php echo $editmode_ref; ?>>
                    <form method="POST" action="wiki.php?short_title=<?php echo urlencode($article['short_title']); ?>">
                        <label for="references_input">Edit references:</label><br>
                        <input type="text" id="references_input" name="references_input" placeholder="Enter text here">
                        <input type="submit" value="Confirm change" name="confirm_references">
                    </form>
                </div>
                <?php echo nl2br(htmlspecialchars($article['reference'])); ?>
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