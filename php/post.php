<?php
include 'db.php';

if (!isset($_GET['id']) || !is_numeric($_GET['id'])) {
    header('Location: index.php');
    exit();
}

$blogId = (int)$_GET['id'];

$postQuery = $conn->query("SELECT BlogID, Title, Content, PublishDate, CategoryName FROM blog WHERE BlogID = $blogId");
$post = $postQuery->fetch_assoc();

if (!$post) {
    header('Location: index.php');
    exit();
}

$relatedPostsQuery = $conn->query("SELECT BlogID, Title, PublishDate FROM blog WHERE CategoryName = '{$post['CategoryName']}' AND BlogID != $blogId ORDER BY PublishDate DESC LIMIT 4");
$relatedPosts = $relatedPostsQuery->fetch_all(MYSQLI_ASSOC);

function formatDate($date) {
    return date('F d, Y', strtotime($date));
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo htmlspecialchars($post['Title']); ?> - MyBlog</title>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <link href="../css/post.css" rel="stylesheet">
</head>
<body>
    <main class="container">
        <article class="post-container">
            <header class="post-header">
                <div class="post-category"><?php echo htmlspecialchars($post['CategoryName']); ?></div>
                <h1 class="post-title"><?php echo htmlspecialchars($post['Title']); ?></h1>
                <div class="post-meta">
                    <i class="far fa-calendar"></i>
                    <span>Published on <?php echo formatDate($post['PublishDate']); ?></span>
                </div>
            </header>

            <div class="post-content">
                <?php echo $post['Content']; ?>
            </div>

            <?php if (!empty($relatedPosts)): ?>
                <div class="related-posts">
                    <h3>More from <?php echo htmlspecialchars($post['CategoryName']); ?></h3>
                    <div class="related-posts-grid">
                        <?php foreach ($relatedPosts as $relatedPost): ?>
                            <a href="post.php?id=<?php echo $relatedPost['BlogID']; ?>" class="related-post-card">
                                <h4><?php echo htmlspecialchars($relatedPost['Title']); ?></h4>
                                <span class="related-post-date"><?php echo formatDate($relatedPost['PublishDate']); ?></span>
                            </a>
                        <?php endforeach; ?>
                    </div>
                </div>
            <?php endif; ?>

            <a href="blog.php" class="back-button">
                <i class="fas fa-arrow-left"></i> Back to Blog
            </a>
        </article>
    </main>
</body>
</html>
