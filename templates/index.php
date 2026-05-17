<?php
declare(strict_types=1);
/** @var \OCP\IL10N $l */
?>
<link rel="preconnect" href="https://fonts.googleapis.com">
<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
<link href="https://fonts.googleapis.com/css2?family=Syne:wght@500;700&family=DM+Sans:wght@400;500&family=Martian+Mono:wght@400;500&display=swap" rel="stylesheet">

<style>
/* ── Design tokens (dark monitoring theme) ─────────────────────────────── */
#app {
  --ab:         #0B0F1A;
  --as:         #121828;
  --as2:        #1A2035;
  --as3:        #212840;
  --abr:        #242D45;
  --abr2:       #2E3855;
  --at:         #D4DCF0;
  --atd:        #8A97B8;
  --atm:        #4D5D80;
  --green:      #4ADE80;
  --green-dim:  rgba(74,222,128,.12);
  --amber:      #FBB040;
  --amber-dim:  rgba(251,176,64,.12);
  --red:        #F87171;
  --red-dim:    rgba(248,113,113,.12);
  --blue:       #60A5FA;
  --blue-dim:   rgba(96,165,250,.12);
  --purple:     #C084FC;
  --purple-dim: rgba(192,132,252,.12);
  --teal:       #34D399;
  --teal-dim:   rgba(52,211,153,.12);
  --grey:       #94A3B8;
  --grey-dim:   rgba(148,163,184,.12);
  --fh: 'Syne', sans-serif;
  --fb: 'DM Sans', var(--font-face, sans-serif);
  --fm: 'Martian Mono', 'Courier New', monospace;
}

/* ── NC layout integration ─────────────────────────────────────────────── */
#app {
  display: flex;
  height: 100%;
  background: var(--ab);
  color: var(--at);
  font-family: var(--fb);
  font-size: 14px;
}

/* Override NC's navigation panel to match our dark sidebar */
#app-navigation {
  background: var(--as) !important;
  border-right: 1px solid var(--abr) !important;
  width: 256px !important;
  min-width: 220px;
  padding: 0 !important;
  display: flex;
  flex-direction: column;
  overflow: hidden;
}

/* Override NC's content area */
#app-content {
  background: var(--ab) !important;
  flex: 1;
  overflow-y: auto;
  min-width: 0;
  padding: 0 !important;
  display: flex;
  flex-direction: column;
}

/* ── Sidebar internals ─────────────────────────────────────────────────── */
#athena-sb-head {
  padding: 14px 14px 10px;
  border-bottom: 1px solid var(--abr);
  flex-shrink: 0;
}
#athena-sb-head h2 {
  font-family: var(--fh); font-size: .9em; font-weight: 700;
  color: var(--at); letter-spacing: .06em; text-transform: uppercase;
  margin-bottom: 8px;
}
.sb-actions { display: flex; gap: 6px; }
#athena-client-list { flex: 1; overflow-y: auto; padding: 6px 0; }

.client-card {
  display: flex; align-items: center; gap: 10px;
  padding: 10px 14px; cursor: pointer;
  border-left: 3px solid transparent;
  transition: background .15s, border-color .15s;
  border-bottom: 1px solid var(--abr);
}
.client-card:last-child { border-bottom: none; }
.client-card:hover  { background: var(--as2); }
.client-card.active { background: var(--as3); border-left-color: var(--blue); }
.cc-info  { flex: 1; min-width: 0; }
.cc-name  { font-weight: 500; font-size: .88em; white-space: nowrap; overflow: hidden; text-overflow: ellipsis; }
.cc-sub   { font-size: .7em; color: var(--atm); font-family: var(--fm); margin-top: 2px; }
.cc-caret { color: var(--atm); font-size: .8em; opacity: .4; }

/* ── Heartbeat dot ─────────────────────────────────────────────────────── */
.hb { width: 8px; height: 8px; border-radius: 50%; flex-shrink: 0; }
.hb.active  { background: var(--green);  box-shadow: 0 0 6px var(--green); }
.hb.idle    { background: var(--amber); }
.hb.offline { background: var(--red); }
.hb.never   { background: var(--atm); }

/* ── Main panel ────────────────────────────────────────────────────────── */
#athena-placeholder {
  flex: 1; display: flex; align-items: center; justify-content: center;
  color: var(--atm); font-size: .88em; font-style: italic;
}

#athena-dashboard {
  display: none; flex-direction: column;
  padding: 20px 24px; gap: 18px;
}

