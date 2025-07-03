<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

session_start();
include("includes/config.php"); // Ensure database connection

$user_mobile = $_SESSION["user_mobile"]; // assuming session contains it

if (!isset($_SESSION["user_mobile"])) {
    header("Location: login.php");
    exit();
}

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $user_mobile = $_SESSION["user_mobile"];
    $amount = $_POST["amount"];
    $transaction_code = $_POST["transaction_code"];

    // Check if the user already has a payment record
    $check_stmt = $conn->prepare("SELECT id FROM myapp_payment WHERE user_mobile_id = ?");
    $check_stmt->bind_param("s", $user_mobile);
    $check_stmt->execute();
    $check_stmt->store_result();
    $num_rows = $check_stmt->num_rows;
    $check_stmt->close();

    if ($num_rows > 0) {
        // User has a payment record, update it
        $stmt = $conn->prepare("UPDATE myapp_payment SET amount = ?, transaction_code = ?, status = '0', created_at = NOW() WHERE user_mobile_id = ?");
        $stmt->bind_param("iss", $amount, $transaction_code, $user_mobile);
    } else {
        // First-time purchase, insert new record
        $stmt = $conn->prepare("INSERT INTO myapp_payment (user_mobile, user_mobile_id, amount, transaction_code, status, created_at) VALUES (?, ?, ?, ?, '0', NOW())");
        $stmt->bind_param("ssis", $user_mobile, $user_mobile, $amount, $transaction_code);

    }

    if ($stmt->execute()) {
        echo "<script>alert('Payment details submitted successfully!'); window.location.href='./';</script>";
    } else {
        echo "<script>alert('Database error: " . addslashes($stmt->error) . "'); window.history.back();</script>";
    }
    $stmt->close();
}
?>
