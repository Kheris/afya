(function () {
  // ---------- Banner ndogo ----------
  function banner(html, id) {
    if (document.getElementById(id)) return null;
    const el = document.createElement('div');
    el.id = id;
    el.setAttribute('role', 'status');
    el.style.cssText = 'position:fixed;left:12px;right:12px;bottom:78px;z-index:9999;background:#1b2a4a;color:#fff;' +
      'padding:12px 14px;border-radius:14px;box-shadow:0 8px 24px rgba(0,0,0,.25);display:flex;gap:10px;' +
      'align-items:center;justify-content:space-between;font-size:14px;max-width:560px;margin:0 auto;';
    el.innerHTML = html;
    document.body.appendChild(el);
    return el;
  }
  const btnCss = 'background:#fff;color:#1b2a4a;border:none;border-radius:10px;padding:8px 12px;font-weight:700;cursor:pointer;';

  // ---------- Service worker + update ya progressive ----------
  if ('serviceWorker' in navigator) {
    const v = encodeURIComponent(window.AFYA_VERSION || '1');
    let reloading = false;

    navigator.serviceWorker.addEventListener('controllerchange', () => {
      if (reloading) return;
      reloading = true;
      location.reload();
    });

    window.addEventListener('load', () => {
      navigator.serviceWorker.register('sw.js?v=' + v).then((reg) => {
        function promptUpdate(worker) {
          const el = banner('<span>Toleo jipya la AfyaCheck liko tayari.</span><button style="' + btnCss + '">Sasisha</button>', 'afya-update');
          if (!el) return;
          el.querySelector('button').addEventListener('click', () => worker.postMessage({ type: 'SKIP_WAITING' }));
        }
        if (reg.waiting && navigator.serviceWorker.controller) promptUpdate(reg.waiting);
        reg.addEventListener('updatefound', () => {
          const nw = reg.installing;
          if (!nw) return;
          nw.addEventListener('statechange', () => {
            if (nw.state === 'installed' && navigator.serviceWorker.controller) promptUpdate(nw);
          });
        });
        // Angalia update kila mtumiaji anaporudi kwenye app
        document.addEventListener('visibilitychange', () => {
          if (document.visibilityState === 'visible') reg.update().catch(() => null);
        });
      }).catch((err) => console.warn('SW haikusajiliwa:', err));
    });
  }

  // ---------- Kitufe cha "Sakinisha app" ----------
  let deferredPrompt = null;
  window.addEventListener('beforeinstallprompt', (e) => {
    e.preventDefault();
    deferredPrompt = e;
    try { if (localStorage.getItem('afya_install_dismissed')) return; } catch (_) {}
    const el = banner('<span>Sakinisha AfyaCheck kwenye simu yako.</span><span><button id="afya-i-yes" style="' + btnCss + '">Sakinisha</button> <button id="afya-i-no" style="background:none;color:#fff;border:none;cursor:pointer;font-size:18px;" aria-label="Funga">&times;</button></span>', 'afya-install');
    if (!el) return;
    el.querySelector('#afya-i-yes').addEventListener('click', async () => {
      el.remove();
      deferredPrompt.prompt();
      await deferredPrompt.userChoice.catch(() => null);
      deferredPrompt = null;
    });
    el.querySelector('#afya-i-no').addEventListener('click', () => {
      el.remove();
      try { localStorage.setItem('afya_install_dismissed', '1'); } catch (_) {}
    });
  });

  // ---------- Hali ya mtandao ----------
  window.addEventListener('offline', () => banner('<span>Hakuna mtandao. Baadhi ya vipengele havitafanya kazi.</span>', 'afya-offline'));
  window.addEventListener('online', () => { const o = document.getElementById('afya-offline'); if (o) o.remove(); });
})();
