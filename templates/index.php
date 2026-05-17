<?php
declare(strict_types=1);
/** @var \OCP\IL10N $l */
?>
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
  --fh: var(--font-face, -apple-system, BlinkMacSystemFont, 'Segoe UI', sans-serif);
  --fb: var(--font-face, -apple-system, BlinkMacSystemFont, 'Segoe UI', sans-serif);
  --fm: ui-monospace, 'Cascadia Code', 'Fira Code', 'Consolas', 'Courier New', monospace;
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
        <button class="btn btn-primary" style="flex:1" id="btn-create-client">+ Client</button>
        <button class="btn btn-ghost"   style="flex:1" id="btn-create-seq">+ Sequence</button>
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
                <button class="evtb active" data-view="list">≡ List</button>
                <button class="evtb"        data-view="timeline">◈ Timeline</button>
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
      <button class="btn btn-primary btn-sm" style="margin-left:auto" id="share-add-btn">Add</button>
    </div>
    <div class="modal-actions" style="margin-top:10px">
      <button class="btn btn-ghost" id="share-close-btn">Close</button>
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
      <button class="btn btn-ghost"   id="mc-cancel">Cancel</button>
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
      <button class="btn btn-primary" id="token-done">Done</button>
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
      <button class="btn btn-ghost"   id="ms-cancel">Cancel</button>
      <button class="btn btn-primary" id="ms-save">Save</button>
    </div>
  </div>
</div>
