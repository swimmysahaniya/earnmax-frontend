<?php
session_start();
include("includes/config.php");

$user_mobile = $_SESSION['user_mobile'] ?? '';

if (!$user_mobile) {
    echo json_encode(["status" => "error", "message" => "User not logged in"]);
    exit;
}

$task_id = intval($_POST['task_id'] ?? 0);
$video_id = intval($_POST['video_id'] ?? 0);

if (empty($task_id) || empty($video_id)) {
    echo json_encode(["status" => "error", "message" => "Invalid input data"]);
    exit;
}

$query = "INSERT INTO myapp_watchedvideo (task_id, video_url, watched_at, user_mobile_id)
          VALUES (?, ?, NOW(), ?)
          ON DUPLICATE KEY UPDATE watched_at = NOW()";

$stmt = $conn->prepare($query);
$stmt->bind_param("sss", $task_id, $video_id, $user_mobile);

if ($stmt->execute()) {
    echo json_encode(["status" => "success", "message" => "Video marked as watched"]);
} else {
    echo json_encode(["status" => "error", "message" => "Execution failed: " . $stmt->error]);
}

$stmt->close();
$conn->close();
?>
