<?php
require 'backend_db.php';        // Load database connection + session handling

// fetch active services
$stmt = $pdo->query('SELECT * FROM services WHERE active = 1 ORDER BY id'); 
// Run query to get all active services from DB, sorted by ID
$services = $stmt->fetchAll();   // Convert results into an array
?>
<!doctype html>
<html>
<head>
  <meta charset="utf-8">         <!-- Sets proper text encoding -->
  <title>Exquisite Barber</title> <!-- Tab title for browser -->
  <link rel="stylesheet" href="css/style.css">  <!-- Include stylesheet -->
</head>
<body>
  <header class="site-header">    <!-- Top navigation + branding -->
    <div class="container">
      <h1 class="logo">Exquisite Barber</h1>  <!-- Site title/logo -->
      <nav>
        <a href="index.php">Home</a> |         <!-- Navigation links -->
        <a href="register.php">Register</a> |
        
        <?php if(empty($_SESSION['user'])): ?>
          <!-- If user NOT logged in: show Login -->
          <a href="login.php">Login</a>

        <?php else: ?>
          <!-- If user IS logged in: show more options -->
          <a href="my_appointments.php">My Appointments</a> |

          <?php if($_SESSION['user']['role_name'] === 'admin'): ?>
            <!-- Admin users see admin dashboard link -->
            <a href="admin_dashboard.php">Admin</a> |
          <?php endif; ?>

          <a href="logout.php">Logout</a>   <!-- Allow user to log out -->
        <?php endif; ?>
      </nav>
    </div>
  </header>

  <main class="container">        <!-- Main page content wrapper -->
    <section class="hero">
      <h2>Exquisite Barber</h2>
      <p>Make your appointment quickly and easily.</p>
      <!-- Simple hero section introducing the business -->
    </section>

    <section class="services">
      <h2>Services</h2>           <!-- Services list header -->
      <div class="cards">         <!-- Grid layout for service cards -->

      <?php foreach($services as $s): ?>   <!-- Loop through each service -->
        <div class="service-card">
          <h3><?=htmlspecialchars($s['name'])?></h3> 
          <!-- Service name (escaped for security) -->

          <p class="desc"><?=htmlspecialchars($s['description'])?></p>
          <!-- Service description -->

          <p class="meta">
            Duration: <?=intval($s['duration_minutes'])?> mins — 
            $<?=number_format($s['price'],2)?>
          </p>
          <!-- Shows duration + price, cleaned/converted safely -->

          <a class="btn" href="book.php?service_id=<?=intval($s['id'])?>">
            Book
          </a>
          <!-- Booking link passes service ID via URL -->
        </div>
      <?php endforeach; ?>

      </div>
    </section>
  </main>

  <footer class="site-footer">    <!-- Footer section -->
    <div class="container">© <?=date('Y')?> Exquisite Barber</div>
    <!-- Dynamic year so the footer stays up-to-date -->
  </footer>
</body>
</html>