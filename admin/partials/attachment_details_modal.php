<?php
// ==============================================================================
// TRUENORTH CMS — WORDPRESS ATTACHMENT DETAILS MODAL
// Replicates the WordPress Media Library & Image SEO Attachment Details Dialog
// Supports Alternative Text, Title, Caption, Description, Dimensions & Real-Time Auto-Save
// ==============================================================================
?>

<style>
/* WORDPRESS ATTACHMENT DETAILS MODAL */
.wp-media-modal-backdrop {
  position: fixed;
  inset: 0;
  background: rgba(15, 23, 42, 0.88);
  backdrop-filter: blur(5px);
  z-index: 99999;
  display: none;
  align-items: center;
  justify-content: center;
  padding: 24px;
}
.wp-media-modal-backdrop.open {
  display: flex;
}
.wp-attachment-dialog {
  background: #1e293b;
  border: 1px solid #334155;
  border-radius: 14px;
  width: 100%;
  max-width: 1140px;
  height: 88vh;
  max-height: 820px;
  display: flex;
  flex-direction: column;
  overflow: hidden;
  box-shadow: 0 25px 60px -15px rgba(0, 0, 0, 0.7);
  animation: wpModalFadeIn 0.18s ease-out;
}
@keyframes wpModalFadeIn {
  from { opacity: 0; transform: scale(0.97); }
  to { opacity: 1; transform: scale(1); }
}

/* TOP HEADER */
.wp-attachment-header {
  padding: 14px 20px;
  background: #0f172a;
  border-bottom: 1px solid #334155;
  display: flex;
  align-items: center;
  justify-content: space-between;
}
.wp-attachment-title {
  font-size: 1.15rem;
  font-weight: 800;
  color: #f8fafc;
  display: flex;
  align-items: center;
  gap: 10px;
}
.wp-attachment-nav {
  display: flex;
  align-items: center;
  gap: 6px;
}
.wp-attachment-nav-btn {
  width: 32px;
  height: 32px;
  display: flex;
  align-items: center;
  justify-content: center;
  background: #1e293b;
  border: 1px solid #475569;
  color: #cbd5e1;
  border-radius: 6px;
  cursor: pointer;
  font-size: 0.9rem;
  transition: all 0.15s;
}
.wp-attachment-nav-btn:hover {
  background: #334155;
  color: #ffffff;
}
.wp-attachment-close-btn {
  width: 32px;
  height: 32px;
  display: flex;
  align-items: center;
  justify-content: center;
  background: transparent;
  border: none;
  color: #94a3b8;
  font-size: 1.4rem;
  line-height: 1;
  cursor: pointer;
  margin-left: 8px;
  border-radius: 6px;
  transition: color 0.15s;
}
.wp-attachment-close-btn:hover {
  color: #f87171;
  background: rgba(239, 68, 68, 0.1);
}

/* TWO-COLUMN BODY */
.wp-attachment-body {
  display: grid;
  grid-template-columns: minmax(0, 1.25fr) minmax(360px, 420px);
  flex: 1;
  overflow: hidden;
  height: calc(100% - 60px);
}
@media (max-width: 900px) {
  .wp-attachment-body {
    grid-template-columns: 1fr;
    overflow-y: auto;
  }
}

/* LEFT: MEDIA PREVIEW */
.wp-attachment-preview-pane {
  background: #090e1a;
  display: flex;
  flex-direction: column;
  align-items: center;
  justify-content: center;
  padding: 30px;
  border-right: 1px solid #334155;
  position: relative;
  overflow: hidden;
}
.wp-attachment-image-wrap {
  max-width: 100%;
  max-height: 520px;
  display: flex;
  align-items: center;
  justify-content: center;
  overflow: hidden;
  margin-bottom: 20px;
}
.wp-attachment-image-wrap img {
  max-width: 100%;
  max-height: 480px;
  object-fit: contain;
  border-radius: 6px;
  box-shadow: 0 10px 25px rgba(0,0,0,0.5);
}
.wp-attachment-edit-img-btn {
  display: inline-flex;
  align-items: center;
  gap: 8px;
  padding: 8px 18px;
  background: #1e293b;
  border: 1px solid #475569;
  border-radius: 6px;
  color: #38bdf8;
  font-size: 0.84rem;
  font-weight: 700;
  cursor: pointer;
  transition: all 0.15s;
}
.wp-attachment-edit-img-btn:hover {
  background: #334155;
  color: #fff;
  border-color: #38bdf8;
}

