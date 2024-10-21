<?php
// Start the session
session_start();
// Include the function file
include 'functions.php';
// Connect to MySQL
$pdo = pdo_connect_mysql();

// Get the poll id
$poll_id = isset($_GET['id']) ? (int)$_GET['id'] : null;

// Get the user's IP address
$user_ip = $_SERVER['REMOTE_ADDR'];

// Check if the user has already voted on this poll
$stmt = $pdo->prepare('SELECT * FROM poll_votes WHERE poll_id = ? AND ip_address = ?');
$stmt->execute([$poll_id, $user_ip]);
$has_voted = $stmt->fetch(PDO::FETCH_ASSOC);

// Get the poll and poll answers
$stmt = $pdo->prepare('SELECT * FROM polls WHERE id = ?');
$stmt->execute([$poll_id]);
$poll = $stmt->fetch(PDO::FETCH_ASSOC);

$stmt = $pdo->prepare('SELECT * FROM poll_answers WHERE poll_id = ?');
$stmt->execute([$poll_id]);
$poll_answers = $stmt->fetchAll(PDO::FETCH_ASSOC);

// If the poll does not exist, redirect back to the polls page
if (!$poll) {
    exit('Poll does not exist!');
}

// When the user votes
if (isset($_POST['poll_answer']) && !$has_voted) {
    // Add the user's vote
    $stmt = $pdo->prepare('UPDATE poll_answers SET votes = votes + 1 WHERE id = ?');
    $stmt->execute([$_POST['poll_answer']]);
    
    // Record the user's IP address and poll ID
    $stmt = $pdo->prepare('INSERT INTO poll_votes (poll_id, ip_address) VALUES (?, ?)');
    $stmt->execute([$poll_id, $user_ip]);
    
    // Redirect to the result page
    header('Location: result.php?id=' . $poll_id);
    exit;
}
?>

<?=template_header('Poll Vote')?>

<style>
    body {
        font-family: Arial, sans-serif;
        margin: 0;
        padding: 0;
        box-sizing: border-box;
        transform: scale(3);
        transform-origin: top left;
        width: 33.3333%;
        height: 33.3333%;
        overflow: hidden;
    }

    .content {
        padding: 20px;
        margin: 0 auto;
        max-width: 600px;
        box-sizing: border-box;
    }

    .poll-vote {
        text-align: center;
    }

    .poll-vote h2 {
        font-size: 24px;
    }

    .poll-vote .message {
        text-align: center;
    }

    .poll-vote form {
        display: flex;
        flex-direction: column;
        align-items: center;
        gap: 10px;
        margin-top: 20px;
    }

    .poll-vote label {
        display: flex;
        align-items: center;
        width: 100%;
        max-width: 300px;
        background: #f9f9f9;
        padding: 10px;
        border: 1px solid #ccc;
        border-radius: 5px;
        box-sizing: border-box;
    }

    .poll-vote input[type="radio"] {
        margin-right: 10px;
    }

    .poll-vote input[type="submit"] {
        padding: 10px 20px;
        border: none;
        border-radius: 5px;
        background-color: #007BFF;
        color: white;
        cursor: pointer;
        text-align: center;
    }

    .poll-vote input[type="submit"]:hover {
        background-color: #0056b3;
    }

    @media (max-width: 600px) {
        .content {
            padding: 10px;
        }

        .poll-vote h2 {
            font-size: 20px;
        }

        .poll-vote label {
            flex-direction: column;
            align-items: flex-start;
        }

        .poll-vote input[type="radio"] {
            margin-bottom: 5px;
        }

        .poll-vote input[type="submit"] {
            width: 100%;
        }
    }
</style>

<div class="content poll-vote">
    <h2><?=$poll['title']?></h2>
    <?php if ($has_voted): ?>
    <div class="message">
        <p>You have already voted in this poll. Thank you for your participation!</p>
        <a href="result.php?id=<?=$poll_id?>" class="button">View Results</a>
    </div>
    <?php else: ?>
    <form action="vote.php?id=<?=$poll_id?>" method="post">
        <?php foreach ($poll_answers as $poll_answer): ?>
        <label>
            <input type="radio" name="poll_answer" value="<?=$poll_answer['id']?>" required>
            <?=$poll_answer['title']?>
        </label>
        <?php endforeach; ?>
        <div>
            <input type="submit" value="Vote">
        </div>
    </form>
    <?php endif; ?>
</div>

<?=template_footer()?>
