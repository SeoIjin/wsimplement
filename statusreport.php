<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="utf-8" />
<meta name="viewport" content="width=device-width,initial-scale=1" />
<title>Request Details - Status Report</title>
<link href="https://fonts.googleapis.com/css2?family=Montserrat:wght@600;700&family=Quicksand:wght@300;400;500;600&display=swap" rel="stylesheet">
<style>
  :root{
    --bg:#eaf6e9;
    --panel:#ffffff;
    --accent:#0f4b3f;
    --muted:#cfe6d3;
    --soft:#f3faf5;
    --shadow: 0 10px 30px rgba(16,40,28,0.06);
  }

  /* Page: lock viewport, keep a 50px outer margin, avoid page scroll */
  html,body{
    height:100vh;
    margin:0;
    background:var(--bg);
    font-family:"Quicksand", system-ui, Arial, sans-serif;
    -webkit-font-smoothing:antialiased;
    -moz-osx-font-smoothing:grayscale;
    color:#0b3229;
    overflow:hidden;
    box-sizing:border-box;
  }

  /* top bar */
  .topbar{
    height:72px;
    display:flex;
    align-items:center;
    gap:18px;
    padding:0 28px;
    background:linear-gradient(#fff,#fbfff9);
    box-shadow:0 2px 6px rgba(0,0,0,0.06);
  }
  .logo{ width:56px;height:56px;border-radius:50%;display:flex;align-items:center;justify-content:center;background:#fff;border:3px solid var(--muted);box-shadow:0 6px 18px rgba(0,0,0,0.04); }
  .logo img{ width:60px;height:60px;object-fit:cover;display:block; border-radius:50%;}
  .title{ font-family:"Montserrat",sans-serif; font-size:28px; margin:0; font-weight:700; }

  /* viewport wrap keeps 50px margins and contains the 3-column grid */
  .viewport-wrap{
    height: calc(100vh - 72px);
    padding:50px;
    box-sizing:border-box;
  }

  /* grid layout: left | main | right; columns stretch to fill height */
  .container{
    height:100%;
    width:100%;
    display:grid;
    grid-template-columns: 230px 1fr 340px;
    gap:28px;
    box-sizing:border-box;
    align-items:stretch;
    overflow:hidden;
  }

  /* panels: use flex column so internal content can stretch/scroll */
  .left-panel, .main, .right{
    background:var(--panel);
    border-radius:12px;
    box-shadow:var(--shadow);
    overflow:auto;
    -webkit-overflow-scrolling:touch;
    display:flex;
    flex-direction:column;
    min-height:0; /* important for internal scrolling in flex containers */
  }

  /* left panel */
  .left-panel{ padding:22px; border-left:10px solid var(--accent); }
  .profile-pic{ width:160px;height:160px;margin:6px auto 12px;border-radius:8px;overflow:hidden;box-shadow:0 6px 16px rgba(20,77,63,0.08);background:#fff;}
  .profile-pic img{ width:100%;height:100%;object-fit:cover;display:block; }
  .left-name{ text-align:center;font-weight:700;font-size:20px;margin:8px 0 4px; }
  .left-sub{ text-align:center;color:#6b7f75;font-size:13px;margin-bottom:12px; }
  .info-row{ font-size:13px;color:#183a33;margin:10px 0;padding:8px 0;border-top:1px solid #f0f6f2; }
  .info-key{ font-weight:700;color:#0d3e34;font-size:13px;margin-bottom:3px; }
  .info-val{ color:#556e67;font-size:13px;margin-bottom:6px; }
  .left-list{ margin-top:12px; display:flex; flex-direction:column; gap:10px; }

  /* main panel */
  .main{ padding:26px; gap:16px; flex:1; }
  .row{ display:flex; gap:18px; align-items:center; flex-wrap:wrap; }
  .label{ font-weight:700; color:var(--accent); font-size:16px; margin-bottom:6px; }
  .select, .input{ background:var(--soft); border:1px solid var(--muted); border-radius:10px; padding:10px 12px; font-size:15px; color:#0b3229; }
  .input{ min-width:420px; max-width:100%; box-sizing:border-box; }

  /* photos area (enlarged) */
  .photos{
    background:#f7fdf7;
    border-radius:10px;
    padding:20px;
    border:1px solid #e3f2e6;
  }
  .photos-title{
    font-weight:700;
    color:var(--accent);
    margin-bottom:14px;
    font-size:18px;
  }
  .thumbs{
    display:flex;
    gap:16px;
    align-items:center;
    flex-wrap:wrap;
  }
  .thumb{
    position:relative;
    width:120px;
    height:120px;
    border-radius:12px;
    background:#fff;
    box-shadow:0 8px 18px rgba(0,0,0,0.06);
    display:flex;
    align-items:center;
    justify-content:center;
    overflow:hidden;
    border:8px solid rgba(15,75,63,0.07);
  }
  .thumb img{ width:100%; height:100%; object-fit:cover; display:block; }
  .thumb-remove{
    position:absolute;
    top:8px;
    right:8px;
    width:26px;
    height:26px;
    border-radius:6px;
    background:rgba(0,0,0,0.6);
    color:#fff;
    display:flex;
    align-items:center;
    justify-content:center;
    font-size:16px;
    cursor:pointer;
    z-index:5;
    border:0;
  }
  /* use a label as the add control for maximum reliability */
  .thumb.add{
    background:linear-gradient(#e6f7eb,#dcede6);
    font-size:36px;
    color:var(--accent);
    font-weight:700;
    cursor:pointer;
    display:flex;
    align-items:center;
    justify-content:center;
    width:120px;
    height:120px;
    border-radius:12px;
    border:0;
    padding:0;
  }
  .photos-hint{ margin-top:10px; color:#5b776e; font-size:14px; }

  /* description textarea */
  textarea.desc-box{
    margin-top:16px;
    min-height:200px;
    max-height:480px;
    background:#efefef;
    border-radius:10px;
    padding:14px;
    color:#3b3b3b;
    font-size:15px;
    border:1px solid #e0e0e0;
    resize:vertical;
    width:100%;
    box-sizing:border-box;
    font-family:inherit;
    line-height:1.45;
  }

  /* hide native input off-screen (label[for] will reliably open file dialog) */
  .file-input{
    position:absolute;
    left:-9999px;
    width:1px;
    height:1px;
    overflow:hidden;
    opacity:0;
    pointer-events:auto;
  }

  /* right column (stacked cards) */
  .right{ padding:18px; gap:16px; }
  .card{ background:var(--panel); border-radius:10px; padding:14px; box-shadow:var(--shadow); border:1px solid #e7f1ea; }
  .timeline{ display:flex; flex-direction:column; gap:12px; }
  .timeline-plot{ display:flex; align-items:end; gap:8px; height:96px; }
  .bar{ width:28px; background:var(--accent); border-radius:4px; opacity:0.95; }
  .meta-row{ display:flex; justify-content:space-between; gap:8px; margin-top:8px; font-size:13px; color:#264d43; }
  .status-updates{ font-size:14px; color:#123f33; }
  .status-item{ display:flex; gap:10px; align-items:flex-start; margin-top:8px; }
  .help-box{ font-size:14px; color:#123f33; }
  .help-box a{ color:var(--accent); text-decoration:none; font-weight:700; }

  /* collapse to single column only on narrow screens */
  @media (max-width:820px){
    .viewport-wrap{ padding:28px; }
    .container{ grid-template-columns: 1fr; gap:18px; }
    .left-panel{ order:2; } 
    .main{ order:1; }
    .right{ order:3; }
  }
</style>
</head>
<body>
  <header class="topbar" role="banner">
    <div class="logo" aria-hidden>
      <img src="https://scontent.fmnl17-6.fna.fbcdn.net/v/t1.15752-9/553974384_4249775165290094_652812993016428145_n.jpg?stp=dst-jpg_s480x480_tt6&_nc_cat=109&ccb=1-7&_nc_sid=0024fc&_nc_eui2=AeG5aFAbTOMad6XgrxKo9IPVtxN5sJJSPgm3E3mwklI-CevhGq9pj3Cf9yZLvoAkhZp-jP9gIE8UhCSWHDzNkMPj&_nc_ohc=4661oLHITcUQ7kNvwHba9ix&_nc_oc=AdkMKRzRVkKxCZ_yKQdOwFfY2PxPlN7KzXQ3KrNTHgQH9OhelLMyyH3li2bRuCmkwtQ&_nc_ad=z-m&_nc_cid=0&_nc_zt=23&_nc_ht=scontent.fmnl17-6.fna&oh=03_Q7cD3gGf-fXtyMrUUUVT9i-XDOAwl095rggWQ7szzxBcsvBmpQ&oe=6923C669" alt="seal">
    </div>
    <h1 class="title">Request Details</h1>
  </header>

  <div class="viewport-wrap">
    <main class="container" role="main" aria-label="Request details layout">
      <!-- LEFT -->
      <aside class="left-panel" role="complementary" aria-label="Requester info">
        <div class="profile-pic" aria-hidden>
          <img src="" alt="avatar">
        </div>
        <div class="left-name">Person</div>
        <div class="left-sub">Submitted by</div>

        <div class="info-row">
          <div class="info-key">Ticket id</div>
          <div class="info-val">n/a</div>
        </div>

        <div class="info-row">
          <div class="info-key">Contact</div>
          <div class="info-val">09138918193</div>
        </div>

        <div class="left-list">
          <div>
            <div class="info-key">Locations</div>
            <div class="info-val">n/a</div>
          </div>

          <div>
            <div class="info-key">Request Type</div>
            <div class="info-val">n/a</div>
          </div>
        </div>
      </aside>

      <!-- MAIN -->
      <section class="main" role="region" aria-label="Main request info">
        <div style="display:flex;justify-content:space-between;align-items:center;gap:18px;flex-wrap:wrap;">
          <div style="flex:1; min-width:220px;">
            <div style="display:flex;gap:12px;align-items:center;flex-wrap:wrap;">
              <div style="min-width:180px;">
                <div class="label">Priority</div>
                <select class="select" aria-label="priority">
                  <option>level of priority</option>
                  <option>High</option>
                  <option>Medium</option>
                  <option>Low</option>
                </select>
              </div>

              <div style="min-width:240px;">
                <div class="label">Status</div>
                <select class="select" aria-label="status">
                  <option>Submitted</option>
                  <option>In progress</option>
                  <option>Completed</option>
                  <option>Rejected</option>
                </select>
              </div>
            </div>

            <div style="margin-top:14px;">
              <div class="label">Barangay</div>
              <input class="input" type="text" value="" aria-label="barangay">
            </div>
          </div>
        </div>

        <div style="height:12px"></div>

        <div class="photos" aria-label="photos">
          <div class="photos-title">Photos</div>

          <!-- thumbnails + add control (label opens file picker reliably) -->
          <div class="thumbs" id="thumbsContainer" aria-live="polite" aria-atomic="true">
            <label for="photoInput" class="thumb add" id="addThumb" title="Add photos" tabindex="0">+</label>
          </div>

          <div class="photos-hint">You can add up to 6 images. Drag & drop images here or click + to select files.</div>

          <!-- native file input (off-screen but reachable via label[for]) -->
          <input id="photoInput" class="file-input" type="file" accept="image/*" multiple>

          <!-- writable description -->
          <textarea id="description" name="description" class="desc-box" placeholder="Write a description about the photos or request..."></textarea>
        </div>
      </section>

      <!-- RIGHT -->
      <aside class="right" role="complementary" aria-label="side info">
        <div class="card timeline" aria-hidden>
          <div style="display:flex;justify-content:space-between;align-items:center;">
            <div style="font-weight:700;color:var(--accent);">Time line</div>
            <button style="background:#e9f8ef;border:1px solid #d7efe0;padding:8px 10px;border-radius:8px;font-weight:700;color:#154b3f;cursor:pointer">Calendar</button>
          </div>

          <div class="timeline-plot" aria-hidden>
            <!-- intentionally left blank -->
          </div>

          <div class="meta-row">
            <div>Submitted <strong>n/a</strong></div>
            <div>Last Updated <strong>n/a</strong></div>
          </div>

          <div style="margin-top:10px;">
            <div style="font-weight:700;color:#556e67">Total Reports</div>
            <div style="background:#f5f5f5;border-radius:6px;padding:8px;margin-top:6px;text-align:center;font-weight:700">0</div>
          </div>
        </div>

        <div class="card status-updates" aria-label="status updates">
          <div style="font-weight:700;color:var(--accent);margin-bottom:8px">Status Updates</div>
          <div style="color:#6b7f75;font-size:14px;margin-bottom:8px">Track the progress of your request</div>

          <div class="status-item">
            <div style="width:14px;height:14px;border-radius:50%;background:var(--accent);margin-top:6px"></div>
            <div>
              <div style="font-weight:700">Submitted</div>
              <div style="color:#6b7f75;font-size:13px;margin-top:6px">Your health request has been received and queued for review.</div>
              <div style="margin-top:8px;font-size:13px;color:#6b7f75">Updated by: System</div>
            </div>
          </div>
        </div>

        <div class="card help-box" aria-label="need help">
          <div style="font-weight:700;color:var(--accent);margin-bottom:8px">Need Help?</div>
          <div>If you have any questions about your request, you can:</div>
          <ul style="margin-top:10px;padding-left:18px;color:#4a6a60">
            <li>Visit the barangay office</li>
            <li>Call: <strong>211122</strong></li>
            <li>Email: <a href="mailto:Barangay211122@example.com">Barangay211122@example.com</a></li>
          </ul>
        </div>
      </aside>
    </main>
  </div>

<script>
  (function () {
    const MAX_FILES = 6;
    const input = document.getElementById('photoInput');
    const thumbs = document.getElementById('thumbsContainer');
    const addLabel = document.getElementById('addThumb');

    let files = [];

    function renderThumbs() {
      // remove existing non-add thumbnails
      thumbs.querySelectorAll('.thumb').forEach(el => {
        if (!el.classList.contains('add')) el.remove();
      });

      files.forEach((file, idx) => {
        const url = URL.createObjectURL(file);
        const el = document.createElement('div');
        el.className = 'thumb';
        el.setAttribute('data-index', idx);

        const img = document.createElement('img');
        img.src = url;
        img.alt = file.name;

        const remove = document.createElement('button');
        remove.type = 'button';
        remove.className = 'thumb-remove';
        remove.innerHTML = '×';
        remove.title = 'Remove photo';
        remove.addEventListener('click', () => removeFile(idx));

        el.appendChild(img);
        el.appendChild(remove);
        // insert before the add label
        thumbs.insertBefore(el, addLabel);
      });

      addLabel.style.display = (files.length >= MAX_FILES) ? 'none' : 'flex';
    }

    function removeFile(index) {
      // revoke object URL by clearing img src if desired (browsers reclaim automatically)
      files.splice(index, 1);
      renderThumbs();
    }

    function handleFiles(selectedFiles) {
      const arr = Array.from(selectedFiles).filter(f => f.type && f.type.startsWith('image/'));
      if (!arr.length) return;
      const capacity = MAX_FILES - files.length;
      const toAdd = arr.slice(0, capacity);
      files = files.concat(toAdd);
      renderThumbs();
    }

    // listen to native file input change
    input.addEventListener('change', (e) => {
      if (!e.target.files) return;
      handleFiles(e.target.files);
      input.value = '';
    });

    // accessibility: allow Enter/Space on the label to open file dialog for keyboard users
    addLabel.addEventListener('keydown', (e) => {
      if (e.key === 'Enter' || e.key === ' ') {
        e.preventDefault();
        // programmatic click on input is safe here because label already targets it
        input.click();
      }
    });

    // drag & drop support
    thumbs.addEventListener('dragover', (e) => { e.preventDefault(); thumbs.classList.add('drag-over'); });
    thumbs.addEventListener('dragleave', (e) => { thumbs.classList.remove('drag-over'); });
    thumbs.addEventListener('drop', (e) => {
      e.preventDefault();
      thumbs.classList.remove('drag-over');
      const dt = e.dataTransfer;
      if (!dt || !dt.files) return;
      handleFiles(dt.files);
    });

    // expose files for form submit handling
    window.getAttachedPhotos = () => files.slice();

    // initial render
    renderThumbs();
  })();
</script>
</body>
</html>