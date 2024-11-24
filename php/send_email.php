<?php


use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

require 'PHPMailer/src/Exception.php';
require 'PHPMailer/src/PHPMailer.php';
require 'PHPMailer/src/SMTP.php';

include 'db.php';
// Check connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Check if form is submitted
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $subject = trim($_POST['subject']);
    $message = trim($_POST['message']);
    $selectedCategory = $_POST['category'];  // Get the selected category (business, sports, books, quotes)

    // Validate inputs
    if (!empty($subject) && !empty($message) && !empty($selectedCategory)) {
        // Create a dynamic SQL query based on the selected category
        $columnName = "";
        if ($selectedCategory == "Business") {
            $columnName = "Business";
        } elseif ($selectedCategory == "Sports") {
            $columnName = "Sports";
        } elseif ($selectedCategory == "Books") {
            $columnName = "Books";
        } elseif ($selectedCategory == "Quotes") {
            $columnName = "Quotes";
        }
        $category = "SELECT CategoryID FROM category WHERE CategoryName = '$selectedCategory'";
        $result = $conn->query($category);
        $CategoryID = $result->fetch_assoc()['CategoryID'];  // Access the CategoryID directly

        if (!empty($columnName)) {
            // Fetch subscriber emails based on the selected category
            $sql = "SELECT u.email,u.UserID FROM user as u INNER JOIN subscription as s ON u.UserID=s.UserID INNER JOIN category as c ON s.CategoryID=c.CategoryID WHERE c.CategoryName = '$columnName'";
            $result = $conn->query($sql);

            if ($result && $result->num_rows > 0) {
                $savetoblog = "INSERT INTO blog(Title,Content,CategoryName) VALUES ('$subject','$message','$selectedCategory')";
                $saveblog = $conn->query($savetoblog);
                $BlogID = $conn->insert_id;
                $savetonewsletter = "INSERT INTO newsletter(Title,Content,CategoryName) VALUES ('$subject','$message','$selectedCategory')";
                $savenewsletter = $conn->query($savetonewsletter);
                $NewsletterNo = $conn->insert_id;

                $publish = "INSERT INTO publishedas(NewsletterNo,CategoryID,BlogID) VALUES ('$NewsletterNo','$CategoryID','$BlogID')";
                $published = $conn->query($publish);

                // Create a new PHPMailer instance
                $mail = new PHPMailer(true);

                try {
                    // Server settings
                    $mail->isSMTP();
                    $mail->Host       = 'smtp.gmail.com';  // Set your SMTP server
                    $mail->SMTPAuth   = true;
                    $mail->Username   = 'mohammedbinahmed007@gmail.com'; // Your email address
                    $mail->Password   = 'qkwvpzgogvmnryyf';   // Your email password
                    $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
                    $mail->Port       = 587;

                    // Set email format to HTML
                    $mail->isHTML(true);
                    $mail->setFrom('mohammedbinahmed007@gmail.com', 'Newsletter');

                    // Loop through each email and send the newsletter
                    while ($row = $result->fetch_assoc()) {
                        $UserID = $row['UserID'];
                        $to = $row['email'];
                        $mail->addAddress($to);  // Add the recipient

                        // Content
                        $mail->Subject = $subject;
                        $mail->Body    = nl2br(htmlspecialchars($message));  // Escape special characters

                        // Send email
                        $mail->send();
                        echo "Email sent to: " . htmlspecialchars($to) . "<br>";

                        $savetoreceivers = "INSERT INTO newsletterreceivers(NewsletterNo,CategoryID,UserID) VALUES ('$NewsletterNo','$CategoryID','$UserID')";
                        $savereceivers = $conn->query($savetoreceivers);

                        // Clear recipient for the next iteration
                        $mail->clearAddresses();
                    }
                } catch (Exception $e) {
                    echo "Message could not be sent. Mailer Error: {$mail->ErrorInfo}";
                }
            } else {
                echo "No subscribers found for the selected category.";
            }
        } else {
            echo "Invalid category selected.";
        }
    } else {
        echo "Please provide a subject, message, and select a category.";
    }
}

// Close connection
$conn->close();
?>
