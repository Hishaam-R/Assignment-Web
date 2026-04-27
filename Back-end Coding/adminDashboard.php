<?php
require 'backend_db.php';  
// Load database connection + start session if backend_db.php handles sessions

// Check if user is logged in and is an admin
if (empty($_SESSION['user']) || $_SESSION['user']['role_name'] !== 'admin') {
    header('Location: login.php');  // Redirect non-admin users to login
    exit;
}

// Fetch today's appointments with customer, barber, and service info
$stmt = $pdo->prepare(
    'SELECT a.*, 
            u.full_name AS customer_name, 
            s.name AS service_name, 
            b.full_name AS barber_name 
     FROM appointments a 
     JOIN users u ON a.customer_id = u.id 
     JOIN users b ON a.barber_id = b.id 
     JOIN services s ON a.service_id = s.id 
     WHERE DATE(a.start_at) = CURDATE() 
     ORDER BY a.start_at'
);
$stmt->execute();
$appts = $stmt->fetchAll();  // Store today's appointments in an array
?>
<!doctype html>
<html>
<head>
  <meta charset="utf-8">  
  <title>Admin Dashboard</title>  <!-- Page title -->
  <link rel="stylesheet" href="css/style.css">  <!-- Link external CSS -->
</head>
<body>
  <div class="container">  <!-- Main content container -->
    <h2>Admin Dashboard</h2>
    <h3>Today's Appointments</h3>

    <?php if(empty($appts)) echo '<p>No appointments today.</p>'; ?>  
    <!-- Show message if no appointments -->

    <?php foreach($appts as $a): ?>  <!-- Loop through each appointment -->
      <div class="service-card">     <!-- Styled card for appointment -->
        <strong><?=htmlspecialchars($a['service_name'])?></strong><br>  <!-- Service name -->
        Customer: <?=htmlspecialchars($a['customer_name'])?><br>       <!-- Customer name -->
        Barber: <?=htmlspecialchars($a['barber_name'])?><br>           <!-- Barber name -->
        Time: <?=htmlspecialchars($a['start_at'])?><br>               <!-- Appointment time -->
        Status: <?=htmlspecialchars($a['status'])?>                   <!-- Appointment status -->
      </div>
    <?php endforeach; ?>

    <p><a href="index.php">Back to site</a></p>  <!-- Link back to main site -->
  </div>
</body>
</html>