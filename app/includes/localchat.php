<?php
/**
 * Msaidizi wa ndani (bila AI, bila mtandao). Hutumika pale AI ya mtandaoni inaposhindikana,
 * ili tathmini ya dalili ISIKWAME. Hufuata mtiririko ule ule: maswali 3 kisha muhtasari.
 * Hii ni tathmini ya msingi tu - si uchunguzi wa daktari.
 */

function localNorm(string $s): string {
    return mb_strtolower($s);
}

function localHas(string $text, array $words): bool {
    foreach ($words as $w) { if (strpos($text, $w) !== false) return true; }
    return false;
}

function localEmergencyCheck(string $all): bool {
    return localHas($all, [
        'maumivu makali ya kifua', 'maumivu ya kifua', 'kifua kinabana', 'kushindwa kupumua', 'siwezi kupumua',
        'napumua kwa shida', 'nimezimia', 'kuzimia', 'kupoteza fahamu', 'amepoteza fahamu', 'degedege', 'kifafa',
        'kutokwa damu nyingi', 'damu nyingi', 'kutapika damu', 'kikohozi cha damu', 'uso umepinda', 'mkono umelegea',
        'kiharusi', 'stroke', 'nimejiua', 'kujiua', 'sumu', 'nimemeza dawa nyingi', 'mimba na damu', 'kujifungua',
    ]);
}

/** @return array{0:string,1:string,2:string,3:string} [jina, uwezekano, hatua, uharaka-chaguo-msingi] */
function localMatchCategory(string $t): array {
    $cats = [
        ['homa/malaria', ['homa', 'baridi', 'joto kali', 'kutetemeka', 'malaria', 'jasho'],
         "Malaria; maambukizi ya virusi (mafua/homa ya kawaida); maambukizi ya bakteria (mf. UTI, typhoid)",
         "Pima malaria (mRDT) kwenye kituo cha afya au duka la dawa lililosajiliwa; kunywa maji mengi; pumzika; usijitibu kwa dawa za kubahatisha"],
        ['kichwa', ['kichwa', 'kipandauso', 'migraine'],
         "Maumivu ya kichwa ya mkazo/uchovu; upungufu wa maji mwilini; kipandauso; malaria (kama kuna homa); shinikizo la damu",
         "Kunywa maji, pumzika mahali tulivu, pima shinikizo la damu; ona daktari kama maumivu ni makali ghafla au yanajirudia mara kwa mara"],
        ['kupumua/kikohozi', ['kikohozi', 'kukohoa', 'mafua', 'pumu', 'kifua', 'kupumua', 'makohozi', 'koo'],
         "Mafua/homa ya kawaida; maambukizi ya njia ya hewa; pumu; nimonia (kama kuna homa na kupumua kwa shida)",
         "Pumzika na kunywa vinywaji vya moto; epuka moshi; ona daktari kama kikohozi kinazidi wiki 2, kuna homa kali, au kupumua kunakuwa kugumu. Kikohozi cha wiki 2+ kinahitaji kupimwa TB"],
        ['tumbo', ['tumbo', 'kuharisha', 'kutapika', 'kichefuchefu', 'choo', 'kuhara', 'gesi', 'kiungulia'],
         "Maambukizi ya tumbo (gastroenteritis); sumu ya chakula; vidonda vya tumbo/kiungulia; minyoo; typhoid",
         "Kunywa maji safi na ORS kuzuia upungufu wa maji mwilini; kula chakula chepesi; ona daktari haraka kama kuna damu kwenye choo, kutapika kusikoisha, au mtoto/mzee ana dalili hizi"],
        ['mgongo/viungo', ['mgongo', 'kiuno', 'viungo', 'goti', 'bega', 'shingo', 'misuli', 'mguu', 'mkono'],
         "Mkazo wa misuli; mkao mbaya wa kukaa/kulala; uvimbe wa viungo (arthritis); jeraha dogo",
         "Pumzika, usibebe vitu vizito, tumia kitambaa chenye joto; ona daktari kama kuna ganzi, udhaifu wa mguu/mkono, au maumivu baada ya ajali"],
        ['ngozi', ['ngozi', 'upele', 'kuwasha', 'vipele', 'uvimbe', 'chunusi', 'fangasi'],
         "Mzio (allergy); fangasi; upele wa joto; maambukizi ya ngozi",
         "Osha eneo kwa maji safi na sabuni laini; usikune; epuka kilichokuchochea; ona daktari kama upele unaenea haraka au kuna homa"],
        ['macho/masikio', ['jicho', 'macho', 'sikio', 'masikio', 'kuona'],
         "Maambukizi ya jicho/sikio; mzio; uchovu wa macho",
         "Usiguse wala kusugua eneo; osha mikono; ona daktari wa macho/masikio haraka kama kuna maumivu makali au kupungua kwa uwezo wa kuona/kusikia"],
        ['mkojo/uzazi', ['mkojo', 'kukojoa', 'uke', 'hedhi', 'kiuno cha chini', 'uume'],
         "Maambukizi ya njia ya mkojo (UTI); maambukizi ya uzazi; mabadiliko ya homoni",
         "Kunywa maji mengi; usizuie mkojo; pima mkojo kituo cha afya; usitumie dawa bila kupimwa"],
        ['uchovu/jumla', ['uchovu', 'kizunguzungu', 'dhaifu', 'usingizi', 'kukosa hamu', 'kupungua uzito', 'kiu'],
         "Ukosefu wa damu (anaemia); upungufu wa maji/lishe; msongo wa mawazo; sukari ya damu isiyo sawa; malaria",
         "Kula mlo kamili, lala masaa 7-9, pima damu (HB) na sukari kituo cha afya"],
    ];
    foreach ($cats as $c) {
        if (localHas($t, $c[1])) return [$c[0], $c[2], $c[3], 'routine'];
    }
    return ['jumla', "Hali ndogo inayoweza kuwa ya muda mfupi, au dalili isiyo wazi inayohitaji kupimwa",
            "Pumzika, kunywa maji, fuatilia dalili kwa siku 2-3; ona daktari kama zinaendelea au kuzidi", 'routine'];
}