/* RIGHT: METADATA & SEO INPUTS */
.wp-attachment-info-pane {
  background: #1e293b;
  display: flex;
  flex-direction: column;
  overflow: hidden;
}
.wp-attachment-info-scroll {
  padding: 22px 24px;
  overflow-y: auto;
  flex: 1;
}
.wp-attachment-meta-block {
  font-size: 0.8rem;
  color: #94a3b8;
  line-height: 1.6;
  border-bottom: 1px solid #334155;
  padding-bottom: 14px;
  margin-bottom: 18px;
}
.wp-attachment-meta-row {
  display: flex;
  gap: 6px;
  margin-bottom: 3px;
  word-break: break-word;
}
.wp-attachment-meta-label {
  font-weight: 700;
  color: #cbd5e1;
  min-width: 95px;
}
.wp-attachment-meta-value {
  color: #94a3b8;
}
.wp-attachment-meta-value a {
  color: #38bdf8;
  text-decoration: none;
}
.wp-attachment-meta-value a:hover {
  text-decoration: underline;
}

/* FORM FIELDS */
.wp-attachment-field-group {
  margin-bottom: 16px;
}
.wp-attachment-field-label {
  display: block;
  font-size: 0.82rem;
  font-weight: 700;
  color: #cbd5e1;
  margin-bottom: 6px;
}
.wp-attachment-help-text {
  font-size: 0.74rem;
  color: #94a3b8;
  margin-top: 5px;
  line-height: 1.4;
}
.wp-attachment-help-text a {
  color: #38bdf8;
  text-decoration: none;
}
.wp-attachment-input, .wp-attachment-textarea {
  width: 100%;
  box-sizing: border-box;
  background: #0f172a;
  border: 1px solid #475569;
  border-radius: 6px;
  padding: 8px 12px;
  color: #f8fafc;
  font-size: 0.86rem;
  font-family: inherit;
  transition: border-color 0.15s;
}
.wp-attachment-input:focus, .wp-attachment-textarea:focus {
  outline: none;
  border-color: #38bdf8;
  box-shadow: 0 0 0 2px rgba(56, 189, 248, 0.2);
}
.wp-attachment-copy-btn {
  margin-top: 6px;
  padding: 6px 14px;
  background: #0f172a;
  border: 1px solid #475569;
  border-radius: 6px;
  color: #38bdf8;
  font-size: 0.78rem;
  font-weight: 700;
  cursor: pointer;
  display: inline-flex;
  align-items: center;
  gap: 6px;
}
.wp-attachment-copy-btn:hover {
  background: #334155;
  color: #fff;
}

/* FOOTER ACTIONS */
.wp-attachment-footer {
  border-top: 1px solid #334155;
  padding: 12px 20px;
  background: #0f172a;
  display: flex;
  align-items: center;
  justify-content: space-between;
  flex-wrap: wrap;
  gap: 10px;
}
.wp-attachment-footer-links {
  font-size: 0.78rem;
  color: #64748b;
  display: flex;
  gap: 8px;
  align-items: center;
  flex-wrap: wrap;
}
.wp-attachment-footer-links a, .wp-attachment-footer-links button {
  color: #38bdf8;
  text-decoration: none;
  background: transparent;
  border: none;
  padding: 0;
  font-size: 0.78rem;
  font-weight: 600;
  cursor: pointer;
}
.wp-attachment-footer-links a:hover {
  text-decoration: underline;
}
.wp-attachment-danger-btn {
  color: #f87171 !important;
}
.wp-attachment-danger-btn:hover {
  text-decoration: underline;
}
.wp-attachment-status-indicator {
  font-size: 0.78rem;
  font-weight: 700;
  color: #34d399;
  display: inline-flex;
  align-items: center;
  gap: 4px;
  opacity: 0;
  transition: opacity 0.25s;
}
.wp-attachment-status-indicator.visible {
  opacity: 1;
}
</style>

