/**
 * AfyaCheck - Cloudflare Worker AI Proxy
 * ----------------------------------------
 * Kazi yake: kupokea ombi kutoka InfinityFree (ambayo haiwezi kuzungumza
 * moja kwa moja na api.anthropic.com), na kulisukuma huko, kisha kurudisha jibu.
 *
 * JINSI YA KU-DEPLOY (bure kabisa, dakika 5):
 * 1. Nenda https://dash.cloudflare.com -> unda akaunti ya bure
 * 2. Workers & Pages -> Create -> Create Worker
 * 3. Ipe jina mfano "afyacheck-ai" -> Deploy
 * 4. Bonyeza "Edit code" -> futa code iliyopo -> bandika code hii yote -> Save & Deploy
 * 5. Nenda Settings -> Variables -> ongeza "Secret" mbili:
 *      ANTHROPIC_API_KEY   = ufunguo wako wa Anthropic API (kutoka console.anthropic.com)
 *      PROXY_SECRET        = neno la siri lolote refu (lazima lifanane na config.php)
 * 6. Nakili URL ya worker (mfano https://afyacheck-ai.username.workers.dev)
 *    na uiweke kwenye AI_PROXY_URL ndani ya config.php ya InfinityFree
 */

const ANTHROPIC_MODEL = "claude-sonnet-4-5-20250929"; // angalia console.anthropic.com kwa jina sahihi la model unalotumia

export default {
  async fetch(request, env) {
    // Ruhusu tu POST kwenye /chat
    const url = new URL(request.url);
    if (request.method !== "POST" || url.pathname !== "/chat") {
      return new Response(JSON.stringify({ error: "Not found" }), {
        status: 404,
        headers: { "Content-Type": "application/json" },
      });
    }

    // Hakiki secret inayotumwa na InfinityFree
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

    try {
      const apiRes = await fetch("https://api.anthropic.com/v1/messages", {
        method: "POST",
        headers: {
          "Content-Type": "application/json",
          "x-api-key": env.ANTHROPIC_API_KEY,
          "anthropic-version": "2023-06-01",
        },
        body: JSON.stringify({
          model: ANTHROPIC_MODEL,
          max_tokens: 1024,
          system: system || "",
          messages: messages,
        }),
      });

      const data = await apiRes.json();

      if (!apiRes.ok) {
        return new Response(
          JSON.stringify({ error: data?.error?.message || "Anthropic API error" }),
          { status: apiRes.status, headers: { "Content-Type": "application/json" } }
        );
      }

      const replyText = (data.content || [])
        .filter((block) => block.type === "text")
        .map((block) => block.text)
        .join("\n");

      return new Response(JSON.stringify({ reply: replyText }), {
        status: 200,
        headers: {
          "Content-Type": "application/json",
          "Access-Control-Allow-Origin": "*", // unaweza kubana hii kwa domain yako maalum baadaye
        },
      });
    } catch (err) {
      return new Response(JSON.stringify({ error: "Proxy error: " + err.message }), {
        status: 500,
        headers: { "Content-Type": "application/json" },
      });
    }
  },
};
