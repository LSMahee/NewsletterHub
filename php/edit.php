<?php
session_start();
include 'db.php';

if (!isset($_SESSION['UserID'])) {
    header('Location: login.php');
    exit();
}

$userID = $_SESSION['UserID'];

if (isset($_POST['cancel'])) {
    header('Location: profile.php');
    exit();
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['save'])) {
    $username = $_POST['username'];
    $fname = $_POST['fname'];
    $lname = $_POST['lname'];
    $email = $_POST['email'];
    $phone = $_POST['phone'];

    $conn->query("UPDATE User 
                  SET UserName = '$username', FName = '$fname', LName = '$lname', Email = '$email', PhoneNumber = '$phone' 
                  WHERE UserID = $userID");

    header('Location: profile.php');
    exit();
}

$user = $conn->query("
    SELECT u.UserName, u.FName, u.LName, u.Email, u.PhoneNumber, 
           GROUP_CONCAT(DISTINCT ur.RoleName) as Roles
    FROM User u
    LEFT JOIN RoleOfUser rou ON u.UserID = rou.UserID
    LEFT JOIN UserRole ur ON rou.RoleID = ur.RoleID
    WHERE u.UserID = $userID
    GROUP BY u.UserID
")->fetch_assoc();

$subscriptions = $conn->query("
    SELECT c.CategoryName
    FROM Subscription s
    JOIN Category c ON s.CategoryID = c.CategoryID
    WHERE s.UserID = $userID
")->fetch_all(MYSQLI_ASSOC);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>User Profile</title>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css" rel="stylesheet">
    <link href="../css/edit.css" rel="stylesheet">
</head>
<body>
    <nav>
        <div class="container">
            <a href="#" class="logo">Profile</a>
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
                <form method="POST">
                    <div class="profile-details">
                        <p>
                            <strong>Username:</strong>
                            <input type="text" name="username" value="<?php echo htmlspecialchars($user['UserName']); ?>" required>
                        </p>
                        <p>
                            <strong>First Name:</strong>
                            <input type="text" name="fname" value="<?php echo htmlspecialchars($user['FName']); ?>" required>
                        </p>
                        <p>
                            <strong>Last Name:</strong>
                            <input type="text" name="lname" value="<?php echo htmlspecialchars($user['LName']); ?>" required>
                        </p>
                        <p>
                            <strong>Email:</strong>
                            <input type="email" name="email" value="<?php echo htmlspecialchars($user['Email']); ?>" required>
                        </p>
                        <p>
                            <strong>Phone:</strong>
                            <input type="text" name="phone" value="<?php echo htmlspecialchars($user['PhoneNumber'] ?? ''); ?>">
                        </p>
                        <p>
                            <strong>Roles:</strong>
                            <?php echo htmlspecialchars($user['Roles'] ?? 'No roles assigned'); ?>
                        </p>
                    </div>
                    <div class="button-group">
                        <button class="read-button" name="save" type="submit">Save Changes</button>
                        <button class="read-button" name="cancel" type="submit">Cancel</button>
                    </div>
                </form>
            </div>

            <div class="sidebar">
                <div class="sidebar-card">
                    <h3>Subscribed Categories</h3>
                    <?php if (!empty($subscriptions)): ?>
                        <?php foreach ($subscriptions as $subscription): ?>
                            <span class="tag">
                                <?php echo htmlspecialchars($subscription['CategoryName']); ?>
                            </span>
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
