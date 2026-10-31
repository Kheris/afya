<?php
/**
 * Vitendaji vya kuhesabu mzunguko wa hedhi na kutoa ushauri wa afya
 * kulingana na awamu ya sasa. Hii ni makadirio ya jumla tu (general
 * wellness awareness), si utambuzi wa kiafya.
 */

function calculateCycleInfo(string $lastPeriodStart, int $avgCycleLength, int $avgPeriodLength): array {
    $today = new DateTime('today');
    $start = new DateTime($lastPeriodStart);

    if ($start > $today) {
        // Tarehe ya baadaye kimakosa - rudisha default salama
        return [
            'cycle_day'     => 1,
            'cycle_start'   => $start,
            'phase'         => 'unknown',
            'next_period'   => $start,
            'fertile_start' => null,
            'fertile_end'   => null,
            'days_until_next' => null,
        ];
    }

    $daysSinceStart = (int)$today->diff($start)->format('%a');
    $cycleDay = ($daysSinceStart % $avgCycleLength) + 1;

    // Mwanzo wa mzunguko WA SASA (si wa hedhi iliyorekodiwa). Kama hedhi ya mwisho ilirekodiwa
    // zaidi ya siku za mzunguko zilizopita, tarehe za fertile/ovulation lazima zihamie mzunguko wa sasa.
    $currentStart = clone $start;
    $currentStart->modify('+' . (intdiv($daysSinceStart, $avgCycleLength) * $avgCycleLength) . ' days');

    $cyclesPassed = intdiv($daysSinceStart, $avgCycleLength) + 1;
    $nextPeriod = clone $start;
    $nextPeriod->modify('+' . ($cyclesPassed * $avgCycleLength) . ' days');

    $ovulationDay = max(1, $avgCycleLength - 14);
    $fertileStartDay = max(1, $ovulationDay - 5);
    $fertileEndDay   = min($avgCycleLength, $ovulationDay + 1);

    if ($cycleDay <= $avgPeriodLength) {
        $phase = 'menstrual';
    } elseif ($cycleDay < $fertileStartDay) {
        $phase = 'follicular';
    } elseif ($cycleDay <= $fertileEndDay) {
        $phase = 'ovulation';
    } else {
        $phase = 'luteal';
    }

    $daysUntilNext = (int)$today->diff($nextPeriod)->format('%a');
    if ($today > $nextPeriod) $daysUntilNext = 0;

    $fertileStart = clone $currentStart;
    $fertileStart->modify('+' . ($fertileStartDay - 1) . ' days');
    $fertileEnd = clone $currentStart;
    $fertileEnd->modify('+' . ($fertileEndDay - 1) . ' days');

    return [
        'cycle_day'        => $cycleDay,
        'cycle_start'      => $currentStart,
        'ovulation_day'    => $ovulationDay,
        'phase'            => $phase,
        'next_period'      => $nextPeriod,
        'fertile_start'    => $fertileStart,
        'fertile_end'      => $fertileEnd,
        'days_until_next'  => $daysUntilNext,
    ];
}

function getPhaseLabel(string $phase): string {
    $labels = [
        'menstrual'  => 'Siku za Hedhi',
        'follicular' => 'Awamu ya Follicular',
        'ovulation'  => 'Kipindi cha Uwezekano wa Kupata Mimba (Ovulation)',
        'luteal'     => 'Awamu ya Luteal',
        'unknown'    => 'Haijabainika',
    ];
    return $labels[$phase] ?? 'Haijabainika';
}

function getPhaseTips(string $phase): array {
    $tips = [
        'menstrual' => [
            'Pumzika zaidi na sikiliza mwili wako - haihitajiki kulazimisha shughuli nzito.',
            'Kunywa maji mengi na kula vyakula vyenye madini ya chuma (mchicha, maharage, nyama nyekundu) kuchukua nafasi ya damu iliyopotea.',
            'Kitambaa/chupa ya maji ya moto tumboni inaweza kupunguza maumivu ya hedhi.',
            'Mazoezi mepesi kama kutembea yanaweza kupunguza maumivu ya tumbo kuliko kukaa tu.',
        ],
        'follicular' => [
            'Nishati kawaida huongezeka kipindi hiki - muda mzuri wa kuanza mazoezi mapya au miradi.',
            'Kula protini na mboga za majani kusaidia mwili kujijenga upya.',
            'Usingizi wa kutosha (masaa 7-9) unasaidia homoni kusawazika vizuri.',
        ],
        'ovulation' => [
            'Hiki ni kipindi cha uwezekano mkubwa wa kupata mimba - zingatia hili kama unapanga au kuepuka mimba.',
            'Baadhi ya wanawake hupata maumivu madogo upande mmoja wa tumbo (mittelschmerz) - ni kawaida, lakini maumivu makali yanahitaji kuonana na daktari.',
            'Nishati na hamu ya chakula mara nyingi huwa juu - endelea kula vyakula vyenye lishe bora.',
        ],
        'luteal' => [
            'Dalili za PMS (hasira, uchovu, maumivu ya matiti) zinaweza kutokea - ni kawaida kabisa.',
            'Punguza kafeini, chumvi, na sukari kama unapata uvimbe (bloating) au mabadiliko ya hisia.',
            'Vyakula vyenye magnesiamu (njugu, ndizi, chokoleti nyeusi) vinaweza kusaidia kupunguza dalili za PMS.',
            'Lala mapema - luteal phase mara nyingi huleta uchovu zaidi.',
        ],
        'unknown' => [
            'Weka tarehe sahihi ya mwanzo wa hedhi yako ya mwisho ili tupate kukupa ushauri sahihi.',
        ],
    ];
    return $tips[$phase] ?? $tips['unknown'];
}
