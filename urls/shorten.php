<?php
require 'config.php';

$response = [];

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $original_url = $_POST['url'];
    $short_code = isset($_POST['custom_code']) && !empty($_POST['custom_code']) ? $_POST['custom_code'] : generateShortCode();

    if (filter_var($original_url, FILTER_VALIDATE_URL) === false) {
        $response['status'] = 'error';
        $response['message'] = 'Invalid URL. Please enter a valid URL.';
    } elseif (checkIfShortCodeExists($short_code)) {
        $response['status'] = 'error';
        $response['message'] = 'Short code already exists. Please choose another one.';
    } else {
        $stmt = $conn->prepare("INSERT INTO urls (original_url, short_code) VALUES (?, ?)");
        $stmt->bind_param("ss", $original_url, $short_code);
        $stmt->execute();
        $stmt->close();

        $short_url = "http://polisimoral.site/urls/" . $short_code;
        $response['status'] = 'success';
        $response['short_url'] = $short_url;
    }

    echo json_encode($response);
    exit;
}

function generateShortCode($length = 6) {
    $characters = '0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ';
    $charactersLength = strlen($characters);
    $randomString = '';
    for ($i = 0; $i < $length; $i++) {
        $randomString .= $characters[rand(0, $charactersLength - 1)];
    }
    return $randomString;
}

function checkIfShortCodeExists($short_code) {
    global $conn;
    $stmt = $conn->prepare("SELECT COUNT(*) FROM urls WHERE short_code = ?");
    $stmt->bind_param("s", $short_code);
    $stmt->execute();
    $stmt->bind_result($count);
    $stmt->fetch();
    $stmt->close();
    return $count > 0;
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>URL Shortener</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.2.0/css/all.min.css">
    <style>
        body {
            font-family: Arial, sans-serif;
            background-color: #f8f9fa;
            display: flex;
            justify-content: center;
            align-items: center;
            height: 100vh;
        }

        .container {
            background: #fff;
            padding: 20px;
            border-radius: 8px;
            box-shadow: 0 0 10px rgba(0,0,0,0.1);
            width: 400px;
        }

        h1 {
            text-align: center;
            margin-bottom: 20px;
        }

        form {
            display: flex;
            flex-direction: column;
        }

        input[type="url"], input[type="text"] {
            padding: 10px;
            margin: 10px 0;
            border: 1px solid #ddd;
            border-radius: 4px;
        }

        button {
            padding: 10px;
            background-color: #007bff;
            color: #fff;
            border: none;
            border-radius: 4px;
            cursor: pointer;
        }

        button:hover {
            background-color: #0056b3;
        }

        .message {
            margin: 10px 0;
            padding: 10px;
            border-radius: 4px;
        }

        .success {
            background-color: #d4edda;
            border: 1px solid #c3e6cb;
            color: #155724;
        }

        .error {
            background-color: #f8d7da;
            border: 1px solid #f5c6cb;
            color: #721c24;
        }

        .short-url {
            display: flex;
            align-items: center;
        }

        .short-url input {
            flex: 1;
            margin-right: 10px;
        }
    </style>
</head>
<body>
    <div class="container">
        <h1>URL Shortener</h1>
        <form id="shorten-form">
            <input type="url" name="url" placeholder="Enter URL" required>
            <input type="text" name="custom_code" placeholder="Enter custom short code (optional)">
            <button type="submit">Shorten</button>
        </form>
        <div class="message" id="message" style="display: none;"></div>
        <div class="short-url" id="short-url" style="display: none;">
            <input type="text" id="short-url-input" readonly>
            <button onclick="copyToClipboard()">Copy</button>
        </div>
    </div>

    <script>
        document.getElementById('shorten-form').addEventListener('submit', function(event) {
            event.preventDefault();
            const formData = new FormData(this);

            fetch('shorten.php', {
                method: 'POST',
                body: formData
            })
            .then(response => response.json())
            .then(data => {
                const messageDiv = document.getElementById('message');
                const shortUrlDiv = document.getElementById('short-url');
                const shortUrlInput = document.getElementById('short-url-input');

                if (data.status === 'success') {
                    messageDiv.className = 'message success';
                    messageDiv.innerText = 'URL shortened successfully!';
                    shortUrlInput.value = data.short_url;
                    shortUrlDiv.style.display = 'flex';
                } else {
                    messageDiv.className = 'message error';
                    messageDiv.innerText = data.message;
                    shortUrlDiv.style.display = 'none';
                }
                messageDiv.style.display = 'block';
            })
            .catch(error => {
                console.error('Error:', error);
            });
        });

        function copyToClipboard() {
            const shortUrlInput = document.getElementById('short-url-input');
            shortUrlInput.select();
            shortUrlInput.setSelectionRange(0, 99999);
            document.execCommand('copy');
            alert('Copied the URL: ' + shortUrlInput.value);
        }
    </script>
</body>
</html>
