<?php
session_start();

// Include your database connection file
// Make sure this file correctly connects to your 'Field_inspection' database
include('../config/db.php'); // Adjust path if necessary

// Check if user is logged in
if (!isset($_SESSION['username'])) {
    header('Location: login.php'); // Redirect to login page if not logged in
    exit();
}

// Initialize filters
$filter_taluka = isset($_GET['filter_taluka']) ? $_GET['filter_taluka'] : '';
$filter_phc = isset($_GET['filter_phc']) ? $_GET['filter_phc'] : '';
$filter_from_date = isset($_GET['filter_from_date']) ? $_GET['filter_from_date'] : '';
$filter_to_date = isset($_GET['filter_to_date']) ? $_GET['filter_to_date'] : '';
$search_query = isset($_GET['search_query']) ? $_GET['search_query'] : '';

// --- Form Submission for New Visit (Add Scheduled Visit) ---
if (isset($_POST['add_visit'])) {
    $taluka_id = $_POST['taluka_id'];
    $phc_id = $_POST['phc_id']; // This is now 'phc_id' from primary_health_centers table
    $visit_date = $_POST['visit_date'];
    $center_type = 'PHC'; // Assuming new visit form is for PHC. Will need separate form for SubCenter.
    $status = 'pending'; // Default status for a newly scheduled visit

    // Prepare and execute the insert statement for scheduled_visits
    $stmt = $conn->prepare("INSERT INTO scheduled_visits (taluka_id, center_id, center_type, visit_date, status) VALUES (?, ?, ?, ?, ?)");
    $stmt->bind_param("iisss", $taluka_id, $phc_id, $center_type, $visit_date, $status);

    if ($stmt->execute()) {
        $_SESSION['message'] = "नवीन भेट यशस्वीरित्या जोडली गेली!"; // New visit added successfully!
        $_SESSION['msg_type'] = "success";
    } else {
        $_SESSION['message'] = "त्रुटी: " . $stmt->error; // Error message
        $_SESSION['msg_type'] = "danger";
    }
    $stmt->close();
    header("Location: health_center_report.php"); // Redirect to prevent form resubmission
    exit();
}

// --- Fetching Data for Filters ---

// Fetch Talukas for dropdown
$talukas_query = "SELECT id, name FROM talukas ORDER BY name ASC";
$talukas_result = $conn->query($talukas_query);
$talukas = [];
if ($talukas_result->num_rows > 0) {
    while ($row = $talukas_result->fetch_assoc()) {
        $talukas[] = $row;
    }
}

// **MODIFICATION START: Populating PHCs for the "Add Visit" form**
// Fetch ALL Primary Health Centers for the 'Add Visit' dropdown
// Since AJAX is removed, this dropdown will not dynamically filter by taluka.
// It will show all PHCs, and the user must select the correct one.
$all_phcs_query = "SELECT id, name FROM primary_health_centers ORDER BY name ASC";
$all_phcs_result = $conn->query($all_phcs_query);
$all_primary_health_centers = [];
if ($all_phcs_result->num_rows > 0) {
    while ($row = $all_phcs_result->fetch_assoc()) {
        $all_primary_health_centers[] = $row;
    }
}
// **MODIFICATION END: Populating PHCs for the "Add Visit" form**


// Fetch Primary Health Centers for the FILTER dropdown (filtered by taluka if selected)
// This part already works without AJAX, based on form submission reload.
$phcs_query_filter = "SELECT id, name FROM primary_health_centers";
if (!empty($filter_taluka)) {
    $phcs_query_filter .= " WHERE taluka_id = " . $conn->real_escape_string($filter_taluka);
}
$phcs_query_filter .= " ORDER BY name ASC";
$phcs_result_filter = $conn->query($phcs_query_filter);
$primary_health_centers_filter = []; // Renamed variable to avoid conflict
if ($phcs_result_filter->num_rows > 0) {
    while ($row = $phcs_result_filter->fetch_assoc()) {
        $primary_health_centers_filter[] = $row;
    }
}


// --- Fetching Data for Report Table ---
$sql = "SELECT
            sv.id AS visit_id,
            t.name AS taluka_name,
            phc.name AS phc_name,
            sv.visit_date,
            sv.status,
            sv.photos_json,
            sv.squad_number,
            sv.squad_leader,
            sv.center_type,
            phc.id AS phc_table_id
        FROM
            scheduled_visits sv
        JOIN
            talukas t ON sv.taluka_id = t.id
        JOIN
            primary_health_centers phc ON sv.center_id = phc.id AND sv.center_type = 'PHC'
        WHERE 1=1";