<!-- MODAL BACKDROP -->
<div class="wp-media-modal-backdrop" id="wpAttachmentModal" onclick="handleWpModalBackdropClick(event)">
  <div class="wp-attachment-dialog" role="dialog" aria-modal="true" aria-labelledby="wpModalTitle">
    <input type="hidden" id="wpatt-csrf-token" value="<?= htmlspecialchars($_SESSION['admin_csrf_token'] ?? (function_exists('generateCsrfToken') ? generateCsrfToken() : '')) ?>" />
    
    <!-- HEADER -->
    <div class="wp-attachment-header">
      <div class="wp-attachment-title" id="wpModalTitle">
        <span>Attachment details</span>
      </div>
      <div class="wp-attachment-nav">
        <button type="button" class="wp-attachment-nav-btn" onclick="navigateAttachment(-1)" title="Previous image (Left Arrow)">&larr;</button>
        <button type="button" class="wp-attachment-nav-btn" onclick="navigateAttachment(1)" title="Next image (Right Arrow)">&rarr;</button>
        <button type="button" class="wp-attachment-close-btn" onclick="closeAttachmentModal()" title="Close (Escape)">&times;</button>
      </div>
    </div>

    <!-- BODY -->
    <div class="wp-attachment-body">
      
      <!-- LEFT PREVIEW -->
      <div class="wp-attachment-preview-pane">
        <div class="wp-attachment-image-wrap">
          <img id="wpatt-preview-img" src="/hero_bottles_banner.webp" alt="Attachment Preview" onerror="this.src='/logo_svg.svg';" />
        </div>
        <button type="button" class="wp-attachment-edit-img-btn" onclick="openFullMediaPreview()">
          <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><path d="M11 4H4a2 2 0 0 0-2 2v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2v-7"/><path d="M18.5 2.5a2.121 2.121 0 0 1 3 3L12 15l-4 1 1-4 9.5-9.5z"/></svg>
          <span>Edit Image</span>
        </button>
      </div>

      <!-- RIGHT INFO & SEO FORM -->
      <div class="wp-attachment-info-pane">
        
        <div class="wp-attachment-info-scroll">
          <!-- METADATA BLOCK -->
          <div class="wp-attachment-meta-block">
            <div class="wp-attachment-meta-row">
              <span class="wp-attachment-meta-label">Uploaded by:</span>
              <span class="wp-attachment-meta-value" id="wpatt-uploaded-by">admin</span>
            </div>
            <div class="wp-attachment-meta-row">
              <span class="wp-attachment-meta-label">Uploaded to:</span>
              <span class="wp-attachment-meta-value">
                <a id="wpatt-uploaded-to-link" href="#" target="_blank" rel="noopener">Unattached</a>
              </span>
            </div>
            <div class="wp-attachment-meta-row">
              <span class="wp-attachment-meta-label">File name:</span>
              <span class="wp-attachment-meta-value" id="wpatt-filename">image.webp</span>
            </div>
            <div class="wp-attachment-meta-row">
              <span class="wp-attachment-meta-label">File type:</span>
              <span class="wp-attachment-meta-value" id="wpatt-filetype">image/webp</span>
            </div>
            <div class="wp-attachment-meta-row">
              <span class="wp-attachment-meta-label">File size:</span>
              <span class="wp-attachment-meta-value" id="wpatt-filesize">32 KB</span>
            </div>
            <div class="wp-attachment-meta-row">
              <span class="wp-attachment-meta-label">Dimensions:</span>
              <span class="wp-attachment-meta-value" id="wpatt-dimensions">800 by 800 pixels</span>
            </div>
          </div>

          <!-- FORM: ALTERNATIVE TEXT -->
          <div class="wp-attachment-field-group">
            <label for="wpatt-alt-input" class="wp-attachment-field-label">Alternative Text</label>
            <textarea id="wpatt-alt-input" rows="2" class="wp-attachment-textarea" placeholder="Descriptive image alternative text..." oninput="queueAttachmentSave()"></textarea>
            <div class="wp-attachment-help-text">
              <a href="https://www.w3.org/WAI/tutorials/images/decision-tree/" target="_blank" rel="noopener">Learn how to describe the purpose of the image &nearr;</a><br/>
              Leave empty if the image is purely decorative.
            </div>
          </div>

          <!-- FORM: TITLE -->
          <div class="wp-attachment-field-group">
            <label for="wpatt-title-input" class="wp-attachment-field-label">Title</label>
            <input type="text" id="wpatt-title-input" class="wp-attachment-input" placeholder="Image Title" oninput="queueAttachmentSave()" />
          </div>

          <!-- FORM: CAPTION -->
          <div class="wp-attachment-field-group">
            <label for="wpatt-caption-input" class="wp-attachment-field-label">Image Caption</label>
            <textarea id="wpatt-caption-input" rows="2" class="wp-attachment-textarea" placeholder="Optional caption to display under image..." oninput="queueAttachmentSave()"></textarea>
          </div>

          <!-- FORM: DESCRIPTION -->
          <div class="wp-attachment-field-group">
            <label for="wpatt-desc-input" class="wp-attachment-field-label">Description</label>
            <textarea id="wpatt-desc-input" rows="3" class="wp-attachment-textarea" placeholder="Detailed contextual description for Google Image indexing..." oninput="queueAttachmentSave()"></textarea>
          </div>

          <!-- FORM: FILE URL -->
          <div class="wp-attachment-field-group">
            <label for="wpatt-url-input" class="wp-attachment-field-label">File URL:</label>
            <input type="text" id="wpatt-url-input" readonly class="wp-attachment-input" style="color: #94a3b8; font-family: monospace; font-size: 0.8rem; background: #0b1120;" />
            <button type="button" class="wp-attachment-copy-btn" id="wpattCopyUrlBtn" onclick="copyAttachmentUrl()">
              <svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><rect x="9" y="9" width="13" height="13" rx="2" ry="2"/><path d="M5 15H4a2 2 0 0 1-2-2V4a2 2 0 0 1 2-2h9a2 2 0 0 1 2 2v1"/></svg>
              <span>Copy URL to clipboard</span>
            </button>
          </div>
        </div>

        <!-- FOOTER LINKS & LIVE SAVE BADGE -->
        <div class="wp-attachment-footer">
          <div class="wp-attachment-footer-links">
            <a id="wpatt-link-view" href="#" target="_blank" rel="noopener">View media file</a>
            <span>|</span>
            <a id="wpatt-link-edit-more" href="/admin/media" target="_blank">Edit more details</a>
            <span>|</span>
            <a id="wpatt-link-download" href="#" download>Download file</a>
            <span>|</span>
            <button type="button" class="wp-attachment-danger-btn" onclick="deleteCurrentAttachmentItem()">Delete permanently</button>
          </div>

          <div style="display: flex; align-items: center; gap: 8px;">
            <span class="wp-attachment-status-indicator" id="wpattStatusIndicator">
              <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><polyline points="20 6 9 17 4 12"/></svg>
              <span>Saved.</span>
            </span>
            <button type="button" onclick="saveAttachmentImmediately()" style="background: #0284c7; color: #fff; border: none; padding: 6px 14px; border-radius: 6px; font-size: 0.78rem; font-weight: 700; cursor: pointer;">
              Save
            </button>
          </div>
        </div>

      </div>

    </div>

  </div>
