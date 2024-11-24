<?php
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

require 'PHPMailer/src/Exception.php';
require 'PHPMailer/src/PHPMailer.php';
require 'PHPMailer/src/SMTP.php';

include 'db.php'; // Your database connection file

// Check connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Assuming that the writing's category, title, and content are passed from writings.php
if (isset($writing['CategoryName'], $writing['title'], $writing['content'])) {
    $category = $writing['CategoryName'];
    $subject = $writing['title'];
    $message = $writing['content'];

    // Query to fetch subscribers' emails based on the category
    $sql_subscribers = "SELECT u.email,u.UserID 
                        FROM user AS u 
                        INNER JOIN subscription AS s ON u.UserID = s.UserID 
                        INNER JOIN category AS c ON s.CategoryID = c.CategoryID 
                        WHERE c.CategoryName = ?";

    $stmt = $conn->prepare($sql_subscribers);
    $stmt->bind_param('s', $category);
    $stmt->execute();
    $result_subscribers = $stmt->get_result();

    if ($result_subscribers && $result_subscribers->num_rows > 0) {

        $savetoblog = $conn->prepare("INSERT INTO blog (Title, Content, CategoryName) VALUES (?, ?, ?)");
        $savetoblog->bind_param("sss", $subject, $message, $category);
        $savetoblog->execute();

        // Get the BlogID
        $BlogID = $conn->insert_id;

        $savetonewsletter = $conn->prepare("INSERT INTO newsletter (Title, Content, CategoryName) VALUES (?, ?, ?)");
        $savetonewsletter->bind_param("sss", $subject, $message, $category);
        $savetonewsletter->execute();

        // Get the NewsletterNo
        $NewsletterNo = $conn->insert_id;

        $publish = $conn->prepare("INSERT INTO publishedas (NewsletterNo, CategoryID, BlogID) VALUES (?, ?, ?)");
        $publish->bind_param("iii", $NewsletterNo, $CategoryID, $BlogID);
        $publish->execute();

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
            $mail->setFrom('mohammedbinahmed007@gmail.com', 'Newsletter');

            // Loop through each subscriber and send the email
            while ($subscriber = $result_subscribers->fetch_assoc()) {
                $UserID = $subscriber['UserID'];
                $to = $subscriber['email'];
                $mail->addAddress($to);  // Add the recipient

                // Content
                $mail->Subject = $subject;
                $mail->Body    = nl2br(htmlspecialchars($message));  // Escape special characters

                // Send email
                if ($mail->send()) {
                    echo "Email sent to: " . htmlspecialchars($to) . "<br>";

                    $savetoreceivers = "INSERT INTO newsletterreceivers(NewsletterNo,CategoryID,UserID) VALUES ('$NewsletterNo','$CategoryID','$UserID')";
                    $savereceivers = $conn->query($savetoreceivers);
                } else {
                    echo "Failed to send email to: " . htmlspecialchars($to) . "<br>";
                }

                // Clear the recipient for the next iteration
                $mail->clearAddresses();
            }
        } catch (Exception $e) {
            echo "Message could not be sent. Mailer Error: {$mail->ErrorInfo}";
        }
    } else {
        echo "No subscribers found for the category '$category'.";
    }
} else {
    echo "Missing required fields: category, title, or content.";
}
?>
