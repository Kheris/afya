<?php
/**
 * Maktaba Fupi ya Elimu ya Afya.
 * Makala hizi ni maandishi rahisi ya uelewa wa jumla (general wellness
 * education), SI ushauri wa kimatibabu wa mtu mmoja mmoja.
 */
function getSeedArticles(): array {
    return [
        [
            'slug' => 'malaria-dalili-kinga',
            'title' => 'Malaria: Dalili, Kinga na Wakati wa Kuonana na Daktari',
            'category' => 'Magonjwa ya Kawaida',
            'summary' => 'Jinsi ya kutambua dalili za malaria mapema na njia za kujikinga.',
            'keywords' => 'malaria,homa,mbu,baridi,kutapika,maumivu ya kichwa',
            'body' => "Malaria ni ugonjwa unaosababishwa na vimelea vinavyoenezwa na mbu aina ya Anopheles. Dalili za kawaida ni homa kali, baridi na kutetemeka, maumivu ya kichwa, uchovu mkubwa, kutapika, na maumivu ya viungo.\n\nDalili mara nyingi huanza siku 7-15 baada ya kuumwa na mbu mwenye vimelea. Ni muhimu kupima haraka kwenye kituo cha afya kama una homa isiyoeleweka, hasa maeneo yenye malaria nyingi.\n\nNENDA HOSPITALINI MARA MOJA kama una: kuchanganyikiwa au kupoteza fahamu, kushindwa kunywa maji au dawa, kutapika mara kwa mara, kupumua kwa shida, au degedege (fits) -- hizi ni dalili za malaria kali (severe malaria) na zinahitaji matibabu ya haraka.\n\nKinga: lala ndani ya chandarua kilichowekwa dawa (LLIN) kila usiku, ziba nyufa na madirisha, ondoa maji yaliyotuama karibu na nyumba (mazalia ya mbu), na tumia dawa za kufukuza mbu jioni.\n\nUkiwa na dalili, tumia kipengele cha 'Anza tathmini ya dalili' kwenye AfyaCheck kupata mwongozo, kisha nenda kituo cha afya kwa kipimo rasmi.",
        ],
        [
            'slug' => 'shinikizo-la-damu',
            'title' => 'Shinikizo la Damu (Presha): Kuelewa Vipimo na Kuvidhibiti',
            'category' => 'Magonjwa Sugu',
            'summary' => 'Nini maana ya vipimo vya BP na hatua rahisi za kuvidhibiti nyumbani.',
            'keywords' => 'presha,shinikizo la damu,systolic,diastolic,moyo',
            'body' => "Shinikizo la damu (blood pressure) hupimwa kwa namba mbili: systolic (ya juu) na diastolic (ya chini), mfano 120/80 mmHg. Shinikizo la kawaida ni chini ya 120/80. Kati ya 120-139/80-89 ni hatua ya awali (elevated/pre-hypertension). 140/90 na kuendelea mara kwa mara ni shinikizo la juu (hypertension).\n\nShinikizo la juu mara nyingi halina dalili za wazi -- ndiyo maana linaitwa 'muuaji kimya'. Ndiyo maana ni muhimu kupima mara kwa mara, hasa ukiwa na umri zaidi ya miaka 40 au una historia ya familia.\n\nHatua za kupunguza shinikizo: punguza chumvi kwenye chakula, fanya mazoezi ya kutembea dakika 30 kwa siku, epuka uvutaji sigara, punguza pombe, na dhibiti uzito.\n\nNENDA HOSPITALINI MARA MOJA kama shinikizo liko juu sana (zaidi ya 180/120) pamoja na maumivu ya kichwa makali, kizunguzungu kikali, kuona ukungu, au maumivu ya kifua -- hii inaweza kuwa dharura ya shinikizo (hypertensive crisis).\n\nTumia kipengele cha 'Vipimo vya Mwili' kwenye AfyaCheck kufuatilia shinikizo lako kwa muda na kuona mwenendo wake.",
        ],
        [
            'slug' => 'kisukari-msingi',
            'title' => 'Kisukari: Dalili za Awali na Jinsi ya Kuishi Nacho',
            'category' => 'Magonjwa Sugu',
            'summary' => 'Dalili za mwanzo za kisukari na tabia za kila siku zinazosaidia kuudhibiti.',
            'keywords' => 'kisukari,sukari,kiu,mkojo,uchovu',
            'body' => "Dalili za awali za kisukari ni pamoja na: kiu ya mara kwa mara, kukojoa mara nyingi (hasa usiku), uchovu usio wa kawaida, njaa kupita kiasi, kupungua uzito bila sababu, na vidonda vinavyochelewa kupona.\n\nKisukari kikiachwa bila kudhibitiwa kinaweza kuathiri macho, figo, mishipa ya fahamu, na moyo kwa muda mrefu.\n\nHatua za kila siku: fuatilia sukari ya damu kama daktari amependekeza, punguza vyakula vya sukari na wanga uliokobolewa, ongeza mboga na nyuzinyuzi, fanya mazoezi mara kwa mara, na hakikisha unatumia dawa/insulini kama ulivyoelekezwa -- usiache dawa bila ushauri wa daktari.\n\nNENDA HOSPITALINI MARA MOJA kama una: kuchanganyikiwa ghafla, kutetemeka sana na jasho jingi (dalili za sukari kushuka chini sana), kupumua haraka na pumzi yenye harufu ya matunda, au kutapika sana pamoja na maumivu ya tumbo (dalili za sukari kupanda sana).\n\nKama una kisukari, hakikisha umejaza taarifa hii kwenye 'Wasifu' wako ili AfyaCheck iweze kukupa ushauri sahihi zaidi wakati wa tathmini ya dalili.",
        ],
        [
            'slug' => 'kuhara-watoto',
            'title' => 'Kuhara kwa Watoto: Jinsi ya Kutunza Nyumbani na Dalili za Hatari',
            'category' => 'Afya ya Mtoto',
            'summary' => 'Jinsi ya kumtunza mtoto mwenye kuhara na wakati wa kukimbilia hospitali.',
            'keywords' => 'kuhara,mtoto,upungufu wa maji,ORS',
            'body' => "Kuhara ni kawaida kwa watoto wadogo, lakini hatari kubwa ni upungufu wa maji mwilini (dehydration). Mpe mtoto maji ya ORS (Oral Rehydration Salts) mara kwa mara, kijiko kimoja hadi viwili kila baada ya dakika chache kama anatapika.\n\nEndelea kumnyonyesha au kumpa chakula cha kawaida -- kutokula hakusaidii, badala yake kunamdhoofisha zaidi.\n\nDalili za upungufu mkubwa wa maji (nenda hospitali MARA MOJA): mdomo mkavu sana, machozi hayatoki analia, hakojoi kwa masaa 6-8, macho yaliyozama ndani, kulegea au kutosikia vizuri, au ngozi inayorudi taratibu sana ukiibana kidogo.\n\nNenda hospitalini pia kama: kuna damu kwenye choo, kuhara kunaendelea zaidi ya siku 2-3, au mtoto ana homa kali pamoja na kuhara.\n\nOsha mikono kwa sabuni kabla ya kumlisha mtoto na baada ya kubadilisha nepi, kunasaidia kuzuia kuhara kuenea.",
        ],
        [
            'slug' => 'afya-ya-uzazi-mzunguko',
            'title' => 'Mzunguko wa Hedhi: Nini Kawaida na Nini Kinachohitaji Uchunguzi',
            'category' => 'Afya ya Uzazi',
            'summary' => 'Kuelewa mzunguko wa kawaida wa hedhi na dalili zinazohitaji kuonana na daktari.',
            'keywords' => 'hedhi,mzunguko,maumivu ya tumbo,damu nyingi',
            'body' => "Mzunguko wa hedhi wa kawaida ni kati ya siku 21-35 (wastani siku 28), na hedhi yenyewe hudumu siku 2-7. Ni kawaida kuwa na tofauti kidogo mwezi hadi mwezi.\n\nMaumivu madogo ya tumbo siku za kwanza za hedhi ni ya kawaida. Lakini muone daktari kama: maumivu ni makali sana hadi yanakuzuia kufanya shughuli za kawaida, damu inatoka nyingi sana (kubadilisha pedi/kitambaa kila saa), hedhi inakosekana kwa miezi 3 mfululizo bila ujauzito, au unavuja damu kati ya hedhi mara kwa mara.\n\nKutumia 'Kalenda ya Hedhi' kwenye AfyaCheck kunakusaidia kufuatilia mzunguko wako na kutambua mabadiliko yasiyo ya kawaida mapema.\n\nHii ni taarifa ya jumla tu -- kama una wasiwasi wowote kuhusu mzunguko wako, ni bora kuonana na daktari au mkunga kwa uchunguzi zaidi.",
        ],
    ];
}

