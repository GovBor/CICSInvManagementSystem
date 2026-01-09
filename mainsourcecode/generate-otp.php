<?php
require 'db_connection.php';

function generateCode() {
    return rand(10000, 99999);
}

$otp = generateCode();
$timestamp = date('Y-m-d H:i:s');

// Update the OTP in the DB for fixed ID = 1
$stmt = $conn->prepare("UPDATE otp_codes SET code = ?, generated_at = ? WHERE id = 1");
$stmt->bind_param("ss", $otp, $timestamp);
$stmt->execute();
$stmt->close();

echo json_encode([
    'otp' => $otp,
    'timestamp' => strtotime($timestamp)
]);
