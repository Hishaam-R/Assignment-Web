<?php
require 'backend_db.php';
// Load database connection + starts session if backend_db.php handles sessions

// Fetch all user roles (admin, customer, etc.) from the database
// Even though only the customer role is used by default, we load all roles
$rolesStmt = $pdo->query('SELECT * FROM roles ORDER BY id'); 
$roles = $rolesStmt->fetchAll();  
// Store roles as an array for potential use

$errors = [];  
// This array will hold validation errors from the form

// Check if the form has been submitted
if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    // Collect and clean form inputs
    $name = trim($_POST['name'] ?? '');        // User's full name
    $email = trim($_POST['email'] ?? '');      // User's email
    $password = $_POST['password'] ?? '';      // User's password
    $role_id = intval($_POST['role_id'] ?? 1); // Default role = customer (ID 1)

    // Validation checks
    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $errors[] = "Invalid email.";          // Email format is not valid
    }
    if (strlen($password) < 6) {
        $errors[] = "Password must be at least 6 characters.";  
        // Password too short
    }
    if (empty($name)) {
        $errors[] = "Name required.";          // Full name is required
    }

    // Proceed only if there are no validation errors
    if (empty($errors)) {

        // Check whether the email is already registered
        $stmt = $pdo->prepare('SELECT id FROM users WHERE email = ?');
        $stmt->execute([$email]);

        if ($stmt->fetch()) {
            // If result found, email already exists
            $errors[] = "Email already registered.";

        } else {
            // Email is unique → proceed with registration

            // Hash the password so it is stored securely in the database
            $hash = password_hash($password, PASSWORD_DEFAULT);

            // Prepare INSERT query to create a new user
            $ins = $pdo->prepare(
                'INSERT INTO users (role_id, email, password_hash, full_name) VALUES (?,?,?,?)'
            );

            // Execute the INSERT query
            $ins->execute([$role_id, $email, $hash, $name]);

            // Redirect to login page with a success indicator
            header('Location: login.php?registered=1');  
            exit;  // Stop script execution after redirect
        }
    }
}
?>
<!doctype html>
<html>
<head>
  <meta charset="utf-8">                 <!-- Ensures correct text encoding -->
  <title>Register</title>                <!-- Browser tab title -->
  <link rel="stylesheet" href="css/style.css"> 
  <!-- Link to external CSS stylesheet -->
</head>

<body>
  <div class="container auth">           <!-- Centered container for the form -->

    <h2>Register</h2>                    <!-- Page heading -->

    <?php if(!empty($errors)): ?>        <!-- If there are errors, show them -->
      <div class="error">                <!-- Styled error message box -->
        <!-- Display each error safely on its own line -->
        <?=implode('<br>', array_map('htmlspecialchars', $errors))?>
      </div>
    <?php endif; ?>

    <!-- Registration form -->
    <form method="post">                 <!-- Submits via POST for security -->

      <label>
        Full name: 
        <input type="text" name="name" required> 
        <!-- Input for user's full name -->
      </label><br>

      <label>
        Email: 
        <input type="email" name="email" required> 
        <!-- Email input with built-in HTML validation -->
      </label><br>

      <label>
        Password: 
        <input type="password" name="password" required> 
        <!-- Password input (hidden characters) -->
      </label><br>

      <input type="hidden" name="role_id" value="1"> 
      <!-- Hidden input: assigns default role 'customer' -->

      <button type="submit">Register</button>  
      <!-- Button to submit the form -->
    </form>

    <p>
      Already have an account?  
      <a href="login.php">Login</a>      <!-- Link to login page -->
    </p>

  </div>
</body>
</html>