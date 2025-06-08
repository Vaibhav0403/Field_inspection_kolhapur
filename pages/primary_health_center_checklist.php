<?php
// report_page.php
session_start();
$currentPage = 'health_center_report';

include("../config/db.php");

function getDistinctTalukas($conn) {
    $talukas = [];
    if (!$conn) {
        error_log("DB connection not available in getDistinctTalukas.");
        return $talukas;
    }
    try {
        // Assuming 'taluka' column exists in 'health_reports' for distinct talukas
        $sql = "SELECT DISTINCT taluka FROM health_reports ORDER BY taluka ASC";
        $result = mysqli_query($conn, $sql);

        if ($result) {
            while ($row = mysqli_fetch_assoc($result)) {
                $talukas[] = $row['taluka'];
            }
            mysqli_free_result($result);
        } else {
            error_log("MySQLi Query Error (getDistinctTalukas): " . mysqli_error($conn));
        }
    } catch (Exception $e) {
        error_log("Error in getDistinctTalukas: " . $e->getMessage());
    }
    return $talukas;
}

// --- Function to fetch distinct Health Centers from primary_health_center table (using MySQLi) ---
function getDistinctHealthCenters($conn) {
    $healthCenters = [];
    if (!$conn) {
        error_log("DB connection not available in getDistinctHealthCenters.");
        return $healthCenters;
    }
    try {
        $sql = "SELECT id, name FROM primary_health_center ORDER BY name ASC";
        $result = mysqli_query($conn, $sql);

        if ($result) {
            while ($row = mysqli_fetch_assoc($result)) {
                $healthCenters[] = $row;
            }
            mysqli_free_result($result);
        } else {
            error_log("MySQLi Query Error (getDistinctHealthCenters): " . mysqli_error($conn));
        }
    } catch (Exception $e) {
        error_log("Error in getDistinctHealthCenters: " . $e->getMessage());
    }
    return $healthCenters;
}

// --- Function to fetch report data with JOIN (using MySQLi) ---
function getReportData($conn, $filters = []) {
    $data = [];
    if (!$conn) {
        error_log("DB connection not available in getReportData.");
        return $data;
    }
    try {
        $sql = "SELECT
                    hr.sr_no,
                    hr.taluka,
                    phc.name AS health_center_name,
                    hr.visit_date,
                    hr.status,
                    hr.photos_json
                FROM
                    health_reports hr
                JOIN
                    primary_health_center phc ON hr.health_center_id = phc.id
                WHERE
                    1=1 ";

        $params = [];
        $param_types = "";

        if (!empty($filters['taluka'])) {
            $sql .= " AND hr.taluka = ?";
            $params[] = $filters['taluka'];
            $param_types .= "s";
        }
        if (!empty($filters['health_center_id'])) {
            $sql .= " AND hr.health_center_id = ?";
            $params[] = $filters['health_center_id'];
            $param_types .= "i"; // 'i' for integer ID
        }
        if (!empty($filters['from_date'])) {
            $sql .= " AND hr.visit_date >= ?";
            $params[] = $filters['from_date'];
            $param_types .= "s"; // 's' for string date
        }
        if (!empty($filters['to_date'])) {
            $sql .= " AND hr.visit_date <= ?";
            $params[] = $filters['to_date'];
            $param_types .= "s"; // 's' for string date
        }
        if (!empty($filters['search_query'])) {
            $sql .= " AND (hr.taluka LIKE ? OR phc.name LIKE ?)";
            $params[] = '%' . $filters['search_query'] . '%';
            $params[] = '%' . $filters['search_query'] . '%';
            $param_types .= "ss"; // 'ss' for two string parameters
        }

        $sql .= " ORDER BY hr.visit_date DESC"; // Add ORDER BY always

        // Implement LIMIT and OFFSET for pagination if needed, based on entries_per_page
        // For simplicity, it's not included in this core query, but you would add it here.
        // Example: $sql .= " LIMIT ? OFFSET ?";
        // $params[] = (int)$filters['limit']; $param_types .= "i";
        // $params[] = (int)$filters['offset']; $param_types .= "i";


        $stmt = mysqli_prepare($conn, $sql);

        if ($stmt) {
            if (!empty($params)) {
                mysqli_stmt_bind_param($stmt, $param_types, ...$params);
            }
            mysqli_stmt_execute($stmt);
            $result = mysqli_stmt_get_result($stmt);

            if ($result) {
                while ($row = mysqli_fetch_assoc($result)) {
                    $row['photos'] = json_decode($row['photos_json'], true) ?: [];
                    unset($row['photos_json']);
                    $data[] = $row;
                }
                mysqli_free_result($result);
            } else {
                error_log("MySQLi Get Result Error (getReportData): " . mysqli_error($conn));
            }
            mysqli_stmt_close($stmt);
        } else {
            error_log("MySQLi Prepare Error (getReportData): " . mysqli_error($conn));
        }
    } catch (Exception $e) {
        error_log("Error in getReportData: " . $e->getMessage());
    }
    return $data;
}


