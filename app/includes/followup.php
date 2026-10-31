<?php
/**
 * Ufuatiliaji wa Baada ya Tathmini (Follow-up).
 * Tathmini zenye urgency 'urgent' au 'emergency' zilizokamilika siku 1-14
 * zilizopita, na bado hazijajibiwa, zinaulizwa kwenye dashboard:
 * "Uliona daktari? Hali ikoje sasa?"
 */
function getPendingFollowUp(PDO $db, int $userId): ?array {
    $stmt = $db->prepare("
        SELECT id, title, urgency_level, created_at
        FROM assessments
        WHERE user_id = ?
          AND status = 'completed'
          AND urgency_level IN ('urgent', 'emergency')
          AND follow_up_status IS NULL
          AND created_at <= DATE_SUB(NOW(), INTERVAL 1 DAY)
          AND created_at >= DATE_SUB(NOW(), INTERVAL 14 DAY)
        ORDER BY created_at DESC
        LIMIT 1
    ");
    $stmt->execute([$userId]);
    return $stmt->fetch() ?: null;
}

function recordFollowUpResponse(PDO $db, int $assessmentId, int $userId, string $response): void {
    $allowed = ['saw_doctor', 'self_care', 'still_sick', 'ignored'];
    if (!in_array($response, $allowed, true)) return;

    $stmt = $db->prepare("UPDATE assessments SET follow_up_status = ?, follow_up_responded_at = NOW() WHERE id = ? AND user_id = ?");
    $stmt->execute([$response, $assessmentId, $userId]);
}
