<?php
// upkendra_inspection_form.php

// 1. Include your database connection file
// Make sure this file (e.g., db_connect.php) establishes a connection to your MySQL database
// and sets up a $conn variable.
include("../config/db.php"); // Adjust path as needed

// 2. Get the record ID (e.g., from the URL)
// $inspection_id = null;
// if (isset($_GET['inspection_id'])) {
//     $inspection_id = intval($_GET['inspection_id']); // Sanitize input as integer
// } else {
//     // Handle the case where inspection_id is not provided
//     // In a real application, you might redirect to a list page or show a more user-friendly error.
//     echo "<p style='color: red;'>त्रुटी: तपासणी आयडी उपलब्ध नाही.</p>"; // Error: Inspection ID not available.
//     exit();
// }

// Initialize variables with default empty values to prevent "undefined variable" errors
$inspection_date_display = '';
$expected_inspection_date_display = '';
$phc_name = '';
$subcenter_name = '';

// 3. Fetch Data from Database
if ($inspection_id) {
    // This SQL query is an example. You'll need to adjust it based on your
    // actual table names and column names.
    // Assuming 'upkendra_scheduled_visits' table for subcenter visits,
    // and it links to 'phc_details' and 'subcenter_details' tables.
    $sql = "SELECT
                usv.visit_date,
                usv.expected_inspection_date_display,
                phc.phc_name,
                sc.subcenter_name
            FROM
                scheduled_visits usv
            LEFT JOIN
                phc_details phc ON usv.phc_id = phc.id
            LEFT JOIN
                subcenter_details sc ON usv.subcenter_id = sc.id
            WHERE
                usv.id = ?";

    $stmt = $conn->prepare($sql);
    if ($stmt === false) {
        die('MySQL prepare error: ' . $conn->error);
    }
    $stmt->bind_param("i", $inspection_id);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows > 0) {
        $row = $result->fetch_assoc();
        $inspection_date_display = htmlspecialchars($row['inspection_date_display']);
        $expected_inspection_date_display = htmlspecialchars($row['expected_inspection_date_display']);
        $phc_name = htmlspecialchars($row['phc_name']);
        $subcenter_name = htmlspecialchars($row['subcenter_name']);
    } else {
        echo "<p style='color: red;'>त्रुटी: दिलेल्या आयडीसाठी तपासणी डेटा आढळला नाही: " . htmlspecialchars($inspection_id) . "</p>"; // Error: No inspection data found for the given ID.
        exit();
    }

    $stmt->close();
}

// Close database connection (if not handled by db_connect.php on script end)
// $conn->close();
?>

<!DOCTYPE html>
<html lang="mr"> <head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>आरोग्य उपकेंद्र तपासणी फॉर्म</title>
    <style>
      body {
    font-family: 'Arial Unicode MS', Arial, sans-serif;
    background-color: #f0f2f5;
    margin: 0;
    display: flex; /* Use flexbox on body to manage sidebar and main content */
    min-height: 100vh; /* Ensure body takes full viewport height */
    flex-direction: column; /* Stack children vertically initially */
}
.main-layout {
    display: flex;
    padding-left: 250px; /* Space for the sidebar's width */
    padding-top: 80px;   /* Space for the fixed header's height */
    padding-right: 20px; /* Add some padding on the right side for overall layout */
    box-sizing: border-box;
   
}
       .container {
    background-color: #fff;
    padding: 30px;
    border-radius: 8px;
    box-shadow: 0 4px 12px rgba(0, 0, 0, 0.1);
    width: 100%; /* This will now take 100% of the available width in .main-content */
    max-width: 1300px; /* Keep this if you want a max-width for the form itself */
    box-sizing: border-box;
    margin: 0 auto; 
    /* Center the form within the main content area */
}
.global-header { /* You might need to check the actual class in header.php */
    position: fixed; /* Fix it to the top */
    top: 0;
    left: 0; /* Align to left edge */
    width: 100%; /* Take full width of the screen */
    height: 80px; /* Example height for your header */
    background-color: #007bff; /* Example background */
    color: white;
    z-index: 1000; /* Ensure it stays on top of other content */
    padding: 15px 20px; /* Adjust padding as needed */
    box-shadow: 0 2px 5px rgba(0,0,0,0.2);
    display: flex;
    justify-content: space-between;
    align-items: center;
    box-sizing: border-box; /* Include padding in width/height */
}

/* Sidebar (from ../includes/sidebar.php) */
/* Assuming sidebar.php renders a div with a class like 'sidebar' */
.sidebar { /* You might need to check the actual class in sidebar.php */
    position: fixed;
    top: 0; /* Start from top of the viewport */
    left: 0;
    width: 250px; /* Width of your sidebar */
    height: 100vh; /* Full viewport height */
    background-color: #343a40; /* Example background */
    color: white;
    z-index: 900; /* Below header, above main content */
    padding-top: 80px; /* Push content down to clear the global header */
    box-sizing: border-box;
    overflow-y: auto; /* Enable scrolling if sidebar content is long */
}

