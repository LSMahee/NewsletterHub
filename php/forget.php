<?php
session_start();
include 'db.php';

$Email = $_POST['email'];
$_SESSION['Email'] = $Email;
$sql = "SELECT * FROM user WHERE Email = '$Email'";
$result = $conn->query($sql);

if($result->num_rows > 0){
    header("Location: ../reset_password.html");
}
else{
    echo "You are not a member of our Newsletter";
    header("Location: ../forget.html");
}

?>