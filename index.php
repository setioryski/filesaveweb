<?php
// Start output buffering
ob_start();

// Error reporting (for debugging)
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

// The initial directory path
$initial_directory = 'uploads/';

// Determine the current directory path
$current_directory = isset($_GET['dir']) ? rtrim($_GET['dir'], '/') . '/' : $initial_directory;

// Include authenticate.php after setting $current_directory
include 'authenticate.php';

// Initialize variables for media files
$audio_file = '';
$video_file = '';

if (isset($_GET['file']) && !isset($_GET['download'])) {
    // Handle file viewing for files only
    if (!is_dir($_GET['file'])) {
        // Check if the file is an audio or video file
        if (preg_match('/audio\/*/', mime_content_type($_GET['file']))) {
            // Play audio file
            $audio_file = $_GET['file'];
        } elseif (preg_match('/video\/*/', mime_content_type($_GET['file']))) {
            // Play video file
            $video_file = $_GET['file'];
        }
    }
}

if (isset($_GET['file']) && isset($_GET['download']) && !is_dir($_GET['file'])) {
    // Download file
    header('Content-Description: File Transfer');
    header('Content-Type: application/octet-stream');
    header('Content-Disposition: attachment; filename="' . basename($_GET['file']) . '"');
    readfile($_GET['file']);
    exit;
}

// Retrieve all files and directories
$results = glob(str_replace(['[', ']', "\f[", "\f]"], ["\f[", "\f]", '[[]', '[]]'], $current_directory . '*'));
// If true, directories will appear first in the populated file list
$directory_first = true;
// Sort files
if ($directory_first) {
    usort($results, function ($a, $b) {
        $a_is_dir = is_dir($a);
        $b_is_dir = is_dir($b);
        if ($a_is_dir === $b_is_dir) {
            return strnatcasecmp($a, $b);
        } else if ($a_is_dir && !$b_is_dir) {
            return -1;
        } else if (!$a_is_dir && $b_is_dir) {
            return 1;
        }
    });
}

function convert_filesize($bytes, $precision = 2)
{
    $units = ['Bytes', 'KB', 'MB', 'GB', 'TB'];
    $bytes = max($bytes, 0);
    $pow = floor(($bytes ? log($bytes) : 0) / log(1024));
    $pow = min($pow, count($units) - 1);
    $bytes /= pow(1024, $pow);
    return round($bytes, $precision) . ' ' . $units[$pow];
}

// Determine the file type icon
function get_filetype_icon($filetype)
{
    if (is_dir($filetype)) {
        return '<i class="fa-solid fa-folder"></i>';
    } else if (preg_match('/image\/*/', mime_content_type($filetype))) {
        return '<i class="fa-solid fa-file-image"></i>';
    } else if (preg_match('/video\/*/', mime_content_type($filetype))) {
        return '<i class="fa-solid fa-file-video"></i>';
    } else if (preg_match('/audio\/*/', mime_content_type($filetype))) {
        return '<i class="fa-solid fa-file-audio"></i>';
    }
    return '<i class="fa-solid fa-file"></i>';
}
?>

<html>

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width,minimum-scale=1">
    <title>FILE SHARING</title>
    <link href="style.css" rel="stylesheet" type="text/css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.2.0/css/all.min.css" integrity="sha512-xh6O/CkQoPOWDdYTDqeRdPCVd1SpvCA9XXcUnZS2FmJNp1coAFzvtCN9BmamE+4aHK8yyUHUSCcJHgXloTyT2A==" crossorigin="anonymous" referrerpolicy="no-referrer">
    <style>
        .video-player {
            position: fixed;
            top: 50%;
            left: 50%;
            transform: translate(-50%, -50%);
            background-color: #000;
            padding: 10px;
            border-radius: 10px;
            z-index: 1000;
            max-width: 80%;
            max-height: 80%;
            display: none;
        }

        .video-player .close-btn {
            position: absolute;
            top: -10px;
            right: -10px;
            background: #ff0000;
            color: #fff;
            border: none;
            border-radius: 50%;
            width: 30px;
            height: 30px;
            cursor: pointer;
            font-size: 16px;
            line-height: 30px;
            text-align: center;
        }
    </style>
</head>

