<?php
?><!doctype html>
<html lang="pl">
<head>
  <meta charset="utf-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1" />
  <title>KSeF API – przykładowy klient</title>
  <style>
    :root {
      color-scheme: light dark;
      --bg: #0f172a;
      --card: #111827;
      --muted: #94a3b8;
      --text: #e5e7eb;
      --accent: #22c55e;
      --danger: #ef4444;
      --border: #334155;
    }
    body {
      margin: 0;
      font-family: Inter, system-ui, -apple-system, Segoe UI, Roboto, sans-serif;
      background: linear-gradient(120deg, #020617, #111827);
      color: var(--text);
      min-height: 100vh;
      padding: 24px;
    }
    .container {
      max-width: 960px;
      margin: 0 auto;
      background: color-mix(in srgb, var(--card) 90%, transparent);
      border: 1px solid var(--border);
      border-radius: 16px;
      padding: 24px;
      box-shadow: 0 12px 40px rgba(0,0,0,.35);
    }
    h1 { margin-top: 0; }
    .grid {
      display: grid;
      grid-template-columns: repeat(2, minmax(0, 1fr));
      gap: 12px;
    }
    @media (max-width: 720px) { .grid { grid-template-columns: 1fr; } }
    label { display: block; font-size: 14px; color: var(--muted); margin-bottom: 6px; }
    input, select, textarea, button {
      width: 100%;
      border: 1px solid var(--border);
      background: #0b1220;
      color: var(--text);
      border-radius: 10px;
      padding: 10px 12px;
      font: inherit;
      box-sizing: border-box;
    }
    textarea { min-height: 170px; resize: vertical; }
    .full { grid-column: 1 / -1; }
    .actions { display: flex; gap: 10px; margin-top: 10px; }
    .actions button { width: auto; cursor: pointer; }
    button.primary { background: var(--accent); color: #052e16; font-weight: 700; border-color: transparent; }
    .log {
      margin-top: 16px;
      border: 1px solid var(--border);
      border-radius: 10px;
      padding: 12px;
      background: #020617;
      min-height: 110px;
      white-space: pre-wrap;
      font-family: ui-monospace, SFMono-Regular, Menlo, Monaco, Consolas, monospace;
      font-size: 13px;
    }
    .ok { color: #4ade80; }
    .err { color: var(--danger); }
    .hint { font-size: 13px; color: var(--muted); margin-top: 6px; }
  </style>
</head>
<body>
<div class="container">
  <h1>KSeF API – prosty klient (PHP + JS)</h1>
  <p class="hint">Przykład edukacyjny. Uzupełnij własne endpointy zgodnie z aktualną dokumentacją MF/KSeF.</p>

  <div class="grid">
    <div>
      <label for="baseUrl">Base URL API KSeF</label>
      <input id="baseUrl" value="https://ksef-test.mf.gov.pl/api" />
    </div>

    <div>
      <label for="token">Token autoryzacyjny KSeF</label>
      <input id="token" placeholder="Wklej token" />
    </div>

    <div>
      <label for="nip">NIP podmiotu</label>
      <input id="nip" placeholder="np. 1234567890" />
    </div>

    <div>
      <label for="endpoint">Endpoint (ścieżka)</label>
      <input id="endpoint" value="/online/Session/InitToken" />
    </div>

    <div class="full">
      <label for="payload">Payload JSON</label>
      <textarea id="payload">{
  "contextIdentifier": {
    "type": "onip",
    "identifier": "1234567890"
  },
  "encryptedToken": "TU_WKLEJ_ZASZYFROWANY_LUB_ZWYKLY_TOKEN_WG_WYMAGAN_ENDPOINTU"
}</textarea>
      <div class="hint">Dla innych endpointów zmień path i payload (np. wysyłka faktury, statusy, pobrania).</div>
    </div>
  </div>

  <div class="actions">
    <button class="primary" id="sendBtn">Wyślij żądanie</button>
    <button id="clearBtn" type="button">Wyczyść log</button>
  </div>

  <div id="log" class="log">Gotowe. Uzupełnij dane i kliknij „Wyślij żądanie”.</div>
</div>

<script>
const logEl = document.getElementById('log');

function log(msg, cls='') {
  const line = document.createElement('div');
  line.textContent = `[${new Date().toLocaleTimeString()}] ${msg}`;
  if (cls) line.className = cls;
  logEl.appendChild(line);
  logEl.scrollTop = logEl.scrollHeight;
}

document.getElementById('clearBtn').addEventListener('click', () => {
  logEl.textContent = '';
});

document.getElementById('sendBtn').addEventListener('click', async () => {
  const baseUrl = document.getElementById('baseUrl').value.trim();
  const token = document.getElementById('token').value.trim();
  const nip = document.getElementById('nip').value.trim();
  const endpoint = document.getElementById('endpoint').value.trim();
  const payloadRaw = document.getElementById('payload').value;

  if (!baseUrl || !endpoint) {
    log('Brak baseUrl lub endpoint.', 'err');
    return;
  }

  let payload;
  try {
    payload = JSON.parse(payloadRaw || '{}');
  } catch (e) {
    log('Niepoprawny JSON payload: ' + e.message, 'err');
    return;
  }

  log(`POST ${baseUrl}${endpoint}`);

  try {
    const resp = await fetch('ksef_proxy.php', {
      method: 'POST',
      headers: { 'Content-Type': 'application/json' },
      body: JSON.stringify({ baseUrl, endpoint, token, nip, payload })
    });

    const data = await resp.json();
    if (!resp.ok || !data.ok) {
      log(`Błąd HTTP ${resp.status}.`, 'err');
      log(JSON.stringify(data, null, 2), 'err');
      return;
    }

    log('Sukces:');
    log(JSON.stringify(data.response, null, 2), 'ok');
  } catch (e) {
    log('Błąd połączenia: ' + e.message, 'err');
  }
});
</script>
</body>
</html>
