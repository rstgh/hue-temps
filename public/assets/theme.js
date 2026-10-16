'use strict';
(() => {
  const key = 'home-temperature-theme';
  const root = document.documentElement;
  let theme;
  try { theme = localStorage.getItem(key); } catch (_) { /* Storage may be unavailable. */ }
  if (theme !== 'light' && theme !== 'dark') {
    theme = window.matchMedia('(prefers-color-scheme: dark)').matches ? 'dark' : 'light';
  }
  // Run before the stylesheet loads to avoid a flash of the wrong theme.
  root.dataset.theme = theme;
  document.addEventListener('DOMContentLoaded', () => {
    const toggle = document.getElementById('dark-mode');
    toggle.checked = root.dataset.theme === 'dark';
    toggle.addEventListener('change', () => {
      const selected = toggle.checked ? 'dark' : 'light';
      root.dataset.theme = selected;
      try { localStorage.setItem(key, selected); } catch (_) { /* Keep working without persistence. */ }
    });
    window.addEventListener('storage', event => {
      if (event.key === key && (event.newValue === 'dark' || event.newValue === 'light')) {
        root.dataset.theme = event.newValue;
        toggle.checked = event.newValue === 'dark';
      }
    });
  });
})();
