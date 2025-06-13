<?php
session_start();
include_once("../config/db.php"); // Assuming db.php contains your database connection logic

// Initialize variables for dropdowns and form values
$talukas = [];
$primaryHealthCenters = [];
$subHealthCenters = [];
$selectedTalukaId = '';
$selectedPhcId = '';
$selectedSubHealthCenterId = '';
$targetDate = date('Y-m-d'); // Default to today's date

// Only pre-fill squad_number and squad_leader if they were previously submitted and there was an error
$squadNumber = $_POST['squad_number'] ?? '';
$squadLeader = $_POST['squad_leader'] ?? '';

// Fetch Talukas from database
if (isset($conn) && $conn) {
    $sql_talukas = "SELECT id, name FROM talukas ORDER BY name ASC";
    $result_talukas = mysqli_query($conn, $sql_talukas);

    if ($result_talukas && mysqli_num_rows($result_talukas) > 0) {
        $talukas[] = ['id' => '', 'name' => 'तालुका निवड']; // Default "Select Taluka" option
        while ($row = mysqli_fetch_assoc($result_talukas)) {
            $talukas[] = $row;
        }
    } else {
        $talukas[] = ['id' => '', 'name' => 'तालुका उपलब्ध नाही']; // Fallback if no talukas found
        error_log("No talukas found or query failed in sub_health_center_checklist.php (talukas): " . mysqli_error($conn));
    }

    // Fetch Primary Health Centers from database (all for client-side filtering)
    $sql_phcs = "SELECT id, taluka_id, name FROM primary_health_centers ORDER BY name ASC";
    $result_phcs = mysqli_query($conn, $sql_phcs);

    if ($result_phcs && mysqli_num_rows($result_phcs) > 0) {
        $primaryHealthCenters[] = ['id' => '', 'taluka_id' => '', 'name' => 'प्राथमिक आरोग्य केंद्र निवडा']; // Default option
        while ($row = mysqli_fetch_assoc($result_phcs)) {
            $primaryHealthCenters[] = $row;
        }
    } else {
        $primaryHealthCenters[] = ['id' => '', 'taluka_id' => '', 'name' => 'प्राथमिक आरोग्य केंद्र उपलब्ध नाही']; // Fallback
        error_log("No primary_health_centers found or query failed in sub_health_center_checklist.php (phcs): " . mysqli_error($conn));
    }

    // Fetch Sub Health Centers from database (all for client-side filtering)
    $sql_shcs = "SELECT id, taluka_id, phc_id, name FROM sub_health_centers ORDER BY name ASC";
    $result_shcs = mysqli_query($conn, $sql_shcs);

    if ($result_shcs && mysqli_num_rows($result_shcs) > 0) {
        $subHealthCenters[] = ['id' => '', 'taluka_id' => '', 'phc_id' => '', 'name' => 'उपकेंद्र निवडा']; // Default option
        while ($row = mysqli_fetch_assoc($result_shcs)) {
            $subHealthCenters[] = $row;
        }
    } else {
        $subHealthCenters[] = ['id' => '', 'taluka_id' => '', 'phc_id' => '', 'name' => 'उपकेंद्र उपलब्ध नाही']; // Fallback
        error_log("No sub_health_centers found or query failed in sub_health_center_checklist.php (shcs): " . mysqli_error($conn));
    }

} else {
    // Handle DB connection error if $conn is not set or not a valid mysqli object
    $_SESSION['message'] = "त्रुटी: डेटाबेस कनेक्शन उपलब्ध नाही.";
    $_SESSION['msg_type'] = "danger";
    error_log("Database connection failed in sub_health_center_checklist.php");
}