</div>

<script>
// ==============================================================================
// WORDPRESS ATTACHMENT MODAL CONTROLLER
// ==============================================================================
let wpAttachmentList = [];
let currentAttachmentIndex = 0;
let attachmentSaveTimer = null;

function setAttachmentDataset(list) {
  wpAttachmentList = Array.isArray(list) ? list : [];
}

function openAttachmentModalByIndex(index) {
  if (!wpAttachmentList || !wpAttachmentList.length) return;
  if (index < 0) index = 0;
  if (index >= wpAttachmentList.length) index = wpAttachmentList.length - 1;
  currentAttachmentIndex = index;
  renderAttachmentDetails(wpAttachmentList[index]);
  const modal = document.getElementById('wpAttachmentModal');
  if (modal) modal.classList.add('open');
  document.body.style.overflow = 'hidden';
}

function openAttachmentModalByUrl(url) {
  if (!wpAttachmentList || !wpAttachmentList.length) return;
  const idx = wpAttachmentList.findIndex(item => item.url === url || item.image_url === url || item.file_name === url);
  if (idx !== -1) {
    openAttachmentModalByIndex(idx);
  } else {
    // If not in preloaded list, fetch single record
    fetchSingleAttachment(url);
  }
}

function fetchSingleAttachment(url) {
  fetch('/admin/seo_images?action=get_single_json&url=' + encodeURIComponent(url))
    .then(r => r.json())
    .then(data => {
      if (data && data.item) {
        wpAttachmentList = [data.item];
        currentAttachmentIndex = 0;
        renderAttachmentDetails(data.item);
        const modal = document.getElementById('wpAttachmentModal');
        if (modal) modal.classList.add('open');
        document.body.style.overflow = 'hidden';
      }
    })
    .catch(() => {});
}

