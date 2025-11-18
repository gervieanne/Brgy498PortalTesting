<?php

$plain_password = "GROUP1SQA";  // Edit Here

$hashed_password = password_hash($plain_password, PASSWORD_DEFAULT);

echo "Plain password: " . $plain_password . "<br>";
echo "Hashed password: " . $hashed_password . "<br><br>";

echo "Copy the hashed password above and paste it into your database!";
?>