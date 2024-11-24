<?php
session_start();

include 'db.php';

$userInput = $_POST['text'];
$inputPassword = $_POST['password'];

$sql = "SELECT u.UserName,u.Email,u.UserID,u.Password,rou.RoleID,ur.RoleName
        FROM user as u 
        INNER JOIN roleofuser as rou ON u.UserID=rou.UserID
        INNER JOIN userrole as ur ON rou.RoleID=ur.RoleID
        WHERE u.UserName = '$userInput' OR u.Email = '$userInput'";

$result = $conn->query($sql);

if ($result->num_rows > 0) {
    $user = $result->fetch_assoc();

    if (password_verify($inputPassword, $user['Password'])) {
        $_SESSION['UserID'] = $user['UserID'];
        $_SESSION['UserName'] = $user['UserName'];
        $_SESSION['RoleName'] = $user['RoleName'];
        $_SESSION['Email'] = $user['Email'];

        include "generate_otp.php";
        header("Location: ../otp.html");
    } else {
        echo "Incorrect password.";
    }
} else {
    echo "User not found.";
}

$conn->close();
?>
