<?php
//ini_set('display_errors', 1);
//ini_set('display_startup_errors', 1);
//error_reporting(E_ALL);

session_start();
include("includes/config.php"); // Ensure database connection

if (!isset($_SESSION["user_mobile"])) {
    header("Location: login.php");
    exit();
}

$user_mobile = $_SESSION["user_mobile"];

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $amount = $_POST["amount"];
    $transaction_code = $_POST["transaction_code"];

    // Handle screenshot upload
    $upload_dir = "/media/payment_screenshot/";
    if (!is_dir($upload_dir)) {
        mkdir($upload_dir, 0755, true);
    }

    $file_path = '';
    if (isset($_FILES["payment_screenshot"]) && $_FILES["payment_screenshot"]["error"] == 0) {
        $filename = basename($_FILES["payment_screenshot"]["name"]);
        $target_file = $upload_dir . $filename;
        $relative_path = "payment_screenshot/" . $filename;  // Store only relative path

        $imageFileType = strtolower(pathinfo($target_file, PATHINFO_EXTENSION));
        $allowed_types = ['jpg', 'jpeg', 'png', 'gif'];

        if (in_array($imageFileType, $allowed_types)) {
            if (move_uploaded_file($_FILES["payment_screenshot"]["tmp_name"], $target_file)) {
                $file_path = $target_file;
            } else {
                echo "<script>alert('Failed to upload screenshot.'); window.history.back();</script>";
                exit();
            }
        } else {
            echo "<script>alert('Only JPG, JPEG, PNG, and GIF files are allowed.'); window.history.back();</script>";
            exit();
        }
    } else {
        echo "<script>alert('Screenshot file is required.'); window.history.back();</script>";
        exit();
    }

    // Check if the user already has a payment record
    $check_stmt = $conn->prepare("SELECT id FROM myapp_payment WHERE user_mobile_id = ?");
    $check_stmt->bind_param("s", $user_mobile);
    $check_stmt->execute();
    $check_stmt->store_result();
    $num_rows = $check_stmt->num_rows;
    $check_stmt->close();

    if ($num_rows > 0) {
        // Update existing record
        $stmt = $conn->prepare("UPDATE myapp_payment SET amount = ?, transaction_code = ?, payment_screenshot = ?, status = '0', created_at = NOW() WHERE user_mobile_id = ?");
        $stmt->bind_param("isss", $amount, $transaction_code, $relative_path, $user_mobile);
    } else {
        // Insert new record
        $stmt = $conn->prepare("INSERT INTO myapp_payment (user_mobile, user_mobile_id, amount, transaction_code, payment_screenshot, status, created_at) VALUES (?, ?, ?, ?, ?, '0', NOW())");
        $stmt->bind_param("ssiss", $user_mobile, $user_mobile, $amount, $transaction_code, $relative_path);
    }

    if ($stmt->execute()) {
        echo "<script>alert('Payment details and screenshot submitted successfully!'); window.location.href='./';</script>";
    } else {
        echo "<script>alert('Database error: " . addslashes($stmt->error) . "'); window.history.back();</script>";
    }

    $stmt->close();
}
?>
