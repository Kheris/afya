<?php
require_once __DIR__ . '/includes/auth.php';
require_once __DIR__ . '/includes/followup.php';
requireLogin();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $assessmentId = (int)($_POST['assessment_id'] ?? 0);
    $response = $_POST['response'] ?? '';
    if ($assessmentId && $response) {
        recordFollowUpResponse(getDB(), $assessmentId, currentUserId(), $response);
    }
}

header('Location: dashboard.php');
exit;
