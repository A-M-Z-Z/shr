<!-- CloudBOX - download.php with Unique File Check and Deletion Feature -->
<?php
session_start(); // Start session

// Increase limits for large file operations
ini_set('memory_limit', '512M');
ini_set('max_execution_time', 600);
ini_set('output_buffering', 'Off');
ini_set('zlib.output_compression', 'Off');

// Database Connection
$host = 'localhost';
$user = 'root';
$pass = 'root';
$dbname = 'cloudbox';
$conn = new mysqli($host, $user, $pass, $dbname);
if ($conn->connect_error) die("Database connection failed: " . $conn->connect_error);
$conn->options(MYSQLI_OPT_CONNECT_TIMEOUT, 60);

// Handle File Deletion
if (isset($_GET['delete']) && is_numeric($_GET['delete'])) {
    $file_id = intval($_GET['delete']);
    $stmt = $conn->prepare("DELETE FROM files WHERE id = ? AND user_id = ?");
    $stmt->bind_param("ii", $file_id, $_SESSION['user_id']);
    if ($stmt->execute()) {
        echo "<p style='color:green;'>File deleted successfully.</p>";
    } else {
        echo "<p style='color:red;'>Failed to delete file.</p>";
    }
    exit;
}

// Handle File Download
if (isset($_GET['id']) && is_numeric($_GET['id'])) {
    $file_id = intval($_GET['id']);
    $stmt = $conn->prepare("SELECT f.filename, f.file_type, fc.content FROM file_content fc JOIN files f ON fc.file_id = f.id WHERE f.id = ? ");
    $stmt->bind_param("i", $file_id);
    $stmt->execute();
    $stmt->store_result();
    $stmt->bind_result($filename, $filetype, $filecontent);

    if ($stmt->fetch()) {
        header("Content-Type: $filetype");
        header("Content-Disposition: attachment; filename=\"$filename\"");
        header("Content-Length: " . strlen($filecontent));
        if (ob_get_level()) ob_end_clean();
        echo $filecontent;
        exit;
    } else {
        echo "<p style='color:red;'>File not found.</p>";
    }
}
?>
