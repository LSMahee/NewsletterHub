<?php
include 'db.php';

$category = $_GET['category'] ?? 'all';
$search = $_GET['search'] ?? '';
$search_type = $_GET['search_type'] ?? 'all';
$sort = $_GET['sort'] ?? 'date-desc';

$query = "SELECT BlogID, Title, Content, PublishDate, CategoryName FROM blog";
$whereClause = '';

if ($category !== 'all') {
    $whereClause = " WHERE CategoryName = '$category'";
}

if ($search) {
    $whereClause .= $category !== 'all' ? " AND" : " WHERE";

    switch ($search_type) {
        case 'title':
            $whereClause .= " Title LIKE '%$search%'";
            break;
        case 'content':
            $whereClause .= " Content LIKE '%$search%'";
            break;
        default:
            $whereClause .= " (Title LIKE '%$search%' OR Content LIKE '%$search%')";
    }
}

switch ($sort) {
    case 'date-asc':
        $whereClause .= " ORDER BY PublishDate ASC";
        break;
    case 'title-asc':
        $whereClause .= " ORDER BY Title ASC";
        break;
    case 'title-desc':
        $whereClause .= " ORDER BY Title DESC";
        break;
    default:
        $whereClause .= " ORDER BY PublishDate DESC";
}

$blogPostsQuery = $query . $whereClause;
$result = $conn->query($blogPostsQuery);

if ($result->num_rows > 0) {
    $blogPosts = [];
    while ($row = $result->fetch_assoc()) {
        $blogPosts[] = $row;
    }
} else {
    $blogPosts = [];
}

$categoriesQuery = "SELECT DISTINCT CategoryName FROM blog ORDER BY CategoryName";
$categoriesResult = $conn->query($categoriesQuery);
$categories = [];
if ($categoriesResult->num_rows > 0) {
    while ($row = $categoriesResult->fetch_assoc()) {
        $categories[] = $row['CategoryName'];
    }
} else {
    $categories = [];
}

function createExcerpt($content, $length = 37) {
    return strlen($content) > $length ? substr(strip_tags($content), 0, $length) . '...' : strip_tags($content);
}

function formatDate($date) {
    return date('M d, Y', strtotime($date));
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>My Blog</title>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <link href="../css/blog.css" rel="stylesheet">
</head>
<body>
    <header class="header">
        <div class="container header-content">
            <a class="back-button" href="dashboard.php">
                <i class="fas fa-arrow-left"></i> Back
            </a>
            <form class="search-container" method="GET" action="">
                <div class="search-wrapper">
                    <input type="text" name="search" class="search-bar" 
                           placeholder="Search blog posts..." 
                           value="<?= htmlspecialchars($search) ?>">
                    <select name="search_type" class="search-type-toggle">
                        <option value="all" <?= $search_type === 'all' ? 'selected' : '' ?>>All</option>
                        <option value="title" <?= $search_type === 'title' ? 'selected' : '' ?>>Title</option>
                        <option value="content" <?= $search_type === 'content' ? 'selected' : '' ?>>Content</option>
                    </select>
                    <button type="submit" class="search-icon">
                        <i class="fas fa-search"></i>
                    </button>
                </div>
                <select name="sort" class="sort-dropdown" onchange="this.form.submit()">
                    <option value="date-desc" <?= $sort === 'date-desc' ? 'selected' : '' ?>>Newest First</option>
                    <option value="date-asc" <?= $sort === 'date-asc' ? 'selected' : '' ?>>Oldest First</option>
                    <option value="title-asc" <?= $sort === 'title-asc' ? 'selected' : '' ?>>Title A-Z</option>
                    <option value="title-desc" <?= $sort === 'title-desc' ? 'selected' : '' ?>>Title Z-A</option>
                </select>
            </form>
        </div>
    </header>

    <main class="container">
        <div class="category-filter">
            <a href="?<?= http_build_query(array_merge($_GET, ['category' => 'all'])) ?>" 
               class="category-btn <?= $category === 'all' ? 'active' : '' ?>">All</a>
            <?php foreach($categories as $cat): ?>
                <a href="?<?= http_build_query(array_merge($_GET, ['category' => $cat])) ?>" 
                   class="category-btn <?= $category === $cat ? 'active' : '' ?>">
                    <?= htmlspecialchars($cat) ?>
                </a>
            <?php endforeach; ?>
        </div>

        <div class="blog-grid">
            <?php if (!empty($blogPosts)): ?>
                <?php foreach ($blogPosts as $post): ?>
                    <article class="blog-card">
                        <div class="card-content">
                            <div class="card-category"><?= htmlspecialchars($post['CategoryName']) ?></div>
                            <h2 class="card-title">
                                <a href="post.php?id=<?= htmlspecialchars($post['BlogID']) ?>">
                                    <?= htmlspecialchars($post['Title']) ?>
                                </a>
                            </h2>
                            <p class="card-excerpt">
                                <?= createExcerpt($post['Content']) ?>
                            </p>
                            <div class="card-meta">
                                <i class="far fa-calendar"></i>
                                <span><?= formatDate($post['PublishDate']) ?></span>
                            </div>
                        </div>
                    </article>
                <?php endforeach; ?>
            <?php else: ?>
                <div class="no-posts">
                    <p>No blog posts found.</p>
                </div>
            <?php endif; ?>
        </div>
    </main>

    <script>
    document.addEventListener('DOMContentLoaded', function() {
        document.querySelector('.sort-dropdown').addEventListener('change', function() {
            this.form.submit();
        });
        
        document.querySelectorAll('.blog-card').forEach((card, index) => {
            card.style.animationDelay = `${index * 0.1}s`;
        });
    });
    </script>
</body>
</html>
