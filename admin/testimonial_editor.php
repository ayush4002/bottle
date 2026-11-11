<?php
require_once __DIR__ . '/header.php';
require_once dirname(__DIR__) . '/app/Models/TestimonialModel.php';

checkAdminAuth();
TestimonialModel::initStorage();

$id = $_GET['id'] ?? null;
$entry = $id ? TestimonialModel::getById($id) : null;
$err = $_GET['err'] ?? '';
$isLogo = !empty($entry['is_logo']);

function tf($key, $default = '') {
    global $entry;
    return htmlspecialchars($entry[$key] ?? $default, ENT_QUOTES);
}
?>

<div style="max-width: 900px; margin: 0 auto;">
  <div class="dash-header" style="margin-bottom: 24px;">
    <h1 style="font-size: 1.8rem; font-weight: 800; margin: 0 0 6px 0; color: #f8fafc;"><?= $id ? 'Edit' : 'Add New' ?> Testimonial / Logo</h1>
    <p style="color: #94a3b8; font-size: 0.9rem; margin: 0;">Toggle between a client quote and a client-logo tile. Both render on the home page social-proof section.</p>
  </div>

  <?php if (!empty($err)): ?>
    <div style="background: rgba(239, 68, 68, 0.15); border: 1px solid rgba(239, 68, 68, 0.4); color: #fca5a5; padding: 14px 18px; border-radius: 8px; font-size: 0.9rem; margin-bottom: 24px;"><?= htmlspecialchars($err) ?></div>
  <?php endif; ?>

  <form method="POST" action="/admin/save_testimonial" enctype="multipart/form-data" style="background: #1e293b; border: 1px solid #334155; border-radius: 14px; padding: 24px;">
    <input type="hidden" name="csrf_token" value="<?= $csrfToken ?>" />
    <input type="hidden" name="id" value="<?= (int)($id ?? 0) ?>" />
    <input type="hidden" name="prev_is_logo" value="<?= $isLogo ? 1 : 0 ?>" />

    <!-- Entry type toggle -->
    <label style="display: block; margin-bottom: 18px;">
      <span style="display: block; font-size: 0.78rem; font-weight: 700; color: #94a3b8; text-transform: uppercase; letter-spacing: 0.05em; margin-bottom: 8px;">Entry type</span>
      <select name="type" id="ttype" style="width: 100%; box-sizing: border-box; padding: 10px 14px; background: #0f172a; border: 1px solid #475569; border-radius: 8px; color: #fff; font-size: 0.9rem;">
        <option value="quote" <?= !$isLogo ? 'selected' : '' ?>>Testimonial / Client Quote</option>
        <option value="logo" <?= $isLogo ? 'selected' : '' ?>>Client Logo Tile</option>
      </select>
    </label>

    <!-- Quote fields -->
    <div id="quote-fields" style="display: <?= $isLogo ? 'none' : 'block' ?>; margin-bottom: 18px;">
      <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 14px; margin-bottom: 14px;">
        <label style="display: block;"><span style="font-size: 0.78rem; font-weight: 700; color: #94a3b8; text-transform: uppercase;">Client Name *</span>
          <input type="text" name="client_name" value="<?= tf('client_name') ?>" style="width: 100%; box-sizing: border-box; padding: 10px 14px; background: #0f172a; border: 1px solid #475569; border-radius: 8px; color: #fff; font-size: 0.9rem;" />
        </label>
        <label style="display: block;"><span style="font-size: 0.78rem; font-weight: 700; color: #94a3b8; text-transform: uppercase;">Company *</span>
          <input type="text" name="company" value="<?= tf('company') ?>" style="width: 100%; box-sizing: border-box; padding: 10px 14px; background: #0f172a; border: 1px solid #475569; border-radius: 8px; color: #fff; font-size: 0.9rem;" />
        </label>
      </div>
      <div style="display: grid; grid-template-columns: 1fr 120px; gap: 14px; margin-bottom: 14px;">
        <label style="display: block;"><span style="font-size: 0.78rem; font-weight: 700; color: #94a3b8; text-transform: uppercase;">Role / Department</span>
          <input type="text" name="role" value="<?= tf('role') ?>" placeholder="Procurement, Operations..." style="width: 100%; box-sizing: border-box; padding: 10px 14px; background: #0f172a; border: 1px solid #475569; border-radius: 8px; color: #fff; font-size: 0.9rem;" />
        </label>
        <label style="display: block;"><span style="font-size: 0.78rem; font-weight: 700; color: #94a3b8; text-transform: uppercase;">Rating (0-5)</span>
          <input type="number" name="rating" min="0" max="5" value="<?= (int)($entry['rating'] ?? 5) ?>" style="width: 100%; box-sizing: border-box; padding: 10px 14px; background: #0f172a; border: 1px solid #475569; border-radius: 8px; color: #fff; font-size: 0.9rem;" />
        </label>
      </div>
      <label style="display: block; margin-bottom: 14px;"><span style="font-size: 0.78rem; font-weight: 700; color: #94a3b8; text-transform: uppercase;">Quote / Testimonial Text</span>
        <textarea name="quote" rows="4" placeholder="A specific, verifiable endorsement. Avoid exaggerated claims." style="width: 100%; box-sizing: border-box; padding: 12px 14px; background: #0f172a; border: 1px solid #475569; border-radius: 8px; color: #fff; font-size: 0.9rem; font-family: inherit; resize: vertical;"><?= tf('quote') ?></textarea>
      </label>
    </div>

    <!-- Logo fields -->
    <div id="logo-fields" style="display: <?= $isLogo ? 'block' : 'none' ?>; margin-bottom: 18px;">
      <label style="display: block; margin-bottom: 14px;"><span style="font-size: 0.78rem; font-weight: 700; color: #94a3b8; text-transform: uppercase;">Logo / Tile Label</span>
        <input type="text" name="company" value="<?= tf('company') ?>" placeholder="e.g. Pharma, Cosmetics" style="width: 100%; box-sizing: border-box; padding: 10px 14px; background: #0f172a; border: 1px solid #475569; border-radius: 8px; color: #fff; font-size: 0.9rem;" />
      </label>
      <label style="display: block; margin-bottom: 14px;"><span style="font-size: 0.78rem; font-weight: 700; color: #94a3b8; text-transform: uppercase;">Logo Image URL</span>
        <input type="text" name="logo_image" value="<?= tf('logo_image') ?>" placeholder="/uploads/... or existing asset path" style="width: 100%; box-sizing: border-box; padding: 10px 14px; background: #0f172a; border: 1px solid #475569; border-radius: 8px; color: #fff; font-size: 0.9rem;" />
      </label>
      <label style="display: block;"><span style="font-size: 0.78rem; font-weight: 700; color: #94a3b8; text-transform: uppercase;">Upload Logo Image</span>
        <input type="file" name="logo_image_file" accept="image/*" style="width: 100%; padding: 8px; background: #0f172a; border: 1px dashed #475569; border-radius: 8px; color: #cbd5e1; font-size: 0.85rem;" />
      </label>
    </div>

    <!-- Shared -->
    <div style="display: grid; grid-template-columns: 160px 160px; gap: 14px; margin-bottom: 18px;">
      <label style="display: block;"><span style="font-size: 0.78rem; font-weight: 700; color: #94a3b8; text-transform: uppercase;">Status</span>
        <select name="status" style="width: 100%; box-sizing: border-box; padding: 10px 14px; background: #0f172a; border: 1px solid #475569; border-radius: 8px; color: #fff; font-size: 0.9rem;">
          <option value="published" <?= in_array($entry['status'] ?? 'published', ['published']) ? 'selected' : '' ?>>Published</option>
          <option value="draft" <?= ($entry['status'] ?? '') === 'draft' ? 'selected' : '' ?>>Draft</option>
        </select>
      </label>
      <label style="display: block;"><span style="font-size: 0.78rem; font-weight: 700; color: #94a3b8; text-transform: uppercase;">Sort Order</span>
        <input type="number" name="sort_order" value="<?= (int)($entry['sort_order'] ?? 0) ?>" style="width: 100%; box-sizing: border-box; padding: 10px 14px; background: #0f172a; border: 1px solid #475569; border-radius: 8px; color: #fff; font-size: 0.9rem;" />
      </label>
    </div>

    <div style="display: flex; gap: 12px; align-items: center;">
      <button type="submit" class="tn-btn-gold" style="padding: 12px 24px; border-radius: 8px; font-weight: 700; text-decoration: none; display: inline-block;">Save Entry</button>
      <a href="/admin/testimonials" style="color: #94a3b8; text-decoration: none; font-weight: 600; font-size: 0.9rem;">Cancel</a>
    </div>
  </form>
</div>

<script>
(function () {
  var sel = document.getElementById('ttype');
  var qf = document.getElementById('quote-fields');
  var lf = document.getElementById('logo-fields');
  function sync() {
    var isLogo = sel.value === 'logo';
    qf.style.display = isLogo ? 'none' : 'block';
    lf.style.display = isLogo ? 'block' : 'none';
  }
  if (sel) sel.addEventListener('change', sync);
})();
</script>

<?php require_once __DIR__ . '/footer.php'; ?>