// Process form submission
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Retrieve form data
    $selectedTalukaId = $_POST['taluka'] ?? '';
    $selectedPhcId = $_POST['primary_health_center'] ?? '';
    $selectedSubHealthCenterId = $_POST['sub_health_center'] ?? '';
    $targetDate = $_POST['target_date'] ?? date('Y-m-d');
    $squadNumber = $_POST['squad_number'] ?? '';
    $squadLeader = $_POST['squad_leader'] ?? '';

    // Basic validation: ensure all required fields are filled
    if (empty($selectedTalukaId) || empty($selectedPhcId) || empty($selectedSubHealthCenterId) || empty($targetDate) || empty($squadNumber) || empty($squadLeader)) {
        $_SESSION['message'] = "कृपया सर्व आवश्यक फील्ड भरा.";
        $_SESSION['msg_type'] = "danger";
        // Do NOT clear selected IDs here, so dropdowns retain state on error
    } else {
        // --- Database Insertion Logic into `visits` table ---
        if (isset($conn) && $conn) {
            $centerType = 'SubCenter'; // This form is specifically for Sub Health Center visits
            $status = 'pending'; // Default status for a newly scheduled visit

            // Use prepared statements for security and efficiency
            // Inserting into 'visits' table. 'health_center_id' will store the ID of the selected sub_health_center.
            $stmt = $conn->prepare("INSERT INTO scheduled_visits (taluka_id, center_id, center_type, visit_date, status, squad_number, squad_leader, created_at) VALUES (?, ?, ?, ?, ?, ?, ?, NOW())");

            if ($stmt) {
                // 'i' for integer (taluka_id, health_center_id), 's' for string (center_type, visit_date, status, squad_number, squad_leader)
                $stmt->bind_param("iisssss", $selectedTalukaId, $selectedSubHealthCenterId, $centerType, $targetDate, $status, $squadNumber, $squadLeader);

                if ($stmt->execute()) {
                    $_SESSION['message'] = "नवीन उपकेंद्र भेट यशस्वीरित्या शेड्यूल केली गेली!";
                    $_SESSION['msg_type'] = "success";
                    // Clear form fields AFTER successful submission
                    $selectedTalukaId = '';
                    $selectedPhcId = '';
                    $selectedSubHealthCenterId = '';
                    $targetDate = date('Y-m-d');
                    $squadNumber = '';
                    $squadLeader = '';
                } else {
                    $_SESSION['message'] = "त्रुटी: भेट शेड्यूल करताना डेटाबेस त्रुटी: " . $stmt->error;
                    $_SESSION['msg_type'] = "danger";
                    error_log("Failed to insert into visits: " . $stmt->error);
                }
                $stmt->close();
            } else {
                $_SESSION['message'] = "त्रुटी: स्टेटमेंट तयार करण्यात अयशस्वी: " . $conn->error;
                $_SESSION['msg_type'] = "danger";
                error_log("Failed to prepare statement: " . $conn->error);
            }
        }
    }
}
?>

<!DOCTYPE html>
<html lang="mr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>उपकेंद्र चेक लिस्ट</title>
    <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css">
    <link rel="stylesheet" href="../public/css/health_center_checklist.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/flatpickr/dist/flatpickr.min.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/flatpickr/dist/themes/material_blue.css">
