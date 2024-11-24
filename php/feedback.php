<?php
include 'db.php';

$selected = $conn->query("SELECT * FROM admin");

$message = $_POST['message'];

if(isset($_POST['submit'])) {
    $send = "INSERT INTO feedback(Message) VALUES ('$message')";
    if($result = $conn->query($send)){
        $FeedbackID = $conn->insert_id;
        echo "We have successfully received your feedback";
        while($row = $selected->fetch_assoc()){
            $AdminID = $row['AdminID'];
            $update = "INSERT INTO receivesfeedback(FeedbackID, AdminID) VALUES ('$FeedbackID', '$AdminID')";
            $conn->query($update);
        }
    }
}
?>
