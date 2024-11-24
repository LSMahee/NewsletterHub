<?php
include 'db.php';

// Fetch pending applications with user details
$sql = "SELECT a.ApplicationID, a.UserID, a.approval_status, u.UserName, u.Email, u.FName, u.LName 
        FROM application AS a 
        JOIN user AS u ON a.UserID = u.UserID
        WHERE a.approval_status = 'Pending'";
$result = $conn->query($sql);

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $userID = $_POST['userID'];
    $action = $_POST['action'];
    
    if ($action === 'Approved') {
        $role_sql = "SELECT RoleID FROM userrole WHERE RoleName = 'Writer'";
        $role_result = $conn->query($role_sql);
        $writerRoleID = $role_result->fetch_assoc()['RoleID'];

        $role_tables = ['Admin', 'Member', 'Writer'];
        foreach ($role_tables as $table) {
            $delete_sql = "DELETE FROM $table WHERE UserID = $userID";
            $conn->query($delete_sql);
        }


        $insert_sql = "INSERT INTO Writer (UserID) VALUES ($userID)";
        $conn->query($insert_sql);


        $update_role_sql = "UPDATE roleofuser SET RoleID = 3 WHERE UserID = $userID";
        $conn->query($update_role_sql);
    }


    $update_sql = "UPDATE application SET approval_status = '$action' WHERE UserID = $userID";
    $conn->query($update_sql);

    header("Location: " . $_SERVER['PHP_SELF']);
    exit;
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Writer Applications</title>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <link href="../css/request.css" rel="stylesheet">
</head>
<body>
    <div class="container">
        <div class="header">Writer Applications</div>
        
        <?php if ($result->num_rows > 0): ?>
            <?php while ($row = $result->fetch_assoc()): ?>
                <div class="application-card">
                    <div class="user-info">
                        <h3><?= htmlspecialchars($row['FName'] . ' ' . $row['LName']) ?></h3>
                        <p><i class="fas fa-user"></i> <?= htmlspecialchars($row['UserName']) ?></p>
                        <p><i class="fas fa-envelope"></i> <?= htmlspecialchars($row['Email']) ?></p>
                    </div>
                    <div class="actions">
                        <form method="POST" style="display: inline;">
                            <input type="hidden" name="userID" value="<?= htmlspecialchars($row['UserID']) ?>">
                            <button type="submit" name="action" value="Approved" class="btn approve-btn">
                                <i class="fas fa-check"></i> Approve
                            </button>
                        </form>
                        <form method="POST" style="display: inline;">
                            <input type="hidden" name="userID" value="<?= htmlspecialchars($row['UserID']) ?>">
                            <button type="submit" name="action" value="Denied" class="btn deny-btn">
                                <i class="fas fa-times"></i> Deny
                            </button>
                        </form>
                    </div>
                </div>
            <?php endwhile; ?>
        <?php else: ?>
            <div class="no-requests">
                <i class="fas fa-inbox" style="font-size: 48px; margin-bottom: 20px;"></i>
                <p>No pending writer applications at the moment.</p>
            </div>
        <?php endif; ?>
    </div>
</body>
</html>
