<?php
require_once __DIR__ . '/../config/db.php';
header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(['success' => false, 'message' => 'Invalid request.']);
    exit;
}

$name = sanitize($_POST['name'] ?? '');
$phone = sanitize($_POST['phone'] ?? '');
$email = sanitize($_POST['email'] ?? '');
$subject = sanitize($_POST['subject'] ?? 'General Inquiry');
$message = sanitize($_POST['message'] ?? '');

if (!$name || !$phone || !$message) {
    echo json_encode(['success' => false, 'message' => 'Please fill in all required fields.']);
    exit;
}

if (strlen($message) < 10) {
    echo json_encode(['success' => false, 'message' => 'Message is too short.']);
    exit;
}

try {
    $stmt = $pdo->prepare("INSERT INTO contacts (name, email, phone, subject, message) VALUES (?, ?, ?, ?, ?)");
    $stmt->execute([$name, $email, $phone, $subject, $message]);
    echo json_encode(['success' => true, 'message' => 'Thank you! Your message has been sent. We will contact you soon.']);
} catch (Exception $e) {
    echo json_encode(['success' => false, 'message' => 'Failed to send. Please try again.']);
}
