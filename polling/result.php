<?php
include 'functions.php';
// Connect to MySQL
$pdo = pdo_connect_mysql();
// If the GET request "id" exists (poll id)...
if (isset($_GET['id'])) {
    // MySQL query that selects the poll records by the GET request "id"
    $stmt = $pdo->prepare('SELECT * FROM polls WHERE id = ?');
    $stmt->execute([ $_GET['id'] ]);
    // Fetch the record
    $poll = $stmt->fetch(PDO::FETCH_ASSOC);
    // Check if the poll record exists with the id specified
    if ($poll) {
        // MySQL Query that will get all the answers from the "poll_answers" table ordered by the number of votes (descending)
        $stmt = $pdo->prepare('SELECT * FROM poll_answers WHERE poll_id = ? ORDER BY votes DESC');
        $stmt->execute([ $_GET['id'] ]);
        // Fetch all poll answers
        $poll_answers = $stmt->fetchAll(PDO::FETCH_ASSOC);
        // Total number of votes, will be used to calculate the percentage
        $total_votes = 0;
        foreach($poll_answers as $poll_answer) {
            // Every poll answers votes will be added to total votes
            $total_votes += $poll_answer['votes'];
        }
    } else {
        exit('Poll with that ID does not exist.');
    }
} else {
    exit('No poll ID specified.');
}
?>

<?=template_header('Poll Results')?>

<style>
    body {
        transform: scale(2);
        transform-origin: top left;
        width: 50%;
        height: 50%;
        overflow: hidden;
    }
    .content {
        padding: 20px;
        margin: 0 auto;
        max-width: 600px;
        box-sizing: border-box;
    }

    .poll-result h2 {
        text-align: center;
        font-size: 24px;
    }

    .poll-result p {
        text-align: center;
        font-size: 18px;
    }

    .wrapper {
        display: flex;
        flex-direction: column;
        gap: 10px;
    }

    .poll-question {
        display: flex;
        flex-direction: column;
        gap: 5px;
    }

    .poll-question p {
        margin: 0;
    }

    .result-bar {
        background-color: #007BFF;
        height: 30px;
        line-height: 30px;
        color: white;
        text-align: center;
        border-radius: 5px;
    }

    @media (max-width: 600px) {
        .content {
            padding: 10px;
        }

        .poll-result h2 {
            font-size: 20px;
        }

        .poll-result p {
            font-size: 16px;
        }

        .poll-question {
            gap: 8px;
        }

        .result-bar {
            height: 25px;
            line-height: 25px;
        }
    }
</style>

<div class="content poll-result">
    <h2><?=$poll['title']?></h2>
    <p><?=$poll['description']?></p>
    <div class="wrapper">
        <?php foreach ($poll_answers as $poll_answer): ?>
        <div class="poll-question">
            <p><?=$poll_answer['title']?> <span>(<?=$poll_answer['votes']?> Votes)</span></p>
            <div class="result-bar" style="width:<?=@round(($poll_answer['votes']/$total_votes)*100)?>%">
                <?=@round(($poll_answer['votes']/$total_votes)*100)?>%
            </div>
        </div>
        <?php endforeach; ?>
    </div>
</div>

<?=template_footer()?>
