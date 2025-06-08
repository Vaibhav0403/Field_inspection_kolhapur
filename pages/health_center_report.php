<?php
// health_center_report.php
session_start();
include("../config/db.php"); // Assuming db.php has $conn for mysqli

// --- Initialize variables for dropdowns and form values ---
$talukas = []; // For the report filter dropdown
$primaryHealthCenters = []; // For the report filter dropdown (full list for filtering)

// Form specific dropdown options
$talukas_form_option = [['id' => '', 'name' => 'तालुका निवड']];
$primaryHealthCenters_form_option = [['id' => '', 'taluka_id' => null, 'name' => 'प्राथमिक आरोग्य केंद्र निवडा']];

// Values for the form (for scheduling new visits)
$selectedTalukaId_form = $_POST['taluka_form'] ?? '';
$selectedPHCId_form = $_POST['primary_health_center_form'] ?? '';
$targetDate_form = $_POST['target_date_form'] ?? date('Y-m-d'); // Default to current date

// --- Fetch Talukas and Primary Health Centers for both forms and filters ---
if (isset($conn) && $conn) {
    // Fetch Talukas
    $sql_talukas = "SELECT id, name FROM talukas ORDER BY name ASC";
    $result_talukas = mysqli_query($conn, $sql_talukas);

    if ($result_talukas) {
        $talukas[] = ['id' => '', 'name' => 'सर्व तालुके']; // Default option for report filter
        while ($row = mysqli_fetch_assoc($result_talukas)) {
            $talukas[] = $row; // Add to array for report filter
            $talukas_form_option[] = $row; // Add to array for new visit form
        }
    } else {
        error_log("Error fetching talukas: " . mysqli_error($conn));
        // Add a visible error message if talukas could not be fetched
        $talukas[] = ['id' => '', 'name' => 'त्रुटी: तालुके उपलब्ध नाहीत'];
        $talukas_form_option = [['id' => '', 'name' => 'त्रुटी: तालुके उपलब्ध नाहीत']];
    }

    // Fetch Primary Health Centers
    $sql_phcs = "SELECT id, taluka_id, name FROM primary_health_centers ORDER BY name ASC";
    $result_phcs = mysqli_query($conn, $sql_phcs);

    if ($result_phcs) {
        $primaryHealthCenters[] = ['id' => '', 'taluka_id' => null, 'name' => 'सर्व आरोग्य केंद्र']; // Default for report filter
        while ($row = mysqli_fetch_assoc($result_phcs)) {
            $primaryHealthCenters[] = $row; // Add to array for report filter
            $primaryHealthCenters_form_option[] = $row; // Add to array for new visit form
        }
    } else {
        error_log("Error fetching primary health centers: " . mysqli_error($conn));
        // Add a visible error message if PHCs could not be fetched
        $primaryHealthCenters[] = ['id' => '', 'taluka_id' => null, 'name' => 'त्रुटी: आरोग्य केंद्र उपलब्ध नाही'];
        $primaryHealthCenters_form_option = [['id' => '', 'taluka_id' => null, 'name' => 'त्रुटी: आरोग्य केंद्र उपलब्ध नाही']];
    }
} else {
    // Error handling if DB connection fails (should be caught by db.php die() but good to have fallback)
    error_log("Database connection variable \$conn is not set or is null in health_center_report.php");
    $talukas = [['id' => '', 'name' => 'त्रुटी: DB कनेक्शन नाही']];
    $talukas_form_option = [['id' => '', 'name' => 'त्रुटी: DB कनेक्शन नाही']];
    $primaryHealthCenters = [['id' => '', 'taluka_id' => null, 'name' => 'त्रुटी: DB कनेक्शन नाही']];
    $primaryHealthCenters_form_option = [['id' => '', 'taluka_id' => null, 'name' => 'त्रुटी: DB कनेक्शन नाही']];
}


