<?php
require_once __DIR__ . '/../config.php';

/** Andika kumbukumbu ya debug (includes/debug.log - imezuiwa kufunguliwa na browser). */
function afyaLog(string $msg): void {
    if (defined('IS_VERCEL') && IS_VERCEL) { error_log('[afya] ' . $msg); return; } // Vercel Logs
    $line = '[' . date('Y-m-d H:i:s') . '] ' . $msg . "\n";
    @file_put_contents(__DIR__ . '/debug.log', $line, FILE_APPEND | LOCK_EX);
}

function getSystemPrompt(array $userProfile = []): string {
    $profileText = '';
    if (!empty($userProfile)) {
        $profileText = "\n\nTaarifa za mtumiaji: Jina: " . ($userProfile['name'] ?? 'Haijulikani')
            . ", Tarehe ya kuzaliwa: " . ($userProfile['date_of_birth'] ?? 'Haijulikani')
            . ", Jinsia aliyozaliwa nayo: " . ($userProfile['sex_at_birth'] ?? 'Haijulikani')
            . ", Anavuta sigara: " . ($userProfile['is_smoker'] ?? 'Haijulikani')
            . ", Ana kisukari: " . ($userProfile['has_diabetes'] ?? 'Haijulikani') . ".";
    }

    return "Wewe ni 'AfyaCheck', msaidizi wa AI wa kuchambua dalili za kiafya (symptom checker), kwa lugha ya Kiswahili."
        . " Kazi yako:\n"
        . "1. Uliza maswali ya kina, MOJA KWA WAKATI, kuhusu dalili za mtumiaji (muda, ukali, mahali, dalili zinazoambatana).\n"
        . "2. USITOE UCHUNGUZI WA UHAKIKA (diagnosis) kamwe. Toa TU uwezekano wa hali (possible conditions) kwa tahadhari, ukisisitiza kuwa hii si mbadala wa daktari.\n"
        . "3. Kama dalili zinaonyesha DHARURA (mf: maumivu makali ya kifua, kushindwa kupumua, kuzimia, kutokwa damu nyingi, dalili za kiharusi/stroke), SIMAMA MARA MOJA na mwambie mtumiaji apige simu huduma za dharura au aende hospitali sasa hivi — usiendelee na maswali mengine.\n"
        . "4. Baada ya maswali ya kutosha (kawaida 4-8), toa MUHTASARI wenye sehemu hizi wazi: 'Uwezekano wa hali' (orodha), 'Kiwango cha uharaka' (dharura/haraka/kawaida/jitunze mwenyewe), na 'Hatua zinazopendekezwa'. Andika 'Kiwango cha uharaka:' kwenye mstari wake peke yake.\n"
        . "5. Kuwa mchangamfu, mwenye huruma, na mfupi katika kila ujumbe (sentensi 2-4 tu kwa kila jibu, isipokuwa muhtasari wa mwisho).\n"
        . "6. Usiwahi kuandika dawa maalum au vipimo vya dozi." . $profileText;
}

/** Kitendo kimoja cha HTTP POST (JSON). Hurudisha [httpCode, body, curlError]. */
function afyaHttpPostJson(string $url, array $headers, array $payload, int $timeout = 40): array {
    if (!function_exists('curl_init')) {
        return [0, '', 'PHP cURL extension haipo kwenye hosting hii'];
    }
    $ch = curl_init($url);
    curl_setopt_array($ch, [
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_POST           => true,
        CURLOPT_HTTPHEADER     => array_merge(['Content-Type: application/json'], $headers),
        CURLOPT_POSTFIELDS     => json_encode($payload, JSON_UNESCAPED_UNICODE),
        CURLOPT_CONNECTTIMEOUT => 10,
        CURLOPT_TIMEOUT        => $timeout,
        CURLOPT_SSL_VERIFYPEER => true,
    ]);
    $body = curl_exec($ch);
    $err  = curl_error($ch);
    $code = (int)curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);
    return [$code, is_string($body) ? $body : '', $err];
}

