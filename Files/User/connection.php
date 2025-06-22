<?php
     $servername = "localhost";
     $username = "root";
     $password = "";
     $db_name = "Pet_Store";
     $conn=mysqli_connect($servername,$username,$password,$db_name);
     if($conn)
     {
      //   echo "Connected Successfully\n";
     }
     else{
        echo"Connect Failed\n";
     }
?>