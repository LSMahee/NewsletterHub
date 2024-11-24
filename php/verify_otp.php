<?php
session_start();
include 'db.php';

$enteredOtp = $_POST['otp'];
$UserID = $_SESSION['UserID'];

$sql = "SELECT otp FROM user WHERE UserID = '$UserID'";
$result = $conn->query($sql);

if ($result->num_rows > 0) {
    $row = $result->fetch_assoc();
    $storedOtp = $row['otp'];
    if ($enteredOtp === $storedOtp) {
        echo "OTP verified successfully!";
        header("Location: dashboard.php");
        exit;
    } else {
        echo "Invalid or expired OTP.";
    }
} else {
    echo "Error: User not found.";
}

$conn->close();
?>