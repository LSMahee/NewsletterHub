<?php
session_start();
include 'db.php';

$title = $_POST['subject'];
$content = $_POST['message'];
$user_email = $_SESSION['Email'];
$category = $_POST['category'];

$sql = "SELECT u.UserName, u.Email, u.Password, w.WriterID
        FROM user as u 
        INNER JOIN writer as w ON u.UserID = w.UserID
        WHERE u.Email = '$user_email'";

$result = $conn->query($sql);

if ($result->num_rows > 0) {
    $row = $result->fetch_assoc(); 
    $WriterID = $row['WriterID'];  

    $insert = "INSERT INTO writings (title, content, WriterID, CategoryName) 
               VALUES ('$title', '$content', $WriterID, '$category')";

    if ($conn->query($insert)) {
        echo "Writing added successfully!";
    } else {
        echo "Error: " . $conn->error;
    }
} else {
    echo "User not found!";
}

$conn->close();
?>
