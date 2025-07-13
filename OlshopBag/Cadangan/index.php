<!DOCTYPE html>
<?php
    session_start();
    if (isset($_SESSION['email'])) {
        header('location: Home.php');
    }
?>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="Login.css" type="text/css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.13.1/font/bootstrap-icons.min.css">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.7/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-LN+7fdVzj6u52u30Kp6M/trliBMCMKTyK833zpbD+pXdCLuTusPj697FH4R/5mcr" crossorigin="anonymous">
    <script defer src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.7/dist/js/bootstrap.bundle.min.js" integrity="sha384-ndDqU0Gzau9qJ1lfW4pNLlhNTkCfHzAVBReH9diLvGRem5+R9g2FzA8ZGN954O5Q" crossorigin="anonymous"></script>
    <title>Login</title>
</head>
<body>
  <div class="container-fluid">
    <form class="form-login" action="Login.php" method="post" accept-charset="utf-8">
      <h3 class="fw-semibold text-center">Login</h3>
      <input type="email" class="form-control mb-3" name="email" id="email" placeholder="Email" required>
      <input type="password" class="form-control mb-3" name="password" id="password" placeholder="Password" required/>
      <button class="btn btn-primary w-100 mb-3" type="submit">Login</button>
      <p>don't have an account? <a href="../Register/Register.html">Register</a></p>
      <p class="text-muted text-center">&copy; 2025</p>
    </form>
  </div>
</body>
</html>