</head>
<body>
    <?php include("../includes/header.php"); ?>
    <?php include("../includes/sidebar.php"); ?>

    <div class="main-layout">
        <div class="form-container">
            <h5>उपकेंद्र चेक लिस्ट</h5>

            <?php
            // Display submission message (success or error)
            if (isset($_SESSION['message'])): ?>
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

            <form action="" method="POST">
                <div class="form-row">
                    <div class="form-group col-md-6">
                        <label for="taluka">तालुका*</label>
                        <select class="form-control" id="taluka" name="taluka" required>
                            <?php foreach ($talukas as $taluka): ?>
                                <option value="<?= htmlspecialchars($taluka['id']) ?>"
                                    <?= ($selectedTalukaId == $taluka['id']) ? 'selected' : '' ?>>
                                    <?= htmlspecialchars($taluka['name']) ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>

                    <div class="form-group col-md-6">
                        <label for="primary_health_center">प्राथमिक आरोग्य केंद्र*</label>
                        <select class="form-control" id="primary_health_center" name="primary_health_center" required>
                            <?php foreach ($primaryHealthCenters as $phc): ?>
                                <option value="<?= htmlspecialchars($phc['id']) ?>"
                                    <?= ($selectedPhcId == $phc['id']) ? 'selected' : '' ?>
                                    <?php if (!empty($phc['taluka_id'])) { echo 'data-taluka-id="' . htmlspecialchars($phc['taluka_id']) . '"'; } ?>
                                >
                                    <?= htmlspecialchars($phc['name']) ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                </div>

                <div class="form-row">
                    <div class="form-group col-md-6">
                        <label for="sub_health_center">उपकेंद्र*</label>
                        <select class="form-control" id="sub_health_center" name="sub_health_center" required>
                            <?php foreach ($subHealthCenters as $shc): ?>
                                <option value="<?= htmlspecialchars($shc['id']) ?>"
                                    <?= ($selectedSubHealthCenterId == $shc['id']) ? 'selected' : '' ?>
                                    <?php if (!empty($shc['taluka_id'])) { echo 'data-taluka-id="' . htmlspecialchars($shc['taluka_id']) . '"'; } ?>
                                    <?php if (!empty($shc['phc_id'])) { echo 'data-phc-id="' . htmlspecialchars($shc['phc_id']) . '"'; } ?>
                                >
                                    <?= htmlspecialchars($shc['name']) ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>

                    <div class="form-group col-md-6">
                        <label for="target_date">उद्दीष्ट दिनांक *</label>
                        <input type="text" class="form-control datepicker" id="target_date" name="target_date"
                            value="<?= htmlspecialchars($targetDate) ?>" required>
                    </div>
                </div>

                <div class="form-row">
                    <div class="form-group col-md-6">
                        <label for="squad_number">पथक क्रमांक *</label>
                        <input type="text" class="form-control" id="squad_number" name="squad_number"
                               placeholder="पथक क्रमांक" value="<?= htmlspecialchars($squadNumber) ?>" required>
                    </div>
                    <div class="form-group col-md-6">
                        <label for="squad_leader">पथक प्रमुख *</label>
                        <input type="text" class="form-control" id="squad_leader" name="squad_leader"
                               placeholder="पथक प्रमुख" value="<?= htmlspecialchars($squadLeader) ?>" required>
                    </div>
                </div>

                <div class="btn-group">
                    <button type="submit" class="btn btn-primary">सबमिट</button>
                    <button type="reset" class="btn btn-secondary">रीसेट</button>
                </div>
            </form>
        </div>
    </div>

    <?php include("../includes/footer.php"); ?>

    <script src="https://cdn.jsdelivr.net/npm/flatpickr"></script>
    <script src="https://cdn.jsdelivr.net/npm/flatpickr/dist/l10n/mr.js"></script> <script>
        document.addEventListener('DOMContentLoaded', function() {
            const talukaSelect = document.getElementById('taluka');
            const phcSelect = document.getElementById('primary_health_center');
            const subHealthCenterSelect = document.getElementById('sub_health_center');

            // Store original options for filtering
            const phcOptions = Array.from(phcSelect.querySelectorAll('option'));
            const subHealthCenterOptions = Array.from(subHealthCenterSelect.querySelectorAll('option'));

            function filterPhcOptions() {
                const selectedTalukaId = talukaSelect.value;
                console.log('Filtering PHCs. Selected Taluka ID:', selectedTalukaId); // Debugging

                phcOptions.forEach(option => {
                    const optionTalukaId = option.getAttribute('data-taluka-id');

                    // Always show the default "Select PHC" option (value="")
                    if (option.value === '') {
                        option.style.display = '';
                        option.disabled = false;
                    }
                    // Show if a taluka is selected and matches the PHC's taluka_id
                    else if (selectedTalukaId !== '' && optionTalukaId === selectedTalukaId) {
                        option.style.display = '';
                        option.disabled = false;
                    }
                    // Hide options that don't match
                    else {
                        option.style.display = 'none';
                        option.disabled = true;
                    }
                });

                // If the currently selected PHC is now hidden, reset the PHC selection to the default
                const currentSelectedPhcOption = phcSelect.options[phcSelect.selectedIndex];
                if (currentSelectedPhcOption && currentSelectedPhcOption.style.display === 'none' && currentSelectedPhcOption.value !== '') {
                    phcSelect.value = ''; // Reset to default only if the selected option is now hidden AND not the default itself
                }
                // After filtering PHCs, always re-filter Sub Health Centers
                filterSubHealthCenterOptions();
            }

            function filterSubHealthCenterOptions() {
                const selectedTalukaId = talukaSelect.value;
                const selectedPhcId = phcSelect.value;
                console.log('Filtering Sub Health Centers. Selected Taluka ID:', selectedTalukaId, 'Selected PHC ID:', selectedPhcId); // Debugging

                subHealthCenterOptions.forEach(option => {
                    const optionTalukaId = option.getAttribute('data-taluka-id');
                    const optionPhcId = option.getAttribute('data-phc-id');

                    // Always show the default "Select Sub Health Center" option (value="")
                    if (option.value === '') {
                        option.style.display = '';
                        option.disabled = false;
                    }
                    // Condition for showing a Sub Health Center:
                    // Both Taluka and PHC must be selected AND match the option's data attributes.
                    else if (selectedTalukaId !== '' && selectedPhcId !== '' &&
                             optionTalukaId === selectedTalukaId && optionPhcId === selectedPhcId) {
                        option.style.display = '';
                        option.disabled = false;
                    }
                    // Hide options that don't match the current Taluka/PHC selections
                    else {
                        option.style.display = 'none';
                        option.disabled = true;
                    }
                });

                // If the currently selected Sub Health Center is now hidden, reset the selection to the default
                const currentSelectedSubHealthCenterOption = subHealthCenterSelect.options[subHealthCenterSelect.selectedIndex];
                if (currentSelectedSubHealthCenterOption && currentSelectedSubHealthCenterOption.style.display === 'none' && currentSelectedSubHealthCenterOption.value !== '') {
                    subHealthCenterSelect.value = ''; // Reset to default only if the selected option is now hidden AND not the default itself
                }
            }

            // Initialize Flatpickr for the date input
            flatpickr("#target_date", {
                dateFormat: "Y-m-d", // MySQL date format
                locale: "mr", // Marathi locale for Flatpickr
                altInput: true, // Show user-friendly date in input
                altFormat: "F j, Y", // e.g., "June 10, 2025"
                defaultDate: "<?= htmlspecialchars($targetDate) ?>"
            });

            // Initial filtering on page load
            // This will ensure correct state if PHP pre-selected values or on fresh page load.
            filterPhcOptions(); // This will also call filterSubHealthCenterOptions internally.

            // Add event listeners for when selections change
            talukaSelect.addEventListener('change', filterPhcOptions);
            phcSelect.addEventListener('change', filterSubHealthCenterOptions);
        });
    </script>
</body>
</html>