<?php
require 'backend_db.php';  
// Load database connection + start session if backend_db.php handles sessions

// Must be logged in to view appointments
if (empty($_SESSION['user'])) {
    header('Location: login.php');  // Redirect to login if not authenticated
    exit;
}

$user = $_SESSION['user'];  // Get logged-in user info

// Fetch all appointments for this customer, including service and barber info
$stmt = $pdo->prepare(
    'SELECT a.*, 
            s.name as service_name, 
            u2.full_name as barber_name 
     FROM appointments a 
     JOIN services s ON a.service_id = s.id 
     JOIN users u2 ON a.barber_id = u2.id 
     WHERE a.customer_id = ? 
     ORDER BY a.start_at DESC'
);
$stmt->execute([$user['id']]);
$appts = $stmt->fetchAll();  // Store all appointments in an array
?>
<!doctype html>
<html>
<head>
  <meta charset="utf-8">  
  <title>My Appointments</title>  <!-- Page title -->
  <link rel="stylesheet" href="css/style.css">  <!-- Link external CSS -->
</head>
<body>
  <div class="container">  <!-- Main content container -->
    <h2>My Appointments</h2>

    <?php foreach($appts as $a): ?>  <!-- Loop through each appointment -->
      <div class="service-card">     <!-- Styled card for appointment -->
        <strong><?=htmlspecialchars($a['service_name'])?></strong><br> <!-- Service name -->
        <?=htmlspecialchars($a['barber_name'])?> — <?=htmlspecialchars($a['start_at'])?> — <?=htmlspecialchars($a['status'])?><br>  
        <!-- Barber, start time, and status -->
        <small>Booked: <?=htmlspecialchars($a['created_at'])?></small>  <!-- Booking creation time -->
      </div>
    <?php endforeach; ?>

    <p><a href="index.php">Back</a></p>  <!-- Link back to homepage -->
  </div>
</body>
</html>