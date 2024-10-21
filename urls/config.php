<?php
$servername = "localhost";
$username = "polc8288_polmor";
$password = "3a&2JB}JORFe";
$dbname = "polc8288_url_shortener";

$conn = new mysqli($servername, $username, $password, $dbname);

if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}
?>