function renderAttachmentDetails(item) {
  if (!item) return;
  const fullUrl = item.url || item.image_url || '';
  const domain = window.location.origin;
  const absoluteUrl = fullUrl.startsWith('http') ? fullUrl : (domain + (fullUrl.startsWith('/') ? '' : '/') + fullUrl);

  const prevImg = document.getElementById('wpatt-preview-img');
  if (prevImg) prevImg.src = fullUrl;

  const upBy = document.getElementById('wpatt-uploaded-by');
  if (upBy) upBy.textContent = item.uploaded_by || 'admin';

  const upToLink = document.getElementById('wpatt-uploaded-to-link');
  if (upToLink) {
    const upToText = item.uploaded_to || item.product_name || 'Unattached';
    upToLink.textContent = upToText;
    upToLink.href = item.uploaded_to_url || '#';
    upToLink.style.display = item.uploaded_to_url ? 'inline' : 'inline-block';
  }

  const fName = document.getElementById('wpatt-filename');
  if (fName) fName.textContent = item.file_name || (fullUrl ? fullUrl.split('/').pop() : '');

  const fType = document.getElementById('wpatt-filetype');
  if (fType) fType.textContent = item.file_type || ('image/' + (fullUrl.split('.').pop() || 'webp'));

  const fSize = document.getElementById('wpatt-filesize');
  if (fSize) fSize.textContent = item.file_size || '32 KB';

  const dims = document.getElementById('wpatt-dimensions');
  if (dims) dims.textContent = item.dimensions || '800 by 800 pixels';

  const altInput = document.getElementById('wpatt-alt-input');
  if (altInput) altInput.value = item.alt_text || '';

  const titleInput = document.getElementById('wpatt-title-input');
  if (titleInput) titleInput.value = item.title_text || item.title || '';

  const capInput = document.getElementById('wpatt-caption-input');
  if (capInput) capInput.value = item.caption || '';

  const descInput = document.getElementById('wpatt-desc-input');
  if (descInput) descInput.value = item.description || '';

  const urlInput = document.getElementById('wpatt-url-input');
  if (urlInput) urlInput.value = absoluteUrl;

  const viewLink = document.getElementById('wpatt-link-view');
  if (viewLink) viewLink.href = fullUrl;

  const downLink = document.getElementById('wpatt-link-download');
  if (downLink) {
    downLink.href = fullUrl;
    downLink.setAttribute('download', fName ? fName.textContent : 'image');
  }

  hideSaveIndicator();
}

