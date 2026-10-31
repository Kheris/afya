# Jinsi ya kuweka AfyaCheck kwenye Vercel kupitia GitHub

## Muhimu kujua kwanza
- Vercel haina MySQL wala hosting ya PHP ya kawaida. PHP inaendeshwa kwa "community runtime" (`vercel-php`) kama serverless function.
- **Database ya InfinityFree HAIFAI** — InfinityFree huzuia miunganisho kutoka nje ya hosting yao. Unahitaji MySQL ya nje (bure): **TiDB Cloud Serverless** (inapendekezwa), Aiven, au Railway.
- Siri (nenosiri la DB, ufunguo wa Gemini) hazimo tena kwenye code. Zinawekwa kwenye Vercel kama Environment Variables.
- Session za login zinahifadhiwa kwenye database (jedwali `sessions`), si kwenye faili.

## Hatua 1: Tengeneza database
1. Fungua akaunti kwenye https://tidbcloud.com → **Create Cluster → Serverless (Free)**.
2. Bonyeza **Connect** → chukua: host, port (4000), user, password. Tengeneza database iitwe `afya`.
3. Kwenye **SQL Editor** (au mteja yeyote wa MySQL) bandika na uendeshe yote yaliyo kwenye `database/install.sql`.
4. (Hiari) Ukitaka kuhamisha data ya zamani ya InfinityFree: export kutoka phpMyAdmin (data tu), import kwenye database mpya.

## Hatua 2: Pandisha kwenye GitHub
```bash
cd afya-vercel
git init
git add .
git commit -m "AfyaCheck kwa Vercel"
git branch -M main
git remote add origin https://github.com/JINA_LAKO/afyacheck.git
git push -u origin main
```
(Tengeneza repo tupu `afyacheck` kwenye github.com kwanza. `.gitignore` tayari inazuia siri kupanda.)

## Hatua 3: Unganisha na Vercel
1. https://vercel.com → **Add New → Project → Import** repo yako.
2. **Framework Preset: Other**. Acha Build Command na Output Directory wazi.
3. Kabla ya **Deploy**, fungua **Environment Variables** na uweke (angalia `.env.example`):

| Jina | Thamani |
|---|---|
| `DB_HOST` | host ya TiDB (mf. `gateway01.xxx.prod.aws.tidbcloud.com`) |
| `DB_PORT` | `4000` (TiDB) au `3306` |
| `DB_NAME` | `afya` |
| `DB_USER` | user wa database |
| `DB_PASS` | nenosiri la database |
| `DB_SSL` | `1` |
| `GEMINI_API_KEY` | ufunguo wako wa Gemini |

4. Bonyeza **Deploy**. Kila `git push` kwenda `main` inasasisha tovuti kiotomatiki.

## Usalama — FANYA HII
Nenosiri la DB ya InfinityFree, `GEMINI_API_KEY` na `PROXY_SECRET` ya zamani zilikuwa wazi kwenye `config.php` ndani ya zip uliyonipa. **Zichukulie kuwa zimevuja**:
- Tengeneza ufunguo mpya wa Gemini kwenye https://aistudio.google.com/apikey na ufute wa zamani.
- Badilisha nenosiri la InfinityFree MySQL, na `PROXY_SECRET` ya Cloudflare Worker ukiendelea kuitumia.
- Usiweke siri kwenye faili zinazopanda GitHub.

## Matatizo ya kawaida
- **"Database connection error"** → angalia Environment Variables, `DB_SSL=1`, na kwamba `install.sql` ilikimbia. Angalia **Vercel → Project → Logs**.
- **Ukurasa unaonyesha 404 / PHP source** → hakikisha `vercel.json` iko mizizi ya repo na Framework = Other. Ikiwa deploy inalalamika kuhusu `vercel-php@0.7.4`, angalia toleo jipya kwenye https://www.npmjs.com/package/vercel-php na ubadilishe namba kwenye `vercel.json`.
- **Unatoka login kila mara** → jedwali `sessions` halipo au DB haifikiki.
- **AI haijibu** → angalia `GEMINI_API_KEY`; kwenye Vercel hauhitaji Cloudflare Worker.
- Ukihariri `APP_VERSION` kwenye `app/config.php`, PWA itaonyesha "Sasisha".
