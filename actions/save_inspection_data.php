<?php
// Set error reporting for development. Disable in production.
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

include("../config/db.php");
    // --- Prepare data for insertion using a function for convenience ---
    function get_post_value($key, $default = null) {
        return isset($_POST[$key]) ? $_POST[$key] : $default;
    }

    

// 2. Get the record ID (e.g., from the URL)
// This ID tells us which specific visit record to load.
$inspection_id = null;
if (isset($_GET['inspection_id'])) {
    $inspection_id = intval($_GET['inspection_id']); // Sanitize input as integer
} else {
    // Handle the case where inspection_id is not provided (e.g., redirect or show error)
    echo "Error: Inspection ID is missing.";
    exit();
}

// Initialize variables to prevent "undefined variable" errors if no data is found
$inspection_date_display = '';
$expected_inspection_date_display = '';
$phc_name = '';
$subcenter_name = '';

// 3. Fetch Data from Database
if ($inspection_id) {
    // This SQL query is an example. You'll need to adjust it based on your
    // actual table names and column names.
    // Assuming 'scheduled_visits' table contains inspection_date, expected_date, phc_id, subcenter_id
    // And 'phc_details' table has phc_name, 'subcenter_details' has subcenter_name
    $sql = "SELECT
                sv.visit_date,
                sv.expected_inspection_date_display,
                phc.phc_name,
                sc.subcenter_name
            FROM
                scheduled_visits sv
            LEFT JOIN
                phc_details phc ON sv.phc_id = phc.id
            LEFT JOIN
                subcenter_details sc ON sv.subcenter_id = sc.id
            WHERE
                sv.id = ?"; // Use 'id' column from scheduled_visits table for lookup

    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $inspection_id); // "i" for integer
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows > 0) {
        $row = $result->fetch_assoc();
        $inspection_date_display = htmlspecialchars($row['inspection_date_display']);
        $expected_inspection_date_display = htmlspecialchars($row['expected_inspection_date_display']);
        $phc_name = htmlspecialchars($row['phc_name']);
        $subcenter_name = htmlspecialchars($row['subcenter_name']);
    } else {
        // Handle case where no record is found for the given ID
        echo "Error: No inspection data found for ID: " . htmlspecialchars($inspection_id);
        // Optionally, redirect or exit
        exit();
    }

    $stmt->close();
}

