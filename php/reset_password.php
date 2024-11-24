<?php
session_start();

include 'db.php';
$Password = $_POST['newPassword'];
$ConfirmPassword = $_POST['confirmPassword'];
$Email = $_SESSION['Email'];
if(isset($Password) && isset($ConfirmPassword))
{
    $hashed_password = password_hash($Password, PASSWORD_DEFAULT);
    if($Password === $ConfirmPassword){

        $update = "UPDATE user SET Password='$hashed_password' WHERE Email='$Email'";
        $updated = $conn->query($update);
        if($updated){
            echo "Your Password Has Been Updated Successfully";
            session_destroy();
            header("Location: ../login.html");
        }
        else{
            echo "Sorry User, Something Went Wrong";
            header("Location: ../reset_password.html");
        }
    }
    else{
        echo "Sorry, Both Password Doesnt Match";
    }
}


?>