<?php
function pdo_connect_mysql() {
    // Update the details below with your MySQL details
    $DATABASE_HOST = 'localhost';
    $DATABASE_USER = 'polc8288_polmor';
    $DATABASE_PASS = '3a&2JB}JORFe';
    $DATABASE_NAME = 'polc8288_phppoll';
    try {
    	return new PDO('mysql:host=' . $DATABASE_HOST . ';dbname=' . $DATABASE_NAME . ';charset=utf8', $DATABASE_USER, $DATABASE_PASS);
    } catch (PDOException $exception) {
    	// If there is an error with the connection, stop the script and display the error.
    	exit('Failed to connect to database!');
    }
}

function template_header($title) {
    // DO NOT INDENT THE BELOW PHP CODE OR YOU WILL ENCOUNTER ISSUES
    echo <<<EOT
    <!DOCTYPE html>
    <html>
        <head>
            <meta charset="utf-8">
            <title>$title</title>
            <link href="style.css" rel="stylesheet" type="text/css">
            <link rel="stylesheet" href="https://use.fontawesome.com/releases/v5.7.1/css/all.css">
        </head>
        <body>
        <nav class="navtop">
            <div>
                <h1>Voting & Poll System</h1>
                <a href="index.php"><i class="fas fa-poll-h"></i>Polls</a>
            </div>
        </nav>
    EOT;
    }

    function template_footer() {
        // DO NOT INDENT THE PHP CODE
        echo <<<EOT
            </body>
        </html>
        EOT;
        }

        