// --- Form Submission Handling (for scheduling a new visit) ---
$form_message = '';
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['submit_form'])) {
    if (isset($conn) && $conn) { // Ensure $conn is available before using it
        $selectedTalukaId_form = mysqli_real_escape_string($conn, $_POST['taluka_form'] ?? '');
        $selectedPHCId_form = mysqli_real_escape_string($conn, $_POST['primary_health_center_form'] ?? '');
        $targetDate_form = mysqli_real_escape_string($conn, $_POST['target_date_form'] ?? '');

        if (!empty($selectedTalukaId_form) && !empty($selectedPHCId_form) && !empty($targetDate_form)) {
            // Assume 'visits_report' table exists with columns: id, taluka_id, phc_id, visit_date, status, center_type
            $insert_sql = "INSERT INTO visits_report (taluka_id, phc_id, visit_date, status, center_type) VALUES (?, ?, ?, ?, ?)";
            $stmt = mysqli_prepare($conn, $insert_sql); // THIS IS LINE 85 IN THIS FILE
            $status = 'pending'; // Default status for new visit
            $center_type = 'आरोग्य केंद्र'; // Assuming this form schedules PHC visits

            if ($stmt) {
                mysqli_stmt_bind_param($stmt, "iisss", $selectedTalukaId_form, $selectedPHCId_form, $targetDate_form, $status, $center_type);
                if (mysqli_stmt_execute($stmt)) {
                    $form_message = "<div class='success-message'>भेट यशस्वीरित्या शेड्यूल केली.</div>";
                    // Optionally, clear form fields after successful submission
                    $selectedTalukaId_form = '';
                    $selectedPHCId_form = '';
                    $targetDate_form = date('Y-m-d');
                } else {
                    $form_message = "<div class='error-message'>भेट शेड्यूल करताना त्रुटी: " . mysqli_error($conn) . "</div>";
                }
                mysqli_stmt_close($stmt);
            } else {
                $form_message = "<div class='error-message'>Database statement preparation failed: " . mysqli_error($conn) . "</div>";
                error_log("Failed to prepare statement for visit insertion: " . mysqli_error($conn));
            }
        } else {
            $form_message = "<div class='error-message'>कृपया सर्व आवश्यक फील्ड भरा.</div>";
        }
    } else {
        $form_message = "<div class='error-message'>Database connection not available.</div>";
    }
}


// --- Report Filtering Logic ---
$report_data = [];
// Get filter values from GET request (for the report section)
$filter_taluka_id = $_GET['filter_taluka'] ?? '';
$filter_phc_id = $_GET['filter_phc'] ?? '';
$filter_from_date = $_GET['filter_from_date'] ?? '';
$filter_to_date = $_GET['filter_to_date'] ?? '';
$search_query = $_GET['search_input'] ?? ''; // Changed name to match HTML search input ID

if (isset($conn) && $conn) { // Ensure $conn is available before using it
    $sql_report = "SELECT v.id, t.name AS taluka_name, phc.name AS phc_name, v.visit_date, v.status, v.photos
                   FROM visits_report v
                   JOIN talukas t ON v.taluka_id = t.id
                   JOIN primary_health_centers phc ON v.phc_id = phc.id
                   WHERE 1=1"; // Start with a true condition

    // Apply filters
    if (!empty($filter_taluka_id)) {
        $sql_report .= " AND v.taluka_id = '" . mysqli_real_escape_string($conn, $filter_taluka_id) . "'";
    }
    if (!empty($filter_phc_id)) {
        $sql_report .= " AND v.phc_id = '" . mysqli_real_escape_string($conn, $filter_phc_id) . "'";
    }
    if (!empty($filter_from_date)) {
        $sql_report .= " AND v.visit_date >= '" . mysqli_real_escape_string($conn, $filter_from_date) . "'";
    }
    if (!empty($filter_to_date)) {
        $sql_report .= " AND v.visit_date <= '" . mysqli_real_escape_string($conn, $filter_to_date) . "'";
    }
    if (!empty($search_query)) {
        $search_query_esc = mysqli_real_escape_string($conn, $search_query);
        $sql_report .= " AND (t.name LIKE '%$search_query_esc%' OR phc.name LIKE '%$search_query_esc%')";
    }

    $sql_report .= " ORDER BY v.visit_date DESC, t.name ASC"; // Order by date, then taluka name

    $result_report = mysqli_query($conn, $sql_report);
    if ($result_report) {
        while ($row = mysqli_fetch_assoc($result_report)) {
            $report_data[] = $row;
        }
    } else {
        error_log("Report query failed: " . mysqli_error($conn));
        // Add a visible message for query failure
        $report_data = []; // Ensure it's empty
    }
} else {
    // If connection isn't available, report data will be empty
    $report_data = [];
    error_log("Database connection variable \$conn is not set or is null for report fetching.");
}

// Close connection if not used elsewhere, or manage it centrally
// It's often better to keep it open until the end of the script if you have more operations
// mysqli_close($conn);

?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>आरोग्य केंद्र चेक लिस्ट रिपोर्ट</title>
    <link rel="stylesheet" href="../public/css/health_center_report.css">
    </head>
