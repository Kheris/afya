<?php
require_once __DIR__ . '/../includes/auth.php';
require_once __DIR__ . '/../includes/ai.php';

header('Content-Type: application/json; charset=utf-8');

if (!currentUserId()) {
    http_response_code(401);
    echo json_encode(['error' => 'Tafadhali ingia kwenye akaunti yako.']);
    exit;
}

$userId = currentUserId();
$db = getDB();

// ================== RATE LIMITING ==================
// Kikomo cha ujumbe kwa siku, ili kulinda gharama za AI credits zako.
const DAILY_MESSAGE_LIMIT = 40;

$today = date('Y-m-d');
$stmt = $db->prepare("SELECT message_count FROM rate_limits WHERE user_id = ? AND usage_date = ?");
$stmt->execute([$userId, $today]);
$rateRow = $stmt->fetch();
$currentCount = $rateRow ? (int)$rateRow['message_count'] : 0;

if ($currentCount >= DAILY_MESSAGE_LIMIT) {
    http_response_code(429);
    echo json_encode(['error' => "Umefikia kikomo cha ujumbe " . DAILY_MESSAGE_LIMIT . " kwa siku ya leo. Tafadhali jaribu tena kesho."]);
    exit;
}
// =====================================================

$input = json_decode(file_get_contents('php://input'), true);
if (!is_array($input)) {
    http_response_code(400);
    echo json_encode(['error' => 'Ombi si sahihi.']);
    exit;
}
$assessmentId = isset($input['assessment_id']) ? (int)$input['assessment_id'] : null;
$userMessage  = trim($input['message'] ?? '');
$dependentId  = isset($input['dependent_id']) && $input['dependent_id'] ? (int)$input['dependent_id'] : null;
$forLabel     = trim($input['for_label'] ?? 'Mwenyewe');

if ($userMessage === '') {
    http_response_code(400);
    echo json_encode(['error' => 'Andika ujumbe kabla ya kutuma.']);
    exit;
}
// Kikomo cha urefu wa ujumbe (kulinda gharama za AI)
if (mb_strlen($userMessage) > 2000) {
    $userMessage = mb_substr($userMessage, 0, 2000);
}

$conversation = [];

// -- Pata au tengeneza assessment record --
if ($assessmentId) {
    $stmt = $db->prepare("SELECT * FROM assessments WHERE id = ? AND user_id = ?");
    $stmt->execute([$assessmentId, $userId]);
    $row = $stmt->fetch();
    if ($row) {
        $conversation = json_decode($row['conversation'], true) ?: [];
        $dependentId  = $row['dependent_id'] ?? $dependentId;
        $forLabel     = $row['for_label'] ?? $forLabel;
    } else {
        $assessmentId = null;
    }
}

// -- Ongeza ujumbe wa mtumiaji --
$conversation[] = ['role' => 'user', 'content' => $userMessage];

// -- Profile: kama ni kwa mwanafamilia, tumia taarifa zake badala ya za mtumiaji --
$profile = currentUser();
if ($dependentId) {
    $stmt = $db->prepare("SELECT name, date_of_birth, sex_at_birth FROM dependents WHERE id = ? AND user_id = ?");
    $stmt->execute([$dependentId, $userId]);
    $dep = $stmt->fetch();
    if ($dep) {
        $profile = [
            'name' => $dep['name'],
            'date_of_birth' => $dep['date_of_birth'],
            'sex_at_birth' => $dep['sex_at_birth'],
            'is_smoker' => 'unknown',
            'has_diabetes' => 'unknown',
        ];
    }
}

// -- Piga simu AI (tuma ujumbe 30 wa mwisho tu, kupunguza gharama) --
try {
    $result = callAI(array_slice($conversation, -30), $profile ?: []);
} catch (Throwable $e) {
    afyaLog('chat.php callAI exception: ' . $e->getMessage());
    require_once __DIR__ . '/../includes/localchat.php';
    $result = ['ok' => true, 'reply' => localAssessmentReply($conversation), 'error' => null, 'source' => 'local'];
}

