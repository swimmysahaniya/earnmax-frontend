<?php
include("includes/config.php");

// Fetch user mobile number from session
$user_mobile = $_SESSION['user_mobile'] ?? '';

if (!$user_mobile) {
    echo "<p class='text-danger text-center'>Error: User not logged in.</p>";
    exit;
}

// Fetch watched videos for the user
$query = "SELECT video_url FROM myapp_watchedvideo WHERE user_mobile_id = ?";
$stmt = $conn->prepare($query);
$stmt->bind_param("s", $user_mobile);
$stmt->execute();
$result = $stmt->get_result();

$watched_videos = [];
while ($row = $result->fetch_assoc()) {
    $watched_videos[] = $row['video_url'];
}
//print_r($watched_videos);die;

// Fetch user's membership amount
$query = "SELECT amount, created_at FROM myapp_payment WHERE user_mobile_id = ? AND status = '1' ORDER BY created_at DESC LIMIT 1";
$stmt = $conn->prepare($query);
$stmt->bind_param("s", $user_mobile);
$stmt->execute();
$result = $stmt->get_result();
$row = $result->fetch_assoc();
$user_membership = $row['amount'] ?? 0;
$membership_date = $row['created_at'] ?? null;
$user_membership = intval($user_membership);


//calculate the days difference for task 30 days validity
$task_expired = false;
if ($membership_date) {
    $membership_date = new DateTime($membership_date);
    $now = new DateTime();
    $diff_days = $membership_date->diff($now)->days;
    if ($diff_days > 30) {
        $task_expired = true;
    }
}

// Fetch completed tasks count and total earnings
$query = "SELECT
        SUM(ct.completed_tasks) AS completed_tasks,
        SUM(ct.total_earnings) AS total_earnings
    FROM myapp_completedtask AS ct
    INNER JOIN myapp_payment AS p ON ct.user_mobile = p.user_mobile_id
    INNER JOIN myapp_task AS t ON ct.task_id = t.id
    WHERE ct.user_mobile = ?
    AND p.status = '1'
    AND p.amount = t.amount";
$stmt = $conn->prepare($query);
$stmt->bind_param("s", $user_mobile);
$stmt->execute();
$result = $stmt->get_result();
$row = $result->fetch_assoc();
$completed_tasks = $row['completed_tasks'] ?? 0;
$total_earnings = $row['total_earnings'] ?? 0;

//print_r($result);die;

// Fetch tasks and associated videos from the database
$query = "SELECT
            t.id AS task_id,
            t.amount,
            t.no_of_videos,
            t.earning,
            t.task_number,
            v.id AS video_id,
            v.video
        FROM myapp_task AS t
        LEFT JOIN myapp_taskvideo AS v ON t.id = v.task_id
        ORDER BY t.amount ASC";

$stmt = $conn->prepare($query);
$stmt->execute();
$result = $stmt->get_result();

$tasks = [];
while ($row = $result->fetch_assoc()) {
    $task_id = $row['task_id'];
    if (!isset($tasks[$task_id])) {
        $tasks[$task_id] = [
            'task_id' => $row['task_id'],
            'amount' => $row['amount'],
            'no_of_videos' => $row['no_of_videos'],
            'earning' => $row['earning'],
            'task_number' => $row['task_number'],
            'videos' => []
        ];
    }
    if ($row['video_id']) {
        $tasks[$task_id]['videos'][] = [
            'video_id' => $row['video_id'],
            'video' => $row['video']
        ];
    }
}

// Organize membership levels
$membership_levels = [];
foreach ($tasks as $task) {
    $membership_levels[$task['amount']] = $task;

// Get user's task and videos based on membership level
$user_task = $membership_levels[$user_membership]['task_number'] ?? 0;
$total_videos = $membership_levels[$user_membership]['no_of_videos'] ?? 0;
$earning_per_video = $membership_levels[$user_membership]['earning'] ?? 0;
$task_videos = $membership_levels[$user_membership]['videos'] ?? [];
$task_id = $membership_levels[$user_membership]['task_id'] ?? 0;
}

// Calculate remaining tasks
$remaining_tasks = max(0, $total_videos - $completed_tasks);
//print_r($remaining_tasks);die;
?>

