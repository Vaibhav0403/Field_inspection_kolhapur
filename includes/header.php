<link rel="stylesheet" href="/php-project/public/css/style.css">
<script src="/php-project/public/js/script.js"></script>

<?php
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}
include("../config/db.php");
?>

<!DOCTYPE html>
<html lang="mr">
<head>
  <meta charset="UTF-8">
  <title>Header</title>
  <style>
    body {
      margin: 300px;
      font-family: 'Segoe UI', sans-serif;
    }

    .header {
      background-color: #6c63ff; /* Purple */
      color: white;
      display: flex;
      justify-content: space-between;
      align-items: center;
      padding: 10px 30px;
      height: 60px;

        /* *** Key for Fixed Header *** */
      position: fixed; /* Make the header stay in place */
      top: 0;          /* Stick to the top of the viewport */
      left: 300px;         /* Stick to the left of the viewport */
      right: 0px;
      z-index: 1000;   /* Ensure it stays on top of other content */
      box-shadow: 0 2px 5px rgba(0,0,0,0.2); /* Optional: Add a subtle shadow */
    }
    

    .header-title {
      font-size: 24px;
      font-weight: bold;
      letter-spacing: 1px;
      margin-left:650px
    }

    .user-info {
      display: flex;
      align-items: center;
    }

    .user-info i {
      background: black;
      color: white;
      border-radius: 50%;
      padding: 10px;
      font-size: 16px;
      margin-right: 10px;
    }

    .user-info span {
      font-size: 16px;
    }
    body {
            font-family: sans-serif;
            margin: 0;
            padding: 0;
            background-color: #f4f4f4;
            color: #333;
        }


        .content {
            margin-left: 250px;
            padding: 20px;
        }

        .dashboard-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 20px;
        }

        .dashboard-header h1 {
            margin: 0;
            font-size: 2em;
            color: #4a148c; /* Deep Purple */
        }

        .user-info {
            display: flex;
            align-items: center;
        }

        .user-info svg {
            margin-right: 5px;
            width: 24px;
            height: 24px;
            fill: #757575;
        }

        .dashboard-cards {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(150px, 1fr));
            gap: 15px;
            margin-bottom: 20px;
        }

        .dashboard-card {
            background-color: white;
            padding: 15px;
            border-radius: 5px;
            box-shadow: 0 1px 3px rgba(0, 0, 0, 0.1);
            text-align: center;
        }

        .card-title {
            font-size: 0.9em;
            color: #757575;
            margin-bottom: 5px;
        }

        .card-value {
            font-size: 1.5em;
            font-weight: bold;
            color: #4a148c; /* Deep Purple */
        }

        .card-details {
            font-size: 0.8em;
            color: #9e9e9e;
            margin-top: 5px;
        }

        .recent-updates {
            background-color: white;
            padding: 15px;
            border-radius: 5px;
            box-shadow: 0 1px 3px rgba(0, 0, 0, 0.1);
            margin-bottom: 20px;
        }

        .recent-updates h2 {
            font-size: 1.2em;
            color: #4a148c; /* Deep Purple */
            margin-top: 0;
            margin-bottom: 10px;
        }

        .report-table {
            width: 100%;
            border-collapse: collapse;
        }

        .report-table th, .report-table td {
            border: 1px solid #ddd;
            padding: 8px;
            text-align: left;
        }

        .report-table th {
            background-color: #f2f2f2;
        }

        .report-table tbody tr:nth-child(even) {
            background-color: #f9f9f9;
        }

        .report-table .action-button {
            background-color: #4a148c; /* Deep Purple */
            color: white;
            border: none;
            padding: 5px 10px;
            border-radius: 3px;
            cursor: pointer;
            font-size: 0.8em;
            transition: background-color 0.3s ease;
        }

        .report-table .action-button:hover {
            background-color: #7b1fa2; /* Purple */
        }
  </style>

  <!-- Font Awesome for user icon -->
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
</head>
<body>

  <div class="header">
    <div class="header-title">फिल्ड इन्स्पेक्शन सिस्टीम</div>
    <div class="user-info">
      <i class="fas fa-user"></i>
      <span>
        Hi, 
        <?php 
        echo isset($_SESSION['username']) 
          ? htmlspecialchars($_SESSION['username']) 
          : 'Guest'; 
        ?>
      </span>
    </div>
  </div>
  

</body>
</html>
