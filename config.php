<?php
    $host = "localhost";
    $dbUser = "root";
    $dbPass = "";
    $dbName = "ankit";
    $conn = mysqli_connect ($host , $dbUser , $dbPass , $dbName);
    if (!$conn) {
        echo "Error: Unable to connect to MySQL. " . mysqli_connect_error();
        exit;
    }