function navigateAttachment(direction) {
  if (!wpAttachmentList || wpAttachmentList.length <= 1) return;
  saveAttachmentImmediately();
  currentAttachmentIndex += direction;
  if (currentAttachmentIndex < 0) currentAttachmentIndex = wpAttachmentList.length - 1;
  if (currentAttachmentIndex >= wpAttachmentList.length) currentAttachmentIndex = 0;
  renderAttachmentDetails(wpAttachmentList[currentAttachmentIndex]);
}

function closeAttachmentModal() {
  saveAttachmentImmediately();
  const modal = document.getElementById('wpAttachmentModal');
  if (modal) modal.classList.remove('open');
  document.body.style.overflow = '';
}

function handleWpModalBackdropClick(e) {
  if (e.target && e.target.id === 'wpAttachmentModal') {
    closeAttachmentModal();
  }
}

function openFullMediaPreview() {
  const img = document.getElementById('wpatt-preview-img');
  if (img && img.src) window.open(img.src, '_blank');
}

function copyAttachmentUrl() {
  const urlInput = document.getElementById('wpatt-url-input');
  if (!urlInput) return;
  urlInput.select();
  urlInput.setSelectionRange(0, 99999);
  navigator.clipboard.writeText(urlInput.value).then(() => {
    const btn = document.getElementById('wpattCopyUrlBtn');
    if (btn) {
      const orig = btn.innerHTML;
      btn.innerHTML = '<span>Copied!</span>';
      setTimeout(() => { btn.innerHTML = orig; }, 1800);
    }
  });
}

function queueAttachmentSave() {
  showSavingIndicator();
  if (attachmentSaveTimer) clearTimeout(attachmentSaveTimer);
  attachmentSaveTimer = setTimeout(saveAttachmentImmediately, 600);
}

function saveAttachmentImmediately() {
  if (attachmentSaveTimer) {
    clearTimeout(attachmentSaveTimer);
    attachmentSaveTimer = null;
  }
  const item = wpAttachmentList[currentAttachmentIndex];
  if (!item) return;

  const fullUrl = item.url || item.image_url || '';
  if (!fullUrl) return;

  const alt = document.getElementById('wpatt-alt-input')?.value || '';
  const title = document.getElementById('wpatt-title-input')?.value || '';
  const caption = document.getElementById('wpatt-caption-input')?.value || '';
  const desc = document.getElementById('wpatt-desc-input')?.value || '';

  // Update in local memory list
  item.alt_text = alt;
  item.title_text = title;
  item.caption = caption;
  item.description = desc;

  const formData = new FormData();
  formData.append('url', fullUrl);
  formData.append('alt_text', alt);
  formData.append('title_text', title);
  formData.append('caption', caption);
  formData.append('description', desc);
  const csrf = document.getElementById('wpatt-csrf-token')?.value || '';
  formData.append('csrf_token', csrf);

  fetch('/admin/save_image_seo', {
    method: 'POST',
    body: formData
  })
  .then(r => r.json())
  .then(res => {
    if (res && res.success) {
      showSavedIndicator();
      // If table row exists on page, update it
      updateTableRowAlt(fullUrl, alt, title);
    }
  })
  .catch(() => {});
}

