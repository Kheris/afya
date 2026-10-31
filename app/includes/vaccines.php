<?php
/**
 * Ratiba ya Chanjo za Watoto Tanzania (EPI - Expanded Programme on Immunization).
 * Chanzo: Wizara ya Afya Tanzania / WHO. Hii ni muongozo wa jumla -
 * daima muulize mkunga/muuguzi wa kliniki kwa ratiba rasmi ya mtoto wako.
 *
 * Kila kipengele: ['weeks' => wiki tangu kuzaliwa, 'name' => jina la chanjo]
 */
function getEpiScheduleTanzania(): array {
    return [
        ['weeks' => 0,  'name' => 'BCG (Kifua Kikuu) + OPV0 (Polio - mdomo)'],
        ['weeks' => 6,  'name' => 'Penta 1, OPV1, PCV1, Rota 1'],
        ['weeks' => 10, 'name' => 'Penta 2, OPV2, PCV2, Rota 2'],
        ['weeks' => 14, 'name' => 'Penta 3, OPV3, PCV3, IPV'],
        ['weeks' => 39, 'name' => 'Surua-Rubela 1 (MR1), Homa ya Manjano (Yellow Fever), Vitamini A'], // miezi 9
        ['weeks' => 78, 'name' => 'Surua-Rubela 2 (MR2)'], // miezi 18
    ];
}

/**
 * Tengeneza rekodi za chanjo (vaccinations) kwa mtoto (dependent) kulingana
 * na tarehe yake ya kuzaliwa, IKIWA bado hazijawahi kutengenezwa.
 */
function ensureVaccinationScheduleForDependent(PDO $db, int $dependentId, int $userId, string $dateOfBirth): void {
    $stmt = $db->prepare("SELECT COUNT(*) FROM vaccinations WHERE dependent_id = ?");
    $stmt->execute([$dependentId]);
    if ((int)$stmt->fetchColumn() > 0) {
        return; // tayari ipo
    }

    $dob = new DateTime($dateOfBirth);
    $insert = $db->prepare("INSERT INTO vaccinations (dependent_id, user_id, vaccine_name, due_date) VALUES (?, ?, ?, ?)");

    foreach (getEpiScheduleTanzania() as $item) {
        $due = clone $dob;
        $due->modify('+' . $item['weeks'] . ' weeks');
        $insert->execute([$dependentId, $userId, $item['name'], $due->format('Y-m-d')]);
    }
}

/**
 * Pata idadi ya chanjo zinazokaribia (siku 14 zijazo) au zilizopitwa na wakati,
 * kwa watoto wote wa mtumiaji. Inatumika kwa 'badge' ya dashboard.
 */
function getVaccinationAlerts(PDO $db, int $userId): array {
    $stmt = $db->prepare("
        SELECT v.*, d.name AS dependent_name
        FROM vaccinations v
        JOIN dependents d ON d.id = v.dependent_id
        WHERE v.user_id = ? AND v.status = 'pending'
        AND v.due_date <= DATE_ADD(CURDATE(), INTERVAL 14 DAY)
        ORDER BY v.due_date ASC
    ");
    $stmt->execute([$userId]);
    $rows = $stmt->fetchAll();

    $overdue = [];
    $upcoming = [];
    $today = date('Y-m-d');
    foreach ($rows as $r) {
        if ($r['due_date'] < $today) {
            $overdue[] = $r;
        } else {
            $upcoming[] = $r;
        }
    }
    return ['overdue' => $overdue, 'upcoming' => $upcoming];
}
