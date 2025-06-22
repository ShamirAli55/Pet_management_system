<?php
include("../User/connection.php");

if (isset($_GET['delete'])) {
    $delete_id = intval($_GET['delete']);
    $delete_query = "UPDATE products SET stock = 0 WHERE product_id = $delete_id";
    
    if (mysqli_query($conn, $delete_query)) {
        echo "<script>alert('✅ Product deleted successfully.'); window.location.href='view_products.php';</script>";
    } else {
        echo "<script>alert('❌ Failed to delete product: " . mysqli_error($conn) . "'); window.location.href='view_products.php';</script>";
    }
} else {
    header("Location: view_products.php");
    exit();
}
