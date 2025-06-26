<?php
//ini_set('display_errors', 1);
//ini_set('display_startup_errors', 1);
//error_reporting(E_ALL);

session_start();
include "includes/config.php";

$errors = [];
$success_message = "";
$generated_referral_code = "";

$referral_from_url = $_GET['ref'] ?? '';

if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST["signup"])) {
    $mobile = trim($_POST["mobile"]);
    $password = trim($_POST["password"]);
    $confirm_password = trim($_POST["confirm_password"]);
    $invitation_code = trim($_POST["invitation_code"]);

    if (empty($mobile) || empty($password) || empty($confirm_password)) {
        $errors[] = "All fields are required.";
    }
    if ($password !== $confirm_password) {
        $errors[] = "Passwords do not match!";
    }

    // Check if mobile number is already registered
    $stmt = $conn->prepare("SELECT id FROM myapp_users WHERE mobile = ?");
    $stmt->bind_param("s", $mobile);
    $stmt->execute();
    $stmt->store_result();
    if ($stmt->num_rows > 0) {
        $errors[] = "Mobile number already registered!";
    }
    $stmt->close();

    // Validate invitation code if provided
    if (!empty($invitation_code)) {
        $stmt = $conn->prepare("SELECT referral_code FROM myapp_users WHERE referral_code = ?");
        $stmt->bind_param("s", $invitation_code);
        $stmt->execute();
        $stmt->store_result();
        if ($stmt->num_rows == 0) {
            $errors[] = "Invalid invitation code!";
        }
        $stmt->close();
    } else {
        $invitation_code = NULL;
    }

    if (empty($errors)) {
        $hashed_password = password_hash($password, PASSWORD_BCRYPT);
        $generated_referral_code = strtoupper(substr(md5(uniqid(mt_rand(), true)), 0, 8));

        $stmt = $conn->prepare("INSERT INTO myapp_users (mobile, password, referral_code, invited_by, created_at, status, membership_level, refund_status) VALUES (?, ?, ?, ?, NOW(), '0', '0', '0')");
        $stmt->bind_param("ssss", $mobile, $hashed_password, $generated_referral_code, $invitation_code);

        if ($stmt->execute()) {
            $_SESSION["user_mobile"] = $mobile;
            $_SESSION["referral_code"] = $generated_referral_code;
            $success_message = "Signup successful! <br>
            Your Referral Code: <b>$generated_referral_code</b> <br>
            Share this referral code to invite new users.";
        } else {
            $errors[] = "Database error: " . $stmt->error;
        }
        $stmt->close();
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0"/>
  <link rel="shortcut icon" href="images/logo.png" />
  <title>The Earn Max - Signup</title>
  <style>
    body, html {
      height: 100%;
      font-family: 'Segoe UI', sans-serif;
      background: linear-gradient(to bottom, #551a8b, rgba(255, 115, 0, 0.34));
      display: flex;
      justify-content: center;
      align-items: center;
      color: #fff;
    }

    header {
      position: absolute;
      top: 20px;
      left: 50%;
      transform: translateX(-50%);
    }

    header img {
      width: 80px;
    }

    .container {
      background: rgba(85, 26, 139, 0.85);
      width: 90%;
      max-width: 400px;
      padding: 30px 25px;
      border-radius: 20px;
      box-shadow: 0 10px 30px rgba(0, 0, 0, 0.3);
      color: #fff;
      margin-top: 80px;
    }

    .btn {
      display: flex;
      justify-content: space-between;
      margin-bottom: 20px;
    }

    .btn a button {
      flex: 1;
      margin: 0 5px;
      padding: 12px;
      border: none;
      border-radius: 8px;
      background-color: rgba(255, 255, 255, 0.1);
      font-weight: 600;
      color: #fff;
      cursor: pointer;
      transition: 0.3s ease;
    }

    .btn a button.active,
    .btn a button:hover {
      background-color: #ff7300;
      color: #fff;
    }

    .signup-box input[type="text"],
    .signup-box input[type="password"] {
        width: 100%;
        max-width: 100%;
        padding: 6px;
        margin: 20px 0;
        background-color: rgba(255, 255, 255, 0.1);
        border: 1px solid rgba(255, 255, 255, 0.3);
        border-radius: 10px;
        color: #fff;
        font-size: 16px;
        transition: 0.2s ease-in-out;
    }

    .signup-box input::placeholder {
      color: rgba(255, 255, 255, 0.7);
    }

    .signup-box input:focus {
      border-color: #ff7300;
      outline: none;
    }

    .clkbtn {
      width: 100%;
      padding: 14px;
      border: none;
      border-radius: 10px;
      background-color: #ff7300;
      color: #fff;
      font-weight: bold;
      font-size: 16px;
      cursor: pointer;
      transition: 0.3s ease;
    }

    .clkbtn:hover {
      background-color: #e86600;
    }

    .error-messages p {
      color: #ffbaba;
      margin: 5px 0;
    }

    .success-message {
      color: lightgreen;
      font-weight: bold;
      padding: 10px;
      border: 1px solid green;
      margin-bottom: 15px;
      border-radius: 8px;
      background: rgba(255, 255, 255, 0.05);
    }

    @media screen and (max-width: 480px) {
      .container {
        padding: 20px;
        margin-top: 60px;
      }

      .btn a button {
        font-size: 14px;
        padding: 10px;
      }

      .clkbtn {
        font-size: 15px;
        padding: 12px;
      }
    }
  </style>
</head>

<body>
  <header>
    <a class="navbar-brand" href="./">
      <img src="images/logo.png" alt="Logo" />
    </a>
  </header>

  <div class="container">
    <div class="btn">
      <a href="login.php">
        <button class="login">Login</button>
      </a>
      <a href="signup.php">
        <button class="signup active">Signup</button>
      </a>
    </div>

    <!-- Success Message -->
    <?php if (!empty($success_message)) { ?>
      <div class="success-message">
        <?php echo $success_message; ?>
      </div>
    <?php } ?>

    <!-- Error Messages -->
    <?php if (!empty($errors)) { ?>
      <div class="error-messages">
        <?php foreach ($errors as $error) {
          echo "<p>$error</p>";
        } ?>
      </div>
    <?php } ?>

    <!-- Signup Form -->
    <div class="signup-box">
      <form method="POST">
        <input type="text" name="mobile" placeholder="Enter your mobile number" pattern="[6-9]{1}[0-9]{9}" required />
        <input type="password" name="password" placeholder="Password" required />
        <input type="password" name="confirm_password" placeholder="Confirm password" required />
        <input type="text" name="invitation_code" placeholder="Please enter the invitation code" value="<?php echo htmlspecialchars($referral_from_url); ?>" required />
        <button type="submit" name="signup" class="clkbtn">Signup</button>
      </form>
    </div>
  </div>
</body>
</html>