<div class="container py-4" id="task">
    <h2 class="text-center mb-4">Task</h2>
    <div class="card">
        <div class="card-body">
            <div class="row mb-4 text-center">
                <div class="col-md-3">
                    <h5>Today's Remaining Tasks: <span class="text-warning" id="remaining-tasks"><?php echo $remaining_tasks; ?></span></h5>
                </div>
                <div class="col-md-3">
                    <h5>Completed Tasks: <span class="text-danger" id="completed-tasks"><?php echo $completed_tasks; ?></span></h5>
                </div>
                <div class="col-md-3">
                    <h5>Total Earnings: <span id="earnings"><?php echo $total_earnings; ?></span> INR</h5>
                </div>
                <div class="col-md-3">
                    <h5>Taken Membership: <?php echo $user_membership; ?></h5>
                    <a href="membership.php" class="flash-button mx-2">Upgrade Membership</a>
                </div>
            </div>

            <div class="row mb-4" id="task-buttons">
                <div class="col text-center">
                    <?php if ($task_expired): ?>
                        <p class="text-danger">You cannot complete your task now. Your 30 days validity has expired.</p>
                    <?php else: ?>
                        <?php if ($completed_tasks == 0): ?>
                            <p class="text-primary">Welcome! Start your first task by watching the videos below.</p>
                            <button class="btn flash-button mx-2 mb-2" id="watch-videos-btn">
                                <i class="fa fa-video" aria-hidden="true"></i> Start Watching Videos
                            </button>
                        <?php elseif ($remaining_tasks == 0): ?>
                            <p class="text-danger">You have completed your tasks for today.</p>
                        <?php else: ?>
                            <button class="btn flash-button mx-2 mb-2" id="watch-videos-btn">
                                <i class="fa fa-video" aria-hidden="true"></i> Watch Videos to Complete Tasks
                            </button>
                        <?php endif; ?>
                    <?php endif; ?>
                </div>
            </div>

            <div id="task-list" style="display: none;">
                <!-- <button class="btn btn-success" id="back-button" onclick="goBack()" style="display: block;">&larr; Back</button>
                <br> -->
                <div class="row g-3" style="width:99%;" id="tasks-container"></div>
            </div>
        </div>
    </div>
</div>

<script>
let watchedVideosFromDB = <?php echo json_encode($watched_videos); ?>;
let videoCount = <?php echo $completed_tasks; ?>;
let allowedVideos = <?php echo $total_videos; ?>;
let totalEarnings = <?php echo $total_earnings; ?>;
let earningPerVideo = <?php echo $earning_per_video; ?>;
let remainingTasks = <?php echo $remaining_tasks; ?>;
let taskExpired = <?php echo $task_expired ? 'true' : 'false'; ?>;

const videoBasePath = "https://theearnmax.com/media/videos/";
let taskId = <?php echo json_encode($task_id); ?>;
let taskVideos = <?php echo json_encode($task_videos); ?>;
let videoUrls = taskVideos.map(video => video.video.startsWith("https") ? video.video : videoBasePath + video.video.split('/').pop());

let watchedVideos = new Set(watchedVideosFromDB.map(String));  // video IDs
let watchingVideoIndex = taskVideos.findIndex(video => !watchedVideos.has(String(video.video_id)));

if (watchingVideoIndex === -1) watchingVideoIndex = 0; // If all are watched, default to first

function updateEarnings() {
    document.getElementById("earnings").innerText = totalEarnings;
    document.getElementById("completed-tasks").innerText = videoCount;
    document.getElementById("remaining-tasks").innerText = allowedVideos - videoCount;
}

document.addEventListener("DOMContentLoaded", function () {
    showTasks();
});

function saveWatchedVideo(videoId) {
    fetch("save_watched_video.php", {
        method: "POST",
        headers: { "Content-Type": "application/x-www-form-urlencoded" },
        body: `task_id=${encodeURIComponent(taskId)}&video_id=${encodeURIComponent(videoId)}`,
    })
    .then(response => response.json())
    .then(data => console.log("Server Response:", data))
    .catch(error => console.error("Error:", error));
}

