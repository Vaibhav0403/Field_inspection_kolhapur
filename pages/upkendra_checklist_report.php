<?php
// Include your database configuration
include ("../config/db.php");

// Initialize variables for filters
$selectedTaluka = isset($_GET['taluka']) ? $_GET['taluka'] : '';
$selectedArogyaKendra = isset($_GET['arogya_kendra']) ? $_GET['arogya_kendra'] : '';
$selectedUpkendra = isset($_GET['upkendra']) ? $_GET['upkendra'] : '';
$dateFrom = isset($_GET['date_from']) ? $_GET['date_from'] : '';
$dateTo = isset($_GET['date_to']) ? $_GET['date_to'] : '';
$searchQuery = isset($_GET['search']) ? $_GET['search'] : '';
$entriesPerPage = isset($_GET['entries']) ? (int)$_GET['entries'] : 10;
$currentPage = isset($_GET['page']) ? (int)$_GET['page'] : 1;
$offset = ($currentPage - 1) * $entriesPerPage;

// --- PHP Functions to Fetch Data for Dropdowns ---

function getTalukas($conn) {
    $sql = "SELECT DISTINCT name FROM talukas ORDER BY name";
    $result = $conn->query($sql);
    $talukas = [];
    if ($result && $result->num_rows > 0) {
        while($row = $result->fetch_assoc()) {
            $talukas[] = $row['name'];
        }
    }
    return $talukas;
}

// Function to get Primary Health Centers (Arogya Kendras)
// Assuming 'primary_health_centers' is the table for Arogya Kendras
function getArogyaKendras($conn, $talukaName = '') {
    $sql = "SELECT DISTINCT phc.name, phc.id FROM primary_health_centers phc";
    if ($talukaName) {
        // Get taluka ID first
        $talukaIdResult = $conn->query("SELECT id FROM talukas WHERE name = '" . $conn->real_escape_string($talukaName) . "'");
        if ($talukaIdResult && $talukaIdResult->num_rows > 0) {
            $talukaIdRow = $talukaIdResult->fetch_assoc();
            $talukaId = $talukaIdRow['id'];
            $sql .= " WHERE phc.taluka_id = " . (int)$talukaId;
        } else {
            // If taluka not found, return empty array
            return [];
        }
    }
    $sql .= " ORDER BY phc.name";
    $result = $conn->query($sql);
    $arogyaKendras = [];
    if ($result && $result->num_rows > 0) {
        while($row = $result->fetch_assoc()) {
            $arogyaKendras[] = $row['name'];
        }
    }
    return $arogyaKendras;
}

// Function to get Sub Health Centers (Upkendras)
// Assuming 'sub_health_centers' is the table for Upkendras, and it has phc_id
function getUpkendras($conn, $arogyaKendraName = '') {
    $sql = "SELECT DISTINCT shc.name, shc.id FROM sub_health_centers shc";
    if ($arogyaKendraName) {
        // Get Arogya Kendra (PHC) ID first
        $phcIdResult = $conn->query("SELECT id FROM primary_health_centers WHERE name = '" . $conn->real_escape_string($arogyaKendraName) . "'");
        if ($phcIdResult && $phcIdResult->num_rows > 0) {
            $phcIdRow = $phcIdResult->fetch_assoc();
            $phcId = $phcIdRow['id'];
            $sql .= " WHERE shc.phc_id = " . (int)$phcId;
        } else {
            // If Arogya Kendra not found, return empty array
            return [];
        }
    }
    $sql .= " ORDER BY shc.name";
    $result = $conn->query($sql);
    $upkendras = [];
    if ($result && $result->num_rows > 0) {
        while($row = $result->fetch_assoc()) {
            $upkendras[] = $row['name'];
        }
    }
    return $upkendras;
}


// Fetch dropdown data using the functions. Pass the main $conn variable.
$allTalukas = getTalukas($conn);
$allArogyaKendras = getArogyaKendras($conn, $selectedTaluka);
$allUpkendras = getUpkendras($conn, $selectedArogyaKendra);


