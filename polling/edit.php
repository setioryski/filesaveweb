<?php
// Start the session
session_start();
// Include the function file
include 'functions.php';
// Connect to MySQL
$pdo = pdo_connect_mysql();

// Get the poll id
$poll_id = isset($_GET['id']) ? (int)$_GET['id'] : null;

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

// When the user submits the edit form
if (isset($_POST['title'])) {
    // Update the poll question
    $stmt = $pdo->prepare('UPDATE polls SET title = ?, description = ? WHERE id = ?');
    $stmt->execute([$_POST['title'], $_POST['description'], $poll_id]);

    // Update the poll answers
    if (isset($_POST['answers'])) {
        foreach ($_POST['answers'] as $id => $answer) {
            if (!empty($answer)) {
                $stmt = $pdo->prepare('UPDATE poll_answers SET title = ? WHERE id = ?');
                $stmt->execute([$answer, $id]);
            }
        }
    }

    // Delete marked answers
    if (isset($_POST['delete_answers'])) {
        foreach ($_POST['delete_answers'] as $id) {
            $stmt = $pdo->prepare('DELETE FROM poll_answers WHERE id = ?');
            $stmt->execute([$id]);
        }
    }

    // Add new answers
    if (isset($_POST['new_answers'])) {
        foreach ($_POST['new_answers'] as $new_answer) {
            if (!empty($new_answer)) {
                $stmt = $pdo->prepare('INSERT INTO poll_answers (poll_id, title) VALUES (?, ?)');
                $stmt->execute([$poll_id, $new_answer]);
            }
        }
    }

    // Redirect to the index page
    header('Location: index.php');
    exit;
}
?>

<?=template_header('Edit Poll')?>

<div class="content update">
    <h2>Edit Poll</h2>
    <form action="edit.php?id=<?=$poll_id?>" method="post">
        <label for="title">Title</label>
        <input type="text" name="title" id="title" value="<?=$poll['title']?>" required>
        <label for="description">Description</label>
        <textarea name="description" id="description" required><?=$poll['description']?></textarea>
        <label for="answers">Answers</label>
        <?php foreach ($poll_answers as $poll_answer): ?>
        <div>
            <input type="text" name="answers[<?=$poll_answer['id']?>]" value="<?=$poll_answer['title']?>" required>
            <button type="button" class="remove-answer" data-id="<?=$poll_answer['id']?>">Delete</button>
            <input type="hidden" name="delete_answers[]" value="" id="delete-<?=$poll_answer['id']?>">
        </div>
        <?php endforeach; ?>
        <div id="new-answers"></div>
        <button type="button" id="add-answer">Add Answer</button>
        <input type="submit" value="Update">
    </form>
</div>

<script>
document.getElementById('add-answer').addEventListener('click', function() {
    var newAnswersDiv = document.getElementById('new-answers');
    var newAnswerDiv = document.createElement('div');
    var newAnswerInput = document.createElement('input');
    newAnswerInput.type = 'text';
    newAnswerInput.name = 'new_answers[]';
    newAnswerInput.placeholder = 'New Answer';
    newAnswerInput.required = true;
    newAnswerDiv.appendChild(newAnswerInput);
    newAnswersDiv.appendChild(newAnswerDiv);
});

document.querySelectorAll('.remove-answer').forEach(function(button) {
    button.addEventListener('click', function() {
        var answerId = this.dataset.id;
        var inputField = document.querySelector('input[name="answers[' + answerId + ']"]');
        var deleteField = document.getElementById('delete-' + answerId);
        inputField.removeAttribute('required'); // Remove the required attribute
        inputField.value = ''; // Set the value to empty to trigger deletion
        deleteField.value = answerId; // Mark the answer for deletion
        inputField.closest('div').style.display = 'none'; // Hide the input field
    });
});
</script>

<?=template_footer()?>