if (!$result['ok']) {
    http_response_code(502);
    echo json_encode(['error' => $result['error']]);
    exit;
}

$conversation[] = ['role' => 'assistant', 'content' => $result['reply']];

// -- Bainisha kiwango cha uharaka kutoka kwenye jibu --
// Zamani: neno "dharura"/"haraka" popote kwenye jibu lilitosha (mf. "si dharura" ilihesabiwa dharura).
// Sasa: tunasoma mstari wa "Kiwango cha uharaka" tu, na muhtasari ndio unaomaliza tathmini.
$replyLower  = mb_strtolower($result['reply']);
$isCompleted = str_contains($replyLower, 'muhtasari') || str_contains($replyLower, 'uwezekano wa hali');
// Ondoa kauli za kukanusha ("si dharura", "sio ya haraka") kabla ya kutafuta neno la kiwango.
$negations = '/\b(si|sio|siyo|hakuna)\s+(ya\s+|la\s+|kitu\s+cha\s+)?(dharura|haraka)/u';
$plain = preg_replace($negations, '', $replyLower);
$urgency = 'unknown';
if (preg_match('/kiwango cha uharaka\W*([^\n]*)/u', $plain, $mm)) {
    $line = $mm[1];   // maneno baada ya lebo tu ("uharaka" yenyewe ina "haraka" ndani yake)
    if (str_contains($line, 'dharura'))                                        $urgency = 'emergency';
    elseif (str_contains($line, 'haraka'))                                     $urgency = 'urgent';
    elseif (str_contains($line, 'jitunze') || str_contains($line, 'nyumbani')) $urgency = 'self_care';
    elseif (str_contains($line, 'kawaida'))                                    $urgency = 'routine';
}
if ($urgency === 'unknown') {
    // AI ilisimama mapema kwa dharura (haikuandika muhtasari): usalama kwanza, tunahifadhi alama nyekundu.
    if (str_contains($plain, 'dharura') || str_contains($plain, 'hospitali sasa')) $urgency = 'emergency';
    elseif ($isCompleted) $urgency = 'routine';
}

// -- Hifadhi kwenye database --
if ($assessmentId) {
    $stmt = $db->prepare("UPDATE assessments SET conversation = ?, urgency_level = ?, status = ?, dependent_id = ?, for_label = ? WHERE id = ?");
    $stmt->execute([
        json_encode($conversation, JSON_UNESCAPED_UNICODE),
        $urgency,
        $isCompleted ? 'completed' : 'ongoing',
        $dependentId,
        $forLabel,
        $assessmentId,
    ]);
} else {
    $title = mb_substr($userMessage, 0, 60);
    $stmt = $db->prepare("INSERT INTO assessments (user_id, title, conversation, urgency_level, status, dependent_id, for_label) VALUES (?, ?, ?, ?, ?, ?, ?)");
    $stmt->execute([
        $userId,
        $title,
        json_encode($conversation, JSON_UNESCAPED_UNICODE),
        $urgency,
        $isCompleted ? 'completed' : 'ongoing',
        $dependentId,
        $forLabel,
    ]);
    $assessmentId = (int)$db->lastInsertId();
}

// -- Sasisha rate limit counter (atomic, hakuna mbio kati ya maombi mawili) --
// Msaidizi wa ndani hana gharama, kwa hiyo hauhesabiwi kwenye kikomo.
if (($result['source'] ?? '') !== 'local') {
    $stmt = $db->prepare("INSERT INTO rate_limits (user_id, usage_date, message_count) VALUES (?, ?, 1)
                          ON DUPLICATE KEY UPDATE message_count = message_count + 1");
    $stmt->execute([$userId, $today]);
}

echo json_encode([
    'reply'          => $result['reply'],
    'assessment_id'  => $assessmentId,
    'urgency'        => $urgency,
    'completed'      => $isCompleted,
    'source'         => $result['source'] ?? 'google',
]);
