<?php
include("connection.php");

$date = $_GET['date'] ?? '';
$vet_id = $_GET['vet_id'] ?? '';

if (!$date || !$vet_id) {
    echo json_encode([]);
    exit;
}

$sql = "SELECT appointment_time FROM appointments WHERE appointment_date = ? AND vet_id = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("si", $date, $vet_id);
$stmt->execute();
$result = $stmt->get_result();

$booked_times = [];
while ($row = $result->fetch_assoc()) {
    $booked_times[] = $row['appointment_time'];
}

echo json_encode($booked_times);
?>