// Get filter values from GET request
$selectedTaluka = $_GET['taluka'] ?? '';
$selectedHealthCenterId = $_GET['health_center'] ?? '';
$fromDate = $_GET['from_date'] ?? '';
$toDate = $_GET['to_date'] ?? '';
$searchQuery = $_GET['search_query'] ?? '';
$entriesPerPage = $_GET['entries_per_page'] ?? '10';

// Prepare filters for the data fetch function
$filters = [
    'taluka'           => $selectedTaluka,
    'health_center_id' => $selectedHealthCenterId,
    'from_date'        => $fromDate,
    'to_date'          => $toDate,
    'search_query'     => $searchQuery,
    'limit'            => (int)$entriesPerPage // Used if you implement pagination
    // 'offset' will be calculated based on current page for pagination
];

// Fetch data for dropdowns and report data using the global $conn
$talukaOptions = getDistinctTalukas($conn);
$healthCenterOptions = getDistinctHealthCenters($conn);
$reportData = getReportData($conn, $filters); // Pass the connection and filters
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Document</title>
     <link rel="stylesheet" href="../public/css/primary_health_center_checklist.css">
</head>
<body>
     <?php
    include("../includes/header.php")
    ?>
        <?php include("../includes/sidebar.php"); ?>
     <div class="main-layout">
    
        <div class="dashboard-container">
            <div class="report-filters">
                <h2>आरोग्य केंद्र चेक लिस्ट- रिपोर्ट</h2>
                <form action="" method="GET">
                    <div class="filter-row">
                        <div class="filter-group">
                            <label for="taluka">तालुका निवड:</label>
                            <select id="taluka" name="taluka">
                                <option value="">सर्व तालुके</option>
                                <?php foreach ($talukaOptions as $taluka): ?>
                                    <option value="<?php echo htmlspecialchars($taluka); ?>"
                                        <?php echo ($selectedTaluka == $taluka) ? 'selected' : ''; ?>>
                                        <?php echo htmlspecialchars($taluka); ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="filter-group">
                            <label for="health-center">आरोग्य केंद्र निवड:</label>
                            <select id="health-center" name="health_center"> <option value="">आरोग्य केंद्र</option>
                                <?php foreach ($healthCenterOptions as $center): ?>
                                    <option value="<?php echo htmlspecialchars($center['id']); ?>"
                                        <?php echo ($selectedHealthCenterId == $center['id']) ? 'selected' : ''; ?>>
                                        <?php echo htmlspecialchars($center['name']); ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="filter-group">
                            <label for="from-date">From:</label>
                            <input type="date" id="from-date" name="from_date"
                                value="<?php echo htmlspecialchars($fromDate); ?>">
                        </div>
                        <div class="filter-group">
                            <label for="to-date">To:</label>
                            <input type="date" id="to-date" name="to_date"
                                value="<?php echo htmlspecialchars($toDate); ?>">
                        </div>
                        <div class="filter-buttons">
                            <button type="submit" class="btn btn-show">Show</button>
                            <button type="reset" class="btn btn-remove">Remove</button>
                        </div>
                    </div>
                    <input type="hidden" name="entries_per_page" value="<?php echo htmlspecialchars($entriesPerPage); ?>">
                    <input type="hidden" name="search_query" value="<?php echo htmlspecialchars($searchQuery); ?>">
                </form>
            </div>

            <div class="report-table-container">
                <a href="#" class="btn-excel">Excel</a>
                <div class="entries-per-page">
                    <label for="entries">Show</label>
                    <select id="entries" name="entries_per_page" onchange="this.form.submit()"> <option value="10" <?php echo ($entriesPerPage == '10') ? 'selected' : ''; ?>>10</option>
                        <option value="25" <?php echo ($entriesPerPage == '25') ? 'selected' : ''; ?>>25</option>
                        <option value="50" <?php echo ($entriesPerPage == '50') ? 'selected' : ''; ?>>50</option>
                        <option value="100" <?php echo ($entriesPerPage == '100') ? 'selected' : ''; ?>>100</option>
                    </select>
                    <span>entries</span>
                </div>
                <div class="search-box">
                    <label for="search">शोध:</label>
                    <input type="text" id="search" placeholder="Search..." name="search_query"
                        value="<?php echo htmlspecialchars($searchQuery); ?>" onkeyup="if(event.key === 'Enter') this.form.submit();"> </div>
                <table>
                    <thead>
                        <tr>
                            <th>अ. क्र.</th>
                            <th>तालुका</th>
                            <th>आरोग्य केंद्र नाव</th>
                            <th>भेट दिनांक</th>
                            <th>Action</th>
                            <th>रिपोर्ट फोटो</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (!empty($reportData)): ?>
                            <?php foreach ($reportData as $row): ?>
                                <tr>
                                    <td data-label="अ. क्र."><?php echo htmlspecialchars($row['sr_no']); ?></td>
                                    <td data-label="तालुका"><?php echo htmlspecialchars($row['taluka']); ?></td>
                                    <td data-label="आरोग्य केंद्र नाव"><?php echo htmlspecialchars($row['health_center_name']); ?></td>
                                    <td data-label="भेट दिनांक"><?php echo htmlspecialchars($row['visit_date']); ?></td>
                                    <td data-label="Action" class="action-btns">
                                        <?php
                                            $status = isset($row['status']) ? $row['status'] : 'pending';
                                            if ($status == 'pending' || $status == 'overdue'):
                                        ?>
                                            <button class="btn-fill">उद्दीष्टये माहिती भरा</button>
                                        <?php elseif ($status == 'completed'): ?>
                                            <button class="btn-complete">उद्दीष्टये माहिती भरा</button>
                                        <?php else: ?>
                                            <button class="btn-fill">उद्दीष्टये माहिती भरा</button>
                                        <?php endif; ?>
                                    </td>
                                    <td data-label="रिपोर्ट फोटो" class="photo-btns">
                                        <?php if (!empty($row['photos'])): ?>
                                            <?php foreach ($row['photos'] as $photo): ?>
                                                <a href="/path/to/photos/<?php echo htmlspecialchars($photo); ?>" target="_blank" class="btn">फोटो</a>
                                            <?php endforeach; ?>
                                        <?php endif; ?>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        <?php else: ?>
                            <tr>
                                <td colspan="6" class="no-data">No data found for the selected filters.</td>
                            </tr>
                        <?php endif; ?>
                    </tbody>
                </table>
                <div class="pagination">
                    <a href="#">&laquo; Previous</a>
                    <span class="current">1</span>
                    <a href="#">2</a>
                    <a href="#">3</a>
                    <a href="#">Next &raquo;</a>
                </div>
            </div>
        </div>
        </body>
     <?php
    include("../includes/footer.php")
    ?>

</html>