/** Kumbukumbu fupi: mtandao wa nje ukizuiwa, tusisubiri tena na tena (kila ombi lingechelewa sekunde nyingi). */
function afyaBlockedFile(): string { return (defined('AFYA_WRITABLE_DIR') ? AFYA_WRITABLE_DIR : __DIR__) . '/ai_state.json'; }
function afyaNetworkBlocked(): bool {
    $f = afyaBlockedFile();
    if (!is_file($f)) return false;
    $d = json_decode((string)@file_get_contents($f), true);
    return is_array($d) && ($d['until'] ?? 0) > time();
}
function afyaMarkBlocked(int $seconds = 300): void {
    @file_put_contents(afyaBlockedFile(), json_encode(['until' => time() + $seconds]), LOCK_EX);
}
function afyaClearBlocked(): void { if (is_file(afyaBlockedFile())) @unlink(afyaBlockedFile()); }

function afyaGeminiPayload(array $messages, string $system, string $model = ''): array {
    $contents = [];
    foreach ($messages as $m) {
        $contents[] = [
            'role'  => ($m['role'] ?? '') === 'assistant' ? 'model' : 'user',
            'parts' => [['text' => (string)($m['content'] ?? '')]],
        ];
    }
    $gen = ['maxOutputTokens' => 2048, 'temperature' => 0.6];
    if (strpos($model, '2.5-flash') !== false) {
        $gen['thinkingConfig'] = ['thinkingBudget' => 0]; // jibu la haraka, tokeni hazipotei kwenye "kufikiri"
    }
    return [
        'contents'          => $contents,
        'systemInstruction' => ['parts' => [['text' => $system]]],
        'generationConfig'  => $gen,
    ];
}

/**
 * Gemini kupitia mojawapo ya milango miwili:
 *  - 'studio': AI Studio (generativelanguage.googleapis.com), ufunguo "AIza..."
 *  - 'vertex': Vertex AI express (aiplatform.googleapis.com), ufunguo "AQ...."
 */
function callGemini(array $messages, string $system, string $model, string $mode = 'studio'): array {
    $payload = afyaGeminiPayload($messages, $system, $model);
    if ($mode === 'vertex') {
        $url = rtrim(GEMINI_VERTEX_BASE, '/') . '/publishers/google/models/' . rawurlencode($model)
             . ':generateContent?key=' . rawurlencode(GEMINI_API_KEY);
        $headers = [];
    } else {
        $url = rtrim(GEMINI_API_BASE, '/') . '/models/' . rawurlencode($model) . ':generateContent';
        $headers = ['x-goog-api-key: ' . GEMINI_API_KEY];
    }

    [$code, $body, $err] = afyaHttpPostJson($url, $headers, $payload, 14);

    if ($err) {
        return ['ok' => false, 'status' => 0, 'error' => 'Muunganisho: ' . $err];
    }
    $data = json_decode($body, true);
    if ($code !== 200) {
        $msg = $data['error']['message'] ?? ($data[0]['error']['message'] ?? ('HTTP ' . $code));
        return ['ok' => false, 'status' => $code, 'error' => $msg];
    }
    $parts = $data['candidates'][0]['content']['parts'] ?? [];
    $text = '';
    foreach ($parts as $p) { if (empty($p['thought'])) $text .= $p['text'] ?? ''; }
    $text = trim($text);
    if ($text === '') {
        $why = $data['promptFeedback']['blockReason'] ?? ($data['candidates'][0]['finishReason'] ?? 'haijulikani');
        return ['ok' => false, 'status' => 502, 'error' => 'AI haikutoa jibu (sababu: ' . $why . ')'];
    }
    return ['ok' => true, 'status' => 200, 'reply' => $text];
}