/* ── Header ────────────────────────────────────────────────────────────── */
#db-header { display: flex; align-items: center; gap: 12px; flex-wrap: wrap; }
#db-header h2 {
  font-family: var(--fh); font-size: 1.3em; font-weight: 700;
  flex: 1; min-width: 0;
}
.hb-label { font-size: .78em; color: var(--atd); font-family: var(--fm); }
.hdr-actions { display: flex; gap: 6px; }

/* ── Stat pills ────────────────────────────────────────────────────────── */
#db-stats { display: flex; gap: 10px; flex-wrap: wrap; }
.stat-pill {
  background: var(--as); border: 1px solid var(--abr);
  border-radius: 6px; padding: 8px 14px; min-width: 100px;
}
.stat-pill .sp-val   { font-family: var(--fm); font-size: 1.4em; font-weight: 500; line-height: 1; }
.stat-pill .sp-label { font-size: .68em; color: var(--atm); margin-top: 4px; text-transform: uppercase; letter-spacing: .06em; }
.stat-pill.blue  .sp-val { color: var(--blue); }
.stat-pill.green .sp-val { color: var(--green); }
.stat-pill.red   .sp-val { color: var(--red); }
.stat-pill.amber .sp-val { color: var(--amber); }

/* ── Heartbeat timeline ────────────────────────────────────────────────── */
#hb-card {
  background: var(--as); border: 1px solid var(--abr);
  border-radius: 8px; padding: 14px 16px;
}
.card-title {
  font-family: var(--fh); font-size: .75em; font-weight: 700;
  text-transform: uppercase; letter-spacing: .08em; color: var(--atm);
  margin-bottom: 12px; display: flex; justify-content: space-between; align-items: center;
}
.card-title-right { font-family: var(--fm); font-size: .88em; color: var(--atd); text-transform: none; letter-spacing: 0; font-weight: 400; }
#hb-slots { display: flex; gap: 2px; align-items: flex-end; }
.hb-slot {
  flex: 1; height: 20px; border-radius: 3px;
  transition: transform .1s; cursor: default;
}
.hb-slot:hover { transform: scaleY(1.4); }
.hb-slot.s-active   { background: var(--green); opacity: .85; }
.hb-slot.s-missed   { background: var(--red);   opacity: .6; }
.hb-slot.s-inactive { background: var(--as3); }
.hb-slot.s-future   { background: var(--as2); }
#hb-labels {
  display: flex; justify-content: space-between;
  margin-top: 6px; font-family: var(--fm); font-size: .62em; color: var(--atm);
}
.hb-legend { display: flex; gap: 14px; margin-top: 10px; }
.hb-legend-item { display: flex; align-items: center; gap: 5px; font-size: .7em; color: var(--atm); }
.hb-legend-swatch { width: 10px; height: 10px; border-radius: 2px; }

/* ── Two-column: events + steps ────────────────────────────────────────── */
#db-columns { display: grid; grid-template-columns: 1fr 300px; gap: 16px; }

/* Event stream */
#ev-card, #steps-card {
  background: var(--as); border: 1px solid var(--abr);
  border-radius: 8px; overflow: hidden; display: flex; flex-direction: column;
}
#steps-card { align-self: start; }
.card-head {
  padding: 10px 14px; border-bottom: 1px solid var(--abr);
  font-family: var(--fh); font-size: .75em; font-weight: 700;
  text-transform: uppercase; letter-spacing: .08em; color: var(--atm);
  display: flex; align-items: center; justify-content: space-between;
  flex-shrink: 0;
}
.card-head-right { font-family: var(--fm); font-size: .88em; color: var(--atd); text-transform: none; letter-spacing: 0; font-weight: 400; }
#ev-list { overflow-y: auto; max-height: 440px; }
.ev-row {
  display: flex; align-items: flex-start; gap: 10px;
  padding: 8px 14px; border-bottom: 1px solid var(--abr2);
  transition: background .1s;
}
.ev-row:last-child { border-bottom: none; }
.ev-row:hover { background: var(--as2); }
.ev-time { font-family: var(--fm); font-size: .68em; color: var(--atm); min-width: 52px; padding-top: 2px; white-space: nowrap; }
.ev-pill { font-size: .62em; font-weight: 500; padding: 2px 6px; border-radius: 3px; white-space: nowrap; font-family: var(--fm); flex-shrink: 0; }
.ev-desc { font-size: .8em; color: var(--atd); line-height: 1.45; min-width: 0; }
.ev-desc strong { color: var(--at); font-weight: 500; }