// --- Fetch data for the Report Table ---
// NOTE: I'm assuming 'reports' table links to 'primary_health_centers' via health_center_id
// and to 'sub_health_centers' via primary_health_centers_id (which is confusingly named).
// It would be better if reports had `phc_id` and `subcenter_id`.
// --- Fetch data for the Report Table ---
$sqlReport = "SELECT
                r.id AS report_id,
                t.name AS taluka_name,
                phc.name AS arogya_kendra_name,
                shc.name AS upkendra_name,
                r.visit_date,
                r.squad_number,
                r.squad_leader,
                r.status,
                r.photos_json
            FROM
                scheduled_visits r
            LEFT JOIN
                primary_health_centers phc ON r.center_id = phc.id AND r.center_type = 'PHC' -- Corrected: using center_id and center_type
            LEFT JOIN
                sub_health_centers shc ON r.center_id = shc.id AND r.center_type = 'SubCenter' -- Corrected: using center_id and center_type
            LEFT JOIN
                talukas t ON phc.taluka_id = t.id
            WHERE 1=1 "; // Start with 1=1 to easily append conditions

$whereClauses = [];
if ($selectedTaluka) {
    $whereClauses[] = "t.name = '" . $conn->real_escape_string($selectedTaluka) . "'";
}
if ($selectedArogyaKendra) {
    $whereClauses[] = "phc.name = '" . $conn->real_escape_string($selectedArogyaKendra) . "'";
}
if ($selectedUpkendra) {
    $whereClauses[] = "shc.name = '" . $conn->real_escape_string($selectedUpkendra) . "'";
}
if ($dateFrom) {
    $whereClauses[] = "r.visit_date >= '" . $conn->real_escape_string($dateFrom) . "'";
}
if ($dateTo) {
    $whereClauses[] = "r.visit_date <= '" . $conn->real_escape_string($dateTo) . "'";
}
if ($searchQuery) {
    $whereClauses[] = "(t.name LIKE '%" . $conn->real_escape_string($searchQuery) . "%' OR
                        phc.name LIKE '%" . $conn->real_escape_string($searchQuery) . "%' OR
                        shc.name LIKE '%" . $conn->real_escape_string($searchQuery) . "%' OR
                        r.squad_leader LIKE '%" . $conn->real_escape_string($searchQuery) . "%')";
}

if (!empty($whereClauses)) {
    $sqlReport .= " AND " . implode(" AND ", $whereClauses);
}

// Get total count for pagination
$sqlCount = "SELECT COUNT(*) AS total_records FROM (" . $sqlReport . ") AS subquery";
$resultCount = $conn->query($sqlCount);
$totalRecords = 0;
if ($resultCount) {
    $row = $resultCount->fetch_assoc();
    $totalRecords = $row['total_records'];
}
$totalPages = ceil($totalRecords / $entriesPerPage);

$sqlReport .= " ORDER BY r.visit_date DESC LIMIT $offset, $entriesPerPage";

$resultReports = $conn->query($sqlReport);
$reports = [];
if ($resultReports && $resultReports->num_rows > 0) {
    while($row = $resultReports->fetch_assoc()) {
        $reports[] = $row;
    }
}

// Close the connection
$conn->close();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>फिल्ड इन्स्पेक्शन सिस्टीम - उपकेंद्र चेक लिस्ट रिपोर्ट</title>
    <link rel="stylesheet" href="../public/css/upkendra_checklist_report.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css">
    
