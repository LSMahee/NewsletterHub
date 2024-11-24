<?php

include 'db.php';

$firstname = $_POST['first_name'];
$lastname = $_POST['last_name'];
$username = $_POST['username'];
$password = $_POST['password'];
$email = $_POST['email'];
$phone = $_POST['phone'];

$sql_check = "SELECT * FROM user WHERE UserName = '$username' OR Email = '$email'";
$result = $conn->query($sql_check);

if ($result->num_rows > 0) {
    $checkupdate = "SELECT * FROM user WHERE UserName IS NULL";
    $update = $conn->query($checkupdate);
    if($update->num_rows>0){
        $row = $update->fetch_assoc();
        $UserID = $row['UserID'];
        $hashed_password = password_hash($password, PASSWORD_DEFAULT);
        $add = "UPDATE user SET UserName='$username', Password='$hashed_password', FName='$firstname', LName='$lastname', PhoneNumber='$phone' WHERE Email='$email'";
        $added = $conn->query($add);
        $roleofuser = "INSERT INTO roleofuser (UserID, RoleID) VALUES ('$UserID', 2)";
        $member = "INSERT INTO member (UserID) VALUES ('$UserID')";

        if ($conn->query($roleofuser) === TRUE) {
            $conn->query($member);
            echo "New record created successfully with role assigned.";

        } else {
            echo "Error: " . $member . "<br>" . $conn->error;
        }
    }else{
        echo "You Are Already A Newsletter Enjoyer!!";
    }
} else {
    $hashed_password = password_hash($password, PASSWORD_DEFAULT);

    $sql = "INSERT INTO user (UserName, Password, FName, LName, Email, PhoneNumber) VALUES ('$username', '$hashed_password', '$firstname', '$lastname', '$email', '$phone')";
    

    if ($conn->query($sql) === TRUE) {
        $user_id = $conn->insert_id;

        $roleofuser = "INSERT INTO roleofuser (UserID, RoleID) VALUES ('$user_id', 2)";
        $member = "INSERT INTO member (UserID) VALUES ('$user_id')";

        if ($conn->query($roleofuser) === TRUE) {
            $conn->query($member);
            echo "New record created successfully with role assigned.";

        } else {
            echo "Error: " . $member . "<br>" . $conn->error;
        }
    } else {
        echo "Error: " . $sql . "<br>" . $conn->error;
    }
}

$conn->close();
?>
