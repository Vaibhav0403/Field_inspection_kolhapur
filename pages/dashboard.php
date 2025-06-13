<?php
include("../config/db.php"); // Assuming db.php contains your database connection logic

// Start session
session_start();
$successMessage = '';
if (isset($_SESSION['success_message'])) {
    $successMessage = $_SESSION['success_message'];
    unset($_SESSION['success_message']); // Clear it after retrieving
}

// Retrieve and clear any potential error messages (though they shouldn't occur after successful login)
$errorMessage = '';
if (isset($_SESSION['error_message'])) {
    $errorMessage = $_SESSION['error_message'];
    unset($_SESSION['error_message']); // Clear it after displaying
}

// Add logic here to check if the user is actually logged in
if (!isset($_SESSION['logged_in']) || $_SESSION['logged_in'] !== true) {
     header("Location: ../public/login.php");
     exit();
 }


// Initialize variables
$totalVisits = 0;
$todayVisits = 0;
$completedVisits = 0;
$upcomingVisits = 0;
$pendingVisits = 0;

$totalHealthCenterVisits = 0;
$totalSubCenterVisits = 0;

$todayHealthCenterVisits = 0;
$todaySubCenterVisits = 0;

$completedHealthCenterVisits = 0;
$completedSubCenterVisits = 0;

$upcomingHealthCenterVisits = 0;
$upcomingSubCenterVisits = 0;

$pendingHealthCenterVisits = 0;
$pendingSubCenterVisits = 0;

$today_phc_visits_data = []; // Array to store data for today's PHC visits table
$today_subcenter_visits_data = []; // Array to store data for today's SubCenter visits table