// Apply filters
if (!empty($filter_taluka)) {
    $sql .= " AND sv.taluka_id = " . $conn->real_escape_string($filter_taluka);
}
if (!empty($filter_phc)) {
    $sql .= " AND sv.center_id = " . $conn->real_escape_string($filter_phc);
    $sql .= " AND sv.center_type = 'PHC'";
}
if (!empty($filter_from_date)) {
    $sql .= " AND sv.visit_date >= '" . $conn->real_escape_string($filter_from_date) . "'";
}
if (!empty($filter_to_date)) {
    $sql .= " AND sv.visit_date <= '" . $conn->real_escape_string($filter_to_date) . "'";
}
if (!empty($search_query)) {
    $search_term = '%' . $conn->real_escape_string($search_query) . '%';
    $sql .= " AND (
                t.name LIKE '$search_term' OR
                phc.name LIKE '$search_term' OR
                sv.status LIKE '$search_term' OR
                sv.squad_leader LIKE '$search_term'
            )";
}

$sql .= " ORDER BY sv.visit_date DESC";

$result = $conn->query($sql);

// --- HTML Output ---
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Health_center_report</title>
    <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css">
    <link rel="stylesheet" href="../public/css/health_center_report.css">
    <link rel="stylesheet" href="https://cdn.datatables.net/1.10.21/css/dataTables.bootstrap4.min.css">
    <link rel="stylesheet" href="https://cdn.datatables.net/buttons/1.6.2/css/buttons.dataTables.min.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/flatpickr/dist/flatpickr.min.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/flatpickr/dist/themes/material_blue.css"> </head>
<body>

