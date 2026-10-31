# AfyaCheck - Update #5 (v10): Progressive Update + Debug

## Hatua za kuweka (dakika 3)
1. Pakia faili zote kwenye `htdocs` (zinabadilisha za zamani). `config.php` tayari ina DB yako na `GEMINI_API_KEY`.
2. phpMyAdmin -> Import -> `schema_update_5.sql` (salama kuendesha mara nyingi).
3. Fungua tovuti, ingia, jaribu tathmini moja ya AI. Ikishindwa, soma `includes/debug.log` (File Manager).
4. Kila unapotoa update ijayo: badilisha `APP_VERSION` kwenye `config.php`. Watumiaji wataona banner "Toleo jipya liko tayari - Sasisha".

## Bugs zilizorekebishwa
- **AI haikuweza kufanya kazi kabisa**: `includes/ai.php` bado ilitumia `AI_PROXY_URL`/`PROXY_SECRET` (hazipo tena kwenye config) -> Fatal error. Sasa inaita Gemini moja kwa moja; Worker ni hiari kama InfinityFree itazuia cURL.
- Model ikikataliwa (404), inajaribu `GEMINI_FALLBACK_MODEL` kiotomatiki.
- `compat.php` haikuwa ikipakiwa popote -> `mb_*` zingevunja kurasa kwenye hosting isiyo na mbstring. Sasa inapakiwa kwenye `config.php`.
- **Kalenda ya hedhi**: kama hedhi ya mwisho ilirekodiwa zaidi ya siku 28 zilizopita, tarehe za fertile/ovulation/kalenda zilionyesha mzunguko wa zamani. Sasa zinahesabiwa kwa mzunguko wa sasa.
- Kalenda: hedhi ya tarehe ile ile haijirudii tena, tarehe zisizo halisi (2026-02-31) zinakataliwa, kuna kitufe cha kufuta rekodi, dark mode inafanya kazi, `days_until_next` tupu haionyeshi "Leo" kimakosa.
- Kiwango cha uharaka kilikosea ("kawaida (si dharura)" ilihesabiwa dharura). Sasa kinasoma mstari wa "Kiwango cha uharaka" tu; AI ikisimama mapema kwa dharura, alama nyekundu bado inahifadhiwa.
- `api/chat.php`: ombi lisilo JSON, ujumbe mrefu (kikomo herufi 2000), historia (ujumbe 30 wa mwisho kwa AI), rate-limit sasa ni atomic.
- Makosa ya kiufundi ya AI hayaonyeshwi kwa mtumiaji; yanaandikwa `includes/debug.log` (imezuiwa na `.htaccess`).
- `schema_update_4.sql` ilishindwa ukiiendesha mara ya pili (CREATE INDEX); sasa ni salama.
- Ulinzi wa CSRF kwenye kalenda (`includes/csrf.php`).

## PWA (progressive)
- Service worker ilikuwepo lakini **haikusajiliwa popote** na icons hazikuwepo. Sasa: `assets/js/pwa.js`, icons 192/512/maskable, manifest kamili (shortcuts: tathmini, kalenda, SOS).
- Update haikatishi tathmini: toleo jipya linasubiri ukubali "Sasisha".
- Faragha: kurasa zenye data ya afya HAZIhifadhiwi offline; ni `emergency.php` na `offline.html` tu. Logout inafuta cache.
- Banner ya "Sakinisha app" na hali ya offline.

## Bado hayajafanyika (mapendekezo)
- CSRF kwenye fomu nyingine (vitals, dependents, vaccinations, settings, profile, login/register).
- Login rate-limit (kuzuia kubahatisha password).
- Kuunganisha `period-tracker.php` na `calendar.php` (zinafanana).

---
## v10.1 - Tathmini ya dalili isigome tena
Njia nne zinajaribiwa kwa zamu hadi moja ifanye kazi:
1. **Gemini** - ufunguo wa `AQ.` hutumia Vertex AI express (`aiplatform.googleapis.com`), `AIza` hutumia AI Studio. Models: `gemini-3.5-flash` -> `2.5-flash` -> `2.0-flash`.
2. **Cloudflare Worker** (hiari; zima `//` kwenye `config.php`). `worker-gemini.js` imesasishwa kwa Vertex key + model fallback.
3. **Msaidizi wa ndani** (`includes/localchat.php`): hauhitaji mtandao wala AI; maswali 3 kisha muhtasari, na kugundua dharura. Haujumuishwi kwenye kikomo cha ujumbe 40.
- Hosting ikizuia mtandao wa nje, mfumo hukumbuka kwa dakika 5 (`includes/ai_state.json`) ili usisubiri kila ombi.
- **Uchunguzi:** fungua `test-ai.php` ukiwa umeingia -> "Jaribu AI sasa". Inaonyesha kila njia, code, na kosa. **Ifute baada ya kutumia.**
- `includes/debug.log` ina historia ya makosa.
