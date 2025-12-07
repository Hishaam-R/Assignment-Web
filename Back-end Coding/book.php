<?php
require 'backend_db.php';  
// Load database connection + start session if backend_db.php handles sessions

// Must be logged in to book — redirect to login if not
if (empty($_SESSION['user'])) {
    header('Location: login.php');
    exit;
}

$user = $_SESSION['user'];                   // Get logged-in user info
$service_id = intval($_GET['service_id'] ?? 0); // Service ID from URL
$service = null;

// Fetch selected service details if service_id is provided
if ($service_id) {
  $stmt = $pdo->prepare('SELECT * FROM services WHERE id = ?');
  $stmt->execute([$service_id]);
  $service = $stmt->fetch();                 // Store service info
}

$errors = [];                                // To store form validation errors
$success = '';                               // To store success message

// Process form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    $service_id = intval($_POST['service_id']);       // Service being booked
    $start_at_raw = trim($_POST['start_at']);         // User-selected start time

    // Normalize datetime-local input to SQL datetime format
    if (strpos($start_at_raw, 'T') !== false) {
      $start_at_raw = str_replace('T', ' ', $start_at_raw) . ':00';
    }

    // Convert input string to DateTime object
    try {
      $start = new DateTime($start_at_raw);
    } catch (Exception $e) {
      $errors[] = 'Invalid start time.';              // Handle invalid date format
    }

    // Fetch service duration from database
    $stmt = $pdo->prepare('SELECT duration_minutes FROM services WHERE id = ?');
    $stmt->execute([$service_id]);
    $s = $stmt->fetch();
    if (!$s) $errors[] = 'Service not found.';       // Check service exists
    else {
      $duration = intval($s['duration_minutes']);    // Duration in minutes
      $end = clone $start;                            // Calculate end time
      $end->modify("+{$duration} minutes");
    }

    // Select a barber (role_id = 2) — simplest approach: first available
    $stmt = $pdo->prepare('SELECT id FROM users WHERE role_id = 2 ORDER BY id LIMIT 1');
    $stmt->execute();
    $barber = $stmt->fetch();
    if (!$barber) $errors[] = 'No barbers are registered yet.';  // No barbers
    else $barber_id = $barber['id'];

    if (empty($errors)) {

      // Check for overlapping appointments for the selected barber
      $check = $pdo->prepare(
        'SELECT COUNT(*) AS cnt 
         FROM appointments 
         WHERE barber_id = ? 
           AND status IN ("booked","confirmed") 
           AND NOT (end_at <= ? OR start_at >= ?)'
      );
      $check->execute([$barber_id, $start->format('Y-m-d H:i:s'), $end->format('Y-m-d H:i:s')]);
      $row = $check->fetch();

      if ($row && intval($row['cnt']) > 0) {
        $errors[] = 'Selected time is not available. Please pick another slot.'; // Overlap
      } else {
        // Insert appointment into database
        $ins = $pdo->prepare(
          'INSERT INTO appointments (customer_id, barber_id, service_id, start_at, end_at, status) 
           VALUES (?, ?, ?, ?, ?, "booked")'
        );
        $ins->execute([
          $user['id'], $barber_id, $service_id, 
          $start->format('Y-m-d H:i:s'), $end->format('Y-m-d H:i:s')
        ]);
        $success = 'Booking successful.';             // Success message
      }
    }
}
?>
<!doctype html>
<html>
<head>
  <meta charset="utf-8">                           <!-- Ensure correct encoding -->
  <title>Book</title>                              <!-- Browser tab title -->
  <link rel="stylesheet" href="css/style.css">    <!-- Link CSS -->
</head>
<body>
  <div class="container auth">                     <!-- Centered form container -->
    <h2>Book Service</h2>

    <?php if($service): ?>                         <!-- Show selected service info -->
      <p>Service: <strong><?=htmlspecialchars($service['name'])?></strong> 
      (<?=intval($service['duration_minutes'])?> mins)</p>
    <?php endif; ?>

    <?php if(!empty($errors)): ?>                  <!-- Display errors -->
      <div class="error"><?=implode('<br>', array_map('htmlspecialchars',$errors))?></div>
    <?php endif; ?>

    <?php if(!empty($success)): ?>                 <!-- Display success message -->
      <div class="success"><?=htmlspecialchars($success)?></div>
    <?php endif; ?>

    <!-- Booking form -->
    <form method="post">
      <input type="hidden" name="service_id" value="<?=intval($service_id)?>">

      <label>Start time:
        <!-- HTML5 datetime-local picker -->
        <input type="datetime-local" name="start_at" required>
      </label><br>

      <button type="submit">Confirm Booking</button>
    </form>

    <p><a href="index.php">Back</a></p>            <!-- Link back to homepage -->
  </div>
</body>
</html>