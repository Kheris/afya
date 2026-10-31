(function () {
  // Weka theme mara moja kabla ya page kuonekana ili kuepuka "flash" ya rangi nyeupe
  const saved = localStorage.getItem('afya_theme') || 'light';
  document.documentElement.setAttribute('data-theme', saved);

  window.toggleAfyaTheme = function () {
    const current = document.documentElement.getAttribute('data-theme');
    const next = current === 'dark' ? 'light' : 'dark';
    document.documentElement.setAttribute('data-theme', next);
    localStorage.setItem('afya_theme', next);
    document.querySelectorAll('.theme-state').forEach((el) => {
      el.textContent = next === 'dark' ? 'Imewashwa' : 'Imezimwa';
    });
  };

  document.addEventListener('DOMContentLoaded', () => {
    document.querySelectorAll('.theme-state').forEach((el) => {
      el.textContent = saved === 'dark' ? 'Imewashwa' : 'Imezimwa';
    });
  });
})();