/* Page Wrapper - New addition to manage the space for main content */
.page-wrapper {
    display: flex; /* Use flexbox to contain main-content */
    flex-grow: 1; /* Allow it to take available vertical space */
    margin-left: 250px; /* Space for the fixed sidebar */
    margin-top: 80px; /* Space for the fixed global header */
    padding: 20px; /* General padding for content inside the wrapper */
    box-sizing: border-box; /* Include padding in dimensions */
    width: calc(100% - 250px); /* Adjust width to account for sidebar */
    /* If you prefer, you can use:
       width: 100%;
       and then rely on main-content's width and margin.
       But this explicitly sets the available area. */
    overflow-y: auto; /* Allow scrolling for the main content area */
}

        h2, h3 {
            color: #0056b3;
            border-bottom: 2px solid #eee;
            padding-bottom: 10px;
            margin-bottom: 20px;
        }
        .section-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            background-color: #e9e9e9;
            padding: 10px 15px;
            border-radius: 5px;
            margin-bottom: 10px;
            cursor: pointer;
            font-weight: bold;
            color: #333;
            border: 1px solid #ddd;
        }
        .section-content {
            padding: 15px;
            border: 1px solid #ddd;
            border-top: none;
            border-radius: 0 0 5px 5px;
            margin-bottom: 20px;
            display: none; /* Hidden by default */
        }
        .section-content.active {
            display: block; /* Shown when active */
        }
        .form-group {
            margin-bottom: 15px;
        }
        .form-group label {
            display: block;
            margin-bottom: 5px;
            font-weight: bold;
        }
        .form-group input[type="radio"] {
            margin-right: 5px;
        }
        .form-group label.radio-label { /* Styling for radio button labels */
            display: inline-block;
            font-weight: normal;
            margin-right: 15px;
        }
        .meta-info p {
            margin: 5px 0;
            font-size: 1.1em;
        }
        .btn-container {
            text-align: center;
            margin-top: 30px;
        }
        .btn-submit, .btn-reset {
            padding: 10px 20px;
            border: none;
            border-radius: 5px;
            cursor: pointer;
            font-size: 16px;
            font-weight: bold;
            transition: background-color 0.3s ease;
            margin: 0 10px;
        }
        .btn-submit {
            background-color: #007bff;
            color: #ffffff;
        }
        .btn-submit:hover {
            background-color: #0056b3;
        }
        .btn-reset {
            background-color: #6c757d;
            color: #ffffff;
        }
        .btn-reset:hover {
            background-color: #5a6268;
        }
        .camera-section {
            border: 1px solid #ddd;
            padding: 20px;
            border-radius: 8px;
            margin-top: 30px;
            background-color: #f9f9f9;
        }
        .camera-button {
            display: block;
            width: 100%;
            padding: 10px;
            background-color: #28a745;
            color: white;
            border: none;
            border-radius: 5px;
            cursor: pointer;
            font-size: 1.1em;
            margin-top: 10px;
            transition: background-color 0.3s ease;
        }
        .camera-button:hover {
            background-color: #218838;
        }
        .photo-preview {
            margin-top: 10px;
            border: 1px solid #ccc;
            padding: 10px;
            text-align: center;
            min-height: 50px; /* Placeholder for preview */
            background-color: #eee;
        }
    </style>
