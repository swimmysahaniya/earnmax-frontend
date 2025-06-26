<?php
session_start();
include "includes/config.php"; // Ensure database connection

if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST["login"])) {
    $mobile = trim($_POST["mobile"]);
    $password = trim($_POST["password"]);

    $login_time = date("Y-m-d H:i:s");
    $query = "INSERT INTO myapp_useractivity (user_mobile_id, login_time) VALUES ('$mobile', '$login_time')";
    mysqli_query($conn, $query);

    $stmt = $conn->prepare("SELECT id, password, referral_code FROM myapp_users WHERE mobile = ?");
    $stmt->bind_param("s", $mobile);
    $stmt->execute();
    $stmt->store_result();
    $stmt->bind_result($user_id, $hashed_password, $referral_code);
    $stmt->fetch();

    if ($stmt->num_rows > 0) {
        if (password_verify($password, $hashed_password)) {
            $_SESSION["user_id"] = $user_id;
            $_SESSION["user_mobile"] = $mobile;
            $_SESSION["referral_code"] = $referral_code;
            header("Location: index.php");
            exit();
        } else {
            $errors[] = "Invalid password!";
        }
    } else {
        $errors[] = "User not found!";
    }
    $stmt->close();
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>The Earn Max - Login</title>
    <link rel="shortcut icon" href="images/logo.png">
    <style>
        /* App-style CSS */
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body, html {
    height: 100%;
    font-family: 'Segoe UI', sans-serif;
    background: linear-gradient(to bottom, #551a8b, rgba(255, 115, 0, 0.34));
    display: flex;
    justify-content: center;
    align-items: center;
    color: #fff;
}

/* Header logo */
header {
    position: absolute;
    top: 20px;
    left: 50%;
    transform: translateX(-50%);
}
header img {
    width: 80px;
}

/* Container styled with semi-transparent violet */
.container {
    background: rgba(85, 26, 139, 0.85); /* translucent dark violet */
    width: 90%;
    max-width: 400px;
    padding: 30px 25px;
    border-radius: 20px;
    box-shadow: 0 10px 30px rgba(0, 0, 0, 0.3);
    color: #fff;
    margin-top: 80px;
}

/* Navigation buttons */
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

/* Form inputs styled with semi-transparent white + purple border */
.login-box input[type="text"],
.login-box input[type="password"] {
    width: 100%;
    padding: 14px;
    margin: 12px 0;
    background-color: rgba(255, 255, 255, 0.1);
    border: 1px solid rgba(255, 255, 255, 0.3);
    border-radius: 10px;
    color: #fff;
    font-size: 16px;
    transition: 0.2s ease-in-out;
}
.login-box input::placeholder {
    color: rgba(255, 255, 255, 0.7);
}
.login-box input:focus {
    border-color: #ff7300;
    outline: none;
}

/* Remember Me checkbox */
.form-check {
    font-size: 14px;
    color: #fff;
    margin: 10px 0;
}
.form-check input {
    accent-color: #ff7300;
}

/* Login button */
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

/* Error message */
.error-messages p {
    color: #ffbaba;
    margin: 5px 0;
}

/* Mobile view */
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
            <img src="images/logo.png" alt="Logo">
        </a>
    </header>

    <div class="container">
        <div class="btn">
            <a href="login.php">
                <button class="login <?php echo (basename($_SERVER['PHP_SELF']) == 'login.php') ? 'active' : ''; ?>">Login</button>
            </a>
            <a href="signup.php">
                <button class="signup <?php echo (basename($_SERVER['PHP_SELF']) == 'signup.php') ? 'active' : ''; ?>">Signup</button>
            </a>
        </div>

        <?php if (!empty($errors)) { ?>
            <div class="error-messages">
                <?php foreach ($errors as $error) {
                    echo "<center><p>$error</p></center>";
                } ?>
            </div>
        <?php } ?>

        <div class="login-box">
            <form method="POST">
                <input type="text" name="mobile" placeholder="Please enter your mobile number" required>
                <input type="password" name="password" placeholder="Please enter your login password" required>

                <div class="form-check">
                    <input type="checkbox" id="rememberMe">
                    <label for="rememberMe"> Remember username & password</label>
                </div>
                <button type="submit" name="login" class="clkbtn">Login</button>
            </form>
        </div>
    </div>
</body>
</html>
