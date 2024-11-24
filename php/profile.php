<?php
session_start();
require_once 'db.php';

if (!isset($_SESSION['UserID'])) {
    header('Location: login.php');
    exit();
}

$userID = $_SESSION['UserID'];

$userQuery = "
    SELECT u.UserName, u.FName, u.LName, u.Email, u.PhoneNumber, 
           GROUP_CONCAT(DISTINCT ur.RoleName) as Roles
    FROM User u
    LEFT JOIN RoleOfUser rou ON u.UserID = rou.UserID
    LEFT JOIN UserRole ur ON rou.RoleID = ur.RoleID
    WHERE u.UserID = $userID
    GROUP BY u.UserID
";
$userResult = $conn->query($userQuery);
$user = $userResult->fetch_assoc();

$subscriptionQuery = "
    SELECT c.CategoryName
    FROM Subscription s
    JOIN Category c ON s.CategoryID = c.CategoryID
    WHERE s.UserID = $userID
";
$subscriptionsResult = $conn->query($subscriptionQuery);
$subscriptions = $subscriptionsResult->fetch_all(MYSQLI_ASSOC);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>User Profile</title>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css" rel="stylesheet">
    <link href="../css/profile.css" rel="stylesheet">
</head>
<body>
    <nav>
        <div class="container">
            <a href="#" class="logo">
                Profile
            </a>
            <ul>
                <li><a href="dashboard.php">Dashboard</a></li>                
                <li><a href="logout.php">Logout</a></li>
            </ul>
        </div>
    </nav>

    <div class="container">
        <div class="welcome-banner">
            <div class="container">
                <h1>Hello, <?php echo htmlspecialchars($user['FName'] . ' ' . $user['LName']); ?></h1>
                <p>Welcome to your profile dashboard</p>
            </div>
        </div>

        <div class="content-grid">
            <div class="main-content">
                <h2>Personal Information</h2>
                <div class="profile-details">
                    <p><strong>Username:</strong> <?php echo htmlspecialchars($user['UserName']); ?></p>
                    <p><strong>Full Name:</strong> <?php echo htmlspecialchars($user['FName'] . ' ' . $user['LName']); ?></p>
                    <p><strong>Email:</strong> <?php echo htmlspecialchars($user['Email']); ?></p>
                    <p><strong>Phone:</strong> <?php echo htmlspecialchars($user['PhoneNumber'] ?? 'Not provided'); ?></p>
                    <p><strong>Role:</strong> <?php echo htmlspecialchars($user['Roles'] ?? 'No roles assigned'); ?></p>
                </div>
                <button class="read-button" onclick="location.href='edit.php'">Edit Profile</button>
            </div>

            <div class="sidebar">
                <div class="sidebar-card">
                    <h3>Subscribed Categories</h3>
                    <?php if (!empty($subscriptions)): ?>
                        <?php foreach ($subscriptions as $subscription): ?>
                            <span class="tag"><?php echo htmlspecialchars($subscription['CategoryName']); ?></span>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <p>No category subscriptions</p>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>
</body>
</html>