</head>
<body>
    <?php include("../includes/header.php")?>
    <?php include("../includes/sidebar.php")?>
    <div class="main-layout">
    <div class="container">
        <h2>आरोग्य उपकेंद्र तपासणी सुची</h2>
        <div class="meta-info">
            <p><strong>आरोग्य उपकेंद्राची तपासणी सुची (ID: <?php echo htmlspecialchars($inspection_id); ?>)</strong></p>
            <p><strong>भेटीचा दिनांक:</strong> <?php echo $inspection_date_display; ?></p>
            <p><strong>अपेक्षित भेटीचा दिनांक:</strong> <?php echo $expected_inspection_date_display; ?></p>
            <p><strong>प्राथमिक आरोग्य केंद्र:</strong> <?php echo $phc_name; ?></p>
            <p><strong>उपकेंद्र:</strong> <?php echo $subcenter_name; ?></p>
        </div>

        <form action="save_inspection_data.php" method="POST" enctype="multipart/form-data">
            <input type="hidden" name="inspection_id" value="<?php echo htmlspecialchars($inspection_id); ?>">
            <input type="hidden" name="inspection_date_display" value="<?php echo $inspection_date_display; ?>">
            <input type="hidden" name="expected_inspection_date_display" value="<?php echo $expected_inspection_date_display; ?>">
            <input type="hidden" name="phc_name" value="<?php echo $phc_name; ?>">
            <input type="hidden" name="subcenter_name" value="<?php echo $subcenter_name; ?>">

            <div class="section">
                <div class="section-header" onclick="toggleSection(this)">
                    <span>1. आरोग्य उपकेंद्र दिशादर्शक फलक, बाह्य सुशोभीकरण आणि परिसर</span>
                    <span class="toggle-icon">+</span>
                </div>
                <div class="section-content">
                    <div class="form-group">
                        <label>१.संरक्षित भिंत *</label>
                        <label class="radio-label"><input type="radio" name="s1_protected_wall" value="होय" required> होय</label>
                        <label class="radio-label"><input type="radio" name="s1_protected_wall" value="नाही"> नाही</label>
                    </div>
                    <div class="form-group">
                        <label>२. प्रवेशद्वार *</label>
                        <p style="margin-left: 20px;">लोखंडी दरवाजा:</p>
                        <label class="radio-label" style="margin-left: 40px;"><input type="radio" name="s1_iron_gate" value="होय" required> होय</label>
                        <label class="radio-label"><input type="radio" name="s1_iron_gate" value="नाही"> नाही</label>
                        <p style="margin-left: 20px;">कमान व नामफलक:</p>
                        <label class="radio-label" style="margin-left: 40px;"><input type="radio" name="s1_arch_nameboard" value="होय" required> होय</label>
                        <label class="radio-label"><input type="radio" name="s1_arch_nameboard" value="नाही"> नाही</label>
                    </div>
                    <div class="form-group">
                        <label>३. दिशादर्शक फलक *</label>
                        <label class="radio-label"><input type="radio" name="s1_direction_board" value="होय" required> होय</label>
                        <label class="radio-label"><input type="radio" name="s1_direction_board" value="नाही"> नाही</label>
                    </div>
                    <div class="form-group">
                        <label>४. इमारतीचे प्लास्टर/रंगरंगोटी *</label>
                        <label class="radio-label"><input type="radio" name="s1_building_plaster_paint" value="होय" required> होय</label>
                        <label class="radio-label"><input type="radio" name="s1_building_plaster_paint" value="नाही"> नाही</label>
                    </div>
                </div>
            </div>

            <div class="section">
                <div class="section-header" onclick="toggleSection(this)">
                    <span>2. आरोग्य उपकेंद्रात उपलब्ध असलेल्या सेवा</span>
                    <span class="toggle-icon">+</span>
                </div>
                <div class="section-content">
                    <div class="form-group">
                        <label>ओपीडी सेवा *</label>
                        <label class="radio-label"><input type="radio" name="s2_opd" value="उपलब्ध" required> उपलब्ध</label>
                        <label class="radio-label"><input type="radio" name="s2_opd" value="उपलब्ध नाही"> उपलब्ध नाही</label>
                    </div>
                     <div class="form-group">
                        <label>२४x७ प्रसूती सेवा *</label>
                        <label class="radio-label"><input type="radio" name="s2_delivery_24_7" value="उपलब्ध" required> उपलब्ध</label>
                        <label class="radio-label"><input type="radio" name="s2_delivery_24_7" value="उपलब्ध नाही"> उपलब्ध नाही</label>
                    </div>
                    <div class="form-group">
                        <label>मोफत आरोग्य सेवा *</label>
                        <label class="radio-label"><input type="radio" name="s2_free_health_services" value="उपलब्ध" required> उपलब्ध</label>
                        <label class="radio-label"><input type="radio" name="s2_free_health_services" value="उपलब्ध नाही"> उपलब्ध नाही</label>
                    </div>
                </div>
            </div>

            <div class="section">
                <div class="section-header" onclick="toggleSection(this)">
                    <span>3. अंतर्गत व बाह्य स्वच्छता</span>
                    <span class="toggle-icon">+</span>
                </div>
                <div class="section-content">
                    <div class="form-group">
                        <label>अंतर्गत स्वच्छता *</label>
                        <label class="radio-label"><input type="radio" name="s3_internal_cleanliness" value="व्यवस्थित" required> व्यवस्थित</label>
                        <label class="radio-label"><input type="radio" name="s3_internal_cleanliness" value="अव्यवस्थित"> अव्यवस्थित</label>
                    </div>
                    <div class="form-group">
                        <label>परिसर स्वच्छता *</label>
                        <label class="radio-label"><input type="radio" name="s3_campus_cleanliness" value="व्यवस्थित" required> व्यवस्थित</label>
                        <label class="radio-label"><input type="radio" name="s3_campus_cleanliness" value="अव्यवस्थित"> अव्यवस्थित</label>
                    </div>
                    <div class="form-group">
                        <label>प्रसूती कक्ष दररोज स्वच्छ *</label>
                        <label class="radio-label"><input type="radio" name="s3_delivery_room_daily_clean" value="होय" required> होय</label>
                        <label class="radio-label"><input type="radio" name="s3_delivery_room_daily_clean" value="नाही"> नाही</label>
                        <label class="radio-label"><input type="radio" name="s3_delivery_room_daily_clean" value="लागू नाही"> लागू नाही</label>
                    </div>
                     <div class="form-group">
                        <label>स्वच्छता उपकरणे उपलब्ध *</label>
                        <label class="radio-label"><input type="radio" name="s3_cleaning_tools_availability" value="होय" required> होय</label>
                        <label class="radio-label"><input type="radio" name="s3_cleaning_tools_availability" value="नाही"> नाही</label>
                    </div>
                </div>
            </div>

            <div class="section">
                <div class="section-header" onclick="toggleSection(this)">
                    <span>4. व्यवस्थापन</span>
                    <span class="toggle-icon">+</span>
                </div>
                <div class="section-content">
                    <div class="form-group">
                        <label>विद्युत फिटिंग आणि प्रकाश व्यवस्था *</label>
                        <label class="radio-label"><input type="radio" name="s4_electric_fitting" value="व्यवस्थित" required> व्यवस्थित</label>
                        <label class="radio-label"><input type="radio" name="s4_electric_fitting" value="अव्यवस्थित"> अव्यवस्थित</label>
                    </div>
                     <div class="form-group">
                        <label>भिंतींना तडे/गळती *</label>
                        <label class="radio-label"><input type="radio" name="s4_cracked_leaking_walls" value="होय" required> होय</label>
                        <label class="radio-label"><input type="radio" name="s4_cracked_leaking_walls" value="नाही"> नाही</label>
                    </div>
                     <div class="form-group">
                        <label>दरवाजे आणि खिडक्यांची स्थिती *</label>
                        <label class="radio-label"><input type="radio" name="s4_doors_windows_condition" value="व्यवस्थित" required> व्यवस्थित</label>
                        <label class="radio-label"><input type="radio" name="s4_doors_windows_condition" value="अव्यवस्थित"> अव्यवस्थित</label>
                    </div>
                    <div class="form-group">
                        <label>छताची स्वच्छता *</label>
                        <label class="radio-label"><input type="radio" name="s4_ceiling_cleaning" value="व्यवस्थित" required> व्यवस्थित</label>
                        <label class="radio-label"><input type="radio" name="s4_ceiling_cleaning" value="अव्यवस्थित"> अव्यवस्थित</label>
                    </div>
                    <div class="form-group">
                        <label>नळ आणि पाईपची स्थिती *</label>
                        <label class="radio-label"><input type="radio" name="s4_water_tap_pipe_condition" value="व्यवस्थित" required> व्यवस्थित</label>
                        <label class="radio-label"><input type="radio" name="s4_water_tap_pipe_condition" value="अव्यवस्थित"> अव्यवस्थित</label>
                    </div>
                </div>
            </div>

            <div class="section">
                <div class="section-header" onclick="toggleSection(this)">
                    <span>5. रुग्ण तपासणी कक्ष</span>
                    <span class="toggle-icon">+</span>
                </div>
                <div class="section-content">
                    <div class="form-group">
                        <label>खुर्च्या/टेबल उपलब्ध *</label>
                        <label class="radio-label"><input type="radio" name="s5_chair_table" value="होय" required> होय</label>
                        <label class="radio-label"><input type="radio" name="s5_chair_table" value="नाही"> नाही</label>
                    </div>
                    <div class="form-group">
                        <label>रुग्ण तपासणी टेबल/स्टूल/पडदा *</label>
                        <label class="radio-label"><input type="radio" name="s5_patient_table_stool_curtain" value="होय" required> होय</label>
                        <label class="radio-label"><input type="radio" name="s5_patient_table_stool_curtain" value="नाही"> नाही</label>
                    </div>
                    <div class="form-group">
                        <label>संस्थेचा नकाशा *</label>
                        <label class="radio-label"><input type="radio" name="s5_institution_map" value="उपलब्ध" required> उपलब्ध</label>
                        <label class="radio-label"><input type="radio" name="s5_institution_map" value="उपलब्ध नाही"> उपलब्ध नाही</label>
                    </div>
                    <div class="form-group">
                        <label>आरोग्य निर्देशकांचा चार्ट *</label>
                        <label class="radio-label"><input type="radio" name="s5_health_indicators_chart" value="उपलब्ध" required> उपलब्ध</label>
                        <label class="radio-label"><input type="radio" name="s5_health_indicators_chart" value="उपलब्ध नाही"> उपलब्ध नाही</label>
                    </div>
                    <div class="form-group">
                        <label>आरोग्य सेवा वेळापत्रक *</label>
                        <label class="radio-label"><input type="radio" name="s5_health_services_schedule" value="उपलब्ध" required> उपलब्ध</label>
                        <label class="radio-label"><input type="radio" name="s5_health_services_schedule" value="उपलब्ध नाही"> उपलब्ध नाही</label>
                    </div>
                    <div class="form-group">
                        <label>अद्ययावित EDD/EPD चार्ट *</label>
                        <label class="radio-label"><input type="radio" name="s5_updated_edd_epd_chart" value="उपलब्ध" required> उपलब्ध</label>
                        <label class="radio-label"><input type="radio" name="s5_updated_edd_epd_chart" value="उपलब्ध नाही"> उपलब्ध नाही</label>
                    </div>
                    <div class="form-group">
                        <label>निमोनिया/सेप्सिस/अतिसारासाठी प्रोटोकॉल *</label>
                        <label class="radio-label"><input type="radio" name="s5_pneumonia_sepsis_diarrhea_protocols" value="होय" required> होय</label>
                        <label class="radio-label"><input type="radio" name="s5_pneumonia_sepsis_diarrhea_protocols" value="नाही"> नाही</label>
                    </div>
                    <div class="form-group">
                        <label>निमोनिया/सेप्सिस/अतिसारासाठी औषधे *</label>
                        <label class="radio-label"><input type="radio" name="s5_pneumonia_sepsis_diarrhea_medicines" value="होय" required> होय</label>
                        <label class="radio-label"><input type="radio" name="s5_pneumonia_sepsis_diarrhea_medicines" value="नाही"> नाही</label>
                    </div>
                </div>
            </div>

            <div class="section">
                <div class="section-header" onclick="toggleSection(this)">
                    <span>6. मनुष्यबळ उपलब्धता</span>
                    <span class="toggle-icon">+</span>
                </div>
                <div class="section-content">
                    <div class="form-group">
                        <label>महिला आरोग्य कर्मचारी *</label>
                        <label class="radio-label"><input type="radio" name="s6_female_health_worker" value="उपलब्ध" required> उपलब्ध</label>
                        <label class="radio-label"><input type="radio" name="s6_female_health_worker" value="उपलब्ध नाही"> उपलब्ध नाही</label>
                        <label for="s6_female_health_worker_count">संख्या:</label>
                        <input type="number" id="s6_female_health_worker_count" name="s6_female_health_worker_count" min="0">
                    </div>
                     <div class="form-group">
                        <label>पुरुष आरोग्य कर्मचारी *</label>
                        <label class="radio-label"><input type="radio" name="s6_male_health_worker" value="उपलब्ध" required> उपलब्ध</label>
                        <label class="radio-label"><input type="radio" name="s6_male_health_worker" value="उपलब्ध नाही"> उपलब्ध नाही</label>
                        <label for="s6_male_health_worker_count">संख्या:</label>
                        <input type="number" id="s6_male_health_worker_count" name="s6_male_health_worker_count" min="0">
                    </div>
                     <div class="form-group">
                        <label>अंशकालीन कर्मचारी *</label>
                        <label class="radio-label"><input type="radio" name="s6_part_time_staff" value="उपलब्ध" required> उपलब्ध</label>
                        <label class="radio-label"><input type="radio" name="s6_part_time_staff" value="उपलब्ध नाही"> उपलब्ध नाही</label>
                        <label for="s6_part_time_staff_count">संख्या:</label>
                        <input type="number" id="s6_part_time_staff_count" name="s6_part_time_staff_count" min="0">
                    </div>
                </div>
            </div>

            <div class="section">
                <div class="section-header" onclick="toggleSection(this)">
                    <span>7. औषधी व्यवस्थापन</span>
                    <span class="toggle-icon">+</span>
                </div>
                <div class="section-content">
                    <div class="form-group">
                        <label>औषधांची व्यवस्था *</label>
                        <label class="radio-label"><input type="radio" name="s7_medicine_arrangement" value="व्यवस्थित" required> व्यवस्थित</label>
                        <label class="radio-label"><input type="radio" name="s7_medicine_arrangement" value="अव्यवस्थित"> अव्यवस्थित</label>
                    </div>
                    <div class="form-group">
                        <label>स्टॉक औषध रजिस्टर *</label>
                        <label class="radio-label"><input type="radio" name="s7_stock_medicine_register" value="होय" required> होय</label>
                        <label class="radio-label"><input type="radio" name="s7_stock_medicine_register" value="नाही"> नाही</label>
                    </div>
                    <div class="form-group">
                        <label>AEFI किट *</label>
                        <label class="radio-label"><input type="radio" name="s7_aefi_kit" value="उपलब्ध" required> उपलब्ध</label>
                        <label class="radio-label"><input type="radio" name="s7_aefi_kit" value="उपलब्ध नाही"> उपलब्ध नाही</label>
                    </div>
                    <div class="form-group">
                        <label>मुदतवाढ स्टॉक रजिस्टर *</label>
                        <label class="radio-label"><input type="radio" name="s7_expired_stock_register" value="होय" required> होय</label>
                        <label class="radio-label"><input type="radio" name="s7_expired_stock_register" value="नाही"> नाही</label>
                    </div>
                    <div class="form-group">
                        <label>मुदतवाढ औषधे आढळली *</label>
                        <label class="radio-label"><input type="radio" name="s7_expired_medicine_found" value="होय" required> होय</label>
                        <label class="radio-label"><input type="radio" name="s7_expired_medicine_found" value="नाही"> नाही</label>
                    </div>
                </div>
            </div>

            <div class="section">
                <div class="section-header" onclick="toggleSection(this)">
                    <span>8. साथरोग विभाग</span>
                    <span class="toggle-icon">+</span>
                </div>
                <div class="section-content">
                    <div class="form-group">
                        <label>कार्डे वितरण *</label>
                        <label class="radio-label"><input type="radio" name="s8_cards_distribution" value="होय" required> होय</label>
                        <label class="radio-label"><input type="radio" name="s8_cards_distribution" value="नाही"> नाही</label>
                    </div>
                    <div class="form-group">
                        <label>कार्ड रूपांतरण माहिती *</label>
                        <label class="radio-label"><input type="radio" name="s8_card_conversion_info" value="उपलब्ध" required> उपलब्ध</label>
                        <label class="radio-label"><input type="radio" name="s8_card_conversion_info" value="उपलब्ध नाही"> उपलब्ध नाही</label>
                    </div>
                    <div class="form-group">
                        <label for="s8_action_taken">केलेली कार्यवाही (टीप)</label>
                        <textarea id="s8_action_taken" name="s8_action_taken" rows="3" style="width: 100%; box-sizing: border-box;"></textarea>
                    </div>
                    <div class="form-group">
                        <label for="s8_correspondence">पत्रव्यवहार (टीप)</label>
                        <textarea id="s8_correspondence" name="s8_correspondence" rows="3" style="width: 100%; box-sizing: border-box;"></textarea>
                    </div>
                    <div class="form-group">
                        <label>मागील 5 वर्षांची साथरोग माहिती *</label>
                        <label class="radio-label"><input type="radio" name="s8_outbreak_info_5yrs" value="उपलब्ध" required> उपलब्ध</label>
                        <label class="radio-label"><input type="radio" name="s8_outbreak_info_5yrs" value="उपलब्ध नाही"> उपलब्ध नाही</label>
                    </div>
                    <div class="form-group">
                        <label>गावनिहाय/वर्षनिहाय रोग माहिती *</label>
                        <label class="radio-label"><input type="radio" name="s8_disease_village_year_info" value="उपलब्ध" required> उपलब्ध</label>
                        <label class="radio-label"><input type="radio" name="s8_disease_village_year_info" value="उपलब्ध नाही"> उपलब्ध नाही</label>
                    </div>
                    <div class="form-group">
                        <label>पाण्याच्या स्त्रोताची माहिती *</label>
                        <label class="radio-label"><input type="radio" name="s8_water_source_info" value="उपलब्ध" required> उपलब्ध</label>
                        <label class="radio-label"><input type="radio" name="s8_water_source_info" value="उपलब्ध नाही"> उपलब्ध नाही</label>
                    </div>
                     <div class="form-group">
                        <label>जलशुद्धीकरण रजिस्टर आणि पत्रव्यवहार *</label>
                        <label class="radio-label"><input type="radio" name="s8_water_purification_register_correspondence" value="उपलब्ध" required> उपलब्ध</label>
                        <label class="radio-label"><input type="radio" name="s8_water_purification_register_correspondence" value="उपलब्ध नाही"> उपलब्ध नाही</label>
                    </div>
                    <div class="form-group">
                        <label>पाण्याचे नमुने रजिस्टर *</label>
                        <label class="radio-label"><input type="radio" name="s8_water_sample_register" value="होय" required> होय</label>
                        <label class="radio-label"><input type="radio" name="s8_water_sample_register" value="नाही"> नाही</label>
                    </div>
                    <div class="form-group">
                        <label>मुख्यालय जल नमुने रेकॉर्ड *</label>
                        <label class="radio-label"><input type="radio" name="s8_hq_water_sample_record" value="उपलब्ध" required> उपलब्ध</label>
                        <label class="radio-label"><input type="radio" name="s8_hq_water_sample_record" value="उपलब्ध नाही"> उपलब्ध नाही</label>
                    </div>
                    <div class="form-group">
                        <label>पाण्याचे टँकर रेकॉर्ड *</label>
                        <label class="radio-label"><input type="radio" name="s8_water_tanker_record" value="होय" required> होय</label>
                        <label class="radio-label"><input type="radio" name="s8_water_tanker_record" value="नाही"> नाही</label>
                    </div>
                    <div class="form-group">
                        <label>गावात TCL उपलब्धता *</label>
                        <label class="radio-label"><input type="radio" name="s8_village_tcl_availability" value="होय" required> होय</label>
                        <label class="radio-label"><input type="radio" name="s8_village_tcl_availability" value="नाही"> नाही</label>
                    </div>
                    <div class="form-group">
                        <label>TCL खरेदी रेकॉर्ड *</label>
                        <label class="radio-label"><input type="radio" name="s8_purchase_record_tcl" value="होय" required> होय</label>
                        <label class="radio-label"><input type="radio" name="s8_purchase_record_tcl" value="नाही"> नाही</label>
                    </div>
                    <div class="form-group">
                        <label>टँकर नमुना रेकॉर्ड *</label>
                        <label class="radio-label"><input type="radio" name="s8_sample_record_tanker" value="होय" required> होय</label>
                        <label class="radio-label"><input type="radio" name="s8_sample_record_tanker" value="नाही"> नाही</label>
                    </div>
                    <div class="form-group">
                        <label>कमी क्लोरीन असलेली गावे *</label>
                        <label class="radio-label"><input type="radio" name="s8_low_chlorine_villages" value="होय" required> होय</label>
                        <label class="radio-label"><input type="radio" name="s8_low_chlorine_villages" value="नाही"> नाही</label>
                    </div>
                    <div class="form-group">
                        <label>दंश अहवाल *</label>
                        <label class="radio-label"><input type="radio" name="s8_bite_reports" value="उपलब्ध" required> उपलब्ध</label>
                        <label class="radio-label"><input type="radio" name="s8_bite_reports" value="उपलब्ध नाही"> उपलब्ध नाही</label>
                    </div>
                    <div class="form-group">
                        <label>मीठ/शौचालय नमुने रेकॉर्ड *</label>
                        <label class="radio-label"><input type="radio" name="s8_salt_stool_sample_record" value="उपलब्ध" required> उपलब्ध</label>
                        <label class="radio-label"><input type="radio" name="s8_salt_stool_sample_record" value="उपलब्ध नाही"> उपलब्ध नाही</label>
                    </div>
                    <div class="form-group">
                        <label>ग्राम आरोग्य जल स्वच्छता बैठक रेकॉर्ड *</label>
                        <label class="radio-label"><input type="radio" name="s8_gram_health_water_sanitation_meeting_record" value="उपलब्ध" required> उपलब्ध</label>
                        <label class="radio-label"><input type="radio" name="s8_gram_health_water_sanitation_meeting_record" value="उपलब्ध नाही"> उपलब्ध नाही</label>
                    </div>
                    <div class="form-group">
                        <label>शुद्धीकरण औषध उपलब्धता *</label>
                        <label class="radio-label"><input type="radio" name="s8_purification_medicine_availability" value="होय" required> होय</label>
                        <label class="radio-label"><input type="radio" name="s8_purification_medicine_availability" value="नाही"> नाही</label>
                    </div>
                    <div class="form-group">
                        <label>साथरोग किट/झिंक ORT कॉर्नर *</label>
                        <label class="radio-label"><input type="radio" name="s8_epidemic_kit_zinc_ort_corner" value="उपलब्ध" required> उपलब्ध</label>
                        <label class="radio-label"><input type="radio" name="s8_epidemic_kit_zinc_ort_corner" value="उपलब्ध नाही"> उपलब्ध नाही</label>
                    </div>
                    <div class="form-group">
                        <label>जलशुद्धीकरण प्रशिक्षण मिनिट्स *</label>
                        <label class="radio-label"><input type="radio" name="s8_water_purification_training_minutes" value="उपलब्ध" required> उपलब्ध</label>
                        <label class="radio-label"><input type="radio" name="s8_water_purification_training_minutes" value="उपलब्ध नाही"> उपलब्ध नाही</label>
                    </div>
                    <div class="form-group">
                        <label for="s8_other_observations_dls">इतर निरीक्षणे (डीएलएस)</label>
                        <textarea id="s8_other_observations_dls" name="s8_other_observations_dls" rows="3" style="width: 100%; box-sizing: border-box;"></textarea>
                    </div>
                </div>
            </div>

            <div class="section">
                <div class="section-header" onclick="toggleSection(this)">
                    <span>9. आरोग्य उपकेंद्र रेकॉर्ड/रजिस्टर</span>
                    <span class="toggle-icon">+</span>
                </div>
                <div class="section-content">
                    <div class="form-group">
                        <label>ओपीडी रजिस्टर *</label>
                        <label class="radio-label"><input type="radio" name="s9_opd_register" value="उपलब्ध" required> उपलब्ध</label>
                        <label class="radio-label"><input type="radio" name="s9_opd_register" value="उपलब्ध नाही"> उपलब्ध नाही</label>
                    </div>
                    <div class="form-group">
                        <label>आयपीडी रजिस्टर *</label>
                        <label class="radio-label"><input type="radio" name="s9_ipd_register" value="उपलब्ध" required> उपलब्ध</label>
                        <label class="radio-label"><input type="radio" name="s9_ipd_register" value="उपलब्ध नाही"> उपलब्ध नाही</label>
                    </div>
                    <div class="form-group">
                        <label>रेफर रजिस्टर *</label>
                        <label class="radio-label"><input type="radio" name="s9_refer_register" value="उपलब्ध" required> उपलब्ध</label>
                        <label class="radio-label"><input type="radio" name="s9_refer_register" value="उपलब्ध नाही"> उपलब्ध नाही</label>
                    </div>
                    <div class="form-group">
                        <label>भेट पुस्तक *</label>
                        <label class="radio-label"><input type="radio" name="s9_visit_book" value="उपलब्ध" required> उपलब्ध</label>
                        <label class="radio-label"><input type="radio" name="s9_visit_book" value="उपलब्ध नाही"> उपलब्ध नाही</label>
                    </div>
                    <div class="form-group">
                        <label>हजेरी पत्रक (मस्टर रोल) *</label>
                        <label class="radio-label"><input type="radio" name="s9_muster_roll" value="उपलब्ध" required> उपलब्ध</label>
                        <label class="radio-label"><input type="radio" name="s9_muster_roll" value="उपलब्ध नाही"> उपलब्ध नाही</label>
                    </div>
                    <div class="form-group">
                        <label>आवक जावक (मूव्हमेंट) रजिस्टर *</label>
                        <label class="radio-label"><input type="radio" name="s9_movement_register" value="उपलब्ध" required> उपलब्ध</label>
                        <label class="radio-label"><input type="radio" name="s9_movement_register" value="उपलब्ध नाही"> उपलब्ध नाही</label>
                    </div>
                    <div class="form-group">
                        <label>मास्टर फाईल *</label>
                        <label class="radio-label"><input type="radio" name="s9_master_file" value="उपलब्ध" required> उपलब्ध</label>
                        <label class="radio-label"><input type="radio" name="s9_master_file" value="उपलब्ध नाही"> उपलब्ध नाही</label>
                    </div>
                </div>
            </div>

            <div class="camera-section">
                <h3>भेटीचे थेट फोटो आणि लोकेशन</h3>
                <p>कॅमेरा सुरू करण्यापूर्वी लोकेशन (GPS) चालू असल्याची खात्री करा. प्रत्येक फोटो कॅप्चर करण्यापूर्वी लोकेशन घेतले जाईल.</p>

                <div class="form-group">
                    <label>भेटीचा थेट फोटो 1</label>
                    <button type="button" class="camera-button" onclick="startCamera(1)">कॅमेरा सुरू</button>
                    <input type="file" name="photo_1" id="photo_1_input" accept="image/*" capture="environment" style="display: none;">
                    <input type="hidden" name="photo_1_latitude" id="photo_1_latitude">
                    <input type="hidden" name="photo_1_longitude" id="photo_1_longitude">
                    <div class="photo-preview" id="photo_1_preview"></div>
                </div>

                <div class="form-group">
                    <label>भेटीचा थेट फोटो 2</label>
                    <button type="button" class="camera-button" onclick="startCamera(2)">कॅमेरा सुरू</button>
                    <input type="file" name="photo_2" id="photo_2_input" accept="image/*" capture="environment" style="display: none;">
                    <input type="hidden" name="photo_2_latitude" id="photo_2_latitude">
                    <input type="hidden" name="photo_2_longitude" id="photo_2_longitude">
                    <div class="photo-preview" id="photo_2_preview"></div>
                </div>

                <div class="form-group">
                    <label>भेटीचा थेट फोटो 3</label>
                    <button type="button" class="camera-button" onclick="startCamera(3)">कॅमेरा सुरू</button>
                    <input type="file" name="photo_3" id="photo_3_input" accept="image/*" capture="environment" style="display: none;">
                    <input type="hidden" name="photo_3_latitude" id="photo_3_latitude">
                    <input type="hidden" name="photo_3_longitude" id="photo_3_longitude">
                    <div class="photo-preview" id="photo_3_preview"></div>
                </div>

                <div class="form-group">
                    <label>भेटीचा थेट फोटो 4</label>
                    <button type="button" class="camera-button" onclick="startCamera(4)">कॅमेरा सुरू</button>
                    <input type="file" name="photo_4" id="photo_4_input" accept="image/*" capture="environment" style="display: none;">
                    <input type="hidden" name="photo_4_latitude" id="photo_4_latitude">
                    <input type="hidden" name="photo_4_longitude" id="photo_4_longitude">
                    <div class="photo-preview" id="photo_4_preview"></div>
                </div>
            </div>

            <div class="btn-container">
                <button class="btn-submit" type="submit">सबमिट</button>
                <button class="btn-reset" type="reset">रीसेट करा</button>
            </div>
        </form>
    </div>
