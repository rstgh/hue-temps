# Home

A PHP-served dashboard for Philips Hue motion sensors, temperature and ambient light in Lux. No Node.js, npm, build step, CDN, or frontend framework is required. Browser JavaScript provides polling, search, motion filtering, sensor details and an explicitly labeled demo preview.

## Run locally

Requirements: PHP 8.1+ with the cURL extension, a modern browser, and network access from PHP to your Hue bridge.

From the repository root:

```bash
php -S 127.0.0.1:3555 -t public
```

Open http://localhost:3555. This single PHP server serves both `/` and `/api/sensors.php`. Stop the previous Next.js and PHP servers first; port 8880 is no longer needed.

## Configuration

Keep bridge credentials in the ignored root-level `config.local.ini`, outside the `public/` document root. For a fresh checkout, copy `config.example.ini` to `config.local.ini` and fill in the local credentials. Alternatively set `HUE_BRIDGE_URL` (bridge origin without `/api`) and `HUE_USERNAME` in the PHP environment; environment variables take precedence. Never put credentials in public assets.

For production, configure your PHP-capable web server with this repository's `public/` directory as its document root. No URL rewrite or proxy configuration is required. Keep the dashboard on your trusted home network; add authentication before exposing it externally.

## Behavior

The API queries the Hue v1 sensors endpoint. Presence, temperature and light resources are grouped by device MAC even when endpoint numbers differ. Disabled motion sensors are excluded. Enabled offline sensors remain visible; their current temperature and light readings are shown as unavailable. Temperature is converted from hundredths of a degree to Celsius. The average includes only reachable, enabled sensors with valid temperature readings.

The browser polls `/api/sensors.php` every 15 seconds and updates readings in place without reloading the page. Manual refresh uses the same request; search, filters, scroll position and open sensor details are preserved. Failed requests keep the last successful snapshot with an explicit stale-data warning. Demo mode is opt-in and never substitutes sample readings for a failed live request. Lux uses a labeled logarithmic scale. Motion reflects the current presence state; no event or temperature history is stored. The summary illustration is decorative.

## Validation

```bash
php tests/sensors.php
php tests/frontend.php
php tests/api.php
find public tests -name '*.php' -exec php -l {} \;
```

To check HTTP routes after starting the server:

```bash
curl -i http://localhost:3555/
curl -i http://localhost:3555/api/sensors.php
```

The API returns 503 if credentials are missing and 502 if the bridge cannot be reached. Only GET is accepted. Configuration files are outside the public document root.

## Layout

- `public/index.php`: dashboard HTML served at `/`.
- `public/assets/`: standalone browser JavaScript and CSS.
- `public/api/sensors.php`: same-origin JSON endpoint.
- `public/api/normalize.php`: device normalization.
- `tests/`: PHP checks.
- `config.local.ini`: ignored, private bridge configuration.
