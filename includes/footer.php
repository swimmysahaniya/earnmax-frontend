<!-- Footer Section -->
<!-- <footer>
  <p>&copy; 2025 The Earn Max, All rights reserved.</p>
</footer> -->
<br><br><br><br>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<!-- <script>
  document.addEventListener('keydown', function (e) {
    if ((e.ctrlKey || e.metaKey) && (e.key === 'c' || e.key === 'C')) {
      e.preventDefault();
    }
  });
</script>
<script>
  document.addEventListener('contextmenu', function (e) {
    e.preventDefault();
  }, false);

</script> -->
<script>
document.addEventListener("keydown", function (e) {
      if ((e.ctrlKey && e.key === 'c') || (e.ctrlKey && e.key === 'a')) {
          e.preventDefault();
      }
  });
  // Disable Right Click
  document.addEventListener("contextmenu", function (e) {
      e.preventDefault();
  });

  // Disable Ctrl+U, Ctrl+Shift+I, F12, Ctrl+S
  document.addEventListener("keydown", function (e) {
      // Ctrl+U
      if (e.ctrlKey && e.key === 'u') {
          e.preventDefault();
      }
      // Ctrl+Shift+I or F12 (DevTools)
      if ((e.ctrlKey && e.shiftKey && e.key === 'I') || e.key === 'F12') {
          e.preventDefault();
      }
      // Ctrl+S
      if (e.ctrlKey && e.key === 's') {
          e.preventDefault();
      }
  });
</script>

</body>

</html>