function showTasks() {
    if (taskExpired) {
        return;
    }

    const container = document.getElementById('tasks-container');
    container.innerHTML = '';

    taskVideos.forEach((video, i) => {
        const videoUrl = video.video.startsWith("https") ? video.video : videoBasePath + video.video.split('/').pop();
        const videoId = video.video_id;
        const isWatched = watchedVideos.has(String(videoId));
        const isDisabled = i !== watchingVideoIndex || isWatched;

        //console.log("Rendering video", i, "ID:", videoId, "Watched:", isWatched);

        const videoDiv = document.createElement('div');
        videoDiv.className = 'col-6 col-md-6 col-lg-4';
        videoDiv.innerHTML = `
            <div class="video-wrapper">
                <video id="video${i}" width="100%" class="video-task" style="object-fit: fill; max-height: 228px;" ${isDisabled ? '' : 'controls'} poster="/images/slide-2.webp">
                    <source src="${videoUrl}" type="video/mp4">
                    Your browser does not support the video tag.
                </video>
                <p id="status${i}" class="bg-light white-text text-center flash-text p-10">
                    <strong>Earning:</strong> INR. ${earningPerVideo} Per Video
                </p>
            </div>
        `;
        container.appendChild(videoDiv);

        const videoElement = document.getElementById(`video${i}`);
        const statusElement = document.getElementById(`status${i}`);

        if (isWatched) {
            markVideoUnplayable(videoElement, statusElement);
        }

        // Prevent fast-forwarding
        videoElement.addEventListener("timeupdate", function () {
            if (this.currentTime > (this.lastTime || 0) + 2) {
                this.currentTime = this.lastTime;
            }
            this.lastTime = this.currentTime;
        });

        // Allow only current video to play
        videoElement.addEventListener("play", function () {
            if (i !== watchingVideoIndex) {
                this.pause();
            } else {
                disableOtherVideos(i);

                if (this.requestFullscreen) {
                    this.requestFullscreen();
                } else if (this.webkitRequestFullscreen) {
                    this.webkitRequestFullscreen();
                } else if (this.msRequestFullscreen) {
                    this.msRequestFullscreen();
                }
            }
        });

        //console.log("Video URL:", videoUrl);

        // On video complete
        videoElement.addEventListener("ended", function () {
            markVideoCompleted(i, videoElement, statusElement, videoId);

            // Exit fullscreen
            if (document.fullscreenElement) {
                document.exitFullscreen();
            } else if (document.webkitFullscreenElement) {
                document.webkitExitFullscreen();
            } else if (document.msFullscreenElement) {
                document.msExitFullscreen();
            }
        });
    });

    document.getElementById('task-list').style.display = 'block';
    document.getElementById('task-buttons').style.display = 'none';
}

function disableOtherVideos(activeIndex) {
    videoUrls.forEach((_, i) => {
        let videoElement = document.getElementById(`video${i}`);
        if (i !== activeIndex) {
            videoElement.controls = false;
            videoElement.style.pointerEvents = "none";
        } else {
            videoElement.controls = true;
            videoElement.style.pointerEvents = "auto";
        }
    });
}

function markVideoCompleted(index, videoElement, statusElement, videoId) {
    // Check if current time is before 5 PM (17:00)
    let now = new Date();
    let currentHour = now.getHours();

    if (currentHour >= 24) {
        alert("Task time has ended. You cannot complete this task after 5 PM.");
        videoElement.currentTime = 0; // Optionally rewind the video
        videoElement.pause();
        return;
    }

    watchedVideos.add(String(videoId));
    videoElement.setAttribute("data-watched-id", videoId);
    markVideoUnplayable(videoElement, statusElement);

    videoCount++;
    totalEarnings += earningPerVideo;
    updateEarnings();

    if (videoCount < allowedVideos) {
        let nextVideo = document.getElementById(`video${index + 1}`);
        if (nextVideo) {
            nextVideo.controls = true;
            nextVideo.style.pointerEvents = "auto";
            watchingVideoIndex = index + 1;
        }
    }

    saveTaskProgress(videoId);
}

function markVideoUnplayable(videoElement, statusElement) {
    videoElement.controls = false;
    videoElement.style.pointerEvents = "none";
    videoElement.style.opacity = "0.6";
    statusElement.innerHTML = `<strong style="color:#fff;">✔ Video Completed</strong>`;
    statusElement.style.backgroundColor = "#28a745";
}

function saveTaskProgress(videoId) {
    fetch("save_task.php", {
        method: "POST",
        headers: { "Content-Type": "application/x-www-form-urlencoded" },
        body: `task_id=${encodeURIComponent(taskId)}&completed_tasks=1&total_earnings=${encodeURIComponent(earningPerVideo)}&video_id=${encodeURIComponent(videoId)}`,
    })
    .then(response => response.json())
    //.then(data => console.log("Server Response:", data))
    .then(data => {
        if (data.status === "error") {
            alert(data.message);
        } else {
            console.log("Server Response:", data);
        }
    })
    .catch(error => console.error("Error:", error));
}

function goBack() {
    document.getElementById('task-list').style.display = 'none';
    document.getElementById('task-buttons').style.display = 'block';
}

document.getElementById("watch-videos-btn").addEventListener("click", showTasks);

</script>
