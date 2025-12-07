<?php
require 'backend_db.php';  
// Load database connection + start session if backend_db.php handles sessions

$err = '';  
// Variable to store error messages

// Check if form is submitted
if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    // Collect and clean inputs
    $email = trim($_POST['email'] ?? '');     // User email input
    $password = $_POST['password'] ?? '';     // User password input

    if ($email && $password) {                // Check both fields are provided

        // Fetch user with role info from database
        $stmt = $pdo->prepare(
            'SELECT u.*, r.name as role_name FROM users u 
             JOIN roles r ON u.role_id = r.id 
             WHERE u.email = ?'
        );
        $stmt->execute([$email]);
        $user = $stmt->fetch();               // Get user record

        if ($user && password_verify($password, $user['password_hash'])) {
            // Login success

            unset($user['password_hash']);    // Remove sensitive password from session
            $_SESSION['user'] = $user;       // Store user info in session

            header('Location: index.php');   // Redirect to homepage
            exit;                             // Stop further execution

        } else {
            $err = "Wrong credentials.";     // Invalid email or password
        }

    } else {
        $err = "Email and password required.";  // Fields missing
    }
}
?>
<!doctype html>
<html>
<head>
  <meta charset="utf-8">                  <!-- Ensure correct text encoding -->
  <title>Login</title>                     <!-- Browser tab title -->
  <link rel="stylesheet" href="css/style.css"> 
  <!-- Link to external stylesheet -->
</head>
<body>
  <div class="container auth">             <!-- Centered login form container -->

    <h2>Login</h2>                        <!-- Page heading -->

    <?php if(!empty($err)) 
        // Display error messages
        echo '<div class="error">'.htmlspecialchars($err).'</div>'; 
    ?>

    <?php if(!empty($_GET['registered'])) 
        // Display success message after registration
        echo '<div class="success">Registration complete. Please login.</div>'; 
    ?>

    <!-- Login form -->
    <form method="post">                   <!-- Form submits via POST -->

      <label>
        Email: 
        <input type="email" name="email" required> 
        <!-- Email input -->
      </label><br>

      <label>
        Password: 
        <input type="password" name="password" required> 
        <!-- Password input -->
      </label><br>

      <button type="submit">Login</button> <!-- Submit button -->
    </form>

  </div>
</body>
</html>