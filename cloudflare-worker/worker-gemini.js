/**
 * AfyaCheck - Cloudflare Worker AI Proxy (GOOGLE GEMINI TOLEO)
 * ----------------------------------------------------------------
 * Hii ni toleo la worker.js linalozungumza na Google Gemini API
 * badala ya Anthropic. Sehemu ya PHP (InfinityFree) HAIHITAJI kubadilika --
 * bado inatuma { system, messages } kwenda /chat na kupokea { reply }.
 *
 * JINSI YA KUWEKA:
 * 1. Fungua Worker yako kwenye Cloudflare -> Edit code
 * 2. FUTA code iliyopo yote -> bandika hii yote -> Save & Deploy
 * 3. Nenda Settings -> Variables and Secrets:
 *      - FUTA/badilisha ile "ANTHROPIC_API_KEY" iliyokuwepo
 *      - Ongeza "GEMINI_API_KEY" = ufunguo wako kutoka aistudio.google.com/apikey
 *      - Hakikisha "PROXY_SECRET" bado ipo (haibadiliki, lazima ifanane na config.php)
 * 4. Save & Deploy tena
 */

// Angalia orodha ya sasa ya models kwenye https://ai.google.dev/gemini-api/docs/models
// kama model hii itaisha muda wake (deprecated) siku za usoni.
const MODELS = ["gemini-3.5-flash", "gemini-2.5-flash", "gemini-2.0-flash"];

export default {
  async fetch(request, env) {
    const url = new URL(request.url);
    if (request.method !== "POST" || url.pathname !== "/chat") {
      return new Response(JSON.stringify({ error: "Not found" }), {
        status: 404,
        headers: { "Content-Type": "application/json" },
      });
    }

    const providedSecret = request.headers.get("X-Proxy-Secret");
    if (!providedSecret || providedSecret !== env.PROXY_SECRET) {
      return new Response(JSON.stringify({ error: "Unauthorized" }), {
        status: 401,
        headers: { "Content-Type": "application/json" },
      });
    }

    let body;
    try {
      body = await request.json();
    } catch (e) {
      return new Response(JSON.stringify({ error: "Invalid JSON" }), {
        status: 400,
        headers: { "Content-Type": "application/json" },
      });
    }

    const { system, messages } = body;
    if (!Array.isArray(messages) || messages.length === 0) {
      return new Response(JSON.stringify({ error: "messages required" }), {
        status: 400,
        headers: { "Content-Type": "application/json" },
      });
    }

    // Gemini haitumii role "assistant" -- inatumia "model".
    // Pia haina "system" kama ujumbe wa kawaida -- ni field tofauti (systemInstruction).
    const geminiContents = messages.map((m) => ({
      role: m.role === "assistant" ? "model" : "user",
      parts: [{ text: m.content }],
    }));

    const payload = {
      contents: geminiContents,
      systemInstruction: {
        parts: [{ text: system || "" }],
      },
      generationConfig: {
        maxOutputTokens: 1024,
      },
    };

    try {
      const key = env.GEMINI_API_KEY;
      // Ufunguo wa "AQ." = Vertex AI express; mwingine = AI Studio.
      const vertex = key.startsWith("AQ.");
      let lastErr = "Gemini API error", lastStatus = 502;

      for (const model of MODELS) {
        const apiUrl = vertex
          ? `https://aiplatform.googleapis.com/v1/publishers/google/models/${model}:generateContent?key=${encodeURIComponent(key)}`
          : `https://generativelanguage.googleapis.com/v1beta/models/${model}:generateContent`;
        const headers = { "Content-Type": "application/json" };
        if (!vertex) headers["x-goog-api-key"] = key;

        const apiRes = await fetch(apiUrl, { method: "POST", headers, body: JSON.stringify(payload) });
        const data = await apiRes.json().catch(() => ({}));

        if (apiRes.ok) {
          const replyText = (data?.candidates?.[0]?.content?.parts || [])
            .filter((p) => !p.thought).map((p) => p.text || "").join("\n").trim();
          if (!replyText) {
            return new Response(JSON.stringify({ error: "Gemini haikutoa jibu (labda safety filter)." }),
              { status: 502, headers: { "Content-Type": "application/json" } });
          }
          return new Response(JSON.stringify({ reply: replyText }), {
            status: 200, headers: { "Content-Type": "application/json", "Access-Control-Allow-Origin": "*" },
          });
        }
        lastErr = data?.error?.message || (Array.isArray(data) && data[0]?.error?.message) || lastErr;
        lastStatus = apiRes.status;
        if (apiRes.status !== 404 && apiRes.status !== 400) break; // jaribu model nyingine kwa 404/400 tu
      }
      return new Response(JSON.stringify({ error: lastErr }), {
        status: lastStatus, headers: { "Content-Type": "application/json" },
      });
    } catch (err) {
      return new Response(JSON.stringify({ error: "Proxy error: " + err.message }), {
        status: 500,
        headers: { "Content-Type": "application/json" },
      });
    }
  },
};