</head>
<body>

    <?php include("../includes/header.php")?>
    <?php include("../includes/sidebar.php")?>

    <div class="main-layout">
        <div class="dashboard-container">
            <div class="report-filters">
                <h2>उपकेंद्र चेक लिस्ट - रिपोर्ट</h2>
                <form action="" method="GET">
                    <div class="filter-row">
                        <div class="filter-group">
                            <label for="taluka">तालुका निवडा:</label>
                            <select id="taluka" name="taluka" onchange="this.form.submit()">
                                <option value="">सर्व तालुके</option>
                                <?php
                                foreach ($allTalukas as $taluka) {
                                    $selected = ($taluka == $selectedTaluka) ? 'selected' : '';
                                    echo "<option value='" . htmlspecialchars($taluka) . "' $selected>" . htmlspecialchars($taluka) . "</option>";
                                }
                                ?>
                            </select>
                        </div>
                        <div class="filter-group">
                            <label for="arogya_kendra">आरोग्य केंद्र निवडा:</label>
                            <select id="arogya_kendra" name="arogya_kendra" onchange="this.form.submit()">
                                <option value="">सर्व आरोग्य केंद्र</option>
                                <?php
                                foreach ($allArogyaKendras as $ak) {
                                    $selected = ($ak == $selectedArogyaKendra) ? 'selected' : '';
                                    echo "<option value='" . htmlspecialchars($ak) . "' $selected>" . htmlspecialchars($ak) . "</option>";
                                }
                                ?>
                            </select>
                        </div>
                        <div class="filter-group">
                            <label for="upkendra">उपकेंद्र निवडा:</label>
                            <select id="upkendra" name="upkendra" onchange="this.form.submit()">
                                <option value="">सर्व उपकेंद्र</option>
                                <?php
                                foreach ($allUpkendras as $upk) {
                                    $selected = ($upk == $selectedUpkendra) ? 'selected' : '';
                                    echo "<option value='" . htmlspecialchars($upk) . "' $selected>" . htmlspecialchars($upk) . "</option>";
                                }
                                ?>
                            </select>
                        </div>
                        <div class="filter-group">
                            <label for="date_from">पासून:</label>
                            <input type="date" id="date_from" name="date_from" value="<?php echo htmlspecialchars($dateFrom); ?>">
                        </div>
                        <div class="filter-group">
                            <label for="date_to">पर्यंत:</label>
                            <input type="date" id="date_to" name="date_to" value="<?php echo htmlspecialchars($dateTo); ?>">
                        </div>
                        <div class="filter-buttons">
                            <button type="submit" class="btn btn-show"><i class="fas fa-filter"></i> फिल्टर करा</button>
                            <button type="button" class="btn btn-remove" onclick="window.location.href='upkendra_checklist_report.php'"><i class="fas fa-sync-alt"></i> रीसेट करा</button>
                        </div>
                    </div>
                </form>
            </div>

            <?php if (!empty($reports)): // Only show Excel button and table if there are reports ?>
            <a href="export_excel_upkendra.php?<?php echo http_build_query($_GET); ?>" class="btn btn-excel">एक्सेलमध्ये एक्सपोर्ट करा <i class="fas fa-file-excel"></i></a>

            <div style="display: flex; justify-content: space-between; width: 100%; max-width: 1300px; align-items: center; margin-bottom: 15px;">
                <div class="entries-per-page">
                    दाखवा
                    <select name="entries" onchange="window.location.href='?entries=' + this.value + '<?php echo '&' . http_build_query(array_diff_key($_GET, ['entries' => '', 'page' => ''])); ?>'">
                        <option value="10" <?php echo ($entriesPerPage == 10) ? 'selected' : ''; ?>>10</option>
                        <option value="25" <?php echo ($entriesPerPage == 25) ? 'selected' : ''; ?>>25</option>
                        <option value="50" <?php echo ($entriesPerPage == 50) ? 'selected' : ''; ?>>50</option>
                        <option value="100" <?php echo ($entriesPerPage == 100) ? 'selected' : ''; ?>>100</option>
                    </select>
                    एंट्री
                </div>
                <div class="search-box">
                    <label for="search">शोध:</label>
                    <input type="text" id="search" name="search" placeholder="शोध..." value="<?php echo htmlspecialchars($searchQuery); ?>" onkeyup="if(event.key === 'Enter') this.form.submit();">
                </div>
            </div>

            <div class="report-table-container">
                <table>
                    <thead>
                        <tr>
                            <th>अ. क्र.</th>
                            <th>तालुका</th>
                            <th>आरोग्य केंद्र नाव</th>
                            <th>उपकेंद्र नाव</th>
                            <th>भेट दिनांक</th>
                            <th>पथक क्रमांक</th>
                            <th>पथक प्रमुख</th>
                            <th>स्थिती</th> <th>कार्यवाही</th>
                            <th>रिपोर्ट फोटो</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($reports as $index => $report): ?>
                            <tr>
                                <td data-label="अ. क्र."><?php echo $offset + $index + 1; ?></td>
                                <td data-label="तालुका"><?php echo htmlspecialchars($report['taluka_name']); ?></td>
                                <td data-label="आरोग्य केंद्र नाव"><?php echo htmlspecialchars($report['arogya_kendra_name']); ?></td>
                                <td data-label="उपकेंद्र नाव"><?php echo htmlspecialchars($report['upkendra_name']); ?></td>
                                <td data-label="भेट दिनांक"><?php echo htmlspecialchars($report['visit_date']); ?></td>
                                <td data-label="पथक क्रमांक"><?php echo htmlspecialchars($report['squad_number']); ?></td>
                                <td data-label="पथक प्रमुख"><?php echo htmlspecialchars($report['squad_leader']); ?></td>
                                <td data-label="स्थिती"><?php echo htmlspecialchars($report['status']); ?></td> <td data-label="कार्यवाही">
                                    <div class="action-btns">
                                        <button class="btn btn-fill" onclick="window.location.href='upkendra_inspection_form.php?id=<?php echo $report['report_id']; ?>'"><i class="fas fa-edit"></i> उरलेली माहिती भरा</button>
                                        </div>
                                </td>
                                <td data-label="रिपोर्ट फोटो">
                                    <div class="photo-btns">
                                        <?php
                                        // Assuming photo_paths is a comma-separated string of paths
                                        $photoPaths = explode(',', $report['photos_json']);
                                        $photoCount = 0;
                                        foreach ($photoPaths as $photoIndex => $path) {
                                            $trimmedPath = trim($path);
                                            if (!empty($trimmedPath)) { // Check if path is not empty
                                                $photoCount++;
                                                echo "<a href='" . htmlspecialchars($trimmedPath) . "' target='_blank' class='btn'><i class='fas fa-image'></i> फोटो " . $photoCount . "</a>";
                                            }
                                        }
                                        if ($photoCount === 0) {
                                            echo "<span class='no-data'>फोटो नाही</span>";
                                        }
                                        ?>
                                    </div>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>

            <div class="pagination">
                <?php if ($currentPage > 1): ?>
                    <a href="?page=<?php echo $currentPage - 1; ?><?php echo '&' . http_build_query(array_diff_key($_GET, ['page' => ''])); ?>">मागे</a>
                <?php endif; ?>

                <?php for ($i = 1; $i <= $totalPages; $i++): ?>
                    <a href="?page=<?php echo $i; ?><?php echo '&' . http_build_query(array_diff_key($_GET, ['page' => ''])); ?>" class="<?php echo ($i == $currentPage) ? 'current' : ''; ?>"><?php echo $i; ?></a>
                <?php endfor; ?>

                <?php if ($currentPage < $totalPages): ?>
                    <a href="?page=<?php echo $currentPage + 1; ?><?php echo '&' . http_build_query(array_diff_key($_GET, ['page' => ''])); ?>">पुढे</a>
                <?php endif; ?>
            </div>
            <?php else: ?>
                <div class="report-table-container">
                    <table>
                        <thead>
                            <tr>
                                <th>अ. क्र.</th>
                                <th>तालुका</th>
                                <th>आरोग्य केंद्र नाव</th>
                                <th>उपकेंद्र नाव</th>
                                <th>भेट दिनांक</th>
                                <th>पथक क्रमांक</th>
                                <th>पथक प्रमुख</th>
                                <th>स्थिती</th>
                                <th>कार्यवाही</th>
                                <th>रिपोर्ट फोटो</th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr>
                                <td colspan="10" class="no-data">निवडलेल्या फिल्टरसाठी कोणताही डेटा आढळला नाही.</td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            <?php endif; ?>
        </div>
    </div>
    <?php include("../includes/footer.php")?>
</body>
</html>