.pill-hb     { color: var(--blue);   background: var(--blue-dim); }
.pill-ack    { color: var(--green);  background: var(--green-dim); }
.pill-missed { color: var(--red);    background: var(--red-dim); }
.pill-alarm  { color: var(--amber);  background: var(--amber-dim); }
.pill-btn    { color: var(--purple); background: var(--purple-dim); }
.pill-loaded { color: var(--teal);   background: var(--teal-dim); }
.pill-config { color: var(--grey);   background: var(--grey-dim); }

/* ── Shared client indicator ───────────────────────────────────────────── */
.cc-shared-badge { font-size:.58em; color:var(--blue); background:var(--blue-dim); border-radius:3px; padding:1px 4px; margin-left:4px; vertical-align:middle; font-family:var(--fm); letter-spacing:0; text-transform:none; font-weight:500; flex-shrink:0; }

/* ── Share modal internals ─────────────────────────────────────────────── */
.share-row { display:flex; align-items:center; gap:8px; padding:8px 0; border-bottom:1px solid var(--abr2); }
.share-row:last-child { border-bottom:none; }
.share-user { flex:1; min-width:0; font-size:.82em; overflow:hidden; text-overflow:ellipsis; white-space:nowrap; }
.share-user small { display:block; font-size:.8em; color:var(--atm); font-family:var(--fm); }
.share-perm { display:flex; align-items:center; gap:5px; font-size:.75em; color:var(--atm); white-space:nowrap; flex-shrink:0; }
.share-perm input[type=checkbox] { accent-color:var(--blue); width:14px; height:14px; cursor:pointer; }
#share-list-wrap { max-height:200px; overflow-y:auto; margin-bottom:10px; }
#share-search-wrap { position:relative; }
#share-search { width:100%; background:var(--as2); border:1px solid var(--abr2); border-radius:5px; padding:7px 10px; font-size:.85em; color:var(--at); font-family:var(--fb); outline:none; box-sizing:border-box; }
#share-search:focus { border-color:var(--blue); box-shadow:0 0 0 2px rgba(96,165,250,.15); }
#share-autocomplete { position:absolute; top:calc(100% + 3px); left:0; right:0; z-index:100; background:var(--as2); border:1px solid var(--abr2); border-radius:5px; box-shadow:0 6px 20px rgba(0,0,0,.5); overflow:hidden; display:none; }
.share-ac-item { padding:8px 12px; cursor:pointer; font-size:.83em; display:flex; flex-direction:column; gap:1px; }
.share-ac-item:hover { background:var(--as3); }
.share-ac-item small { color:var(--atm); font-family:var(--fm); font-size:.85em; }
.share-add-row { display:flex; gap:8px; align-items:center; margin-top:10px; }
.share-add-row .share-perm { flex-shrink:0; }

/* ── Event view toggle ─────────────────────────────────────────────────── */
.ev-view-toggle { display:flex; border:1px solid var(--abr2); border-radius:5px; overflow:hidden; flex-shrink:0; }
.evtb { background:transparent; color:var(--atm); border:none; border-right:1px solid var(--abr2); padding:3px 10px; font-size:.7em; font-family:var(--fb); cursor:pointer; white-space:nowrap; }
.evtb:last-child { border-right:none; }
.evtb:hover { background:var(--as3); color:var(--at); }
.evtb.active { background:var(--as3); color:var(--at); }
#ev-timeline { overflow-x:hidden; padding:12px 4px; }
#ev-timeline svg { display:block; }
#ev-tip { position:fixed; z-index:9999; pointer-events:none; background:#1A2035; border:1px solid #2E3855; border-radius:5px; padding:6px 10px; font-family:'Martian Mono',monospace; font-size:.72em; color:#D4DCF0; box-shadow:0 4px 16px rgba(0,0,0,.5); display:none; max-width:280px; line-height:1.5; white-space:pre-line; }

