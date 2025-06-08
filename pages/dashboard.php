<?php
include("../config/db.php"); // Assuming db.php contains your database connection logic

// Start session
session_start();

// Initialize variables
$totalVisits = 0;
$todayVisits = 0;
$completedVisits = 0;
$upcomingVisits = 0;
$pendingVisits = 0;

$todayHealthCenterVisits = 0;
$todaySubCenterVisits = 0;
$completedHealthCenterVisits = 0;
$completedSubCenterVisits = 0;
$pendingHealthCenterVisits = 0;
$pendingSubCenterVisits = 0;

$today_visits_data = []; // Array to store data for today's visits table

// Database connection (assuming $conn is established in db.php)
if (isset($conn)) {
    // --- Query for total visits ---
    $sql_total = "SELECT COUNT(*) AS total FROM visits"; // Replace your_visits_table_name
    $result_total = mysqli_query($conn, $sql_total);
    if ($result_total && mysqli_num_rows($result_total) > 0) {
        $row_total = mysqli_fetch_assoc($result_total);
        $totalVisits = $row_total['total'];
    }

    // --- Query for today's visits ---
    $current_date = date('Y-m-d');
    $sql_today = "SELECT COUNT(*) AS today_count, 
                         SUM(CASE WHEN center_type = 'आरोग्य केंद्र' THEN 1 ELSE 0 END) AS today_hc_count,
                         SUM(CASE WHEN center_type = 'उपकेंद्र' THEN 1 ELSE 0 END) AS today_sc_count
                  FROM visits
                  WHERE visit_date = '$current_date'"; // Replace your_visits_table_name, visit_date, visit_type
    $result_today = mysqli_query($conn, $sql_today);
    if ($result_today && mysqli_num_rows($result_today) > 0) {
        $row_today = mysqli_fetch_assoc($result_today);
        $todayVisits = $row_today['today_count'];
        $todayHealthCenterVisits = $row_today['today_hc_count'];
        $todaySubCenterVisits = $row_today['today_sc_count'];
    }

    // --- Query for completed visits ---
    $sql_completed = "SELECT COUNT(*) AS completed_count,
                             SUM(CASE WHEN center_type = 'आरोग्य केंद्र' THEN 1 ELSE 0 END) AS completed_hc_count,
                             SUM(CASE WHEN center_type = 'उपकेंद्र' THEN 1 ELSE 0 END) AS completed_sc_count
                      FROM visits
                      WHERE status = 'completed'"; // Replace your_visits_table_name, visit_status
    $result_completed = mysqli_query($conn, $sql_completed);
    if ($result_completed && mysqli_num_rows($result_completed) > 0) {
        $row_completed = mysqli_fetch_assoc($result_completed);
        $completedVisits = $row_completed['completed_count'];
        $completedHealthCenterVisits = $row_completed['completed_hc_count'];
        $completedSubCenterVisits = $row_completed['completed_sc_count'];
    }

    // --- Query for pending visits ---
    $sql_pending = "SELECT COUNT(*) AS pending_count,
                           SUM(CASE WHEN center_type = 'आरोग्य केंद्र' THEN 1 ELSE 0 END) AS pending_hc_count,
                           SUM(CASE WHEN center_type = 'उपकेंद्र' THEN 1 ELSE 0 END) AS pending_sc_count
                    FROM visits 
                    WHERE status = 'pending'"; // Replace your_visits_table_name, visit_status
    $result_pending = mysqli_query($conn, $sql_pending);
    if ($result_pending && mysqli_num_rows($result_pending) > 0) {
        $row_pending = mysqli_fetch_assoc($result_pending);
        $pendingVisits = $row_pending['pending_count'];
        $pendingHealthCenterVisits = $row_pending['pending_hc_count'];
        $pendingSubCenterVisits = $row_pending['pending_sc_count'];
    }
    
    // --- Query for upcoming visits (future dates and not completed/pending yet) ---
    // This assumes an 'upcoming' status or a date in the future.
    // Adjust logic based on your 'visit_status' values or if you solely rely on dates.
    $sql_upcoming = "SELECT COUNT(*) AS upcoming_count 
                     FROM visits
                     WHERE visit_date > '$current_date' 
                     AND status NOT IN ('completed', 'cancelled')"; // Adjust statuses as needed
    $result_upcoming = mysqli_query($conn, $sql_upcoming);
    if ($result_upcoming && mysqli_num_rows($result_upcoming) > 0) {
        $row_upcoming = mysqli_fetch_assoc($result_upcoming);
        $upcomingVisits = $row_upcoming['upcoming_count'];
    }


    // --- Query for today's visits data for the table ---
    $sql_today_data = "SELECT taluka, center_type, visit_date
                       FROM visits 
                       WHERE visit_date = '$current_date' 
                       AND center_type = 'आरोग्य केंद्र'"; // Replace column names and table name
    $result_today_data = mysqli_query($conn, $sql_today_data);
    if ($result_today_data && mysqli_num_rows($result_today_data) > 0) {
        while ($row_data = mysqli_fetch_assoc($result_today_data)) {
            $today_visits_data[] = $row_data;
        }
    }

    // Close the database connection if needed (or keep it open if other includes use it)
    // mysqli_close($conn); 

} else {
    // Handle database connection error
    echo "Error: Database connection not established.";
}