<body>
    <div class="file-manager">

        <div class="file-manager-header">
            <div style="position: relative; display: inline-block;">
                <form method="post"
                    action="<?= isset($_POST['password']) && $_POST['password'] === '12345' ? 'upload.php' : '#' ?>"
                    style="display: inline-block;">
                    <input type="hidden" name="directory" value="<?= $current_directory ?>">
                    <input type="password" name="password" placeholder="Enter password"
                        value="<?= isset($_POST['password']) ? htmlspecialchars($_POST['password'], ENT_QUOTES) : '' ?>">
                    <button type="submit" name="submit_password"><i class="fa-solid fa-plus"></i></button>
                </form>
                <button class="emoji-btn" onclick="window.location.href='liatwajah.php';"
                    style="position: absolute; top: 0; right: -40px;"><span>🚨</span></button>
            </div>

        </div>

        <table class="file-manager-table">
            <thead>
                <tr>
                    <td class="selected-column">Name<i class="fa-solid fa-arrow-down-long fa-xs"></i></td>
                    <td>Size</td>
                    <td>Modified</td>
                    <td style="width: 80px;">Actions</td>
                </tr>
            </thead>
            <tbody>
                <?php if ($current_directory != $initial_directory): ?>
                <tr>
                    <td colspan="10" class="name"><i class="fa-solid fa-folder"></i><a
                            href="?dir=<?= urlencode(dirname(rtrim($current_directory, '/'))) ?>">...</a></td>
                </tr>
                <?php endif; ?>
                <?php foreach ($results as $result): ?>
<tr class="file">
    <td class="name">
        <?= get_filetype_icon($result) ?>
        <a class="view-file"
           href="<?= is_dir($result) ? '?dir=' . urlencode($result) : '?dir=' . urlencode($current_directory) . '&file=' . urlencode($result) ?>">
           <?= basename($result) ?>
        </a>
    </td>
    <td><?= is_dir($result) ? 'Folder' : convert_filesize(filesize($result)) ?></td>
    <td class="date"><?= date('F j, Y H:ia', filemtime($result)) ?></td>
    <td class="actions">
        <?php if (!is_dir($result)): ?>
        <a href="?dir=<?= urlencode($current_directory) ?>&file=<?= urlencode($result) ?>&download=true" class="btn green">
            <i class="fa-solid fa-download fa-xs"></i>
        </a>
        <?php if (isset($_POST['password']) && $_POST['password'] === '12345'): ?>
            <!-- Rename Button -->
            <a href="rename.php?file=<?= urlencode($result) ?>" class="btn blue">
                <i class="fa-solid fa-pen-to-square fa-xs"></i>
            </a>
            <!-- Delete Button -->
            <a href="delete.php?file=<?= urlencode($result) ?>&dir=<?= urlencode($current_directory) ?>" 
   class="btn red" 
   onclick="return confirm('Are you sure you want to delete this file?');">
    <i class="fa-solid fa-trash fa-xs"></i>
</a>

        <?php endif; ?>
    <?php endif; ?>
</td>

                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>

        <div class="audio-player" style="<?= $audio_file ? '' : 'display:none;' ?>">
            <audio controls autoplay id="audio-player">
                <?php if ($audio_file): ?>
                <source src="<?= $audio_file ?>" type="<?= mime_content_type($audio_file) ?>">
                <?php endif; ?>
                Your browser does not support the audio element.
            </audio>
        </div>

        <div class="video-player" style="<?= $video_file ? 'display:block;' : 'display:none;' ?>">
            <button class="close-btn" onclick="closeVideoPlayer()">X</button>
            <video controls autoplay id="video-player" style="max-width: 100%; height: auto;">
                <?php if ($video_file): ?>
                <source src="<?= $video_file ?>" type="<?= mime_content_type($video_file) ?>">
                <?php endif; ?>
                Your browser does not support the video element.
            </video>
        </div>

    </div>

    <script>
        function closeVideoPlayer() {
            const videoPlayer = document.getElementById('video-player');
            videoPlayer.pause(); // Pause the video
            videoPlayer.currentTime = 0; // Reset the video to the beginning
            document.querySelector('.video-player').style.display = 'none'; // Hide the video player
        }

        document.querySelectorAll('.view-file').forEach(function (link) {
            link.addEventListener('click', function (event) {
                var href = this.getAttribute('href');
                var fileExtension = href.split('.').pop().toLowerCase();
                if (fileExtension === 'mp3' || fileExtension === 'mp4') {
                    // Reload the page with the selected file, ensuring the current directory is preserved
                    window.location.href = href;
                } else if (fileExtension === 'folder') {
                    // Navigate to the folder
                    window.location.href = href;
                } else {
                    // Prevent default link behavior
                    event.preventDefault();
                    // Set the href to download the file
                    window.location.href = href + '&download=true';
                }
            });
        });

        document.querySelectorAll('tr.file a.view-file').forEach(function (link) {
            link.addEventListener('click', function (event) {
                var href = this.getAttribute('href');
                if (href.includes('file=') && !href.includes('.')) {
                    // Navigate to the folder
                    event.preventDefault();
                    window.location.href = href;
                }
            });
        });
    </script>

</body>

</html>
