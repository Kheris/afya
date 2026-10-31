# AfyaCheck - Vipengele Vipya (Update #4)

Vipengele vilivyoongezwa:
1. **Chanjo za Watoto** - `vaccinations.php` + `includes/vaccines.php`
2. **Ufuatiliaji wa Baada ya Tathmini** - `follow-up.php` + `includes/followup.php` + banner kwenye `dashboard.php`
3. **Grafu za Mienendo kwenye Dashboard** - uzito na mzunguko wa hedhi sasa vinaonekana kwenye `dashboard.php`
4. **Kushiriki Ripoti WhatsApp** - kitufe kipya kwenye `assessment-report.php`
5. **Maktaba ya Elimu ya Afya** - `articles.php` + `article.php` + `includes/articles.php` (makala 5 za awali zinaongezwa kiotomatiki)
6. **Offline Bora Zaidi** - `sw.js` mpya + `offline.html`

## MUHIMU: Bug iliyorekebishwa kwenye code ya zamani

`includes/compat.php` ni faili **mpya muhimu**. Code yako iliyokuwepo (`api/chat.php`)
ilikuwa inatumia `mb_strtolower()` na `mb_substr()`. Hosting nyingi za bure
(ikiwemo baadhi ya akaunti za InfinityFree) hazina extension ya `mbstring`,
na hapo ungepata **"Fatal error: Call to undefined function mb_strtolower()"**
wakati wa tathmini ya AI. `compat.php` inatoa polyfill salama, na inapakiwa
kiotomatiki kupitia `config.php`. Usisahau kuipakia.

## Hatua za Ku-install (InfinityFree / hosting yoyote ya PHP+MySQL)

1. **Pakia faili zote** kwenye folda ya `htdocs` (au public_html) - zitachanganyika na zilizopo, hazitafuta chochote.
2. **Endesha SQL mpya**: fungua phpMyAdmin -> database yako -> tab ya "Import" -> chagua `schema_update_4.sql` -> Go.
   (Hii inaongeza tu majedwali/columns mpya: `vaccinations`, `articles`, columns za `follow_up_*`, na index mbili za utendaji. Haigusi data iliyopo.)
3. Hakuna mabadiliko ya `config.php` yanayohitajika (isipokuwa mstari mmoja wa `compat.php` ambao tayari upo kwenye faili niliyokupa).
4. Fungua tovuti yako, ingia, fungua Dashboard - vipengele vipya vitaonekana moja kwa moja.

## Majaribio yaliyofanyika

Code hii imejaribiwa kwa vitendo kwenye PHP 8.3 + MariaDB 10.11:
- `php -l` kwenye faili zote 33 za PHP - hakuna syntax error
- Migrations zote 5 zimeendeshwa kwa mfululizo kwenye database safi - zote OK
- Mtiririko kamili: register -> consent -> dashboard -> dependents -> vaccinations -> articles -> vitals -> period tracker -> assessment report -> follow-up
- **Usalama (IDOR)**: mtumiaji mmoja hawezi kusoma wala kubadilisha tathmini, chanjo, au follow-up ya mtumiaji mwingine - majaribio 4 yote yamezuiwa
- **XSS**: majina yenye `<script>` yanatolewa yakiwa yamefungwa (escaped)
- **SQL injection**: prepared statements zimeshikilia
- Edge cases: slug isiyopo, dependent asiye na tarehe ya kuzaliwa, dependent ID isiyo namba, thamani za follow-up zisizo halali, mtumiaji asiye na data - zote hazivunji ukurasa
- Hakuna rekodi za chanjo zinazojirudia (duplicates) hata ukifungua dashboard mara nyingi

## Vidokezo

- Makala za elimu (articles) zinaongezwa kiotomatiki mara ya kwanza mtu anapofungua `articles.php` au `assessment-report.php` (function `seedArticlesIfEmpty`). Unaweza kuongeza makala zaidi moja kwa moja kwenye jedwali la `articles` kupitia phpMyAdmin.
- Ratiba ya chanjo (EPI Tanzania) inatengenezwa kiotomatiki kwa kila mtoto (dependent) mwenye tarehe ya kuzaliwa, mara ya kwanza dashboard au vaccinations.php inapofunguliwa. Inaweza kuhaririwa kwenye `includes/vaccines.php` -> `getEpiScheduleTanzania()`.
- Banner ya follow-up inaonekana kwa tathmini za `urgent`/`emergency` ambazo zimepita angalau siku 1 (na si zaidi ya siku 14) bila kujibiwa.
- Ratiba ya EPI ni muongozo wa jumla. Kabla ya kuitumia kwa watumiaji halisi, ni vyema kuihakiki na muuguzi/mtaalamu wa afya au nyaraka rasmi za Wizara ya Afya, kwa sababu ratiba hizi hubadilika mara kwa mara.
