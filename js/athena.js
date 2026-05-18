/* Athena monitoring dashboard — loaded via OCP\Util::addScript */
(function () {
'use strict';

const BASE = OC.generateUrl('/apps/athena/api/v1/manage');
const SEARCH_URL = OC.generateUrl('/apps/athena/api/v1/manage/users/search');
const hdrs = () => ({ 'Content-Type': 'application/json', 'requesttoken': OC.requestToken });
const $ = id => document.getElementById(id);
const esc = s => String(s ?? '').replace(/&/g,'&amp;').replace(/</g,'&lt;').replace(/>/g,'&gt;');

function slugify(name) {
  return name.toLowerCase()
    .replace(/[àáâãäå]/g,'a').replace(/[èéêë]/g,'e').replace(/[ìíîï]/g,'i')
    .replace(/[òóôõöø]/g,'o').replace(/[ùúûü]/g,'u').replace(/[ñ]/g,'n').replace(/[ç]/g,'c')
    .replace(/[^a-z0-9]+/g,'-').replace(/^-+|-+$/g,'');
}

function relTime(iso) {
  if (!iso) return 'never';
  const s = Math.round((Date.now() - new Date(iso)) / 1000);
  if (s < 5)    return 'just now';
  if (s < 60)   return s + 's ago';
  if (s < 3600) return Math.round(s / 60) + 'm ago';
  return new Date(iso).toLocaleTimeString([], { hour: '2-digit', minute: '2-digit' });
}
function fmtTs(iso) {
  if (!iso) return '—';
  return new Date(iso).toLocaleTimeString([], { hour: '2-digit', minute: '2-digit', second: '2-digit' });
}
function hbStatus(ts) {
  if (!ts) return 'never';
  const s = (Date.now() - new Date(ts)) / 1000;
  return s < 300 ? 'active' : s < 1800 ? 'idle' : 'offline';
}
function slotLabel(i) {
  const h = Math.floor(i / 2), m = i % 2 ? '30' : '00';
  return String(h).padStart(2, '0') + ':' + m;
}

/* ── State ─────────────────────────────────────────────────────────────── */
let sequences = [], clients = [], activeId = null, refreshTimer = null, editingId = null;
let evView = 'list', lastEvents = [];
let shareTarget = null, shareSearchResult = [];

/* ── Safe fetch helper ──────────────────────────────────────────────────── */
async function apiFetch(url, opts = {}) {
  const r = await fetch(url, { headers: hdrs(), ...opts });
  const ct = r.headers.get('content-type') ?? '';
  const body = ct.includes('application/json') ? await r.json() : await r.text();
  if (!r.ok) {
    const msg = (typeof body === 'object' ? body.error ?? body.message : body) || `HTTP ${r.status}`;
    throw new Error(msg);
  }
  return body;
}

/* ── Bootstrap ─────────────────────────────────────────────────────────── */
async function init() {
  try {
    await Promise.all([
      apiFetch(BASE + '/sequences').then(d => sequences = d),
      apiFetch(BASE + '/clients').then(d => clients = d),
    ]);
  } catch (e) {
    console.error('Athena init failed:', e);
    return;
  }
  renderSidebar();
  if (clients.length) await selectClient(clients[0].id);
}

/* ── Sidebar ────────────────────────────────────────────────────────────── */
function renderSidebar() {
  const el = $('athena-client-list');
  el.innerHTML = '';
  for (const c of clients) {
    const status = hbStatus(c.last_heartbeat);
    const seq    = sequences.find(s => s.id === c.sequence_id);
    const div    = document.createElement('div');
    div.className = 'client-card' + (activeId === c.id ? ' active' : '');
    const sharedBadge = c.is_owner ? '' : `<span class="cc-shared-badge">${c.can_edit ? 'edit' : 'view'}</span>`;
    div.innerHTML = `
      <span class="hb ${status}" id="sb-hb-${c.id}"></span>
      <div class="cc-info">
        <div class="cc-name">${esc(c.name)}${sharedBadge}</div>
        <div class="cc-sub">${esc(c.slug)} &middot; ${esc(seq?.name ?? '—')}</div>
      </div>
      <span class="cc-caret">›</span>`;
    if (c.is_owner) {
      const del = document.createElement('button');
      del.className = 'cc-del'; del.title = 'Delete client'; del.textContent = '✕';
      del.addEventListener('click', e => { e.stopPropagation(); _deleteClient(c); });
      div.appendChild(del);
    }
    div.onclick = () => selectClient(c.id);
    el.appendChild(div);
  }
}

/* ── Select + auto-refresh ──────────────────────────────────────────────── */
async function selectClient(id) {
  activeId = id;
  clearTimeout(refreshTimer);
  renderSidebar();
  $('athena-placeholder').style.display = 'none';
  $('athena-dashboard').style.display   = 'flex';
  await Promise.all([renderMonitor(id), renderEvents(id)]);
  refreshTimer = setTimeout(function tick() {
    if (activeId !== id) return;
    Promise.all([renderMonitor(id), renderEvents(id)])
      .then(() => { refreshTimer = setTimeout(tick, 30000); });
  }, 30000);
}

/* ── Monitor panel ──────────────────────────────────────────────────────── */
async function renderMonitor(id) {
  const r = await fetch(`${BASE}/clients/${id}/monitor`, { headers: hdrs() });
  if (!r.ok) return;
  const { client, today } = await r.json();

  $('db-name').textContent     = client.name;
  $('db-hb-dot').className     = 'hb ' + client.heartbeat_status;
  $('db-hb-label').textContent = client.last_heartbeat
    ? 'Last seen ' + relTime(client.last_heartbeat) : 'Never connected';

  const actsDom = $('db-actions');
  actsDom.innerHTML = '';
  const mkBtn = (label, cls, fn) => {
    const b = document.createElement('button');
    b.className = cls; b.textContent = label;
    b.addEventListener('click', fn);
    actsDom.appendChild(b);
  };
  if (client.can_edit) mkBtn('Edit',         'btn btn-ghost btn-sm',  () => window.athena.openEditClient());
  if (client.is_owner) mkBtn('Rotate token', 'btn btn-ghost btn-sm',  () => window.athena.doRotateToken());
  if (client.is_owner) mkBtn('Share',        'btn btn-ghost btn-sm',  () => window.athena.openShare());
  if (client.is_owner) mkBtn('Delete',       'btn btn-danger btn-sm', () => _deleteClient(clients.find(x => x.id === activeId) ?? client));

  const { stats } = today;
  $('db-stats').innerHTML = `
    <div class="stat-pill blue">  <div class="sp-val">${stats.heartbeats}</div>  <div class="sp-label">Heartbeats</div></div>
    <div class="stat-pill green"> <div class="sp-val">${stats.acknowledged}</div><div class="sp-label">Acknowledged</div></div>
    <div class="stat-pill red">   <div class="sp-val">${stats.missed}</div>      <div class="sp-label">Missed</div></div>
    <div class="stat-pill amber"> <div class="sp-val">${stats.alarms}</div>      <div class="sp-label">Alarms fired</div></div>`;

  $('hb-slots').innerHTML = (today.hb_timeline ?? []).map((s, i) =>
    `<div class="hb-slot s-${s}" title="${slotLabel(i)}: ${s}"></div>`
  ).join('');
  $('hb-updated').textContent = 'updated ' +
    new Date().toLocaleTimeString([], { hour: '2-digit', minute: '2-digit', second: '2-digit' });

  const stepCls  = { acknowledged: 'acked', missed: 'missed', pending: 'pending' };
  const stepIcon = { acknowledged: '✅', missed: '❌', pending: '⏳' };
  if (!today.steps.length) {
    $('steps-list').innerHTML = '<div style="padding:14px;color:var(--atm);font-size:.8em;font-style:italic">No steps for today.</div>';
  } else {
    $('steps-list').innerHTML = today.steps.map(s => `
      <div class="step-row">
        <span class="step-time-badge">${esc(s.scheduled_time)}</span>
        <span class="step-icon">${stepIcon[s.status] ?? '?'}</span>
        <span class="step-name ${stepCls[s.status] ?? ''}">${esc(s.title)}</span>
      </div>`).join('');
  }

  const sbDot = document.getElementById('sb-hb-' + id);
  if (sbDot) sbDot.className = 'hb ' + client.heartbeat_status;
}

/* ── Event timeline ─────────────────────────────────────────────────────── */
const evColors = {
  heartbeat:         '#2563EB',
  step_acknowledged: '#16A34A',
  step_missed:       '#DC2626',
  alarm_escalated:   '#D97706',
  button_press:      '#7C3AED',
  sequence_loaded:   '#0D9488',
  config_changed:    '#64748B',
};
const evLanes     = Object.keys(evColors);
const evLaneLabel = {
  heartbeat:'Heartbeat', step_acknowledged:"Ack'd", step_missed:'Missed',
  alarm_escalated:'Alarm', button_press:'Button', sequence_loaded:'Loaded', config_changed:'Config',
};

function buildTimelineSVG(events) {
  const VW = 800, LANE_H = 30, LABEL_W = 80, PAD_T = 10, PAD_B = 26, R = 5;
  const VH = PAD_T + evLanes.length * LANE_H + PAD_B;
  const PLOT_W = VW - LABEL_W - 8;

  const now  = Date.now();
  const tMax = now;
  const oldest = events.length ? new Date(events[events.length - 1].occurred_at).getTime() : now - 3600000;
  const tMin = Math.min(oldest, now - 3600000);
  const tRange = tMax - tMin || 1;
  const xOf = t => LABEL_W + ((t - tMin) / tRange) * PLOT_W;

  let gridLines = '';
  const hourMs = 3600000;
  let gt = Math.ceil(tMin / hourMs) * hourMs;
  while (gt <= tMax) {
    const x = xOf(gt).toFixed(1);
    const lbl = new Date(gt).toLocaleTimeString([], { hour: '2-digit', minute: '2-digit' });
    gridLines += `<line x1="${x}" y1="${PAD_T}" x2="${x}" y2="${PAD_T + evLanes.length * LANE_H}" stroke="#C4CEDE" stroke-width="1"/>`;
    gridLines += `<text x="${x}" y="${VH - 4}" fill="#8A97B8" font-size="9" font-family="monospace" text-anchor="middle">${lbl}</text>`;
    gt += hourMs;
  }

  let lanesBg = '', labels = '';
  evLanes.forEach((type, i) => {
    const y  = PAD_T + i * LANE_H;
    const bg = i % 2 === 0 ? '#F8F9FC' : '#F0F3F8';
    lanesBg += `<rect x="${LABEL_W}" y="${y}" width="${PLOT_W}" height="${LANE_H}" fill="${bg}"/>`;
    lanesBg += `<line x1="${LABEL_W}" y1="${y + LANE_H}" x2="${LABEL_W + PLOT_W}" y2="${y + LANE_H}" stroke="#D1D8E8" stroke-width="1"/>`;
    const col = evColors[type];
    labels   += `<rect x="4" y="${y + LANE_H / 2 - 5}" width="8" height="8" rx="2" fill="${col}"/>`;
    labels   += `<text x="17" y="${y + LANE_H / 2 + 4}" fill="#4D5D80" font-size="10" font-family="sans-serif">${evLaneLabel[type]}</text>`;
  });

  const nowX    = xOf(tMax).toFixed(1);
  const nowLine = `<line x1="${nowX}" y1="${PAD_T}" x2="${nowX}" y2="${PAD_T + evLanes.length * LANE_H}" stroke="#A0B0CC" stroke-width="1" stroke-dasharray="3,3"/>`;

  let dots = '';
  events.forEach(ev => {
    const li = evLanes.indexOf(ev.event_type);
    if (li < 0) return;
    const x   = xOf(new Date(ev.occurred_at).getTime()).toFixed(1);
    const y   = (PAD_T + li * LANE_H + LANE_H / 2).toFixed(1);
    const col = evColors[ev.event_type] || '#94A3B8';
    const meta = evMeta[ev.event_type] ?? { desc: () => ev.event_type };
    const raw  = meta.desc(ev.payload ?? {}).replace(/<[^>]*>/g, '');
    const ts   = new Date(ev.occurred_at).toLocaleTimeString([], { hour: '2-digit', minute: '2-digit', second: '2-digit' });
    const tip  = (ts + '\n' + raw).replace(/"/g, '&quot;');
    dots += `<circle cx="${x}" cy="${y}" r="${R}" fill="${col}" opacity=".85" class="ev-dot" data-tip="${tip}"/>`;
  });

  const svgEl = document.createElementNS('http://www.w3.org/2000/svg', 'svg');
  svgEl.setAttribute('width', '100%');
  svgEl.setAttribute('height', VH);
  svgEl.setAttribute('viewBox', `0 0 ${VW} ${VH}`);
  svgEl.setAttribute('preserveAspectRatio', 'none');
  svgEl.innerHTML = lanesBg + gridLines + nowLine + labels + dots;

  const tl  = $('ev-timeline');
  const tip = $('ev-tip');
  tl.innerHTML = '';
  tl.appendChild(svgEl);

  tl.querySelectorAll('.ev-dot').forEach(dot => {
    dot.style.cursor = 'pointer';
    dot.addEventListener('mouseenter', () => { tip.textContent = dot.dataset.tip; tip.style.display = 'block'; });
    dot.addEventListener('mousemove',  e  => { tip.style.left = (e.clientX + 14) + 'px'; tip.style.top = (e.clientY - 8) + 'px'; });
    dot.addEventListener('mouseleave', () => { tip.style.display = 'none'; });
  });
}

function switchEvView(view) {
  evView = view;
  document.querySelectorAll('.evtb').forEach(b => b.classList.toggle('active', b.dataset.view === view));
  $('ev-list').style.display     = view === 'list'     ? '' : 'none';
  $('ev-timeline').style.display = view === 'timeline' ? '' : 'none';
  if (view === 'timeline' && lastEvents.length) requestAnimationFrame(() => buildTimelineSVG(lastEvents));
}

/* ── Event stream ───────────────────────────────────────────────────────── */
const evMeta = {
  heartbeat:         { label: 'hb',     cls: 'pill-hb',     desc: ()  => 'Client connected' },
  step_acknowledged: { label: 'ack',    cls: 'pill-ack',    desc: p   => `Acknowledged: <strong>${esc(p.title ?? p.step_key)}</strong>` },
  step_missed:       { label: 'missed', cls: 'pill-missed', desc: p   => `Missed: <strong>${esc(p.title ?? p.step_key)}</strong>` },
  alarm_escalated:   { label: 'alarm',  cls: 'pill-alarm',  desc: p   => `Alarm level ${p.level ?? '?'} — <strong>${esc(p.title ?? p.step_key)}</strong>` },
  button_press:      { label: 'btn',    cls: 'pill-btn',    desc: p   => `Button <strong>${esc(p.button)}</strong>${p.context ? ' (' + esc(p.context) + ')' : ''}` },
  sequence_loaded:   { label: 'loaded', cls: 'pill-loaded', desc: p   => `Sequence loaded — <strong>${esc(String(p.step_count))} steps</strong>` },
  config_changed:    { label: 'config', cls: 'pill-config', desc: p   => `Sequence reassigned by <strong>${esc(p.changed_by)}</strong>` },
};

async function renderEvents(id) {
  const r = await fetch(`${BASE}/clients/${id}/events`, { headers: hdrs() });
  if (!r.ok) return;
  const events = await r.json();
  lastEvents = events;
  $('ev-count').textContent = events.length + ' events';

  if (!events.length) {
    $('ev-list').innerHTML     = '<div style="padding:16px;color:var(--atm);font-size:.8em;font-style:italic">No events yet.</div>';
    $('ev-timeline').innerHTML = '';
    return;
  }
  $('ev-list').innerHTML = events.map(ev => {
    const meta = evMeta[ev.event_type] ?? { label: ev.event_type, cls: 'pill-config', desc: () => ev.event_type };
    return `<div class="ev-row">
      <span class="ev-time">${fmtTs(ev.occurred_at)}</span>
      <span class="ev-pill ${meta.cls}">${meta.label}</span>
      <span class="ev-desc">${meta.desc(ev.payload ?? {})}</span>
    </div>`;
  }).join('');
  if (evView === 'timeline') requestAnimationFrame(() => buildTimelineSVG(events));
}

/* ── Client CRUD ────────────────────────────────────────────────────────── */
function populateSeqSel(sel) {
  $('mc-seq').innerHTML =
    `<option value=""${sel == null ? ' selected' : ''}>— none —</option>` +
    sequences.map(s => `<option value="${s.id}"${s.id === sel ? ' selected' : ''}>${esc(s.name)}</option>`).join('');
}

function seqSelValue() {
  const v = $('mc-seq').value;
  return v === '' ? null : +v;
}

async function reloadClients() {
  clients = await apiFetch(BASE + '/clients');
}

async function _deleteClient(c) {
  if (!confirm(`Delete "${c.name}"? This cannot be undone.`)) return;
  try { await apiFetch(`${BASE}/clients/${c.id}`, { method: 'DELETE' }); }
  catch (e) { alert('Error deleting client: ' + e.message); return; }
  clearTimeout(refreshTimer);
  if (activeId === c.id) {
    activeId = null;
    $('athena-dashboard').style.display   = 'none';
    $('athena-placeholder').style.display = '';
  }
  await reloadClients();
  renderSidebar();
  if (activeId === null && clients.length) await selectClient(clients[0].id);
}

window.athena = {
  openCreateClient() {
    editingId = null;
    $('mc-title').textContent = 'New client';
    $('mc-name').value = ''; $('mc-slug').value = '';
    populateSeqSel(null);
    let slugManuallyEdited = false;
    $('mc-slug').oninput = () => { slugManuallyEdited = $('mc-slug').value !== ''; };
    $('mc-name').oninput = () => {
      if (!slugManuallyEdited) $('mc-slug').value = slugify($('mc-name').value);
    };
    $('mc-save').onclick = async () => {
      const p = { name: $('mc-name').value.trim(), slug: $('mc-slug').value.trim() || null, sequenceId: seqSelValue() };
      if (!p.name) { alert('Name is required.'); return; }
      let d;
      try { d = await apiFetch(BASE + '/clients', { method: 'POST', body: JSON.stringify(p) }); }
      catch (e) { alert('Error creating client: ' + e.message); return; }
      this.closeModal('athena-modal-client');
      this.showToken(d.token);
      await reloadClients(); renderSidebar();
    };
    $('athena-modal-client').classList.add('open');
  },

  async openEditClient() {
    if (!activeId) return;
    editingId = activeId;
    const c = clients.find(x => x.id === activeId); if (!c) return;
    $('mc-title').textContent = 'Edit client';
    $('mc-name').value = c.name; $('mc-slug').value = c.slug;

    let seqList = sequences;
    if (!c.is_owner) {
      const r = await fetch(`${BASE}/clients/${c.id}/sequences`, { headers: hdrs() });
      if (r.ok) seqList = await r.json();
    }
    $('mc-seq').innerHTML =
      `<option value=""${c.sequence_id == null ? ' selected' : ''}>— none —</option>` +
      seqList.map(s => `<option value="${s.id}"${s.id === c.sequence_id ? ' selected' : ''}>${esc(s.name)}</option>`).join('');

    $('mc-save').onclick = async () => {
      const p = { name: $('mc-name').value.trim(), slug: $('mc-slug').value.trim(), sequenceId: seqSelValue() };
      try { await apiFetch(`${BASE}/clients/${editingId}`, { method: 'PUT', body: JSON.stringify(p) }); }
      catch (e) { alert('Error updating client: ' + e.message); return; }
      this.closeModal('athena-modal-client');
      await reloadClients();
      if (activeId === editingId) await renderMonitor(activeId);
      renderSidebar();
    };
    $('athena-modal-client').classList.add('open');
  },

  async doDeleteClient() {
    if (!activeId) return;
    const c = clients.find(x => x.id === activeId);
    if (c) await _deleteClient(c);
  },

  async doRotateToken() {
    if (!activeId) return;
    const c = clients.find(x => x.id === activeId);
    if (!confirm(`Rotate token for "${c?.name}"? The old token stops working immediately.`)) return;
    let d;
    try { d = await apiFetch(`${BASE}/clients/${activeId}/token`, { method: 'POST' }); }
    catch (e) { alert('Error rotating token: ' + e.message); return; }
    this.showToken(d.token);
  },

  openCreateSequence() {
    $('ms-name').value = ''; $('ms-abstract').value = '';
    $('ms-save').onclick = async () => {
      const p = { name: $('ms-name').value.trim(), abstract: $('ms-abstract').value.trim() };
      if (!p.name) { alert('Name is required.'); return; }
      try { await apiFetch(BASE + '/sequences', { method: 'POST', body: JSON.stringify(p) }); }
      catch (e) { alert('Error creating sequence: ' + e.message); return; }
      this.closeModal('athena-modal-seq');
      sequences = await apiFetch(BASE + '/sequences');
      renderSidebar();
    };
    $('athena-modal-seq').classList.add('open');
  },

  showToken(t) { $('token-val').textContent = t; $('athena-modal-token').classList.add('open'); },
  closeModal(id) { $(id).classList.remove('open'); },
  setEvView(view) { switchEvView(view); },

  async openShare() {
    if (!activeId) return;
    const c = clients.find(x => x.id === activeId); if (!c || !c.is_owner) return;
    shareTarget = { id: c.id, name: c.name };
    $('share-search').value = '';
    $('share-can-edit').checked = false;
    $('share-autocomplete').style.display = 'none';
    shareSearchResult = [];
    await this._refreshShareList();
    $('athena-modal-share').classList.add('open');
  },

  async _refreshShareList() {
    let shares;
    try { shares = await apiFetch(`${BASE}/clients/${shareTarget.id}/shares`); }
    catch { return; }
    const wrap = $('share-list');
    if (!shares.length) {
      wrap.innerHTML = '<div style="color:var(--atm);font-size:.8em;font-style:italic;padding:8px 0">No shares yet.</div>';
      return;
    }
    wrap.innerHTML = '';
    shares.forEach(s => {
      const row = document.createElement('div');
      row.className = 'share-row'; row.id = 'sr-' + s.id;
      const userDiv = document.createElement('div');
      userDiv.className = 'share-user';
      userDiv.innerHTML = esc(s.shared_with_display) + '<small>' + esc(s.shared_with_user_id) + '</small>';
      const permLabel = document.createElement('label');
      permLabel.className = 'share-perm';
      const cb = document.createElement('input');
      cb.type = 'checkbox'; cb.checked = !!s.can_edit;
      cb.addEventListener('change', () => window.athena.toggleShareEdit(s.id, cb.checked));
      permLabel.appendChild(cb);
      permLabel.append(' Edit');
      const rmBtn = document.createElement('button');
      rmBtn.className = 'btn btn-danger btn-sm'; rmBtn.textContent = 'Remove';
      rmBtn.addEventListener('click', () => window.athena.removeShare(s.id));
      row.appendChild(userDiv); row.appendChild(permLabel); row.appendChild(rmBtn);
      wrap.appendChild(row);
    });
  },

  async addShare() {
    const uid = $('share-search').dataset.selectedUid;
    if (!uid) { alert('Select a user from the search results first.'); return; }
    const canEdit = $('share-can-edit').checked;
    try { await apiFetch(`${BASE}/clients/${shareTarget.id}/shares`, { method: 'POST', body: JSON.stringify({ shareWith: uid, canEdit }) }); }
    catch (e) { alert('Error creating share: ' + e.message); return; }
    $('share-search').value = '';
    delete $('share-search').dataset.selectedUid;
    $('share-can-edit').checked = false;
    $('share-autocomplete').style.display = 'none';
    await this._refreshShareList();
  },

  async toggleShareEdit(shareId, canEdit) {
    await apiFetch(`${BASE}/clients/${shareTarget.id}/shares/${shareId}`, { method: 'PUT', body: JSON.stringify({ canEdit }) });
  },

  async removeShare(shareId) {
    await apiFetch(`${BASE}/clients/${shareTarget.id}/shares/${shareId}`, { method: 'DELETE' });
    await this._refreshShareList();
  },
};

/* ── DOM setup — run once the DOM is ready ──────────────────────────────── */
function _run() {

  document.querySelectorAll('.athena-modal-back').forEach(el =>
    el.addEventListener('click', e => { if (e.target === el) el.classList.remove('open'); })
  );

  let searchDebounce = null;
  $('share-search').addEventListener('input', () => {
    clearTimeout(searchDebounce);
    const q = $('share-search').value.trim();
    delete $('share-search').dataset.selectedUid;
    if (q.length < 2) { $('share-autocomplete').style.display = 'none'; return; }
    searchDebounce = setTimeout(async () => {
      let users;
      try { users = await apiFetch(SEARCH_URL + '?q=' + encodeURIComponent(q)); }
      catch { return; }
      shareSearchResult = users;
      const ac = $('share-autocomplete');
      if (!users.length) { ac.style.display = 'none'; return; }
      ac.innerHTML = users.map((u, i) =>
        `<div class="share-ac-item" data-idx="${i}">${esc(u.displayName)}<small>${esc(u.uid)}</small></div>`
      ).join('');
      ac.querySelectorAll('.share-ac-item').forEach(el => {
        el.addEventListener('click', () => {
          const u = shareSearchResult[+el.dataset.idx];
          $('share-search').value = u.displayName + ' (' + u.uid + ')';
          $('share-search').dataset.selectedUid = u.uid;
          ac.style.display = 'none';
        });
      });
      ac.style.display = 'block';
    }, 300);
  });
  document.addEventListener('click', e => {
    const wrap = $('share-search-wrap');
    if (wrap && !wrap.contains(e.target)) $('share-autocomplete').style.display = 'none';
  });

  $('btn-create-client').addEventListener('click', () => window.athena.openCreateClient());
  $('btn-create-seq').addEventListener('click',    () => window.athena.openCreateSequence());
  $('mc-cancel').addEventListener('click',         () => window.athena.closeModal('athena-modal-client'));
  $('ms-cancel').addEventListener('click',         () => window.athena.closeModal('athena-modal-seq'));
  $('token-done').addEventListener('click',        () => window.athena.closeModal('athena-modal-token'));
  $('share-add-btn').addEventListener('click',     () => window.athena.addShare());
  $('share-close-btn').addEventListener('click',   () => window.athena.closeModal('athena-modal-share'));

  document.querySelectorAll('.evtb').forEach(b =>
    b.addEventListener('click', () => switchEvView(b.dataset.view))
  );

  init();
}

if (document.readyState !== 'loading') {
  _run();
} else {
  document.addEventListener('DOMContentLoaded', _run);
}

})(); // IIFE
