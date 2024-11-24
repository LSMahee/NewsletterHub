<?php
session_start();

include 'db.php';

$UserID = $_SESSION['UserID'];

$sql = "INSERT INTO application(UserID) VALUES ('$UserID')";
if($result = $conn->query($sql)){
    echo "Applied Successfully!";
}
else{
    echo "error";
}
?>