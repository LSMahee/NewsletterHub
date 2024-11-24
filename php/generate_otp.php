<?php
session_start();
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

require 'PHPMailer/src/Exception.php';
require 'PHPMailer/src/PHPMailer.php';
require 'PHPMailer/src/SMTP.php';



include 'db.php';

$otp = rand(100000, 999999);
$UserID = $_SESSION['UserID'];

$query = "UPDATE user SET otp=$otp where UserID='$UserID'";


if ($result = $conn->query($query)) {
    
    $UID = "SELECT * FROM user WHERE UserID='$UserID'";
    $fetch = $conn->query($UID);
    $row = $fetch->fetch_assoc();
    $generated_otp = $row['otp'];
    // Create a new PHPMailer instance
    $mail = new PHPMailer(true);

    try {
        // Server settings
        $mail->isSMTP();
        $mail->Host       = 'smtp.gmail.com';  // Set your SMTP server
        $mail->SMTPAuth   = true;
        $mail->Username   = 'mohammedbinahmed007@gmail.com'; // Your email address
        $mail->Password   = 'qkwvpzgogvmnryyf';   // Your email password (or use environment variables for better security)
        $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
        $mail->Port       = 587;

        // Set email format to HTML
        $mail->isHTML(true);
        $mail->setFrom('mohammedbinahmed007@gmail.com', 'OTP');

        $UserID = $_SESSION['UserID'];
        $to = $_SESSION['Email'];
        $mail->addAddress($to);  // Add the recipient

        // Content
        $mail->Subject = "Your OTP";
        $mail->Body    = nl2br(htmlspecialchars($generated_otp));  // Escape special characters

        // Send email
        if ($mail->send()) {
            echo "Email sent to: " . htmlspecialchars($to) . "<br>";          
        } else {
            echo "Failed to send email to: " . htmlspecialchars($to) . "<br>";
        }

        // Clear the recipient for the next iteration
        $mail->clearAddresses();
    } catch (Exception $e) {
        echo "Message could not be sent. Mailer Error: {$mail->ErrorInfo}";
    }
}

?>