/** Cloudflare Worker proxy (hiari). */
function callProxy(array $messages, string $system): array {
    [$code, $body, $err] = afyaHttpPostJson(
        rtrim(AI_PROXY_URL, '/') . '/chat',
        ['X-Proxy-Secret: ' . PROXY_SECRET],
        ['system' => $system, 'messages' => $messages],
        25
    );
    if ($err) return ['ok' => false, 'status' => 0, 'error' => 'Proxy: ' . $err];
    $data = json_decode($body, true);
    if ($code !== 200 || empty($data['reply'])) {
        return ['ok' => false, 'status' => $code, 'error' => $data['error'] ?? ('Proxy HTTP ' . $code)];
    }
    return ['ok' => true, 'status' => 200, 'reply' => $data['reply']];
}

function afyaModelList(): array {
    $m = [GEMINI_MODEL];
    if (defined('GEMINI_FALLBACK_MODEL')) $m[] = GEMINI_FALLBACK_MODEL;
    $m[] = 'gemini-2.5-flash';
    $m[] = 'gemini-2.0-flash';
    return array_values(array_unique($m));
}

/** Milango ya Google kwa mpangilio. AI Studio kwanza (imethibitishwa kufanya kazi na ufunguo huu); Vertex ni akiba. */
function afyaGoogleModes(): array {
    // Ufunguo wa "AQ." ni wa Vertex express; wa "AIza" ni wa AI Studio. Anza na mlango sahihi.
    return (strpos((string)GEMINI_API_KEY, 'AQ.') === 0) ? ['vertex', 'studio'] : ['studio', 'vertex'];
}

/**
 * Mpangilio: (1) Google Gemini [vertex/studio x models] -> (2) Cloudflare proxy -> (3) msaidizi wa ndani.
 * Hurudisha daima jibu: tathmini HAIKWAMI hata mtandao ukishindwa.
 * @return array ['ok'=>bool,'reply'=>string|null,'error'=>string|null,'source'=>'google'|'proxy'|'local']
 */
function callAI(array $messages, array $userProfile = []): array {
    $system = getSystemPrompt($userProfile);
    $lastError = 'Haijulikani';
    $keySet = defined('GEMINI_API_KEY') && GEMINI_API_KEY !== '' && strpos(GEMINI_API_KEY, 'WEKA_') !== 0;

    if ($keySet && !afyaNetworkBlocked()) {
        $attempts = 0;
        $networkDown = false;
        $t0 = microtime(true);
        foreach (afyaGoogleModes() as $mode) {
            foreach (afyaModelList() as $model) {
                if (++$attempts > 5 || (microtime(true) - $t0) > 16) break 2; // Vercel: jumla chini ya sekunde 30
                $r = callGemini($messages, $system, $model, $mode);
                if ($r['ok']) {
                    afyaClearBlocked();
                    return ['ok' => true, 'reply' => $r['reply'], 'error' => null, 'source' => 'google'];
                }
                $lastError = $r['error'];
                afyaLog("Gemini[$mode/$model] status={$r['status']} error={$lastError}");
                if ($r['status'] === 0) { $networkDown = true; break 2; }          // hosting inazuia mtandao wa nje
                if (in_array($r['status'], [401, 403], true)) break;               // ufunguo/ruhusa: badili mlango, si model
                if (!in_array($r['status'], [400, 404], true)) break;              // 429/5xx: badili mlango
            }
        }
        if ($networkDown) afyaMarkBlocked(300);
    } elseif (!$keySet) {
        $lastError = 'GEMINI_API_KEY haijawekwa kwenye config.php';
        afyaLog($lastError);
    }

    if (defined('AI_PROXY_URL') && defined('PROXY_SECRET')) {
        $r = callProxy($messages, $system);
        if ($r['ok']) return ['ok' => true, 'reply' => $r['reply'], 'error' => null, 'source' => 'proxy'];
        afyaLog('Proxy error=' . $r['error']);
        $lastError = $r['error'];
    }

    // Njia ya mwisho: msaidizi wa ndani (haitegemei mtandao).
    require_once __DIR__ . '/localchat.php';
    afyaLog('Using LOCAL assistant. Sababu ya mwisho: ' . $lastError);
    return ['ok' => true, 'reply' => localAssessmentReply($messages), 'error' => null, 'source' => 'local'];
}