// Close database connection (if not handled by db_connect.php on script end)
// $conn->close();


    // Hidden meta-data
    $inspection_id = get_post_value('inspection_id');
    $inspection_date_display = get_post_value('inspection_date_display');
    $expected_inspection_date_display = get_post_value('expected_inspection_date_display');
    $phc_name = get_post_value('phc_name');
    $subcenter_name = get_post_value('subcenter_name');

    // Section 1
    $s1_protected_wall = get_post_value('section1_q1_protected_wall');
    $s1_iron_gate = get_post_value('section1_q2a_iron_gate');
    $s1_arch_nameboard = get_post_value('section1_q2b_arch_nameboard');
    $s1_direction_board = get_post_value('section1_q3_direction_board');
    $s1_building_plaster_paint = get_post_value('section1_q4_building_plaster_paint');

    // Section 2
    $s2_opd = get_post_value('section2_q1_opd');
    $s2_delivery_24_7 = get_post_value('section2_q2_delivery_24_7');
    $s2_free_health_services = get_post_value('section2_q3_free_health_services');

    // Section 3
    $s3_internal_cleanliness = get_post_value('section3_q1_internal_cleanliness');
    $s3_campus_cleanliness = get_post_value('section3_q2_campus_cleanliness');
    $s3_delivery_room_daily_clean = get_post_value('section3_q3_delivery_room_daily_clean');
    $s3_cleaning_tools_availability = get_post_value('section3_q4_cleaning_tools_availability');

    // Section 4
    $s4_electric_fitting = get_post_value('section4_q1_electric_fitting');
    $s4_cracked_leaking_walls = get_post_value('section4_q2_cracked_leaking_walls');
    $s4_doors_windows_condition = get_post_value('section4_q3_doors_windows_condition');
    $s4_ceiling_cleaning = get_post_value('section4_q4_ceiling_cleaning');
    $s4_water_tap_pipe_condition = get_post_value('section4_q5_water_tap_pipe_condition');

    // Section 5
    $s5_chair_table = get_post_value('section5_q1_chair_table');
    $s5_patient_table_stool_curtain = get_post_value('section5_q2_patient_table_stool_curtain');
    $s5_institution_map = get_post_value('section5_q3_institution_map');
    $s5_health_indicators_chart = get_post_value('section5_q4_health_indicators_chart');
    $s5_health_services_schedule = get_post_value('section5_q5_health_services_schedule');
    $s5_updated_edd_epd_chart = get_post_value('section5_q6_updated_edd_epd_chart');
    $s5_pneumonia_sepsis_diarrhea_protocols = get_post_value('section5_q7_pneumonia_sepsis_diarrhea_protocols');
    $s5_pneumonia_sepsis_diarrhea_medicines = get_post_value('section5_q8_pneumonia_sepsis_diarrhea_medicines');

    // Section 6
    $s6_female_health_worker = get_post_value('section6_q1_female_health_worker');
    $s6_female_health_worker_count = (int)get_post_value('section6_q1_female_health_worker_count', 0);
    $s6_male_health_worker = get_post_value('section6_q2_male_health_worker');
    $s6_male_health_worker_count = (int)get_post_value('section6_q2_male_health_worker_count', 0);
    $s6_part_time_staff = get_post_value('section6_q3_part_time_staff');
    $s6_part_time_staff_count = (int)get_post_value('section6_q3_part_time_staff_count', 0);

    // Section 7
    $s7_medicine_arrangement = get_post_value('section7_q1_medicine_arrangement');
    $s7_stock_medicine_register = get_post_value('section7_q2_stock_medicine_register');
    $s7_aefi_kit = get_post_value('section7_q3_aefi_kit');
    $s7_expired_stock_register = get_post_value('section7_q4_expired_stock_register');
    $s7_expired_medicine_found = get_post_value('section7_q5_expired_medicine_found');

    // Section 8
    $s8_cards_distribution = get_post_value('section8_q1_cards_distribution');
    $s8_card_conversion_info = get_post_value('section8_q2_card_conversion_info');
    $s8_action_taken = get_post_value('section8_q2_1_action_taken');
    $s8_correspondence = get_post_value('section8_q2_2_correspondence');
    $s8_outbreak_info_5yrs = get_post_value('section8_q3_outbreak_info_5yrs');
    $s8_disease_village_year_info = get_post_value('section8_q4_disease_village_year_info');
    $s8_water_source_info = get_post_value('section8_q5_1_water_source_info');
    $s8_water_purification_register_correspondence = get_post_value('section8_q5_2_water_purification_register_correspondence');
    $s8_water_sample_register = get_post_value('section8_q5_3_water_sample_register');
    $s8_hq_water_sample_record = get_post_value('section8_q5_4_hq_water_sample_record');
    $s8_water_tanker_record = get_post_value('section8_q6_1_water_tanker_record');
    $s8_village_tcl_availability = get_post_value('section8_q6_2_village_tcl_availability');
    $s8_purchase_record_tcl = get_post_value('section8_q6_3_purchase_record_tcl');
    $s8_sample_record_tanker = get_post_value('section8_q6_4_sample_record_tanker');
    $s8_low_chlorine_villages = get_post_value('section8_q6_5_low_chlorine_villages');
    $s8_bite_reports = get_post_value('section8_q7_1_bite_reports');
    $s8_salt_stool_sample_record = get_post_value('section8_q8_1_salt_stool_sample_record');
    $s8_gram_health_water_sanitation_meeting_record = get_post_value('section8_q8_2_gram_health_water_sanitation_meeting_record');
    $s8_purification_medicine_availability = get_post_value('section8_q8_3_purification_medicine_availability');
    $s8_epidemic_kit_zinc_ort_corner = get_post_value('section8_q8_4_epidemic_kit_zinc_ort_corner');
    $s8_water_purification_training_minutes = get_post_value('section8_q8_5_water_purification_training_minutes');
    $s8_other_observations_dls = get_post_value('section8_q9_other_observations_dls');

    // Section 9
    $s9_opd_register = get_post_value('section9_q1_opd_register');
    $s9_ipd_register = get_post_value('section9_q2_ipd_register');
    $s9_refer_register = get_post_value('section9_q3_refer_register');
    $s9_visit_book = get_post_value('section9_q4_visit_book');
    $s9_muster_roll = get_post_value('section9_q6_muster_roll'); // Note: q5 was skipped in your template
    $s9_movement_register = get_post_value('section9_q7_movement_register');
    $s9_master_file = get_post_value('section9_q8_master_file');

    // --- Handle File Uploads and GPS Data ---
    $upload_dir = 'uploads/'; // Directory to save uploaded images
    if (!is_dir($upload_dir)) {
        mkdir($upload_dir, 0777, true); // Create directory if it doesn't exist
    }

    $photo_paths = [];
    $photo_gps = [];

    for ($i = 1; $i <= 4; $i++) {
        $file_key = "live_photo_" . $i;
        $lat_key = "photo_" . $i . "_latitude";
        $lon_key = "photo_" . $i . "_longitude";

        $photo_paths[$i] = null;
        $photo_gps[$i]['latitude'] = null;
        $photo_gps[$i]['longitude'] = null;

        if (isset($_FILES[$file_key]) && $_FILES[$file_key]['error'] == UPLOAD_ERR_OK) {
            $file_tmp_name = $_FILES[$file_key]['tmp_name'];
            $file_name = uniqid() . "_" . basename($_FILES[$file_key]['name']); // Unique file name
            $target_file = $upload_dir . $file_name;

            if (move_uploaded_file($file_tmp_name, $target_file)) {
                $photo_paths[$i] = $target_file;
            } else {
                error_log("Failed to move uploaded file: " . $file_name);
            }
        }

        // Get GPS data even if photo wasn't uploaded (e.g., if user skipped camera but GPS was active for some reason)
        if (isset($_POST[$lat_key]) && isset($_POST[$lon_key]) && $_POST[$lat_key] !== '' && $_POST[$lon_key] !== '') {
            $photo_gps[$i]['latitude'] = (float)get_post_value($lat_key);
            $photo_gps[$i]['longitude'] = (float)get_post_value($lon_key);
        }
    }

    // --- Prepare SQL INSERT statement with prepared statements for security ---
    $sql = "INSERT INTO phc_inspection_data (
                inspection_id, inspection_date_display, expected_inspection_date_display, phc_name, subcenter_name,
                s1_protected_wall, s1_iron_gate, s1_arch_nameboard, s1_direction_board, s1_building_plaster_paint,
                s2_opd, s2_delivery_24_7, s2_free_health_services,
                s3_internal_cleanliness, s3_campus_cleanliness, s3_delivery_room_daily_clean, s3_cleaning_tools_availability,
                s4_electric_fitting, s4_cracked_leaking_walls, s4_doors_windows_condition, s4_ceiling_cleaning, s4_water_tap_pipe_condition,
                s5_chair_table, s5_patient_table_stool_curtain, s5_institution_map, s5_health_indicators_chart, s5_health_services_schedule, s5_updated_edd_epd_chart, s5_pneumonia_sepsis_diarrhea_protocols, s5_pneumonia_sepsis_diarrhea_medicines,
                s6_female_health_worker, s6_female_health_worker_count, s6_male_health_worker, s6_male_health_worker_count, s6_part_time_staff, s6_part_time_staff_count,
                s7_medicine_arrangement, s7_stock_medicine_register, s7_aefi_kit, s7_expired_stock_register, s7_expired_medicine_found,
                s8_cards_distribution, s8_card_conversion_info, s8_action_taken, s8_correspondence, s8_outbreak_info_5yrs, s8_disease_village_year_info, s8_water_source_info, s8_water_purification_register_correspondence, s8_water_sample_register, s8_hq_water_sample_record, s8_water_tanker_record, s8_village_tcl_availability, s8_purchase_record_tcl, s8_sample_record_tanker, s8_low_chlorine_villages, s8_bite_reports, s8_salt_stool_sample_record, s8_gram_health_water_sanitation_meeting_record, s8_purification_medicine_availability, s8_epidemic_kit_zinc_ort_corner, s8_water_purification_training_minutes, s8_other_observations_dls,
                s9_opd_register, s9_ipd_register, s9_refer_register, s9_visit_book, s9_muster_roll, s9_movement_register, s9_master_file,
                photo_1_path, photo_1_latitude, photo_1_longitude,
                photo_2_path, photo_2_latitude, photo_2_longitude,
                photo_3_path, photo_3_latitude, photo_3_longitude,
                photo_4_path, photo_4_latitude, photo_4_longitude
            ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";

    $stmt = $conn->prepare($sql);

    if ($stmt === FALSE) {
        error_log("Prepare failed: " . $conn->error);
        header("Location: arogya_kendra_inspection_form.php?status=error&message=db_prepare_error");
        exit();
    }

    // 's' for string, 'i' for integer, 'd' for double (decimal for floats)
    $stmt->bind_param("issssssssssssssssssssssssssssssiisssssssssssssssssssssssssssssssssssssddsdssdssd",
        $inspection_id, $inspection_date_display, $expected_inspection_date_display, $phc_name, $subcenter_name,
        $s1_protected_wall, $s1_iron_gate, $s1_arch_nameboard, $s1_direction_board, $s1_building_plaster_paint,
        $s2_opd, $s2_delivery_24_7, $s2_free_health_services,
        $s3_internal_cleanliness, $s3_campus_cleanliness, $s3_delivery_room_daily_clean, $s3_cleaning_tools_availability,
        $s4_electric_fitting, $s4_cracked_leaking_walls, $s4_doors_windows_condition, $s4_ceiling_cleaning, $s4_water_tap_pipe_condition,
        $s5_chair_table, $s5_patient_table_stool_curtain, $s5_institution_map, $s5_health_indicators_chart, $s5_health_services_schedule, $s5_updated_edd_epd_chart, $s5_pneumonia_sepsis_diarrhea_protocols, $s5_pneumonia_sepsis_diarrhea_medicines,
        $s6_female_health_worker, $s6_female_health_worker_count, $s6_male_health_worker, $s6_male_health_worker_count, $s6_part_time_staff, $s6_part_time_staff_count,
        $s7_medicine_arrangement, $s7_stock_medicine_register, $s7_aefi_kit, $s7_expired_stock_register, $s7_expired_medicine_found,
        $s8_cards_distribution, $s8_card_conversion_info, $s8_action_taken, $s8_correspondence, $s8_outbreak_info_5yrs, $s8_disease_village_year_info, $s8_water_source_info, $s8_water_purification_register_correspondence, $s8_water_sample_register, $s8_hq_water_sample_record, $s8_water_tanker_record, $s8_village_tcl_availability, $s8_purchase_record_tcl, $s8_sample_record_tanker, $s8_low_chlorine_villages, $s8_bite_reports, $s8_salt_stool_sample_record, $s8_gram_health_water_sanitation_meeting_record, $s8_purification_medicine_availability, $s8_epidemic_kit_zinc_ort_corner, $s8_water_purification_training_minutes, $s8_other_observations_dls,
        $s9_opd_register, $s9_ipd_register, $s9_refer_register, $s9_visit_book, $s9_muster_roll, $s9_movement_register, $s9_master_file,
        $photo_paths[1], $photo_gps[1]['latitude'], $photo_gps[1]['longitude'],
        $photo_paths[2], $photo_gps[2]['latitude'], $photo_gps[2]['longitude'],
        $photo_paths[3], $photo_gps[3]['latitude'], $photo_gps[3]['longitude'],
        $photo_paths[4], $photo_gps[4]['latitude'], $photo_gps[4]['longitude']
    );

    if ($stmt->execute()) {
        header("Location: arogya_kendra_inspection_form.php?status=success");
        exit();
    } else {
        error_log("Execute failed: " . $stmt->error);
        header("Location: arogya_kendra_inspection_form.php?status=error&message=db_insert_error");
        exit();
    }

    $stmt->close();
    $conn->close();

?>