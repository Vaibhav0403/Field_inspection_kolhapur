<!DOCTYPE html>
<html lang="mr">
<head>
  <meta charset="UTF-8">
  <title>Sidebar</title>
  <style>
    body {
      margin: 0;
      font-family: 'Segoe UI', sans-serif;
      background: #f4f4f4;
    }

    .sidebar {
      width: 300px;
      height: 1000px;
      background-color: #fff;
      box-shadow: 2px 0 5px rgba(0,0,0,0.1);
      padding: 20px;
      margin-top:0;
      position:fixed;

    }

    .sidebar img.logo {
      display: block;
      margin: 0 auto;
      width: 80px;
    }

    .sidebar h2 {
      text-align: center;
      color: #1b1464;
      margin: 10px 0 20px;
      font-size: 18px;
    }

    .menu {
      list-style: none;
      padding: 0;
      margin-top: 20px;
    }

    .menu li {
      margin-bottom: 10px;
    }

    .menu li a {
      display: flex;
      align-items: center;
      text-decoration: none;
      color: #444;
      padding: 10px 12px;
      border-radius: 8px;
      transition: 0.3s;
    }

    .menu li a.active,
    .menu li a:hover {
      background-color: #6c63ff;
      color: white;
    }

    .menu li a i {
      margin-right: 10px;
    }
  </style>

  <!-- Icons (you can use Font Awesome or SVG icons) -->
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
</head>
<body>

  <div class="sidebar">
    <img src="../assets/images/satymev-jayte.png" alt="Logo" class="logo">
    <h2>जिल्हा परिषद,<br>कोल्हापूर</h2>

    <ul class="menu">
      <li><a href="dashboard.php" class="active"><i class="fas fa-home"></i> डॅशबोर्ड</a></li>
      <li><a href="../pages/health_center_checklist.php"><i class="fas fa-plus"></i> आरोग्य केंद्र चेक लिस्ट</a></li>
      <li><a href="../pages/health_center_report.php"><i class="fas fa-user"></i> आरोग्य केंद्र चेक लिस्ट - रिपोर्ट</a></li>
      <li><a href="../pages/primary_health_center_checklist.php"><i class="fas fa-clipboard-list"></i> उपकेंद्र चेक लिस्ट</a></li>
      <li><a href="upkendra_checklist_report.php"><i class="fas fa-list"></i> उपकेंद्र चेक लिस्ट - रिपोर्ट</a></li>
      <li><a href="../actions/logout.php" id="Logout"onclick="return confirm('Are you sure you want to log out?');"><i class="fas fa-power-off"></i> Log Out</a></li>
    </ul>
  </div>
  <script>
document.addEventListener('DOMContentLoaded', function() {
    const logoutLink = document.getElementById('logoutLink');
    // Or for a button: const logoutButton = document.getElementById('logoutButton');

    if (logoutLink) { // Check if the element exists
        logoutLink.addEventListener('click', function(event) {
            // Prevent the default link behavior immediately
            event.preventDefault();

            // Show the confirmation dialog
            if (confirm('Are you sure you want to log out?')) {
                // If user clicks OK, proceed to logout.php
                window.location.href = '../public/index.php'; // Adjust path if needed
            }
            // If user clicks Cancel, nothing happens (script just ends)
        });
    }

    // If you're using a button, similar logic
    // if (logoutButton) {
    //     logoutButton.addEventListener('click', function() {
    //         if (confirm('Are you sure you want to log out?')) {
    //             window.location.href = '../includes/logout.php';
    //         }
    //     });
    // }
});
</script>

</body>
</html>