function seedArticlesIfEmpty(PDO $db): void {
    $count = (int)$db->query("SELECT COUNT(*) FROM articles")->fetchColumn();
    if ($count > 0) return;

    $stmt = $db->prepare("INSERT INTO articles (slug, title, category, summary, body, keywords) VALUES (?, ?, ?, ?, ?, ?)");
    foreach (getSeedArticles() as $a) {
        $stmt->execute([$a['slug'], $a['title'], $a['category'], $a['summary'], $a['body'], $a['keywords']]);
    }
}

/**
 * Pendekeza makala zinazohusiana na maandishi fulani (mfano muhtasari wa
 * tathmini ya dalili), kwa kulinganisha na 'keywords' za kila makala.
 */
function getRelatedArticles(PDO $db, string $text, int $limit = 2): array {
    $text = mb_strtolower($text);
    $stmt = $db->query("SELECT id, slug, title, category, summary, keywords FROM articles");
    $all = $stmt->fetchAll();

    $scored = [];
    foreach ($all as $article) {
        $score = 0;
        $kws = array_filter(array_map('trim', explode(',', mb_strtolower($article['keywords'] ?? ''))));
        foreach ($kws as $kw) {
            if ($kw !== '' && mb_strpos($text, $kw) !== false) {
                $score++;
            }
        }
        if ($score > 0) {
            $scored[] = ['score' => $score, 'article' => $article];
        }
    }

    usort($scored, fn($a, $b) => $b['score'] <=> $a['score']);
    return array_map(fn($s) => $s['article'], array_slice($scored, 0, $limit));
}
