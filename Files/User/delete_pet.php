<?php
include("connection.php");
session_start();

if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit;
}

$user_id = $_SESSION['user_id'];

if (isset($_GET['id'])) {
    $pet_id = $_GET['id'];

    $check = mysqli_query($conn, "SELECT * FROM pets WHERE pet_id = '$pet_id' AND added_by = '$user_id'");
    if (mysqli_num_rows($check) > 0) {
       
        $delete = mysqli_query($conn, "DELETE FROM pets WHERE pet_id = '$pet_id' AND added_by = '$user_id'");
    }
}

header("Location:pets.php");
exit;
?>
