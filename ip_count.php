<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

// Define the file path to store IP addresses and counts
$filename = 'ip_counts.txt';

// Get the visitor's IP address
$ip_address = $_SERVER['REMOTE_ADDR'];

// Read the current data from the file
$data = [];
if (file_exists($filename)) {
    $data = json_decode(file_get_contents($filename), true);
}

// Function to get location info from ipwhois.io
function get_location_info($ip) {
    $api_url = "https://ipwhois.app/json/{$ip}";
    $response = file_get_contents($api_url);
    return json_decode($response, true);
}

// Initialize the entry if it does not exist or is not an array
if (!isset($data[$ip_address]) || !is_array($data[$ip_address])) {
    $location_info = get_location_info($ip_address);
    $data[$ip_address] = [
        'count' => 0,
        'last_access' => '',
        'country' => $location_info['country'] ?? 'Unknown',
        'city' => $location_info['city'] ?? 'Unknown',
        'isp' => $location_info['isp'] ?? 'Unknown',
        'timezone_gmt' => $location_info['timezone_gmt'] ?? 'Unknown'
    ];
}

// Update the count and last access time
$data[$ip_address]['count']++;
$data[$ip_address]['last_access'] = date('Y-m-d H:i:s');

// Save the updated data back to the file
file_put_contents($filename, json_encode($data));

// Display the IP address, the number of visits, last access time, country, city, ISP, and timezone GMT for the current IP
// echo "IP Address: $ip_address<br>";
// echo "Number of visits: " . $data[$ip_address]['count'] . "<br>";
// echo "Last access time: " . $data[$ip_address]['last_access'] . "<br>";
// echo "Country: " . $data[$ip_address]['country'] . "<br>";
// echo "City: " . $data[$ip_address]['city'] . "<br>";
// echo "ISP: " . $data[$ip_address]['isp'] . "<br>";
// echo "Timezone GMT: " . $data[$ip_address]['timezone_gmt'] . "<br>";
?>
