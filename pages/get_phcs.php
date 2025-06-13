<?php
// db_connection.php should be in the same directory or accessible path
include('../config/db.php');

$phcs = [];

if (isset($_POST['taluka_id'])) {
    $taluka_id = $conn->real_escape_string($_POST['taluka_id']);

    if (!empty($taluka_id)) {
        $sql = "SELECT id, name FROM primary_health_centers WHERE taluka_id = '$taluka_id' ORDER BY name ASC";
    } else {
        $sql = "SELECT id, name FROM primary_health_centers ORDER BY name ASC";
    }

    $result = $conn->query($sql);

    if ($result->num_rows > 0) {
        while ($row = $result->fetch_assoc()) {
            $phcs[] = $row;
        }
    }
}

// Return PHCs as JSON
header('Content-Type: application/json');
echo json_encode($phcs);

$conn->close();
?>