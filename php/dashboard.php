<?php
session_start();


include 'db.php';

if($_SESSION['RoleName']=='Admin'){
    header("Location: admin-home.php");
    exit;
}else if($_SESSION['RoleName']=='Member'){
    header("Location: user-home.php");
    exit;
}else if($_SESSION['RoleName']=='Writer'){
    header("Location: writer.php");
    exit;
}else{
    echo "Sorry User! Something went wrong.";
}

?>