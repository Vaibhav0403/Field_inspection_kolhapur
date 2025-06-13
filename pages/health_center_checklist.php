<?php
session_start();
include_once("../config/db.php"); // Assuming db.php contains your database connection logic

// Initialize variables for dropdowns and form values
$talukas = [];
$primaryHealthCenters = [];
$selectedTalukaId = '';
$selectedPHCId = '';
$targetDate = date('Y-m-d'); // Default to today's date

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
        error_log("No talukas found or query failed in health_center_checklist.php (talukas): " . mysqli_error($conn));
    }

    // Fetch Primary Health Centers from database (all of them for client-side filtering)
    $sql_phcs = "SELECT id, taluka_id, name FROM primary_health_centers ORDER BY name ASC";
    $result_phcs = mysqli_query($conn, $sql_phcs);

    if ($result_phcs && mysqli_num_rows($result_phcs) > 0) {
        $primaryHealthCenters[] = ['id' => '', 'taluka_id' => '', 'name' => 'प्राथमिक आरोग्य केंद्र निवडा']; // Default "Select PHC" option
        while ($row = mysqli_fetch_assoc($result_phcs)) {
            $primaryHealthCenters[] = $row;
        }
    } else {
        $primaryHealthCenters[] = ['id' => '', 'taluka_id' => '', 'name' => 'प्राथमिक आरोग्य केंद्र उपलब्ध नाही']; // Fallback
        error_log("No primary health centers found or query failed in health_center_checklist.php (PHCs): " . mysqli_error($conn));
    }

} else {
    // Handle DB connection error if $conn is not set or not a valid mysqli object
    $_SESSION['message'] = "त्रुटी: डेटाबेस कनेक्शन उपलब्ध नाही.";
    $_SESSION['msg_type'] = "danger";
    error_log("Database connection failed in health_center_checklist.php");
}

// Process form submission here
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Retrieve form data
    $selectedTalukaId = $_POST['taluka'] ?? '';
    $selectedPHCId = $_POST['primary_health_center'] ?? ''; // This is the PHC ID
    $targetDate = $_POST['target_date'] ?? date('Y-m-d');

    // --- Database Insertion Logic into `scheduled_visits` table ---
    if (isset($conn) && $conn) {
        // Prepare variables for insertion into scheduled_visits
        $centerType = 'PHC'; // This form is specifically for PHC visits
        $status = 'pending'; // Default status for a newly scheduled visit

        // Use prepared statements for security and efficiency
        $stmt = $conn->prepare("INSERT INTO scheduled_visits (taluka_id, center_id, center_type, visit_date, status, created_at) VALUES (?, ?, ?, ?, ?, NOW())");

        if ($stmt) {
            $stmt->bind_param("iisss", $selectedTalukaId, $selectedPHCId, $centerType, $targetDate, $status);

            if ($stmt->execute()) {
                $_SESSION['message'] = "नवीन आरोग्य केंद्र भेट यशस्वीरित्या शेड्यूल केली गेली!";
                $_SESSION['msg_type'] = "success";
                // Optionally, clear form fields after successful submission
                $selectedTalukaId = '';
                $selectedPHCId = '';
                $targetDate = date('Y-m-d');
            } else {
                $_SESSION['message'] = "त्रुटी: भेट शेड्यूल करताना डेटाबेस त्रुटी: " . $stmt->error;
                $_SESSION['msg_type'] = "danger";
                error_log("Failed to insert into scheduled_visits: " . $stmt->error);
            }
            $stmt->close();
        } else {
            $_SESSION['message'] = "त्रुटी: स्टेटमेंट तयार करण्यात अयशस्वी: " . $conn->error;
            $_SESSION['msg_type'] = "danger";
            error_log("Failed to prepare statement: " . $conn->error);
        }
    }
}
?>

<!DOCTYPE html>
<html lang="mr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>आरोग्य केंद्र चेक लिस्ट</title>
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
            <h5>आरोग्य केंद्र चेक लिस्ट - नवीन भेट शेड्यूल करा</h5>

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
                    <div class="form-group col-md-6"> <label for="taluka">तालुका*</label>
                        <select class="form-control" id="taluka" name="taluka" required>
                            <?php foreach ($talukas as $taluka): ?>
                                <option value="<?= htmlspecialchars($taluka['id']) ?>"
                                    <?= ($selectedTalukaId == $taluka['id']) ? 'selected' : '' ?>>
                                    <?= htmlspecialchars($taluka['name']) ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>

                    <div class="form-group col-md-6"> <label for="primary_health_center">प्राथमिक आरोग्य केंद्र*</label>
                        <select class="form-control" id="primary_health_center" name="primary_health_center" required>
                            <?php foreach ($primaryHealthCenters as $phc): ?>
                                <option value="<?= htmlspecialchars($phc['id']) ?>"
                                    <?= ($selectedPHCId == $phc['id']) ? 'selected' : '' ?>
                                    <?php if (!empty($phc['taluka_id'])) { echo 'data-taluka-id="' . htmlspecialchars($phc['taluka_id']) . '"'; } ?>
                                >
                                    <?= htmlspecialchars($phc['name']) ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                </div>

                <div class="form-row">
                    <div class="form-group col-md-6"> <label for="target_date">उद्दिष्ट दिनांक *</label>
                        <input type="text" class="form-control datepicker" id="target_date" name="target_date"
                            value="<?= htmlspecialchars($targetDate) ?>" required>
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

    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const talukaSelect = document.getElementById('taluka');
            const phcSelect = document.getElementById('primary_health_center');
            // Convert NodeList to Array for easier manipulation (especially if using filter/map later)
            const phcOptions = Array.from(phcSelect.querySelectorAll('option'));

            function filterPhcOptions() {
                const selectedTalukaId = talukaSelect.value;

                phcOptions.forEach(option => {
                    const optionTalukaId = option.getAttribute('data-taluka-id');

                    // Always show the default "Select PHC" option (value="")
                    if (option.value === '') {
                        option.style.display = '';
                        option.disabled = false;
                    }
                    // If a taluka is selected, show only PHCs belonging to that taluka
                    else if (selectedTalukaId !== '' && optionTalukaId === selectedTalukaId) {
                        option.style.display = '';
                        option.disabled = false;
                    }
                    // If no taluka is selected, or it doesn't match, hide it
                    else {
                        option.style.display = 'none';
                        option.disabled = true; // Disable hidden options so they can't be selected
                    }
                });

                // If the currently selected PHC is now hidden, reset the PHC selection to the default
                const currentSelectedPHCOption = phcSelect.options[phcSelect.selectedIndex];
                if (currentSelectedPHCOption && currentSelectedPHCOption.style.display === 'none') {
                    phcSelect.value = ''; // Reset to default "प्राथमिक आरोग्य केंद्र निवडा"
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

            // Initial filter on page load in case a value was pre-selected by PHP (e.g., after form submission)
            filterPhcOptions();

            // Add event listener for when taluka selection changes
            talukaSelect.addEventListener('change', filterPhcOptions);

            // You might want to initialize DataTables if there were tables on this page
            // $('#someTable').DataTable();
        });
    </script>
</body>
</html>