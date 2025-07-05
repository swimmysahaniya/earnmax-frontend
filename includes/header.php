<!-- Header with Logo + User Info (no toggle) -->
<nav class="navbar navbar-expand-lg navbar-light bg-light shadow-sm">
  <div class="container">
    <!-- brand / logo -->
    <a class="navbar-brand" href="./">
      <img src="images/logo.png"
           alt="Logo"
           width="80"
           height="80"
           class="d-inline-block align-text-top img-responsive">
    </a>

    <!-- user block always visible, aligned right -->
    <div class="d-flex ms-auto align-items-center">
      <?php
        include "includes/config.php";  // DB connection

        // 1️⃣ Is the user logged in?
        if (!isset($_SESSION['user_mobile'])) {
            echo '<a href="login.php" class="btn btn-outline-success btn-sm me-2">Log In</a>';
            echo '<a href="login.php" class="btn btn-success btn-sm">Sign Up</a>';
        } else {
            $user_mobile = $_SESSION['user_mobile'];

            /* ---- fetch profile ---- */
            $stmt = $conn->prepare(
              "SELECT name, profile_image
               FROM myapp_profile
               WHERE user_mobile_id = ?"
            );
            $stmt->bind_param("s", $user_mobile);
            $stmt->execute();
            $stmt->bind_result($name, $imgName);
            $stmt->fetch();
            $stmt->close();

            // defaults
            $name  = $name ?: "User";
            $img   = $imgName
                     ? "images/uploads/profile/$imgName"
                     : "images/user-image-1.jpg";

            // mask mobile
            $masked = "******" . substr($user_mobile, -4);

            echo '
              <img src="' . $img . '" alt="' . htmlspecialchars($name) . '"
                   class="rounded-circle me-2" width="28" height="28">
              <span class="me-3 fw-semibold text-white">' . $masked . '</span>
            ';
        }
      ?>
    </div><!-- /user block -->
  </div><!-- /container -->
</nav>