/** @param array $conversation mazungumzo yote yakiwemo ujumbe wa mtumiaji wa sasa */
function localAssessmentReply(array $conversation): string {
    $userMsgs = [];
    foreach ($conversation as $m) {
        if (($m['role'] ?? '') === 'user') $userMsgs[] = (string)$m['content'];
    }
    $n = count($userMsgs);
    $all = localNorm(implode(' . ', $userMsgs));

    if (localEmergencyCheck($all)) {
        return "Dalili ulizotaja zinaweza kuwa za DHARURA. Tafadhali nenda hospitali iliyo karibu SASA HIVI au piga simu ya dharura "
             . "(Ambulance 115, Polisi 112). Usisubiri, na usiendeshe mwenyewe kama unajisikia vibaya. Kama ni mtu mwingine, mkae naye hadi msaada ufike.";
    }

    switch ($n) {
        case 1:
            return "Pole kwa hali hiyo. Dalili hizi zilianza lini (leo, siku chache zilizopita, au zaidi ya wiki)?";
        case 2:
            return "Asante. Kwa kipimo cha 1 hadi 10 (10 ikiwa ni kali kupita kiasi), ukali wa dalili zako ni kiasi gani?";
        case 3:
            return "Sawa. Je, una dalili nyingine zinazoambatana, kama homa, kutapika, kikohozi, kuharisha, kizunguzungu, au upele? Kama hakuna, andika \"hakuna\".";
    }

    // --- Muhtasari (baada ya majibu 4+) ---
    [$cat, $conds, $steps, $urg] = localMatchCategory($all);

    $severity = 0;
    // Mpangilio wa majibu: [0]=dalili, [1]=muda, [2]=ukali (1-10), [3]=dalili nyingine
    if (isset($userMsgs[2]) && preg_match('/\b(10|[1-9])\b/', $userMsgs[2], $m)) $severity = (int)$m[1];
    $long = localHas(localNorm($userMsgs[1] ?? ''), ['wiki', 'mwezi', 'miezi', 'siku nyingi', 'siku 4', 'siku 5', 'siku 6', 'siku 7', 'zaidi ya']);
    $fever = localHas($all, ['homa', 'joto kali', 'kutetemeka']);
    $severe = localHas($all, ['kali sana', 'siwezi kuvumilia', 'inazidi', 'inaongezeka', 'kutapika kila', 'sili wala', 'siwezi kula']);

    $urg = 'kawaida';
    if ($severity >= 8 || $severe)                                   $urg = 'haraka';
    elseif (($severity >= 6 && $long) || ($fever && $severity >= 6)) $urg = 'haraka';
    elseif ($severity > 0 && $severity <= 3 && !$long)               $urg = 'jitunze mwenyewe';

    $urgText = [
        'haraka' => 'haraka (ona daktari ndani ya saa 24)',
        'kawaida' => 'kawaida (panga kumuona daktari siku chache zijazo kama hali haiboreshi)',
        'jitunze mwenyewe' => 'jitunze mwenyewe (unaweza kujihudumia nyumbani na kufuatilia)',
    ][$urg];

    return "Muhtasari wa tathmini yako (tathmini ya msingi bila AI)\n\n"
         . "Uwezekano wa hali:\n" . implode("\n", array_map(fn($x) => '- ' . trim($x), explode(';', $conds))) . "\n\n"
         . "Kiwango cha uharaka: " . $urgText . "\n\n"
         . "Hatua zinazopendekezwa:\n" . $steps . ".\n\n"
         . "Nenda hospitali SASA kama utapata kushindwa kupumua, maumivu makali ya kifua, kuzimia, degedege, au kutokwa damu nyingi. "
         . "Hii si uchunguzi rasmi wala mbadala wa daktari.";
}
