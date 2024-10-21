<?php
// Read the raw POST data
$json = file_get_contents('php://input');

// Decode the JSON data
$data = json_decode($json, true);

// Create a JSON string to save
$locationData = json_encode($data);

// Save the JSON string to a file named detect.txt
$file = 'detect.txt';
file_put_contents($file, $locationData);

// Respond back to the client
echo 'Location saved successfully';
?>
