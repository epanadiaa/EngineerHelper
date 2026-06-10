<?php

$conn = mysqli_connect(
    "localhost",
    "root",
    "",
    "engineerhelperdb"
);

if (!$conn) {
    die("Connection Failed: " . mysqli_connect_error());
}

?>