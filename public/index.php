<?php declare(strict_types=1); ?>
<!doctype html>
<html lang="en">
<head>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<meta name="description" content="Live home temperature, motion and ambient light monitoring.">
<title>Home Temperature</title>
<script src="/assets/theme.js"></script>
<link rel="stylesheet" href="/assets/dashboard.css">
<script src="/assets/dashboard.js" defer></script>
</head>
<body>
<div class="app-shell"><main>
<header class="topbar"><a class="brand" href="/" aria-label="Home"><span class="brand-icon" data-icon="home"></span><span>Home</span></a><label class="theme-toggle"><span>Dark mode</span><input type="checkbox" id="dark-mode" role="switch" aria-label="Dark mode"><span class="theme-track" aria-hidden="true"></span></label></header>
<div class="page-content">
<noscript><p class="error-banner">Enable JavaScript to load live sensor readings.</p></noscript>
<div id="error-banner" class="error-banner" role="alert" hidden><span><strong>Let’s get your home connected.</strong> <span id="error-message"></span></span><button data-demo="on">Explore demo ↗</button></div>
<div id="demo-banner" class="demo-banner" hidden><span>Previewing a sample home. These readings do not come from your bridge.</span><button data-demo="off">Exit demo ×</button></div>
<section class="summary-grid" aria-label="Home summary">
<div class="average-panel"><div class="panel-label"><span data-icon="temperature"></span>Home temperature<span class="live-tag" id="live-tag">—</span></div><div class="average-body"><div><div class="average-number"><span id="average" style="font:inherit;color:inherit;margin:0">—</span><span>°C</span></div><div class="average-caption" id="average-caption">Average across 0 sensors</div><span class="comfort"><span data-icon="leaf"></span><span id="comfort">Waiting for readings</span></span></div><div class="mini-chart" aria-hidden="true"><svg viewBox="0 0 180 85"><path d="M0 70 Q20 72 35 52 T65 48 T95 27 T125 33 T155 13 T180 17" fill="none" stroke="#a2c5a7" stroke-width="2"/><path d="M0 70 Q20 72 35 52 T65 48 T95 27 T125 33 T155 13 T180 17 V85 H0Z" fill="#a2c5a7" opacity=".09"/></svg><span>YOUR HOME, IN BALANCE</span></div></div><div class="average-footer"><span><span class="tiny-dot"></span>Reachable, enabled sensors only</span><span>Celsius °C</span></div></div>
<div class="stat-panel"><div class="stat-icon" data-icon="radio"></div><div class="stat-value"><b id="active-count" style="font-weight:400">0</b><span id="total-count">/ 0</span></div><h2>Active sensors</h2><p id="active-caption">No sensors loaded yet</p></div>
<div class="stat-panel"><div class="stat-icon amber" data-icon="activity"></div><div class="stat-value"><b id="motion-count" style="font-weight:400">0</b><span>sensors</span></div><h2>Motion detected</h2><p id="motion-caption">All quiet around the house</p></div>
</section>
<section id="sensors" class="sensor-section"><div class="section-title"><h2>Your sensors <span id="sensor-count">0</span></h2><div class="sensor-controls"><label class="search"><span data-icon="search"></span><input id="search" aria-label="Search sensors" placeholder="Find a sensor..."></label><select id="filter" aria-label="Filter sensors"><option value="all">All sensors</option><option value="motion">Motion detected</option></select><button class="button refresh" id="refresh" aria-label="Refresh data"><span data-icon="refresh"></span><span id="refresh-label">Refresh data</span></button></div></div><div class="sensor-grid" id="sensor-grid"></div><div class="empty-state" id="empty-state"><span data-icon="radio"></span><h3 id="empty-title">Finding your sensors…</h3><p id="empty-description">Waiting for live temperature and motion readings.</p><button class="button" id="empty-demo" data-demo="on">Explore a sample home ↗</button></div></section>
<section class="insights"><div class="insight-intro"><span class="stat-icon" data-icon="leaf"></span><div><h3><span id="bridge-dot" class="status-dot muted"></span><span id="bridge-status" role="status">Connecting to bridge</span></h3><p id="bridge-caption">192.168.1.10 · Refreshes every 15s</p></div></div><div class="insight-item"><span>↑ Warmest room</span><strong id="warmest">No readings</strong></div><div class="insight-item"><span>↓ Coolest room</span><strong id="coldest">No readings</strong></div><div class="insight-item"><span>Temperature range</span><strong><span id="range">—</span><small>across your home</small></strong></div></section>
<footer class="page-footer"><span><span data-icon="shield"></span>Your home data stays on your network.</span><span><span id="last-sync">Waiting for first sync</span><span class="footer-dot">·</span>Made for everyday peace of mind</span></footer>
</div></main></div>
<dialog class="modal" id="sensor-dialog" aria-labelledby="detail-name"><button class="close-button" id="close-dialog" aria-label="Close dialog" autofocus>×</button><div class="eyebrow">SENSOR DETAILS</div><h2 id="detail-name"></h2><div id="detail-content"></div></dialog>
</body></html>
