<?php
include 'db.php';

$sql = "SELECT u.UserID, u.UserName, ur.RoleID, ur.RoleName 
        FROM user AS u 
        LEFT JOIN roleofuser AS rou ON u.UserID = rou.UserID
        LEFT JOIN userrole AS ur ON rou.RoleID = ur.RoleID
        WHERE u.UserName IS NOT NULL";
$result = $conn->query($sql);

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $userID = $_POST['userID'];
    $action = $_POST['action']; // Get the action from the button

    if ($action === 'update') {
        $roleID = $_POST['roleID'];

        $role_sql = "SELECT RoleName FROM userrole WHERE RoleID = $roleID";
        $role_result = $conn->query($role_sql);
        $role = $role_result->fetch_assoc()['RoleName'];

        $role_tables = ['Admin', 'Member', 'Writer'];
        foreach ($role_tables as $table) {
            $delete_sql = "DELETE FROM $table WHERE UserID = $userID";
            $conn->query($delete_sql);
        }

        $insert_sql = "INSERT INTO $role (UserID) SELECT UserID FROM user WHERE UserID = $userID";
        $conn->query($insert_sql);

        $update_sql = "UPDATE roleofuser SET RoleID = $roleID WHERE UserID = $userID";
        $conn->query($update_sql);
    } elseif ($action === 'delete') {
        $delete_user = "DELETE FROM user WHERE UserID = $userID";
        $conn->query($delete_user);
    }

    header("Location: " . $_SERVER['PHP_SELF']);
    exit;
}


$roles_sql = "SELECT RoleID, RoleName FROM userrole";
$roles_result = $conn->query($roles_sql);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Assign Roles</title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
            font-family: 'Arial', sans-serif;
        }
        body {
            background-color: #f3f4f6;
            color: #333;
            display: flex;
            justify-content: center;
            align-items: center;
            min-height: 100vh;
            padding: 20px;
        }
        .container {
            background: #ffffff;
            width: 100%;
            max-width: 900px;
            border-radius: 15px;
            padding: 25px;
            box-shadow: 0 4px 15px rgba(0, 0, 0, 0.1);
            animation: fadeIn 0.8s ease-in-out;
        }
        .header {
            font-size: 2rem;
            font-weight: 700;
            text-align: center;
            margin-bottom: 25px;
            color: #4a90e2;
            text-shadow: 0 0 10px rgba(74, 144, 226, 0.3);
        }
        table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 20px;
        }
        th, td {
            text-align: left;
            padding: 14px;
            border-bottom: 1px solid #eaeaea;
        }
        th {
            background-color: #f5f6f8;
            font-weight: 600;
            color: #555;
        }
        tr {
            transition: background-color 0.3s ease;
        }
        tr:hover {
            background-color: #f9f9f9;
        }
        td {
            vertical-align: middle;
        }
        form {
            display: flex;
            gap: 10px;
            align-items: center;
        }
        select {
            padding: 8px;
            border: 1px solid #ddd;
            border-radius: 6px;
            background-color: #ffffff;
            font-size: 14px;
            color: #333;
            transition: border-color 0.3s;
        }
        select:focus {
            outline: none;
            border-color: #4a90e2;
            box-shadow: 0 0 5px rgba(74, 144, 226, 0.3);
        }
        button {
            padding: 10px 16px;
            border: none;
            border-radius: 6px;
            background: linear-gradient(135deg, #4a90e2, #357abd);
            color: #fff;
            font-size: 14px;
            cursor: pointer;
            transition: transform 0.3s, background-color 0.3s;
        }
        button:hover {
            background: linear-gradient(135deg, #357abd, #4a90e2);
            transform: scale(1.05);
        }
        /* Animations */
        @keyframes fadeIn {
            from {
                opacity: 0;
                transform: translateY(20px);
            }
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }
        /* Mobile-Friendly Styling */
        @media screen and (max-width: 600px) {
            table {
                font-size: 0.9rem;
            }
            form {
                flex-direction: column;
                align-items: flex-start;
                gap: 5px;
            }
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">Edit Users</div>
        <table>
            <thead>
                <tr>
                    <th>User Name</th>
                    <th>Current Role</th>
                    <th>Change Role</th>
                </tr>
            </thead>
            <tbody>
                <?php while ($row = $result->fetch_assoc()): ?>
                    <tr>
                        <td><?= htmlspecialchars($row['UserName']) ?></td>
                        <td><?= htmlspecialchars($row['RoleName']) ?></td>
                        <td>
                            <form method="POST">
                                <input type="hidden" name="userID" value="<?= htmlspecialchars($row['UserID']) ?>">
                                <select name="roleID">
                                    <?php
                                    $roles_result->data_seek(0);
                                    while ($role = $roles_result->fetch_assoc()):
                                    ?>
                                        <option value="<?= htmlspecialchars($role['RoleID']) ?>" <?= $role['RoleID'] == $row['RoleID'] ? 'selected' : '' ?>>
                                            <?= htmlspecialchars($role['RoleName']) ?>
                                        </option>
                                    <?php endwhile; ?>
                                </select>
                                <button type="submit" name="action" value="update">Update Role</button>
                                <button type="submit" name="action" value="delete">Delete User</button>

                            </form>
                        </td>
                    </tr>
                <?php endwhile; ?>
            </tbody>
        </table>
    </div>
</body>
</html>