// Database connection (assuming $conn is established in db.php)
if (isset($conn)) {
    // --- Query for all types of visits (total, completed, pending, upcoming) ---
    // Using a single, more efficient query for counts
    $sql_summary_counts = "
        SELECT
            COUNT(sv.id) AS total_visits,
            SUM(CASE WHEN sv.center_type = 'PHC' THEN 1 ELSE 0 END) AS total_phc_visits,
            SUM(CASE WHEN sv.center_type = 'SubCenter' THEN 1 ELSE 0 END) AS total_subcenter_visits,

            SUM(CASE WHEN sv.visit_date = CURDATE() THEN 1 ELSE 0 END) AS today_visits,
            SUM(CASE WHEN sv.visit_date = CURDATE() AND sv.center_type = 'PHC' THEN 1 ELSE 0 END) AS today_phc_visits,
            SUM(CASE WHEN sv.visit_date = CURDATE() AND sv.center_type = 'SubCenter' THEN 1 ELSE 0 END) AS today_subcenter_visits,

            SUM(CASE WHEN sv.status = 'completed' THEN 1 ELSE 0 END) AS completed_visits,
            SUM(CASE WHEN sv.status = 'completed' AND sv.center_type = 'PHC' THEN 1 ELSE 0 END) AS completed_phc_visits,
            SUM(CASE WHEN sv.status = 'completed' AND sv.center_type = 'SubCenter' THEN 1 ELSE 0 END) AS completed_subcenter_visits,

            SUM(CASE WHEN sv.status = 'pending' THEN 1 ELSE 0 END) AS pending_visits,
            SUM(CASE WHEN sv.status = 'pending' AND sv.center_type = 'PHC' THEN 1 ELSE 0 END) AS pending_phc_visits,
            SUM(CASE WHEN sv.status = 'pending' AND sv.center_type = 'SubCenter' THEN 1 ELSE 0 END) AS pending_subcenter_visits,

            SUM(CASE WHEN sv.visit_date > CURDATE() AND sv.status NOT IN ('completed', 'cancelled') THEN 1 ELSE 0 END) AS upcoming_visits,
            SUM(CASE WHEN sv.visit_date > CURDATE() AND sv.status NOT IN ('completed', 'cancelled') AND sv.center_type = 'PHC' THEN 1 ELSE 0 END) AS upcoming_phc_visits,
            SUM(CASE WHEN sv.visit_date > CURDATE() AND sv.status NOT IN ('completed', 'cancelled') AND sv.center_type = 'SubCenter' THEN 1 ELSE 0 END) AS upcoming_subcenter_visits

        FROM
            scheduled_visits sv";

    $result_summary = mysqli_query($conn, $sql_summary_counts);

    if ($result_summary && mysqli_num_rows($result_summary) > 0) {
        $summary_data = mysqli_fetch_assoc($result_summary);

        $totalVisits = $summary_data['total_visits'];
        $totalHealthCenterVisits = $summary_data['total_phc_visits'];
        $totalSubCenterVisits = $summary_data['total_subcenter_visits'];

        $todayVisits = $summary_data['today_visits'];
        $todayHealthCenterVisits = $summary_data['today_phc_visits'];
        $todaySubCenterVisits = $summary_data['today_subcenter_visits'];

        $completedVisits = $summary_data['completed_visits'];
        $completedHealthCenterVisits = $summary_data['completed_phc_visits'];
        $completedSubCenterVisits = $summary_data['completed_subcenter_visits'];

        $pendingVisits = $summary_data['pending_visits'];
        $pendingHealthCenterVisits = $summary_data['pending_phc_visits'];
        $pendingSubCenterVisits = $summary_data['pending_subcenter_visits'];

        $upcomingVisits = $summary_data['upcoming_visits'];
        $upcomingHealthCenterVisits = $summary_data['upcoming_phc_visits'];
        $upcomingSubCenterVisits = $summary_data['upcoming_subcenter_visits'];
    }

    // --- Query for today's PHC visits data for the table ---
    $sql_today_phc_data = "SELECT
                                sv.id AS visit_id,
                                t.name AS taluka_name,
                                phc.name AS center_name,
                                sv.visit_date,
                                sv.status,
                                sv.center_type
                            FROM
                                scheduled_visits sv
                            JOIN
                                talukas t ON sv.taluka_id = t.id
                            JOIN
                                primary_health_centers phc ON sv.center_id = phc.id
                            WHERE
                                sv.visit_date = CURDATE() AND sv.center_type = 'PHC'";
    $result_today_phc_data = mysqli_query($conn, $sql_today_phc_data);
    if ($result_today_phc_data && mysqli_num_rows($result_today_phc_data) > 0) {
        while ($row_data = mysqli_fetch_assoc($result_today_phc_data)) {
            $today_phc_visits_data[] = $row_data;
        }
    }

    // --- Query for today's SubCenter visits data for the table ---
    $sql_today_subcenter_data = "SELECT
                                        sv.id AS visit_id,
                                        t.name AS taluka_name,
                                        shc.name AS center_name,
                                        phc.name AS phc_name, -- Include PHC name for sub-centers
                                        sv.visit_date,
                                        sv.status,
                                        sv.center_type
                                    FROM
                                        scheduled_visits sv
                                    JOIN
                                        talukas t ON sv.taluka_id = t.id
                                    JOIN
                                        sub_health_centers shc ON sv.center_id = shc.id
                                    LEFT JOIN
                                        primary_health_centers phc ON shc.phc_id = phc.id -- Link sub-center to its PHC
                                    WHERE
                                        sv.visit_date = CURDATE() AND sv.center_type = 'SubCenter'"; // This condition is key!
    $result_today_subcenter_data = mysqli_query($conn, $sql_today_subcenter_data);
    if ($result_today_subcenter_data && mysqli_num_rows($result_today_subcenter_data) > 0) {
        while ($row_data = mysqli_fetch_assoc($result_today_subcenter_data)) {
            $today_subcenter_visits_data[] = $row_data;
        }
    }

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
    <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css">
    <link rel="stylesheet" href="../public/css/dashboard.css">
</head>
<body>
    <?php include("../includes/header.php"); ?>
    <?php include("../includes/sidebar.php"); ?>

    <div class="main-layout">
        <div class="dashboard-container">
            <h2>डॅशबोर्ड</h2>
            <div id="messageBox" class="message-box"></div>

            <div class="summary-cards">
                <div class="card blue">
                    <p>एकूण भेटी<br><strong><?= $totalVisits ?></strong></p>
                    <small>आरोग्य केंद्र: <?= $totalHealthCenterVisits ?> | उपकेंद्र भेट: <?= $totalSubCenterVisits ?></small>
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
                    <small>आरोग्य केंद्र: <?= $upcomingHealthCenterVisits ?> | उपकेंद्र भेट: <?= $upcomingSubCenterVisits ?></small>
                </div>
                <div class="card red">
                    <p>अपूर्ण भेटी<br><strong><?= $pendingVisits ?></strong></p>
                    <small>आरोग्य केंद्र: <?= $pendingHealthCenterVisits ?> | उपकेंद्र भेट: <?= $pendingSubCenterVisits ?></small>
                </div>
            </div>

            <div class="section">
                <h3>आजचे भेटी</h3>
                <div class="btn-group" role="group" aria-label="Today's Visits Filter">
                    <button type="button" class="btn btn-primary active" id="btn-phc">आरोग्य केंद्र माहिती</button>
                    <button type="button" class="btn btn-secondary" id="btn-subcenter">उपकेंद्र भेट माहिती</button>
                </div>

                <table class="table table-bordered table-striped mt-3" id="phc-visits-table">
                    <thead>
                        <tr>
                            <th>अ. क्र.</th>
                            <th>तालुका</th>
                            <th>आरोग्य केंद्र नाव</th>
                            <th>भेट दिनांक</th>
                            <th>स्थिती</th>
                            <th>कार्यवाही</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (empty($today_phc_visits_data)): ?>
                            <tr>
                                <td colspan="6" class="text-center no-data">आज आरोग्य केंद्राची कोणतीही भेट नाही.</td>
                            </tr>
                        <?php else: ?>
                            <?php $i = 1; ?>
                            <?php foreach ($today_phc_visits_data as $visit): ?>
                                <tr>
                                    <td><?= $i++ ?></td>
                                    <td><?= htmlspecialchars($visit['taluka_name']) ?></td>
                                    <td><?= htmlspecialchars($visit['center_name']) ?></td>
                                    <td><?= htmlspecialchars($visit['visit_date']) ?></td>
                                    <td><?= htmlspecialchars($visit['status']) ?></td>
                                    <td>
                                        <a href="arogya_inspection_form.php?visit_id=<?= $visit['visit_id'] ?>" class="btn btn-info btn-sm">माहिती भरा/पहा</a>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </tbody>
                </table>

                <table class="table table-bordered table-striped mt-3" id="subcenter-visits-table" style="display: none;">
                    <thead>
                        <tr>
                            <th>अ. क्र.</th>
                            <th>तालुका</th>
                            <th>प्राथमिक आरोग्य केंद्र</th>
                            <th>उपकेंद्र नाव</th>
                            <th>भेट दिनांक</th>
                            <th>स्थिती</th>
                            <th>कार्यवाही</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (empty($today_subcenter_visits_data)): ?>
                            <tr>
                                <td colspan="7" class="text-center no-data">आज उपकेंद्राची कोणतीही भेट नाही.</td>
                            </tr>
                        <?php else: ?>
                            <?php $i = 1; ?>
                            <?php foreach ($today_subcenter_visits_data as $visit): ?>
                                <tr>
                                    <td><?= $i++ ?></td>
                                    <td><?= htmlspecialchars($visit['taluka_name']) ?></td>
                                    <td><?= htmlspecialchars($visit['phc_name']) ?></td>
                                    <td><?= htmlspecialchars($visit['center_name']) ?></td>
                                    <td><?= htmlspecialchars($visit['visit_date']) ?></td>
                                    <td><?= htmlspecialchars($visit['status']) ?></td>
                                    <td>
                                        <a href="subcenter_inspection_form.php?visit_id=<?= $visit['visit_id'] ?>" class="btn btn-info btn-sm">माहिती भरा/पहा</a>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <?php include("../includes/footer.php"); ?>

    <script src="https://code.jquery.com/jquery-3.5.1.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/popper.js@1.16.1/dist/umd/popper.min.js"></script>
    <script src="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.min.js"></script>


    <script>
        // Ensure jQuery is loaded *before* this script
        $(document).ready(function() {
            $('#btn-phc').click(function() {
                $('#phc-visits-table').show();
                $('#subcenter-visits-table').hide();
                $(this).addClass('active btn-primary').removeClass('btn-secondary');
                $('#btn-subcenter').removeClass('active btn-primary').addClass('btn-secondary');
            });

            $('#btn-subcenter').click(function() {
                $('#subcenter-visits-table').show();
                $('#phc-visits-table').hide();
                $(this).addClass('active btn-primary').removeClass('btn-secondary');
                $('#btn-phc').removeClass('active btn-primary').addClass('btn-secondary');
            });
        });

       
        document.addEventListener('DOMContentLoaded', function() {
            const messageBox = document.getElementById('messageBox');
            const phpSuccessMessage = "<?php echo htmlspecialchars($successMessage); ?>";
            const phpErrorMessage = "<?php echo htmlspecialchars($errorMessage); ?>";

            if (phpSuccessMessage) {
                messageBox.textContent = phpSuccessMessage;
                messageBox.classList.add('success-message');
                messageBox.style.display = 'block';

                setTimeout(() => {
                    messageBox.style.display = 'none';
                    messageBox.textContent = '';
                    messageBox.classList.remove('success-message');
                }, 5000); // Hide after 5 seconds
            } else if (phpErrorMessage) {
                // This 'else if' is for consistency, though ideally errors go back to login.php
                messageBox.textContent = phpErrorMessage;
                messageBox.classList.add('error-message');
                messageBox.style.display = 'block';

                setTimeout(() => {
                    messageBox.style.display = 'none';
                    messageBox.textContent = '';
                    messageBox.classList.remove('error-message');
                }, 5000); // Hide after 5 seconds
            }
        });
   
    </script>

</body>
</html>