<body>
    <?php include("../includes/header.php"); ?>
    <?php include("../includes/sidebar.php"); ?>

    <div class="main-layout">
        

        <div class="dashboard-container">
            <div class="form-container">
                
                     
            <div class="report-filters">
                <h2>आरोग्य केंद्र चेक लिस्ट रिपोर्ट</h2>
                <form action="" method="GET" id="reportFilterForm">
                    <div class="filter-row">
                        <div class="filter-group">
                            <label for="filter_taluka">तालुका निवड</label>
                            <select id="filter_taluka" name="filter_taluka">
                                <?php foreach ($talukas as $taluka): ?>
                                    <option value="<?= htmlspecialchars($taluka['id']) ?>"
                                        <?= ($filter_taluka_id == $taluka['id']) ? 'selected' : '' ?>>
                                        <?= htmlspecialchars($taluka['name']) ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>

                        <div class="filter-group">
                            <label for="filter_phc">आरोग्य केंद्र निवड</label>
                            <select id="filter_phc" name="filter_phc">
                                <?php foreach ($primaryHealthCenters as $phc): ?>
                                    <option value="<?= htmlspecialchars($phc['id']) ?>"
                                        <?= ($filter_phc_id == $phc['id']) ? 'selected' : '' ?>
                                        <?php if ($phc['taluka_id'] !== null && $phc['taluka_id'] !== '') { echo 'data-taluka-id="' . htmlspecialchars($phc['taluka_id']) . '"'; } ?>
                                    >
                                        <?= htmlspecialchars($phc['name']) ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>

                        <div class="filter-group">
                            <label for="filter_from_date">From:</label>
                            <input type="date" id="filter_from_date" name="filter_from_date"
                                       value="<?= htmlspecialchars($filter_from_date) ?>">
                        </div>

                        <div class="filter-group">
                            <label for="filter_to_date">To:</label>
                            <input type="date" id="filter_to_date" name="filter_to_date"
                                       value="<?= htmlspecialchars($filter_to_date) ?>">
                        </div>

                        <div class="filter-buttons">
                            <button type="submit" class="btn btn-show">Show</button>
                            <button type="button" class="btn btn-remove" onclick="window.location.href='health_center_report.php'">Remove</button>
                        </div>
                    </div>
                    <input type="hidden" id="hidden_search_input" name="search_input" value="<?= htmlspecialchars($search_query) ?>">
                </form>
            </div>

            <div class="report-table-container">
                <a href="#" class="btn btn-excel">Excel</a>
                <div class="search-box">
                    <label for="search_input">शोधा:</label>
                    <input type="text" id="search_input" placeholder="Search..." value="<?= htmlspecialchars($search_query) ?>" onkeyup="updateHiddenSearchAndSubmit(event)">
                </div>

                <table>
                    <thead>
                        <tr>
                            <th>अ. क्र.</th>
                            <th>तालुका</th>
                            <th>आरोग्य केंद्र नाव</th>
                            <th>भेट दिनांक</th>
                            <th>Action</th>
                            <th></th>
                            <th>रिपोर्ट फोटो</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (empty($report_data)): ?>
                            <tr>
                                <td colspan="6" class="no-data">कोणतीही भेट माहिती उपलब्ध नाही.</td>
                            </tr>
                        <?php else: ?>
                            <?php $i = 1; ?>
                            <?php foreach ($report_data as $visit): ?>
                                <tr>
                                    <td><?= $i++ ?></td>
                                    <td><?= htmlspecialchars($visit['taluka_name']) ?></td>
                                    <td><?= htmlspecialchars($visit['phc_name']) ?></td>
                                    <td><?= htmlspecialchars($visit['visit_date']) ?></td>
                                    <td class="action-btns">
                                        <?php
                                            $visit_status = htmlspecialchars($visit['status']);
                                            $current_date = date('Y-m-d');
                                            $visit_date = $visit['visit_date'];

                                            if ($visit_status === 'completed') {
                                                echo '<button class="btn btn-complete">पूर्ण झाले</button>';
                                            } else if ($visit_date < $current_date) {
                                                echo '<button class="btn btn-reset">अपूर्ण</button>'; // Or specific "Overdue" button
                                            } else {
                                                // Link to update form, pass visit ID
                                                echo '<a href="update_visit_info.php?visit_id=' . htmlspecialchars($visit['id']) . '" class="btn btn-fill">उद्दिष्टाची माहिती भरा</a>';
                                            }
                                        ?>
                                    </td>
                                    <td>
                                    <td class="photo-btns">
                                        <?php
                                        // It's best to store photos as JSON array in the database.
                                        // Ensure 'photos' column is TEXT and stores valid JSON like '["path/to/img1.jpg", "path/to/img2.png"]'
                                        $photos = json_decode($visit['photos'] ?? '[]', true);

                                        if (!empty($photos) && is_array($photos)) {
                                            foreach ($photos as $j => $photo_path) {
                                                if (!empty($photo_path)) {
                                                    echo '<a href="' . htmlspecialchars($photo_path) . '" target="_blank" class="btn">फोटो ' . ($j + 1) . '</a>';
                                                }
                                            }
                                        } else {
                                            echo '<span>फोटो उपलब्ध नाही</span>';
                                        }
                                        ?>
                                    </td>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </tbody>
                </table>
                <div class="pagination">
                    </div>
            </div>

        </div>
    </div>

   

    <script>
        document.addEventListener('DOMContentLoaded', function() {
            // --- Form PHC Filtering Logic (for scheduling new visits) ---
            const talukaSelectForm = document.getElementById('taluka_form');
            const phcSelectForm = document.getElementById('primary_health_center_form');
            const phcOptionsForm = Array.from(phcSelectForm.querySelectorAll('option')); // Convert to array for easy iteration

            function filterPhcOptionsForm() {
                const selectedTalukaId = talukaSelectForm.value;
                let hasValidSelection = false;

                phcOptionsForm.forEach(option => {
                    const optionTalukaId = option.getAttribute('data-taluka-id');

                    // Always show the default "प्राथमिक आरोग्य केंद्र निवडा" option
                    if (option.value === '') {
                        option.style.display = '';
                        option.disabled = false;
                        if (phcSelectForm.value === '') { // Keep default selected if it was
                            hasValidSelection = true;
                        }
                    }
                    // If a taluka is selected, show only PHCs belonging to that taluka
                    else if (selectedTalukaId !== '' && optionTalukaId === selectedTalukaId) {
                        option.style.display = '';
                        option.disabled = false;
                        if (option.value === phcSelectForm.value) { // Keep current PHC selected if valid
                            hasValidSelection = true;
                        }
                    }
                    // Otherwise, hide and disable the option
                    else {
                        option.style.display = 'none';
                        option.disabled = true;
                    }
                });

                // If the currently selected PHC is no longer visible/valid, reset to default
                if (!hasValidSelection && phcSelectForm.value !== '') {
                    phcSelectForm.value = '';
                }
            }
            filterPhcOptionsForm(); // Call initially to apply filter based on pre-selected value
            talukaSelectForm.addEventListener('change', filterPhcOptionsForm);


            // --- Report PHC Filtering Logic (for the report table) ---
            const talukaSelectFilter = document.getElementById('filter_taluka');
            const phcSelectFilter = document.getElementById('filter_phc');
            const phcOptionsFilter = Array.from(phcSelectFilter.querySelectorAll('option'));
            const initialSelectedPHCFilter = '<?= htmlspecialchars($filter_phc_id) ?>'; // Capture initial filter PHC ID

            function filterPhcOptionsFilter() {
                const selectedTalukaId = talukaSelectFilter.value;
                let foundInitialSelected = false; // Flag to check if the initially selected PHC is still available

                phcOptionsFilter.forEach(option => {
                    const optionTalukaId = option.getAttribute('data-taluka-id');

                    // Always show "सर्व आरोग्य केंद्र" option
                    if (option.value === '') {
                        option.style.display = '';
                        option.disabled = false;
                        if (option.value === initialSelectedPHCFilter) {
                             phcSelectFilter.value = initialSelectedPHCFilter;
                             foundInitialSelected = true;
                        }
                    }
                    // Show if it matches the selected taluka
                    else if (selectedTalukaId !== '' && optionTalukaId === selectedTalukaId) {
                        option.style.display = '';
                        option.disabled = false;
                        if (option.value === initialSelectedPHCFilter) {
                            phcSelectFilter.value = initialSelectedPHCFilter;
                            foundInitialSelected = true;
                        }
                    }
                    // Hide otherwise
                    else {
                        option.style.display = 'none';
                        option.disabled = true;
                    }
                });

                // After filtering, if the initially selected PHC is no longer visible,
                // or if a new taluka is selected and the previous PHC doesn't belong to it,
                // reset the PHC filter to the default "सर्व आरोग्य केंद्र" (empty value).
                if (selectedTalukaId !== '' && !foundInitialSelected) {
                    phcSelectFilter.value = ''; // Reset to default "सर्व आरोग्य केंद्र"
                } else if (selectedTalukaId === '' && phcSelectFilter.value !== initialSelectedPHCFilter && initialSelectedPHCFilter !== '') {
                    // If no taluka selected, and the current PHC filter is not the initial one (if any)
                    // and initial was not empty, reset it to empty for "सर्व आरोग्य केंद्र"
                    phcSelectFilter.value = '';
                }
            }

            filterPhcOptionsFilter(); // Call initially to apply filter based on pre-selected value
            talukaSelectFilter.addEventListener('change', filterPhcOptionsFilter);

            // --- Search Functionality (Submit on Enter) ---
            function updateHiddenSearchAndSubmit(event) {
                const searchInput = document.getElementById('search_input');
                const hiddenSearchInput = document.getElementById('hidden_search_input');
                const reportFilterForm = document.getElementById('reportFilterForm');

                // Update the hidden input that's part of the GET form
                hiddenSearchInput.value = searchInput.value;

                // Submit the form only on Enter key press
                if (event.key === 'Enter') {
                    reportFilterForm.submit();
                }
            }

        });
    </script>
</body>
 <?php include("../includes/footer.php"); ?>
</html>