/* Steps */
.step-row {
  display: flex; align-items: center; gap: 8px;
  padding: 9px 14px; border-bottom: 1px solid var(--abr2);
}
.step-row:last-child { border-bottom: none; }
.step-time-badge {
  font-family: var(--fm); font-size: .62em; color: var(--atm);
  background: var(--as3); padding: 2px 5px; border-radius: 3px;
  white-space: nowrap; flex-shrink: 0;
}
.step-icon { font-size: .9em; flex-shrink: 0; width: 18px; text-align: center; }
.step-name { flex: 1; font-size: .8em; overflow: hidden; text-overflow: ellipsis; white-space: nowrap; }
.step-name.acked  { color: var(--green); }
.step-name.missed { color: var(--red); text-decoration: line-through; opacity: .8; }
.step-name.pending{ color: var(--atd); }

/* ── Buttons ────────────────────────────────────────────────────────────── */
button { cursor: pointer; font-family: var(--fb); }
.btn { border: none; border-radius: 5px; padding: 6px 13px; font-size: .8em; font-weight: 500; }
.btn-primary { background: var(--blue); color: #0B0F1A; }
.btn-primary:hover { filter: brightness(1.1); }
.btn-ghost   { background: var(--as3); color: var(--atd); border: 1px solid var(--abr2); }
.btn-ghost:hover { background: var(--as2); color: var(--at); }
.btn-danger  { background: var(--red-dim); color: var(--red); border: 1px solid rgba(248,113,113,.2); }
.btn-danger:hover { background: rgba(248,113,113,.2); }
.btn-sm { padding: 4px 10px; font-size: .75em; }

/* ── Modals ─────────────────────────────────────────────────────────────── */
.athena-modal-back {
  display: none; position: fixed; inset: 0;
  background: rgba(0,0,0,.65); z-index: 9999;
  align-items: center; justify-content: center;
}
.athena-modal-back.open { display: flex; }
.athena-modal {
  background: var(--as); border: 1px solid var(--abr2);
  border-radius: 10px; padding: 24px; width: 460px; max-width: 95vw;
  box-shadow: 0 20px 60px rgba(0,0,0,.7);
}
.athena-modal h3 { font-family: var(--fh); font-size: 1em; font-weight: 700; margin-bottom: 14px; color: var(--at); }
.athena-modal label { display: block; font-size: .72em; color: var(--atm); text-transform: uppercase; letter-spacing: .06em; margin: 12px 0 5px; }
.athena-modal input, .athena-modal select, .athena-modal textarea {
  width: 100%; background: var(--as2); border: 1px solid var(--abr2);
  border-radius: 5px; padding: 7px 10px; font-size: .85em; color: var(--at);
  font-family: var(--fb); outline: none; box-sizing: border-box;
}
.athena-modal input:focus, .athena-modal select:focus, .athena-modal textarea:focus {
  border-color: var(--blue); box-shadow: 0 0 0 2px rgba(96,165,250,.15);
}
.athena-modal textarea { min-height: 72px; resize: vertical; }
.athena-modal select option { background: var(--as2); }
.modal-actions { display: flex; gap: 8px; justify-content: flex-end; margin-top: 18px; }
.modal-note {
  background: var(--amber-dim); border: 1px solid rgba(251,176,64,.2);
  border-radius: 5px; padding: 8px 12px; font-size: .75em; color: var(--amber);
  margin-top: 12px; line-height: 1.5;
}
.token-box {
  background: var(--as2); border: 1px dashed var(--abr2); border-radius: 5px;
  padding: 10px 12px; font-family: var(--fm); font-size: .78em;
  word-break: break-all; color: var(--green); margin-top: 10px; line-height: 1.6;
}
</style>

<div id="app">

  <!-- ── Sidebar / NC app-navigation ─────────────────────────────────── -->
  <div id="app-navigation">
    <div id="athena-sb-head">
      <h2>Athena</h2>
      <div class="sb-actions">
        <button class="btn btn-primary" style="flex:1" onclick="athena.openCreateClient()">+ Client</button>
        <button class="btn btn-ghost"   style="flex:1" onclick="athena.openCreateSequence()">+ Sequence</button>
      </div>
    </div>
    <div id="athena-client-list"></div>
  </div>

  <!-- ── Main content / NC app-content ────────────────────────────────── -->
  <div id="app-content">

    <div id="athena-placeholder">← Select a client to open its monitoring board</div>

    <div id="athena-dashboard">

      <!-- Header -->
      <div id="db-header">
        <span class="hb" id="db-hb-dot"></span>
        <h2 id="db-name"></h2>
        <span class="hb-label" id="db-hb-label"></span>
        <div class="hdr-actions" id="db-actions"></div>
      </div>

      <!-- Stats -->
      <div id="db-stats"></div>

      <!-- Heartbeat timeline -->
      <div id="hb-card">
        <div class="card-title">
          Heartbeat &mdash; last 24 h
          <span class="card-title-right" id="hb-updated"></span>
        </div>
        <div id="hb-slots"></div>
        <div id="hb-labels">
          <span>00:00</span><span>04:00</span><span>08:00</span>
          <span>12:00</span><span>16:00</span><span>20:00</span><span>24:00</span>
        </div>
        <div class="hb-legend">
          <div class="hb-legend-item"><div class="hb-legend-swatch" style="background:var(--green)"></div>heartbeat received</div>
          <div class="hb-legend-item"><div class="hb-legend-swatch" style="background:var(--red);opacity:.6"></div>no heartbeat (gap)</div>
          <div class="hb-legend-item"><div class="hb-legend-swatch" style="background:var(--as3)"></div>inactive / future</div>
        </div>
      </div>

      <!-- Events + Steps -->
      <div id="db-columns">
        <div id="ev-card">
          <div class="card-head">
            Event stream
            <div style="display:flex;align-items:center;gap:10px">
              <span class="card-head-right" id="ev-count"></span>
              <div class="ev-view-toggle">
                <button class="evtb active" data-view="list"     onclick="athena.setEvView('list')">≡ List</button>
                <button class="evtb"        data-view="timeline" onclick="athena.setEvView('timeline')">◈ Timeline</button>
              </div>
            </div>
          </div>
          <div id="ev-list"></div>
          <div id="ev-timeline" style="display:none"></div>
        </div>

        <div id="steps-card">
          <div class="card-head">Today's steps</div>
          <div id="steps-list"></div>
        </div>
      </div>

      <div style="height:8px"></div>
    </div><!-- /athena-dashboard -->

  </div><!-- /app-content -->

</div><!-- /app -->

<div id="ev-tip"></div>

<!-- ── Modals (fixed, outside #app) ───────────────────────────────────── -->

<div class="athena-modal-back" id="athena-modal-share">
  <div class="athena-modal" style="width:500px">
    <h3>Share client</h3>
    <div id="share-list-wrap"><div id="share-list"></div></div>
    <label style="margin-top:4px">Add user</label>
    <div id="share-search-wrap">
      <input id="share-search" placeholder="Search by name or username…" autocomplete="off"/>
      <div id="share-autocomplete"></div>
    </div>
    <div class="share-add-row">
      <label class="share-perm" style="margin:0">
        <input type="checkbox" id="share-can-edit"/> Allow editing
      </label>
      <button class="btn btn-primary btn-sm" style="margin-left:auto" onclick="athena.addShare()">Add</button>
    </div>
    <div class="modal-actions" style="margin-top:10px">
      <button class="btn btn-ghost" onclick="athena.closeModal('athena-modal-share')">Close</button>
    </div>
  </div>
</div>

<div class="athena-modal-back" id="athena-modal-client">
  <div class="athena-modal">
    <h3 id="mc-title">New client</h3>
    <label>Name</label>
    <input id="mc-name" placeholder="Maria's Kobo"/>
    <label>Slug <small style="text-transform:none;font-weight:400;letter-spacing:0">(lowercase, digits, hyphens)</small></label>
    <input id="mc-slug" placeholder="maria" style="font-family:var(--fm)"/>
    <label>Assigned sequence</label>
    <select id="mc-seq"></select>
    <div class="modal-actions">
      <button class="btn btn-ghost"   onclick="athena.closeModal('athena-modal-client')">Cancel</button>
      <button class="btn btn-primary" id="mc-save">Save</button>
    </div>
  </div>
</div>

<div class="athena-modal-back" id="athena-modal-token">
  <div class="athena-modal">
    <h3>Bearer token</h3>
    <p style="font-size:.82em;color:var(--atd);line-height:1.5">
      Copy this token now — it is shown only once and will not be stored in plaintext.
    </p>
    <div class="token-box" id="token-val"></div>
    <div class="modal-note">
      Configure this as <code>Authorization: Bearer &lt;token&gt;</code> on the Kobo device.
    </div>
    <div class="modal-actions">
      <button class="btn btn-primary" onclick="athena.closeModal('athena-modal-token')">Done</button>
    </div>
  </div>
</div>

<div class="athena-modal-back" id="athena-modal-seq">
  <div class="athena-modal">
    <h3>New sequence</h3>
    <label>Name / slug</label>
    <input id="ms-name" placeholder="daily-routine" style="font-family:var(--fm)"/>
    <label>Abstract</label>
    <textarea id="ms-abstract" placeholder="Short description shown on the Kobo…"></textarea>
    <div class="modal-actions">
      <button class="btn btn-ghost"   onclick="athena.closeModal('athena-modal-seq')">Cancel</button>
      <button class="btn btn-primary" id="ms-save">Save</button>
    </div>
  </div>
</div>

<script>
(function () {
'use strict';

const BASE   = OC.generateUrl('/apps/athena/api/v1/manage');
const hdrs   = () => ({ 'Content-Type': 'application/json', 'requesttoken': OC.requestToken });
const $      = id => document.getElementById(id);
const esc    = s  => String(s ?? '').replace(/&/g,'&amp;').replace(/</g,'&lt;').replace(/>/g,'&gt;');

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

/* ── State ──────────────────────────────────────────────────────────────── */
let sequences = [], clients = [], activeId = null, refreshTimer = null, editingId = null, evView = 'list', lastEvents = [];
let shareTarget = null; // {id, name} of client whose share modal is open
let shareSearchResult = []; // autocomplete results

/* ── Bootstrap ──────────────────────────────────────────────────────────── */
async function init() {
  await Promise.all([
    fetch(BASE + '/sequences', { headers: hdrs() }).then(r => r.json()).then(d => sequences = d),
    fetch(BASE + '/clients',   { headers: hdrs() }).then(r => r.json()).then(d => clients   = d),
  ]);
  renderSidebar();
  if (clients.length) await selectClient(clients[0].id);
}

/* ── Sidebar ─────────────────────────────────────────────────────────────── */
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
        <div class="cc-sub">${esc(c.slug)} · ${esc(seq?.name ?? '—')}</div>
      </div>
      <span class="cc-caret">›</span>`;
    div.onclick = () => selectClient(c.id);
    el.appendChild(div);
  }
}

/* ── Select + auto-refresh ───────────────────────────────────────────────── */
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

/* ── Monitor panel ────────────────────────────────────────────────────────── */
async function renderMonitor(id) {
  const r = await fetch(`${BASE}/clients/${id}/monitor`, { headers: hdrs() });
  if (!r.ok) return;
  const { client, today } = await r.json();

  $('db-name').textContent     = client.name;
  $('db-hb-dot').className     = 'hb ' + client.heartbeat_status;
  $('db-hb-label').textContent = client.last_heartbeat
    ? 'Last seen ' + relTime(client.last_heartbeat) : 'Never connected';

  // Render action buttons according to permission tier
  const acts = [];
  if (client.can_edit)  acts.push(`<button class="btn btn-ghost btn-sm" onclick="athena.openEditClient()">Edit</button>`);
  if (client.is_owner)  acts.push(`<button class="btn btn-ghost btn-sm" onclick="athena.doRotateToken()">Rotate token</button>`);
  if (client.is_owner)  acts.push(`<button class="btn btn-ghost btn-sm" onclick="athena.openShare()">Share</button>`);
  if (client.is_owner)  acts.push(`<button class="btn btn-danger btn-sm" onclick="athena.doDeleteClient()">Delete</button>`);
  $('db-actions').innerHTML = acts.join('');

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

/* ── Event timeline ───────────────────────────────────────────────────────── */
const evColors = {
  heartbeat:         '#60A5FA',
  step_acknowledged: '#4ADE80',
  step_missed:       '#F87171',
  alarm_escalated:   '#FBB040',
  button_press:      '#C084FC',
  sequence_loaded:   '#34D399',
  config_changed:    '#94A3B8',
};
const evLanes     = Object.keys(evColors);
const evLaneLabel = { heartbeat:'Heartbeat', step_acknowledged:'Ack\'d', step_missed:'Missed',
  alarm_escalated:'Alarm', button_press:'Button', sequence_loaded:'Loaded', config_changed:'Config' };

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
    gridLines += `<line x1="${x}" y1="${PAD_T}" x2="${x}" y2="${PAD_T + evLanes.length * LANE_H}" stroke="#242D45" stroke-width="1"/>`;
    gridLines += `<text x="${x}" y="${VH - 4}" fill="#4D5D80" font-size="9" font-family="Martian Mono,monospace" text-anchor="middle">${lbl}</text>`;
    gt += hourMs;
  }

  let lanesBg = '', labels = '';
  evLanes.forEach((type, i) => {
    const y  = PAD_T + i * LANE_H;
    const bg = i % 2 === 0 ? '#121828' : '#0F1522';
    lanesBg += `<rect x="${LABEL_W}" y="${y}" width="${PLOT_W}" height="${LANE_H}" fill="${bg}"/>`;
    lanesBg += `<line x1="${LABEL_W}" y1="${y + LANE_H}" x2="${LABEL_W + PLOT_W}" y2="${y + LANE_H}" stroke="#242D45" stroke-width="1"/>`;
    const col = evColors[type];
    labels   += `<rect x="4" y="${y + LANE_H / 2 - 5}" width="8" height="8" rx="2" fill="${col}"/>`;
    labels   += `<text x="17" y="${y + LANE_H / 2 + 4}" fill="#8A97B8" font-size="10" font-family="DM Sans,sans-serif">${evLaneLabel[type]}</text>`;
  });

  const nowX   = xOf(tMax).toFixed(1);
  const nowLine = `<line x1="${nowX}" y1="${PAD_T}" x2="${nowX}" y2="${PAD_T + evLanes.length * LANE_H}" stroke="#3B4F70" stroke-width="1" stroke-dasharray="3,3"/>`;

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

  const tl = $('ev-timeline');
  tl.innerHTML = '';
  tl.appendChild(svgEl);

  const tip = $('ev-tip');
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

/* ── Event stream ─────────────────────────────────────────────────────────── */
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

/* ── Client CRUD ──────────────────────────────────────────────────────────── */
function populateSeqSel(sel) {
  $('mc-seq').innerHTML = sequences
    .map(s => `<option value="${s.id}"${s.id === sel ? ' selected' : ''}>${esc(s.name)}</option>`)
    .join('');
}

async function reloadClients() {
  clients = await fetch(BASE + '/clients', { headers: hdrs() }).then(r => r.json());
}

window.athena = {
  openCreateClient() {
    editingId = null;
    $('mc-title').textContent = 'New client';
    $('mc-name').value = ''; $('mc-slug').value = '';
    populateSeqSel(null);
    $('mc-save').onclick = async () => {
      const p = { name: $('mc-name').value.trim(), slug: $('mc-slug').value.trim(), sequenceId: +$('mc-seq').value };
      if (!p.name || !p.slug) { alert('Name and slug are required.'); return; }
      const r = await fetch(BASE + '/clients', { method: 'POST', headers: hdrs(), body: JSON.stringify(p) });
      const d = await r.json();
      if (!r.ok) { alert(d.error ?? 'Error creating client'); return; }
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

    // Shared editors need the owner's sequences, not their own
    let seqList = sequences;
    if (!c.is_owner) {
      const r = await fetch(`${BASE}/clients/${c.id}/sequences`, { headers: hdrs() });
      if (r.ok) seqList = await r.json();
    }
    $('mc-seq').innerHTML = seqList
      .map(s => `<option value="${s.id}"${s.id === c.sequence_id ? ' selected' : ''}>${esc(s.name)}</option>`)
      .join('');

    $('mc-save').onclick = async () => {
      const p = { name: $('mc-name').value.trim(), slug: $('mc-slug').value.trim(), sequenceId: +$('mc-seq').value };
      const r = await fetch(`${BASE}/clients/${editingId}`, { method: 'PUT', headers: hdrs(), body: JSON.stringify(p) });
      if (!r.ok) { alert('Error updating client'); return; }
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
    if (!confirm(`Delete "${c?.name}"? This cannot be undone.`)) return;
    await fetch(`${BASE}/clients/${activeId}`, { method: 'DELETE', headers: hdrs() });
    clearTimeout(refreshTimer);
    await reloadClients();
    activeId = null;
    $('athena-dashboard').style.display   = 'none';
    $('athena-placeholder').style.display = '';
    renderSidebar();
    if (clients.length) await selectClient(clients[0].id);
  },

  async doRotateToken() {
    if (!activeId) return;
    const c = clients.find(x => x.id === activeId);
    if (!confirm(`Rotate token for "${c?.name}"? The old token stops working immediately.`)) return;
    const r = await fetch(`${BASE}/clients/${activeId}/token`, { method: 'POST', headers: hdrs() });
    const d = await r.json();
    if (!r.ok) { alert('Error rotating token'); return; }
    this.showToken(d.token);
  },

  openCreateSequence() {
    $('ms-name').value = ''; $('ms-abstract').value = '';
    $('ms-save').onclick = async () => {
      const p = { name: $('ms-name').value.trim(), abstract: $('ms-abstract').value.trim() };
      if (!p.name) { alert('Name is required.'); return; }
      const r = await fetch(BASE + '/sequences', { method: 'POST', headers: hdrs(), body: JSON.stringify(p) });
      if (!r.ok) { alert('Error creating sequence'); return; }
      this.closeModal('athena-modal-seq');
      sequences = await fetch(BASE + '/sequences', { headers: hdrs() }).then(r => r.json());
      renderSidebar();
    };
    $('athena-modal-seq').classList.add('open');
  },

  showToken(t) { $('token-val').textContent = t; $('athena-modal-token').classList.add('open'); },
  closeModal(id) { $(id).classList.remove('open'); },
  setEvView(view) { switchEvView(view); },

  // ── Share management ────────────────────────────────────────────────────
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
    const r = await fetch(`${BASE}/clients/${shareTarget.id}/shares`, { headers: hdrs() });
    if (!r.ok) return;
    const shares = await r.json();
    const wrap = $('share-list');
    if (!shares.length) {
      wrap.innerHTML = '<div style="color:var(--atm);font-size:.8em;font-style:italic;padding:8px 0">No shares yet.</div>';
      return;
    }
    wrap.innerHTML = shares.map(s => `
      <div class="share-row" id="sr-${s.id}">
        <div class="share-user">
          ${esc(s.shared_with_display)}
          <small>${esc(s.shared_with_user_id)}</small>
        </div>
        <label class="share-perm">
          <input type="checkbox" onchange="athena.toggleShareEdit(${s.id}, this.checked)"${s.can_edit ? ' checked' : ''}/>
          Edit
        </label>
        <button class="btn btn-danger btn-sm" onclick="athena.removeShare(${s.id})">Remove</button>
      </div>`).join('');
  },

  async addShare() {
    const uid = $('share-search').dataset.selectedUid;
    if (!uid) { alert('Select a user from the search results first.'); return; }
    const canEdit = $('share-can-edit').checked;
    const r = await fetch(`${BASE}/clients/${shareTarget.id}/shares`, {
      method: 'POST', headers: hdrs(),
      body: JSON.stringify({ shareWith: uid, canEdit }),
    });
    if (!r.ok) {
      const d = await r.json().catch(() => ({}));
      alert(d.message ?? 'Error creating share');
      return;
    }
    $('share-search').value = '';
    delete $('share-search').dataset.selectedUid;
    $('share-can-edit').checked = false;
    $('share-autocomplete').style.display = 'none';
    await this._refreshShareList();
  },

  async toggleShareEdit(shareId, canEdit) {
    await fetch(`${BASE}/clients/${shareTarget.id}/shares/${shareId}`, {
      method: 'PUT', headers: hdrs(), body: JSON.stringify({ canEdit }),
    });
  },

  async removeShare(shareId) {
    await fetch(`${BASE}/clients/${shareTarget.id}/shares/${shareId}`, {
      method: 'DELETE', headers: hdrs(),
    });
    await this._refreshShareList();
  },
};

/* ── Close modals on backdrop click ──────────────────────────────────────── */
document.querySelectorAll('.athena-modal-back').forEach(el =>
  el.addEventListener('click', e => { if (e.target === el) el.classList.remove('open'); })
);

/* ── Share user-search autocomplete ─────────────────────────────────────── */
let searchDebounce = null;
$('share-search').addEventListener('input', () => {
  clearTimeout(searchDebounce);
  const q = $('share-search').value.trim();
  delete $('share-search').dataset.selectedUid;
  if (q.length < 2) { $('share-autocomplete').style.display = 'none'; return; }
  searchDebounce = setTimeout(async () => {
    const r = await fetch(`${BASE.replace('/manage', '')}/manage/users/search?q=` + encodeURIComponent(q), { headers: hdrs() });
    if (!r.ok) return;
    const users = await r.json();
    shareSearchResult = users;
    const ac = $('share-autocomplete');
    if (!users.length) { ac.style.display = 'none'; return; }
    ac.innerHTML = users.map((u, i) =>
      `<div class="share-ac-item" data-idx="${i}">
         ${esc(u.displayName)}<small>${esc(u.uid)}</small>
       </div>`
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
  if (!$('share-search-wrap').contains(e.target)) $('share-autocomplete').style.display = 'none';
});

init();
}());
</script>