<?php include('../includes/header.php'); // Includes <!DOCTYPE html>, <head>, opening <body>, and top navbar ?>

    <?php include('../includes/sidebar.php'); // Includes the sidebar navigation ?>
    <div class="main-layout">
        <div class="dashboard-container">
            <div class="content col-md-10">
                <?php if (isset($_SESSION['message'])): ?>
                    <div class="alert alert-<?php echo $_SESSION['msg_type']; ?> alert-dismissible fade show" role="alert">
                        <?php echo $_SESSION['message']; ?>
                        <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                            <span aria-hidden="true">&times;</span>
                        </button>
                    </div>
                    <?php
                    unset($_SESSION['message']);
                    unset($_SESSION['msg_type']);
                    ?>
                <?php endif; ?>

                <div class="form-container mb-4">
                    <h5>नवीन आरोग्य केंद्र भेट नोंदवा</h5>
                    <form method="POST" action="">
                        <div class="row">
                            <div class="form-group col-md-4">
                                <label for="taluka_id">तालुका निवड:</label>
                                <select class="form-control" id="taluka_id" name="taluka_id" required>
                                    <option value="">तालुका निवडा</option>
                                    <?php foreach ($talukas as $taluka): ?>
                                        <option value="<?php echo $taluka['id']; ?>"><?php echo htmlspecialchars($taluka['name']); ?></option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                            <div class="form-group col-md-4">
                                <label for="phc_id">प्राथमिक आरोग्य केंद्र निवड:</label>
                                <select class="form-control" id="phc_id" name="phc_id" required>
                                    <option value="">प्रा. आ. केंद्र निवडा</option>
                                    <?php foreach ($all_primary_health_centers as $phc): ?>
                                        <option value="<?php echo $phc['id']; ?>"><?php echo htmlspecialchars($phc['name']); ?></option>
                                    <?php endforeach; ?>
                                    </select>
                            </div>
                            <div class="form-group col-md-4">
                                <label for="visit_date">भेट दिनांक:</label>
                                <input type="date" class="form-control datepicker" id="visit_date" name="visit_date" required>
                            </div>
                        </div>
                        <button type="submit" name="add_visit" class="btn btn-primary">भेट जोडा</button>
                    </form>
                </div>

                <div class="report-filters mb-4">
                    <h5>आरोग्य केंद्र चेक लिस्ट - रिपोर्ट</h5>
                    <form method="GET" action="">
                        <div class="row">
                            <div class="form-group col-md-3">
                                <label for="filter_taluka">तालुका निवड:</label>
                                <select class="form-control" id="filter_taluka" name="filter_taluka">
                                    <option value="">सर्व तालुका</option>
                                    <?php foreach ($talukas as $taluka): ?>
                                        <option value="<?php echo $taluka['id']; ?>" <?php echo ($filter_taluka == $taluka['id']) ? 'selected' : ''; ?>>
                                            <?php echo htmlspecialchars($taluka['name']); ?>
                                        </option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                            <div class="form-group col-md-3">
                                <label for="filter_phc">आरोग्य केंद्र निवड:</label>
                                <select class="form-control" id="filter_phc" name="filter_phc">
                                    <option value="">सर्व आरोग्य केंद्र</option>
                                    <?php foreach ($primary_health_centers_filter as $phc): ?>
                                        <option value="<?php echo $phc['id']; ?>" <?php echo ($filter_phc == $phc['id']) ? 'selected' : ''; ?>>
                                            <?php echo htmlspecialchars($phc['name']); ?>
                                        </option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                            <div class="form-group col-md-3">
                                <label for="filter_from_date">From:</label>
                                <input type="text" class="form-control datepicker" id="filter_from_date" name="filter_from_date" value="<?php echo htmlspecialchars($filter_from_date); ?>">
                            </div>
                            <div class="form-group col-md-3">
                                <label for="filter_to_date">To:</label>
                                <input type="text" class="form-control datepicker" id="filter_to_date" name="filter_to_date" value="<?php echo htmlspecialchars($filter_to_date); ?>">
                            </div>
                        </div>
                        <div class="row">
                            <div class="form-group col-md-6">
                                <label for="search_query">शोध:</label>
                                <input type="text" class="form-control" id="search_query" name="search_query" placeholder="तालुका, केंद्र किंवा स्थितीनुसार शोधा" value="<?php echo htmlspecialchars($search_query); ?>">
                            </div>
                            <div class="col-md-6 d-flex align-items-end">
                                <button type="submit" class="btn btn-primary mr-2">दाखवा</button>
                                <a href="health_center_report.php" class="btn btn-secondary">रीसेट करा</a>
                            </div>
                        </div>
                    </form>
                </div>

                <div class="report-table">
                    <div class="table-responsive">
                        <table id="reportTable" class="table table-bordered table-striped">
                            <thead>
                                <tr>
                                    <th>अ. क्र.</th>
                                    <th>तालुका</th>
                                    <th>आरोग्य केंद्र नाव</th>
                                    <th>भेट दिनांक</th>
                                    <th>स्थिती</th>
                                    <th>कार्यवाही</th>
                                    <th>फोटो</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php if ($result->num_rows > 0): ?>
                                    <?php $sr_no = 1; ?>
                                    <?php while ($row = $result->fetch_assoc()): ?>
                                        <tr>
                                            <td><?php echo $sr_no++; ?></td>
                                            <td><?php echo htmlspecialchars($row['taluka_name']); ?></td>
                                            <td><?php echo htmlspecialchars($row['phc_name']); ?></td>
                                            <td><?php echo htmlspecialchars($row['visit_date']); ?></td>
                                            <td><?php echo htmlspecialchars($row['status']); ?></td>
                                            <td>
                                                <?php
                                                $visit_status = $row['status'];
                                                $visit_id = $row['visit_id'];
                                                $phc_id_for_form = $row['phc_table_id'];
                                                $visit_date_for_form = $row['visit_date'];

                                                if ($visit_status == 'pending' || $visit_status == 'upcoming') {
                                                    echo '<a href="arogya_inspection_form.php?visit_id=' . $visit_id . '&phc_id=' . $phc_id_for_form . '&visit_date=' . $visit_date_for_form . '" class="btn btn-info btn-sm">उद्दिष्टाची माहिती भरा</a>';
                                                } elseif ($visit_status == 'completed') {
                                                    echo '<a href="view_arogya_kendra_inspection.php?visit_id=' . $visit_id . '" class="btn btn-success btn-sm">रिपोर्ट पहा</a>';
                                                } elseif ($visit_status == 'overdue') {
                                                    echo '<a href="arogya_kendra_inspection_form.php?visit_id=' . $visit_id . '&phc_id=' . $phc_id_for_form . '&visit_date=' . $visit_date_for_form . '" class="btn btn-warning btn-sm">उद्दिष्टाची माहिती भरा (विहित मुदतीबाहेर)</a>';
                                                }
                                                ?>
                                            </td>
                                            <td>
                                                <?php
                                                $photos = json_decode($row['photos_json']);
                                                if (!empty($photos)) {
                                                    foreach ($photos as $photo_path) {
                                                        echo '<a href="' . htmlspecialchars($photo_path) . '" target="_blank" class="btn btn-secondary btn-sm m-1">फोटो</a>';
                                                    }
                                                } else {
                                                    echo 'फोटो नाहीत';
                                                }
                                                ?>
                                            </td>
                                        </tr>
                                    <?php endwhile; ?>
                                <?php else: ?>
                                    <tr>
                                        <td colspan="7">कोणतीही भेट उपलब्ध नाही.</td>
                                    </tr>
                                <?php endif; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>

<?php include('../includes/footer.php'); // Includes all JS scripts and closing </body>, </html> ?>

<script>
    // Initialize DataTables
    $(document).ready(function() { // Ensure jQuery is loaded *before* this script runs
        $('#reportTable').DataTable({
            dom: 'Bfrtip',
            buttons: [
                'excelHtml5',
                'csvHtml5',
                'print'
            ],
            "language": {
                "url": "//cdn.datatables.net/plug-ins/1.10.21/i18n/Marathi.json" // Marathi localization
            }
        });

        // Initialize Flatpickr for date inputs
        flatpickr(".datepicker", {
            dateFormat: "Y-m-d", // MySQL date format
            locale: "mr", // Marathi locale for Flatpickr
        });

        // Removed AJAX logic for dynamic PHC dropdowns.
        // The PHC dropdowns are now populated by PHP on page load.
    });
</script>

</body>
</html>