?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard</title>
    <link rel="stylesheet" href="../public/css/dashboard.css">
</head>
<body>
    <?php
    include("../includes/header.php")
    ?>
    <?php include("../includes/sidebar.php"); ?>
     <div class="main-layout">
        
        
          <div class="dashboard-container">
    <h2>डॅशबोर्ड</h2>

    <div class="summary-cards">
      <div class="card blue">
        <p>एकूण भेटी<br><strong><?= $totalVisits ?></strong></p>
        <small>आरोग्य केंद्र: <?= $totalVisits - ($todaySubCenterVisits + $completedSubCenterVisits + $pendingSubCenterVisits) ?> | उपकेंद्र भेट: <?= $todaySubCenterVisits + $completedSubCenterVisits + $pendingSubCenterVisits ?></small>
      </div>
      <div class="card orange">
        <p>आजचे भेटी<br><strong><?= $todayVisits ?></strong></p>
        <small>आरोग्य केंद्र: <?= $todayHealthCenterVisits ?> | उपकेंद्र भेट: <?= $todaySubCenterVisits ?></small>
      </div>
      <div class="card green">
        <p>पूर्ण भेटी<br><strong><?= $completedVisits ?></strong></p>
        <small>आरोग्य केंद्र: <?= $completedHealthCenterVisits ?> | उपकेंद्र भेट: <?= $completedSubCenterVisits ?></small>
      </div>
      <div class="card sky">
        <p>येणारे भेटी<br><strong><?= $upcomingVisits ?></strong></p>
        <small>आरोग्य केंद्र: 0 | उपकेंद्र भेट: 0</small> </div>
      <div class="card red">
        <p>अपूर्ण भेटी<br><strong><?= $pendingVisits ?></strong></p>
        <small>आरोग्य केंद्र: <?= $pendingHealthCenterVisits ?> | उपकेंद्र भेट: <?= $pendingSubCenterVisits ?></small>
      </div>
    </div>

    <div class="section">
      <h3>आजचे भेटी</h3>
      <div class="btn-group">
        <button class="btn active">आरोग्य केंद्र माहिती</button>
        <button class="btn">उपकेंद्र भेट माहिती</button>
      </div>

      <table>
        <thead>
          <tr>
            <th>अ. क्र.</th>
            <th>तालुका</th>
            <th>आरोग्य केंद्र नाव</th>
            <th>भेट दिनांक</th>
            <th>Action</th>
          </tr>
        </thead>
        <tbody>
          <?php if ($todayVisits == 0): ?>
            <tr>
              <td colspan="5" class="no-data">आज आरोग्य केंद्राची कोणतीही भेट नाही.</td>
            </tr>
          <?php else: ?>
            <?php $i = 1; ?>
            <?php foreach ($today_visits_data as $visit): ?> 
            <tr>
              <td><?= $i++ ?></td>
              <td><?= htmlspecialchars($visit['taluka']) ?></td>
              <td><?= htmlspecialchars($visit['center_type']) ?></td>
              <td><?= htmlspecialchars($visit['visit_date']) ?></td>
              <td><a href="#">View</a></td>
            </tr>
            <?php endforeach; ?>
          <?php endif; ?>
        </tbody>
      </table>
    </div>
  </div>
    </div>
    <?php
    include("../includes/footer.php")
    ?>
    
</body>
</html>