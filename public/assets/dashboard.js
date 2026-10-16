'use strict';
(() => {
  const $ = id => document.getElementById(id);
  const escape = value => String(value).replace(/[&<>"']/g, char => ({ '&': '&amp;', '<': '&lt;', '>': '&gt;', '"': '&quot;', "'": '&#39;' })[char]);
  const paths = {
    home: '<path d="m3 10 9-7 9 7v10H3Z"/><path d="M9 20v-7h6v7"/>',
    refresh: '<path d="M20 7v5h-5M4 17v-5h5"/><path d="M5 8a8 8 0 0 1 13-3l2 3M4 16l2 3a8 8 0 0 0 13-3"/>',
    temperature: '<path d="M9 14V5a3 3 0 0 1 6 0v9a5 5 0 1 1-6 0Z"/><path d="M12 8v10"/>',
    leaf: '<path d="M20 3C7 2 2 8 5 15s17 6 15-12Z"/><path d="m3 21 12-12"/>',
    radio: '<circle cx="12" cy="12" r="2"/><path d="M7 7a7 7 0 0 0 0 10M17 7a7 7 0 0 1 0 10M4 4a11 11 0 0 0 0 16M20 4a11 11 0 0 1 0 16"/>',
    activity: '<path d="M2 12h5l3-8 4 16 3-8h5"/>',
    search: '<circle cx="10" cy="10" r="7"/><path d="m15 15 6 6"/>',
    sun: '<circle cx="12" cy="12" r="4"/><path d="M12 1v2m0 18v2M1 12h2m18 0h2M4 4l2 2m12 12 2 2M4 20l2-2M18 6l2-2"/>',
    battery: '<rect x="2" y="6" width="17" height="12" rx="2"/><path d="M22 10v4M5 9v6m3-6v6m3-6v6"/>',
    shield: '<path d="m12 2 8 4v6c0 5-8 10-8 10S4 17 4 12V6Z"/><path d="m8 12 3 3 5-6"/>',
  };
  const icon = name => `<svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.7" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">${paths[name] || ''}</svg>`;
  document.querySelectorAll('[data-icon]').forEach(el => { el.innerHTML = icon(el.dataset.icon); });
  const samples = [ ['Living room', 22.4, true, 94, 284], ['Kitchen', 23.1, true, 87, 412], ['Bedroom', 20.8, false, 98, 126], ['Hallway', 21.6, false, 76, 82], ['Home office', 22.2, true, 91, 356], ['Guest room', 20.3, false, 83, 98] ].map(([name, temperature, motion, battery, lux], i) => ({ id: String(i), name, temperature, motion, battery, lux, reachable: true, enabled: true, updated: null }));
  let snapshot = null, demo = false, loading = true, error = '', busy = false, selectedId = null;
  const sensors = () => (demo ? samples : snapshot?.sensors || []).filter(s => s.enabled);
  const finite = value => typeof value === 'number' && Number.isFinite(value);
  function gauge(value, large = false) {
    const valid = finite(value);
    const progress = valid ? Math.min(100, Math.max(0, value / 40 * 100)) : 0;
    const ticks = Array.from({ length: 9 }, (_, index) => {
      const angle = Math.PI - index * Math.PI / 8;
      const point = radius => [120 + radius * Math.cos(angle), 120 - radius * Math.sin(angle)];
      const [x1, y1] = point(77);
      const [x2, y2] = point(82);
      return `<line x1="${x1.toFixed(2)}" y1="${y1.toFixed(2)}" x2="${x2.toFixed(2)}" y2="${y2.toFixed(2)}"/>`;
    }).join('');
    return `<div class="gauge ${large ? 'gauge-large' : ''}"><svg viewBox="0 0 240 148" role="img" aria-label="${valid ? value.toFixed(1) + ' degrees Celsius' : 'Temperature unavailable'}"><path d="M29 120 A91 91 0 0 1 211 120" fill="none" stroke="var(--track)" stroke-width="13" stroke-linecap="round"/><path d="M29 120 A91 91 0 0 1 211 120" fill="none" stroke="#527b63" stroke-width="13" stroke-linecap="round" pathLength="100" stroke-dasharray="${progress} 100"/><g class="gauge-ticks" aria-hidden="true">${ticks}</g><text x="120" y="105" text-anchor="middle" class="gauge-number">${valid ? value.toFixed(1) : '—'}<tspan class="degree">°</tspan></text><text x="120" y="128" text-anchor="middle" class="gauge-unit">CELSIUS</text><text x="25" y="145" text-anchor="middle" class="gauge-label">0°</text><text x="215" y="145" text-anchor="middle" class="gauge-label">40°</text></svg></div>`;
  }
  function light(lux) {
    const valid = finite(lux) && lux >= 0;
    const progress = valid ? Math.min(100, Math.log10(lux + 1) / 4 * 100) : 0;
    return `<div class="light-level${valid ? '' : ' light-unavailable'}"><div class="light-heading"><span>${icon('sun')}Ambient light</span><strong>${valid ? Math.round(lux).toLocaleString('en-US') : '—'} <small>Lux</small></strong></div><div class="light-track" aria-hidden="true"><span style="width:${progress}%"></span>${valid ? `<i style="left:${progress}%"></i>` : ''}</div><div class="light-scale" aria-hidden="true"><span>0</span><span>10</span><span>100</span><span>1k</span><span>10k+</span></div><span class="light-caption">${valid ? 'Logarithmic light scale' : 'Light reading unavailable'}</span></div>`;
  }
  // Keep existing elements so polling preserves focus, animations and scroll position.
  function updateElement(current, next) {
    if (current.nodeType !== next.nodeType || current.nodeName !== next.nodeName) {
      current.replaceWith(next.cloneNode(true));
      return;
    }
    if (current.nodeType === Node.TEXT_NODE) {
      if (current.nodeValue !== next.nodeValue) current.nodeValue = next.nodeValue;
      return;
    }
    if (current.nodeType !== Node.ELEMENT_NODE) return;
    for (const attribute of Array.from(current.attributes)) {
      if (!next.hasAttribute(attribute.name)) current.removeAttribute(attribute.name);
    }
    for (const attribute of Array.from(next.attributes)) {
      if (current.getAttribute(attribute.name) !== attribute.value) current.setAttribute(attribute.name, attribute.value);
    }
    const children = Array.from(next.childNodes);
    children.forEach((child, index) => {
      if (current.childNodes[index]) updateElement(current.childNodes[index], child);
      else current.append(child.cloneNode(true));
    });
    while (current.childNodes.length > children.length) current.lastChild.remove();
  }
  function updateCards(markup) {
    const grid = $('sensor-grid');
    const template = document.createElement('template');
    template.innerHTML = markup;
    const existing = new Map(Array.from(grid.children, card => [card.dataset.sensor, card]));
    Array.from(template.content.children).forEach((next, index) => {
      const card = existing.get(next.dataset.sensor);
      const element = card || next.cloneNode(true);
      if (card) { updateElement(card, next); existing.delete(next.dataset.sensor); }
      if (grid.children[index] !== element) grid.insertBefore(element, grid.children[index] || null);
    });
    existing.forEach(card => card.remove());
  }
  function renderCards() {
    const all = sensors();
    const visible = all.filter(s => ($('filter').value !== 'motion' || s.motion) && s.name.toLowerCase().includes($('search').value.toLowerCase()));
    const markup = visible.map((s, i) => `<button class="sensor-card" data-sensor="${escape(s.id)}" style="animation-delay:${i * 50}ms"><div class="card-top"><span class="room-icon">${icon('home')}</span><span class="room-status ${s.reachable ? '' : 'offline'}"><span class="tiny-dot"></span>${s.reachable ? 'Online' : 'Offline'}</span><span class="card-menu">···</span></div><h3>${escape(s.name)}</h3><div class="sensor-subtitle">Hue motion sensor</div>${gauge(s.reachable ? s.temperature : null)}${light(s.reachable ? s.lux : null)}<div class="card-divider"></div><div class="card-footer"><span class="${s.motion ? 'motion-active' : 'motion-quiet'}">${icon('activity')}${s.motion ? 'Motion detected' : 'No motion'}</span><span class="battery">${icon('battery')}${finite(s.battery) ? s.battery + '%' : '—'}</span></div></button>`).join('');
    updateCards(markup);
    $('empty-state').hidden = visible.length > 0;
    $('empty-title').textContent = loading && !demo ? 'Finding your sensors…' : all.length ? 'No sensors match this view' : 'Your home is ready to connect';
    $('empty-description').textContent = all.length ? 'Try another search or sensor filter.' : 'Check the bridge connection to see live temperature and motion readings.';
    $('empty-demo').hidden = all.length > 0;
  }
  function render() {
    const all = sensors(), active = all.filter(s => s.reachable), temps = active.filter(s => finite(s.temperature)).sort((a, b) => a.temperature - b.temperature);
    const average = temps.length ? temps.reduce((sum, s) => sum + s.temperature, 0) / temps.length : null;
    const motion = active.filter(s => s.motion).length;
    const connected = !demo && !!snapshot && !error;
    $('refresh').disabled = loading && !demo;
    $('refresh-label').textContent = demo ? 'Connect live' : 'Refresh data';
    $('refresh').setAttribute('aria-label', $('refresh-label').textContent);
    $('refresh').querySelector('svg').classList.toggle('spin', loading && !demo);
    $('error-banner').hidden = !error || demo;
    $('error-message').textContent = error + (snapshot ? ' Last successful readings are shown; they may be stale.' : '');
    $('demo-banner').hidden = !demo;
    $('live-tag').textContent = demo ? 'DEMO' : connected ? 'LIVE' : '—';
    $('average').textContent = average === null ? '—' : average.toFixed(1);
    $('average-caption').textContent = `Average across ${temps.length} sensors`;
    $('comfort').textContent = average === null ? 'Waiting for readings' : average < 18 ? 'A little cool' : average > 26 ? 'A little warm' : 'Comfortably balanced';
    $('active-count').textContent = active.length;
    $('total-count').textContent = `/ ${all.length}`;
    $('active-caption').textContent = !all.length ? 'No sensors loaded yet' : active.length === all.length ? 'All sensors are online' : `${all.length - active.length} offline`;
    $('motion-count').textContent = motion;
    $('motion-caption').textContent = motion ? 'A little life around the house' : 'All quiet around the house';
    $('sensor-count').textContent = all.length;
    $('bridge-dot').classList.toggle('muted', !connected);
    $('bridge-status').textContent = demo ? 'Demo preview' : connected ? 'Bridge connected' : loading ? 'Connecting to bridge' : 'Bridge unavailable';
    $('bridge-caption').textContent = `192.168.1.10 · ${demo ? 'Sample data · not live' : 'Refreshes every 15s'}`;
    for (const [id, s] of [['coldest', temps[0]], ['warmest', temps.at(-1)]]) {
      $(id).textContent = s ? `${s.name} ${s.temperature.toFixed(1)}°` : 'No readings';
    }
    $('range').textContent = temps.length ? `${(temps.at(-1).temperature - temps[0].temperature).toFixed(1)}°C` : '—';
    $('last-sync').textContent = demo ? 'Demo readings' : snapshot ? `Last synced ${new Date(snapshot.updatedAt).toLocaleTimeString()}${error ? ' · stale' : ''}` : 'Waiting for first sync';
    renderCards();
    if ($('sensor-dialog').open) showDetails(selectedId);
  }
  async function refresh() {
    if (busy || demo) return;
    busy = true; loading = true; render();
    const controller = new AbortController();
    const timeout = setTimeout(() => controller.abort(), 10000);
    try {
      const response = await fetch('/api/sensors.php', { cache: 'no-store', signal: controller.signal });
      const result = await response.json();
      if (!response.ok) throw new Error(result.error || 'Unable to read sensors.');
      if (!Array.isArray(result.sensors) || !result.sensors.every(s => s && typeof s.id === 'string' && typeof s.name === 'string') || !Number.isFinite(Date.parse(result.updatedAt))) throw new Error('The sensor API returned an invalid response.');
      snapshot = result; error = '';
    } catch (e) { error = e.name === 'AbortError' ? 'The sensor request timed out.' : e.message || 'Unable to connect to the PHP API.'; }
    finally { clearTimeout(timeout); busy = false; loading = false; render(); }
  }
  function showDetails(id) {
    const s = sensors().find(item => item.id === id);
    if (!s) { $('sensor-dialog').close(); return; }
    selectedId = id;
    $('detail-name').textContent = s.name;
    const row = (label, value) => `<div class="detail-row"><span>${label}</span><strong>${escape(value)}</strong></div>`;
    const markup = gauge(s.reachable ? s.temperature : null, true) + row('Connection', s.reachable ? 'Reachable · Enabled' : 'Offline · Enabled') + row('Motion', s.motion ? 'Detected' : 'No motion') + light(s.reachable ? s.lux : null) + row('Battery', finite(s.battery) ? `${s.battery}%` : 'Unavailable') + row('Presence state updated', s.updated ? new Date(s.updated).toLocaleString() : demo ? 'Sample reading' : 'Unavailable');
    const template = document.createElement('div');
    template.innerHTML = markup;
    updateElement($('detail-content'), Object.assign(template, { id: 'detail-content' }));
    if (!$('sensor-dialog').open) $('sensor-dialog').showModal();
  }
  $('refresh').addEventListener('click', () => { demo = false; void refresh(); });
  document.querySelectorAll('[data-demo]').forEach(button => button.addEventListener('click', () => { demo = button.dataset.demo === 'on'; $('sensor-dialog').close(); render(); if (!demo) void refresh(); }));
  $('search').addEventListener('input', renderCards);
  $('filter').addEventListener('change', renderCards);
  $('sensor-grid').addEventListener('click', event => { const card = event.target.closest('[data-sensor]'); if (card) showDetails(card.dataset.sensor); });
  $('close-dialog').addEventListener('click', () => $('sensor-dialog').close());
  $('sensor-dialog').addEventListener('click', event => { if (event.target === $('sensor-dialog')) { const rect = event.target.getBoundingClientRect(); if (event.clientX < rect.left || event.clientX > rect.right || event.clientY < rect.top || event.clientY > rect.bottom) event.target.close(); } });
  void refresh();
  setInterval(() => void refresh(), 15000);
})();
