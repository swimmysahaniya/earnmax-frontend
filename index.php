<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <title>The Earn Max</title>
  <link rel="manifest" href="manifest.json" />
  <meta name="theme-color" content="#0f4c81" />
  <link rel="apple-touch-icon" href="images/icons/icon-192x192.png" />
  <style>
    #installBtn {
      background-color: #0d6efd;
      color: white;
      padding: 10px 20px;
      border: none;
      border-radius: 6px;
      cursor: pointer;
      font-size: 16px;
    }
    .btn a button.active, .btn a button:hover {
        background-color: #ff7300;
        color: #fff;
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
    .disp {
      display: flex;
      justify-content: center;
      align-items: center;
    }
  </style>
  <script>
    if ('serviceWorker' in navigator) {
      navigator.serviceWorker.register('/service-worker.js');
    }
  </script>
</head>
<body style="text-align: center;">
  <h1>Welcome to The Earn Max</h1>
  <p>You can install this web app!</p>
  <div class="disp">
    <button id="installBtn" style="display: none;">
      Download App
    </button>
    <div class="btn">
      <a href="login.php">
        <button class="login active">Login</button>
      </a>
    </div>
  </div>
<script>
  let deferredPrompt;
  const installBtn = document.getElementById('installBtn');

  // Listen for install prompt
  window.addEventListener('beforeinstallprompt', (e) => {
    e.preventDefault(); // Prevent automatic prompt
    deferredPrompt = e; // Save the event
    installBtn.style.display = 'inline-block'; // Show the button
  });

  // Handle button click
  installBtn.addEventListener('click', () => {
    if (deferredPrompt) {
      deferredPrompt.prompt(); // Show prompt
      deferredPrompt.userChoice.then((choiceResult) => {
        if (choiceResult.outcome === 'accepted') {
          console.log('User accepted the A2HS prompt');
        } else {
          console.log('User dismissed the A2HS prompt');
        }
        deferredPrompt = null;
        installBtn.style.display = 'none';
      });
    }
  });
</script>
</body>
</html>
