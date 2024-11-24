<?php
session_start();

if (!isset($_SESSION['Email'])) {
    header("Location: ../login.html");
    exit;
}

include 'db.php';

$requestQuery = "SELECT COUNT(*) as pending_count FROM application WHERE approval_status = 'Pending'";
$requestResult = $conn->query($requestQuery);
$pendingCount = $requestResult->fetch_assoc()['pending_count'];

$query = "SELECT DISTINCT n.NewsletterNo, n.Title, n.Content, n.CategoryName, 
          DATE_FORMAT(pa.PublishDate, '%Y-%m-%d') as PublishDate
          FROM Newsletter n
          JOIN NewsletterReceivers nr ON n.NewsletterNo = nr.NewsletterNo
          JOIN PublishedAs pa ON n.NewsletterNo = pa.NewsletterNo
          WHERE nr.UserID = {$_SESSION['UserID']}
          ORDER BY pa.PublishDate DESC
          LIMIT 3";

$result = $conn->query($query);

$newsletters = array();
while ($row = $result->fetch_assoc()) {
    $newsletters[] = $row;
}

$subQuery = "SELECT c.CategoryName 
             FROM user as u 
             INNER JOIN subscription as s ON u.UserID = s.UserID 
             INNER JOIN category as c ON s.CategoryID = c.CategoryID 
             WHERE u.Email = '{$_SESSION['Email']}'";

$subResult = $conn->query($subQuery);

$subscriptions = [
    'Business' => false,
    'Books' => false,
    'Sports' => false,
    'Quotes' => false
];

while ($subrow = $subResult->fetch_assoc()) {
    $category = $subrow['CategoryName'];
    if (array_key_exists($category, $subscriptions)) {
        $subscriptions[$category] = true;
    }
}

$subCount = count(array_filter($subscriptions));

$conn->close();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Your Newsletters - NewsletterHub</title>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <link href="../css/user_home.css" rel="stylesheet">
</head>
<body>
    <nav>
        <div class="container">
            <a href="admin-home.php" class="logo">
                <i class="fas fa-paper-plane"></i>
                NewsletterHub
            </a>
            <ul>
                <li><a href="blog.php">Blogs</a></li>
                <li><a href="assign.php">Edit Users</a></li>
                <li><a href="writings.php">Writings</a></li>
                <li><a href="viewfeedback.php">Feedbacks</a></li>
                <li><a href="../send_email.html">Send Newsletter</a></li>
                <li class="dropdown">
                    <button class="dropbtn" onclick="toggleDropdown()">
                        <i class="fas fa-user-circle"></i>
                        <?php echo htmlspecialchars($_SESSION['UserName']); ?>
                        <i class="fas fa-chevron-down"></i>
                    </button>
                    <div class="dropdown-content" id="userDropdown">
                        <a href="profile.php"><i class="fas fa-user"></i> Profile</a>
                        <a href="logout.php"><i class="fas fa-sign-out-alt"></i> Logout</a>
                    </div>
                </li>
            </ul>
        </div>
    </nav>

    <section class="welcome-banner">
        <div class="container">
            <h1>Welcome back, <?php echo htmlspecialchars($_SESSION['UserName']); ?>! 👋</h1>
            <p>Here are your latest newsletter updates</p>
        </div>
    </section>

    <div class="container">
        <div class="content-grid">
            <main class="main-content">
                <h2 style="margin-bottom: 20px;">Latest Updates</h2>
                
                <?php if (empty($newsletters)): ?>
                    <div class="newsletter-item">
                        <p>No newsletters found. Subscribe to some categories to see updates here!</p>
                    </div>
                <?php else: ?>
                    <?php foreach ($newsletters as $newsletter): ?>
                        <div class="newsletter-item">
                            <div class="newsletter-icon">
                                <i class="fas fa-newspaper"></i>
                            </div>
                            <div class="newsletter-content">
                                <div class="newsletter-title">
                                    <span><?php echo htmlspecialchars($newsletter['Title']); ?>
                                        <?php if (strtotime($newsletter['PublishDate']) > strtotime('-24 hours')): ?>
                                            <span class="tag">New</span>
                                        <?php endif; ?>
                                    </span>
                                    <button class="read-button" onclick="window.location.href='newsletter_post.php?id=<?php echo $newsletter['NewsletterNo']; ?>'">Read Now</button>
                                </div>
                                <p><?php echo htmlspecialchars(substr($newsletter['Content'], 0, 100)) . '...'; ?></p>
                                <div class="newsletter-meta">
                                    <?php 
                                    $date = new DateTime($newsletter['PublishDate']);
                                    $now = new DateTime();
                                    $interval = $date->diff($now);
                                    
                                    if ($interval->days == 0) {
                                        echo "Today";
                                    } elseif ($interval->days == 1) {
                                        echo "Yesterday";
                                    } else {
                                        echo $interval->days . " days ago";
                                    }
                                    ?> • 5 min read
                                </div>
                            </div>
                        </div>
                    <?php endforeach; ?>
                <?php endif; ?>
            </main>

            <aside class="sidebar">
                <div class="sidebar-card">
                    <h3 style="margin-bottom: 15px;">Your Subscriptions</h3>
                    <div style="color: #666;">
                        <?php if (array_filter($subscriptions)): ?>
                            <?php foreach ($subscriptions as $category => $isSubscribed): ?>
                                <?php if ($isSubscribed): ?>
                                    <p>
                                        <i class="fas fa-check-circle" style="color: #007AFF;"></i>
                                        <?php echo htmlspecialchars($category); ?>
                                    </p>
                                <?php endif; ?>
                            <?php endforeach; ?>
                            <p style="margin-top: 10px;">
                                <a href="usersubscribe.php" style="color: #007AFF; text-decoration: none;">
                                    <i class="fas fa-plus-circle"></i> Add More Subscriptions!
                                </a>
                            </p>
                        <?php else: ?>
                            <p>You are not subscribed to any categories.</p>
                            <p style="margin-top: 10px;">
                                <a href="usersubscribe.php" style="color: #007AFF; text-decoration: none;">
                                    <i class="fas fa-plus-circle"></i> Subscribe Now!
                                </a>
                            </p>
                        <?php endif; ?>
                    </div>
                </div>

                <div class="sidebar-card">
                    <h3 style="margin-bottom: 15px;">Reading Stats</h3>
                    <div style="color: #666;">
                        <p><i class="fas fa-book-reader"></i> <?php echo count($newsletters); ?> newsletters read this week</p>
                        <p><i class="fas fa-clock"></i> Avg. 5 min read time</p>
                        <p><i class="fas fa-star"></i> <?php echo $subCount; ?> active subscriptions</p>
                    </div>
                </div>
            </aside>
        </div>
    </div>

    <div class="sidebar-card">
        <h3 style="margin-bottom: 15px;">Writer Requests</h3>
        <div style="color: #666;">
            <p>
                <i class="fas fa-user-edit"></i> <?php echo $pendingCount; ?> pending requests
            </p>
            <p style="margin-top: 10px;">
                <a href="request.php" style="color: #007AFF; text-decoration: none;">
                    <i class="fas fa-arrow-right"></i> View Requests
                </a>
            </p>
        </div>
    </div>
    <script>
        function toggleDropdown() {
            const dropdown = document.querySelector('.dropdown');
            dropdown.classList.toggle('open');
        }

        document.addEventListener('click', function(event) {
            const dropdown = document.querySelector('.dropdown');
            const dropbtn = document.querySelector('.dropbtn');
            
            if (!dropdown.contains(event.target)) {
                dropdown.classList.remove('open');
            }
        });
    </script>
</body>
</html>
