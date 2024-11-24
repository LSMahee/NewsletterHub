<?php
session_start();

if (!isset($_SESSION['Email'])) {
    header("Location: login.php");
    exit;
}

include 'db.php';

if (!isset($_GET['id']) || !is_numeric($_GET['id'])) {
    header('Location: user-home.php');
    exit();
}

$newsletterId = (int)$_GET['id'];

$query = "SELECT n.NewsletterNo, n.Title, n.Content, n.CategoryName, 
          DATE_FORMAT(pa.PublishDate, '%Y-%m-%d') as PublishDate
          FROM Newsletter n
          JOIN PublishedAs pa ON n.NewsletterNo = pa.NewsletterNo
          WHERE n.NewsletterNo = $newsletterId";

$result = $conn->query($query);

$newsletter = $result->fetch_assoc();

if (!$newsletter) {
    header('Location: user-home.php');
    exit();
}

$relatedQuery = "SELECT n.NewsletterNo, n.Title, p.PublishDate 
                 FROM Newsletter as n INNER JOIN PublishedAs as p ON n.NewsletterNo=p.NewsletterNo
                 WHERE n.CategoryName = '{$newsletter['CategoryName']}' AND n.NewsletterNo != $newsletterId 
                 LIMIT 4";

$relatedResult = $conn->query($relatedQuery);

$relatedNewsletters = [];
while ($related = $relatedResult->fetch_assoc()) {
    $relatedNewsletters[] = $related;
}

mysqli_close($conn);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo htmlspecialchars($newsletter['Title']); ?> - NewsletterHub</title>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <link href="../css/newsletter_post.css" rel="stylesheet">    
</head>
<body>
    <header class="header">
        <div class="container header-content">
            <a href="user-home.php" class="logo">
                <i class="fas fa-paper-plane"></i> NewsletterHub
            </a>
        </div>
    </header>

    <main class="container">
        <article class="newsletter-container">
            <header class="newsletter-header">
                <div class="newsletter-category"><?php echo htmlspecialchars($newsletter['CategoryName']); ?></div>
                <h1 class="newsletter-title"><?php echo htmlspecialchars($newsletter['Title']); ?></h1>
                <div class="newsletter-meta">
                    <i class="far fa-calendar"></i>
                    <span>Published on <?php echo date('F d, Y', strtotime($newsletter['PublishDate'])); ?></span>
                </div>
            </header>

            <div class="newsletter-content">
                <?php echo $newsletter['Content']; ?>
            </div>

            <?php if (!empty($relatedNewsletters)): ?>
                <div class="related-newsletters">
                    <h3>More from <?php echo htmlspecialchars($newsletter['CategoryName']); ?></h3>
                    <div class="related-newsletters-grid">
                        <?php foreach ($relatedNewsletters as $relatedNewsletter): ?>
                            <a href="newsletter_post.php?id=<?php echo $relatedNewsletter['NewsletterNo']; ?>" class="related-newsletter-card">
                                <h4><?php echo htmlspecialchars($relatedNewsletter['Title']); ?></h4>
                                <span class="related-newsletter-date"><?php echo date('F d, Y', strtotime($relatedNewsletter['PublishDate'])); ?></span>
                            </a>
                        <?php endforeach; ?>
                    </div>
                </div>
            <?php endif; ?>

            <a href="dashboard.php" class="back-button">
                <i class="fas fa-arrow-left"></i> Back to Dashboard
            </a>
        </article>
    </main>  
</body>
</html>
