<?php
require 'config.php';

if (isset($_GET['code'])) {
    $short_code = $_GET['code'];

    $stmt = $conn->prepare("SELECT original_url FROM urls WHERE short_code = ?");
    $stmt->bind_param("s", $short_code);
    $stmt->execute();
    $stmt->bind_result($original_url);
    $stmt->fetch();
    $stmt->close();

    if ($original_url) {
        header("Location: " . $original_url);
        exit;
    } else {
        echo "URL not found.";
    }
} else {
    echo "No short code provided.";
}
?>