</div>
    <script>
        // JavaScript for accordion-like section toggling
        function toggleSection(headerElement) {
            const content = headerElement.nextElementSibling;
            const toggleIcon = headerElement.querySelector('.toggle-icon');

            if (content.classList.contains('active')) {
                content.classList.remove('active');
                toggleIcon.textContent = '+';
            } else {
                content.classList.add('active');
                toggleIcon.textContent = '−'; // Minus sign
            }
        }

        // JavaScript for Camera/GPS functionality
        function startCamera(photoNumber) {
            const fileInput = document.getElementById(`photo_${photoNumber}_input`);
            const latInput = document.getElementById(`photo_${photoNumber}_latitude`);
            const lonInput = document.getElementById(`photo_${photoNumber}_longitude`);
            const previewDiv = document.getElementById(`photo_${photoNumber}_preview`);

            // Get GPS location first
            if (navigator.geolocation) {
                navigator.geolocation.getCurrentPosition(
                    (position) => {
                        latInput.value = position.coords.latitude;
                        lonInput.value = position.coords.longitude;
                        alert(`लोकेशन घेतले: ${position.coords.latitude}, ${position.coords.longitude}`); // Marathi for "Location taken"
                        fileInput.click(); // Trigger file input (camera) after getting location
                    },
                    (error) => {
                        alert(`लोकेशन घेण्यात त्रुटी: ${error.message}`); // Marathi for "Error getting location"
                        // Still allow taking photo, but without location
                        fileInput.click();
                    },
                    { enableHighAccuracy: true, timeout: 10000, maximumAge: 0 }
                );
            } else {
                alert("तुमचा ब्राउझर लोकेशनला समर्थन देत नाही."); // Marathi for "Your browser does not support geolocation."
                fileInput.click(); // Trigger file input (camera) even without location
            }

            // Handle file input change to show preview
            fileInput.onchange = function(event) {
                const file = event.target.files[0];
                if (file) {
                    const reader = new FileReader();
                    reader.onload = function(e) {
                        previewDiv.innerHTML = `<img src="${e.target.result}" alt="Photo Preview" style="max-width: 100%; height: auto; display: block; margin: auto;">`;
                    };
                    reader.readAsDataURL(file);
                } else {
                    previewDiv.innerHTML = ''; // Clear preview if no file selected
                }
            };
        }
    </script>
    <?php include("../includes/footer.php")?>
</body>
</html>