function updateTableRowAlt(url, alt, title) {
  const row = document.querySelector(`tr[data-img-url="${url}"]`);
  if (row) {
    const altCell = row.querySelector('.tbl-alt-display');
    if (altCell) altCell.textContent = alt || '—';
    const statusPill = row.querySelector('.tbl-status-pill');
    if (statusPill) {
      if (alt.trim()) {
        statusPill.innerHTML = '<span style="width:7px;height:7px;border-radius:50%;background:#22c55e;"></span><span style="color:#86efac;">Optimized</span>';
      } else {
        statusPill.innerHTML = '<span style="width:7px;height:7px;border-radius:50%;background:#ef4444;"></span><span style="color:#fca5a5;">Missing Alt</span>';
      }
    }
  }
  const card = document.querySelector(`.img-card[data-img-url="${url}"]`);
  if (card) {
    const badge = card.querySelector('.card-seo-badge');
    if (badge) {
      if (alt.trim()) {
        badge.innerHTML = '<span style="width:6px;height:6px;border-radius:50%;background:#22c55e;"></span><span>Alt Ready</span>';
        badge.style.color = '#86efac';
        badge.style.borderColor = 'rgba(34,197,94,0.4)';
      } else {
        badge.innerHTML = '<span style="width:6px;height:6px;border-radius:50%;background:#ef4444;"></span><span>Missing Alt</span>';
        badge.style.color = '#fca5a5';
        badge.style.borderColor = 'rgba(239,68,68,0.4)';
      }
    }
  }
}

function showSavingIndicator() {
  const ind = document.getElementById('wpattStatusIndicator');
  if (ind) {
    ind.innerHTML = '<span style="color:#cbd5e1;">Saving...</span>';
    ind.classList.add('visible');
  }
}

function showSavedIndicator() {
  const ind = document.getElementById('wpattStatusIndicator');
  if (ind) {
    ind.innerHTML = '<svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><polyline points="20 6 9 17 4 12"/></svg><span>Saved.</span>';
    ind.style.color = '#34d399';
    ind.classList.add('visible');
    setTimeout(() => { ind.classList.remove('visible'); }, 2200);
  }
}

function hideSaveIndicator() {
  const ind = document.getElementById('wpattStatusIndicator');
  if (ind) ind.classList.remove('visible');
}

function deleteCurrentAttachmentItem() {
  const item = wpAttachmentList[currentAttachmentIndex];
  if (!item) return;
  const fileName = item.file_name || item.url;
  if (!confirm(`Are you sure you want to permanently delete '${fileName}'? This cannot be undone.`)) {
    return;
  }
  const csrf = document.getElementById('wpatt-csrf-token')?.value || '';
  const urlToDel = item.url || item.image_url;
  
  if (window.location.pathname.indexOf('/admin/media') !== -1) {
    const form = document.createElement('form');
    form.method = 'POST';
    form.action = '/admin/media';
    const actInp = document.createElement('input'); actInp.type = 'hidden'; actInp.name = 'action'; actInp.value = 'delete_media'; form.appendChild(actInp);
    const csrfInp = document.createElement('input'); csrfInp.type = 'hidden'; csrfInp.name = 'csrf_token'; csrfInp.value = csrf; form.appendChild(csrfInp);
    const fnInp = document.createElement('input'); fnInp.type = 'hidden'; fnInp.name = 'filename'; fnInp.value = urlToDel; form.appendChild(fnInp);
    document.body.appendChild(form);
    form.submit();
  } else {
    // delete_img is a state-changing GET guarded by the global CSRF check, so
    // carry the token in the URL (this direct navigation bypasses the header's
    // auto-tokenising <a> click interceptor).
    window.location.href = '/admin/seo_images?action=delete_img&url=' + encodeURIComponent(urlToDel) + '&csrf_token=' + encodeURIComponent(csrf);
  }
}

// Keyboard navigation
document.addEventListener('keydown', function(e) {
  const modal = document.getElementById('wpAttachmentModal');
  if (!modal || !modal.classList.contains('open')) return;

  if (e.key === 'Escape') {
    closeAttachmentModal();
  } else if (e.key === 'ArrowLeft' && e.target.tagName !== 'INPUT' && e.target.tagName !== 'TEXTAREA') {
    navigateAttachment(-1);
  } else if (e.key === 'ArrowRight' && e.target.tagName !== 'INPUT' && e.target.tagName !== 'TEXTAREA') {
    navigateAttachment(1);
  }
});
</script>

