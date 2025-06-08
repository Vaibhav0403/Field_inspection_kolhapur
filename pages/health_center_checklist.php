<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Heath_center_checklist</title>
    <link rel="stylesheet" href="../public/css/health_center_checklist.css">
</head>
<body>
    <?php
    include("../includes/header.php");
    include("../includes/sidebar.php");
    ?>
    <div class="main-layout">
        <div class="form-container">
        <h2>आरोग्य केंद्र चेक लिस्ट</h2>

        <?php
        // PHP for handling form submission (if any) and fetching data
        include_once("../config/db.php"); // Assuming db.php has $conn for mysqli or PDO

        // Initialize variables for dropdowns
        $talukas = [];
        $primaryHealthCenters = [];

        // Fetch Talukas from database
        if (isset($conn) && $conn) { // Ensure connection exists and is valid
            $sql_talukas = "SELECT id, name FROM talukas ORDER BY name ASC";
            $result_talukas = mysqli_query($conn, $sql_talukas);

            if ($result_talukas && mysqli_num_rows($result_talukas) > 0) {
                $talukas[] = ['id' => '', 'name' => 'तालुका निवड']; // Add default option
                while ($row = mysqli_fetch_assoc($result_talukas)) {
                    $talukas[] = $row;
                }
            } else {
                $talukas[] = ['id' => '', 'name' => 'तालुका उपलब्ध नाही']; // Fallback if no talukas found
                error_log("No talukas found or query failed: " . mysqli_error($conn));
            }

            // Fetch Primary Health Centers from database
            $sql_phcs = "SELECT id, taluka_id, name FROM primary_health_centers ORDER BY name ASC";
            $result_phcs = mysqli_query($conn, $sql_phcs);

            if ($result_phcs && mysqli_num_rows($result_phcs) > 0) {
                $primaryHealthCenters[] = ['id' => '', 'taluka_id' => null, 'name' => 'प्राथमिक आरोग्य केंद्र निवडा']; // Add default option
                while ($row = mysqli_fetch_assoc($result_phcs)) {
                    $primaryHealthCenters[] = $row;
                }
            } else {
                $primaryHealthCenters[] = ['id' => '', 'taluka_id' => null, 'name' => 'प्राथमिक आरोग्य केंद्र उपलब्ध नाही']; // Fallback
                error_log("No primary health centers found or query failed: " . mysqli_error($conn));
            }

            // Close connection if not used elsewhere, or manage it centrally in db.php
            // mysqli_close($conn); // Only close if this is the only script using $conn
        } else {
            // Handle DB connection error if $conn is not set or not a valid mysqli object
            $talukas[] = ['id' => '', 'name' => 'त्रुटी: DB कनेक्शन नाही'];
            $primaryHealthCenters[] = ['id' => '', 'taluka_id' => null, 'name' => 'त्रुटी: DB कनेक्शन नाही'];
            error_log("Database connection failed in health_center_checklist.php");
        }


        // Initialize variables for form values (for sticky form after submission)
        $selectedTalukaId = '';
        $selectedPHCId = '';
        $targetDate = date('Y-m-d'); // Default to today's date

        // Variable to hold success/error messages
        $submissionMessage = '';

        // Process form submission here
        if ($_SERVER["REQUEST_METHOD"] == "POST") {
            // Retrieve form data
            $selectedTalukaId = $_POST['taluka'] ?? '';
            $selectedPHCId = $_POST['primary_health_center'] ?? '';
            $targetDate = $_POST['target_date'] ?? date('Y-m-d');

            // --- Database Insertion Logic ---
            if (isset($conn) && $conn) {
                // 1. Sanitize input to prevent SQL injection
                $sanitizedTalukaId = mysqli_real_escape_string($conn, $selectedTalukaId);
                $sanitizedPHCId = mysqli_real_escape_string($conn, $selectedPHCId);
                $sanitizedTargetDate = mysqli_real_escape_string($conn, $targetDate);

                // Assuming you have a table named 'health_center_checklist_data'
                // and columns 'taluka_id', 'phc_id', 'target_date', and 'created_at'
                $sql_insert = "INSERT INTO health_center_checklist_data (taluka_id, phc_id, target_date, created_at)
                               VALUES ('$sanitizedTalukaId', '$sanitizedPHCId', '$sanitizedTargetDate', NOW())";

                if (mysqli_query($conn, $sql_insert)) {
                    // Set a success message to be displayed in the main content
                    $submissionMessage = "<div style='background-color: #d4edda; border: 1px solid #c3e6cb; padding: 10px; border-radius: 5px; margin-bottom: 20px; color: #155724;'>";
                    $submissionMessage .= "<h3>डेटा यशस्वीरित्या सबमिट केला!</h3>"; // Data submitted successfully!
                    $submissionMessage .= "</div>";

                    // Optionally, clear the form fields by resetting the variables for sticky form behavior
                    $selectedTalukaId = '';
                    $selectedPHCId = '';
                    $targetDate = date('Y-m-d');

                } else {
                    // Set an error message to be displayed in the main content
                    $submissionMessage = "<div style='background-color: #f8d7da; border: 1px solid #f5c6cb; padding: 10px; border-radius: 5px; margin-bottom: 20px; color: #721c24;'>";
                    $submissionMessage .= "<h3>डेटा सबमिट करताना त्रुटी:</h3>"; // Error submitting data
                    $submissionMessage .= "<p>" . mysqli_error($conn) . "</p>"; // Display MySQL error for debugging
                    $submissionMessage .= "</div>";
                    error_log("Database insert failed: " . mysqli_error($conn));
                }
            } else {
                // Set an error message for DB connection issues
                $submissionMessage = "<div style='background-color: #f8d7da; border: 1px solid #f5c6cb; padding: 10px; border-radius: 5px; margin-bottom: 20px; color: #721c24;'>";
                $submissionMessage .= "<h3>त्रुटी: डेटाबेस कनेक्शन उपलब्ध नाही.</h3>"; // Error: Database connection not available.
                $submissionMessage .= "</div>";
            }
            // --- End Database Insertion Logic ---
        }

        // Display the submission message if set
        echo $submissionMessage;

        // Display success message if redirected from a previous successful submission (though not using header() now)
        // This block is kept here for completeness in case you re-introduce redirect with GET parameter later.
        if (isset($_GET['success']) && $_GET['success'] == 'true') {
            echo "<div style='background-color: #d4edda; border: 1px solid #c3e6cb; padding: 10px; border-radius: 5px; margin-bottom: 20px; color: #155724;'>";
            echo "<h3>डेटा यशस्वीरित्या सबमिट केला!</h3>"; // Data submitted successfully! (from GET parameter)
            echo "</div>";
        }
        ?>

        <form action="" method="POST">
            <div class="form-row">
                <div class="form-group">
                    <label for="taluka">तालुका*</label>
                    <select id="taluka" name="taluka" required>
                        <?php foreach ($talukas as $taluka): ?>
                            <option value="<?= htmlspecialchars($taluka['id']) ?>"
                                <?= ($selectedTalukaId == $taluka['id']) ? 'selected' : '' ?>>
                                <?= htmlspecialchars($taluka['name']) ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <div class="form-group">
                    <label for="primary_health_center">प्राथमिक आरोग्य केंद्र</label>
                    <select id="primary_health_center" name="primary_health_center" required>
                        <?php foreach ($primaryHealthCenters as $phc): ?>
                            <option value="<?= htmlspecialchars($phc['id']) ?>"
                                <?= ($selectedPHCId == $phc['id']) ? 'selected' : '' ?>
                                <?php if ($phc['taluka_id'] !== null && $phc['taluka_id'] !== '') { echo 'data-taluka-id="' . htmlspecialchars($phc['taluka_id']) . '"'; } ?>
                            >
                                <?= htmlspecialchars($phc['name']) ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>
            </div>

            <div class="form-row">
                <div class="form-group">
                    <label for="target_date">उद्दिष्ट दिनांक *</label>
                    <input type="date" id="target_date" name="target_date"
                            value="<?= htmlspecialchars($targetDate) ?>" required>
                </div>
            </div>

            <div class="btn-group">
                <button type="submit" class="btn btn-submit">सबमिट</button>
                <button type="reset" class="btn btn-reset">रीसेट</button>
            </div>
        </form>
    </div>
    </div>
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const talukaSelect = document.getElementById('taluka');
            const phcSelect = document.getElementById('primary_health_center');
            const phcOptions = phcSelect.querySelectorAll('option');

            function filterPhcOptions() {
                const selectedTalukaId = talukaSelect.value;

                phcOptions.forEach(option => {
                    const optionTalukaId = option.getAttribute('data-taluka-id');

                    // If it's the default "Select PHC" option (which has no data-taluka-id or empty)
                    // Or if no taluka is selected, show it.
                    if (optionTalukaId === null || optionTalukaId === '' || selectedTalukaId === '') {
                        option.style.display = '';
                        option.disabled = false; // Ensure it's enabled
                    }
                    // Show if it matches the selected taluka
                    else if (optionTalukaId === selectedTalukaId) {
                        option.style.display = '';
                        option.disabled = false; // Ensure it's enabled
                    }
                    // Hide if it doesn't match
                    else {
                        option.style.display = 'none';
                        option.disabled = true; // Disable hidden options
                    }
                });

                // If the currently selected PHC is now hidden, reset the PHC selection
                if (phcSelect.selectedIndex > 0 && phcSelect.options[phcSelect.selectedIndex].style.display === 'none') {
                    phcSelect.value = ''; // Reset to default "Select PHC"
                }
            }

            // Initial filter on page load in case a value was pre-selected by PHP (e.g., after form submission)
            filterPhcOptions();

            // Add event listener for when taluka selection changes
            talukaSelect.addEventListener('change', filterPhcOptions);
        });
    </script>
    <?php
    include("../includes/footer.php")
    ?>
</body>
</html>
