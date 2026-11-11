<?php
require_once dirname(__DIR__) . '/includes/upload_guard.php';
require_once __DIR__ . '/header.php';
require_once dirname(__DIR__) . '/app/Models/SeoModel.php';

checkAdminAuth();
SeoModel::init();

$msg = '';
$err = '';
$adminRole = $_SESSION['admin_role'] ?? 'Editor';
$adminUser = $_SESSION['admin_user'] ?? 'admin';

// Handle POST Save
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!verifyCsrfToken($_POST['csrf_token'] ?? '')) {
        $err = "Invalid security token. Please refresh and try again.";
    } else {
        $fields = [
            'site_name', 'title_suffix', 'default_meta_description', 'twitter_handle',
            'preferred_domain', 'google_verification', 'bing_verification', 'ga4_id',
            'gtm_id', 'facebook_pixel_id', 'org_name', 'org_legal_name', 'org_phone',
            'org_email', 'org_address_street', 'org_address_locality', 'org_address_region',
            'org_address_postal', 'org_address_country', 'org_social_linkedin',
            'org_social_facebook', 'org_social_twitter', 'org_social_instagram',
            'currency', 'language'
        ];

        $saveData = [];
        foreach ($fields as $f) {
            // Only update fields actually submitted, so a partial POST can never wipe settings
            if (array_key_exists($f, $_POST) && is_scalar($_POST[$f])) {
                $saveData[$f] = trim((string)$_POST[$f]);
            }
        }

        // Handle Image Uploads (OG Image, Logo, Favicon)
        $uploadDir = dirname(__DIR__) . '/public/uploads/';
        if (!is_dir($uploadDir)) @mkdir($uploadDir, 0755, true);

        if (!empty($_FILES['default_og_image_file']['name'])) {
            $upExt = tn_upload_safe_image_ext($_FILES['default_og_image_file']['tmp_name'], $_FILES['default_og_image_file']['name']);
            $fName = 'og_default_' . time() . '.' . $upExt;
            if ($upExt !== false && move_uploaded_file($_FILES['default_og_image_file']['tmp_name'], $uploadDir . $fName)) {
                $saveData['default_og_image'] = '/uploads/' . $fName;
            }
        } elseif (!empty($_POST['default_og_image'])) {
            $saveData['default_og_image'] = trim($_POST['default_og_image']);
        }

        if (!empty($_FILES['org_logo_file']['name'])) {
            $upExt = tn_upload_safe_image_ext($_FILES['org_logo_file']['tmp_name'], $_FILES['org_logo_file']['name']);
            $fName = 'logo_' . time() . '.' . $upExt;
            if ($upExt !== false && move_uploaded_file($_FILES['org_logo_file']['tmp_name'], $uploadDir . $fName)) {
                $saveData['org_logo'] = '/uploads/' . $fName;
            }
        } elseif (!empty($_POST['org_logo'])) {
            $saveData['org_logo'] = trim($_POST['org_logo']);
        }

        if (!empty($_FILES['favicon_file']['name'])) {
            $upExt = tn_upload_safe_image_ext($_FILES['favicon_file']['tmp_name'], $_FILES['favicon_file']['name']);
            $fName = 'favicon_' . time() . '.' . $upExt;
            if ($upExt !== false && move_uploaded_file($_FILES['favicon_file']['tmp_name'], $uploadDir . $fName)) {
                $saveData['favicon'] = '/uploads/' . $fName;
            }
        } elseif (!empty($_POST['favicon'])) {
            $saveData['favicon'] = trim($_POST['favicon']);
        }

        // Custom Header & Footer Scripts (Restricted to Super Admin)
        if ($adminRole === 'Super Admin') {
            foreach (['header_scripts', 'footer_scripts'] as $scriptKey) {
                if (array_key_exists($scriptKey, $_POST) && is_scalar($_POST[$scriptKey])) {
                    $saveData[$scriptKey] = (string)$_POST[$scriptKey];
                }
            }
        }

        SeoModel::saveSettings($saveData, $adminUser);
        $msg = "Global SEO settings successfully updated and cache refreshed.";
    }
}

$s = SeoModel::getSettings();
$csrfToken = generateCsrfToken();
?>

<div style="max-width: 1200px; margin: 0 auto;">

  <!-- PAGE HEADER -->
  <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 24px; flex-wrap: wrap; gap: 14px;">
    <div>
      <div style="display: flex; align-items: center; gap: 8px; margin-bottom: 6px; font-size: 0.88rem; color: #94a3b8;">
        <a href="/admin/seo" style="color: #94a3b8; text-decoration: none;">&larr; SEO Overview</a>
        <span>/</span>
        <span style="color: #38bdf8; font-weight: 600;">Global Settings</span>
      </div>
      <h1 style="font-size: 1.8rem; font-weight: 800; color: #f8fafc; margin: 0;">
        Global Technical SEO &amp; Entity Settings
      </h1>
    </div>
    <div>
      <button type="button" onclick="document.getElementById('seoSettingsForm').submit();" class="tn-btn-gold" style="padding: 11px 24px; border: none; border-radius: 8px; font-weight: 700; font-size: 0.92rem; cursor: pointer; box-shadow: 0 4px 14px rgba(184, 137, 46, 0.35);">
        SAVE GLOBAL SETTINGS
      </button>
    </div>
  </div>

  <?php if (!empty($msg)): ?>
    <div style="background: rgba(52, 211, 153, 0.15); border: 1px solid rgba(52, 211, 153, 0.4); color: #6ee7b7; padding: 14px 18px; border-radius: 8px; font-size: 0.9rem; margin-bottom: 24px;">
      <?= htmlspecialchars($msg) ?>
    </div>
  <?php endif; ?>

  <?php if (!empty($err)): ?>
    <div style="background: rgba(239, 68, 68, 0.15); border: 1px solid rgba(239, 68, 68, 0.4); color: #fca5a5; padding: 14px 18px; border-radius: 8px; font-size: 0.9rem; margin-bottom: 24px;">
      <?= htmlspecialchars($err) ?>
    </div>
  <?php endif; ?>

  <form id="seoSettingsForm" method="POST" action="/admin/seo_settings" enctype="multipart/form-data">
    <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($csrfToken) ?>" />

    <!-- 1. BRANDING & TITLE DEFAULTS -->
    <div style="background: #1e293b; border: 1px solid #334155; border-radius: 14px; padding: 24px; margin-bottom: 24px;">
      <h3 style="margin: 0 0 16px 0; font-size: 1.1rem; color: #38bdf8; font-weight: 800; border-bottom: 1px solid #334155; padding-bottom: 10px;">
        1. Site Identity &amp; Title Patterns
      </h3>

      <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(300px, 1fr)); gap: 18px; margin-bottom: 18px;">
        <div>
          <label style="display: block; font-size: 0.82rem; font-weight: 700; color: #cbd5e1; margin-bottom: 6px;">
            Brand / Site Name
            <span style="color: #94a3b8; font-weight: 400;">(Replaces {site_name} variable)</span>
          </label>
          <input type="text" name="site_name" value="<?= htmlspecialchars($s['site_name'] ?? 'TrueNorth Group') ?>" required style="width: 100%; box-sizing: border-box; padding: 11px 14px; background: #0f172a; border: 1px solid #475569; border-radius: 8px; color: #fff; font-size: 0.9rem;" />
        </div>

        <div>
          <label style="display: block; font-size: 0.82rem; font-weight: 700; color: #cbd5e1; margin-bottom: 6px;">
            Title Suffix
            <span style="color: #94a3b8; font-weight: 400;">(Appended automatically to inner page titles)</span>
          </label>
          <input type="text" name="title_suffix" value="<?= htmlspecialchars($s['title_suffix'] ?? ' | TrueNorth Group') ?>" style="width: 100%; box-sizing: border-box; padding: 11px 14px; background: #0f172a; border: 1px solid #475569; border-radius: 8px; color: #fff; font-size: 0.9rem;" />
        </div>
      </div>

      <div style="margin-bottom: 18px;">
        <label style="display: block; font-size: 0.82rem; font-weight: 700; color: #cbd5e1; margin-bottom: 6px;">
          Default Meta Description (Fallback for pages without custom description)
        </label>
        <textarea name="default_meta_description" rows="3" style="width: 100%; box-sizing: border-box; padding: 11px 14px; background: #0f172a; border: 1px solid #475569; border-radius: 8px; color: #fff; font-size: 0.9rem; line-height: 1.45;"><?= htmlspecialchars($s['default_meta_description'] ?? '') ?></textarea>
      </div>

      <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(300px, 1fr)); gap: 18px;">
        <div>
          <label style="display: block; font-size: 0.82rem; font-weight: 700; color: #cbd5e1; margin-bottom: 6px;">Twitter Handle</label>
          <input type="text" name="twitter_handle" value="<?= htmlspecialchars($s['twitter_handle'] ?? '@TrueNorthPack') ?>" placeholder="@YourBrandHandle" style="width: 100%; box-sizing: border-box; padding: 11px 14px; background: #0f172a; border: 1px solid #475569; border-radius: 8px; color: #fff; font-size: 0.9rem;" />
        </div>
        <div>
          <label style="display: block; font-size: 0.82rem; font-weight: 700; color: #cbd5e1; margin-bottom: 6px;">Canonical Preferred Hostname</label>
          <select name="preferred_domain" style="width: 100%; box-sizing: border-box; padding: 11px 14px; background: #0f172a; border: 1px solid #475569; border-radius: 8px; color: #fff; font-size: 0.9rem;">
            <option value="non-www" <?= ($s['preferred_domain'] ?? '') === 'non-www' ? 'selected' : '' ?>>Non-WWW (https://wetruenorthgroup.com)</option>
            <option value="www" <?= ($s['preferred_domain'] ?? '') === 'www' ? 'selected' : '' ?>>WWW (https://www.wetruenorthgroup.com)</option>
          </select>
        </div>
      </div>
    </div>

    <!-- 2. DEFAULT MEDIA ASSETS -->
    <div style="background: #1e293b; border: 1px solid #334155; border-radius: 14px; padding: 24px; margin-bottom: 24px;">
      <h3 style="margin: 0 0 16px 0; font-size: 1.1rem; color: #38bdf8; font-weight: 800; border-bottom: 1px solid #334155; padding-bottom: 10px;">
        2. Default Social Images &amp; Favicon
      </h3>

      <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(280px, 1fr)); gap: 20px;">
        
        <!-- Default OG Image -->
        <div>
          <label style="display: block; font-size: 0.82rem; font-weight: 700; color: #cbd5e1; margin-bottom: 6px;">
            Default Social Share (OG) Image (1200x630px recommended)
          </label>
          <input type="text" name="default_og_image" value="<?= htmlspecialchars($s['default_og_image'] ?? '/logo.webp') ?>" style="width: 100%; box-sizing: border-box; padding: 9px 12px; background: #0f172a; border: 1px solid #475569; border-radius: 6px; color: #fff; font-size: 0.85rem; margin-bottom: 6px;" />
          <input type="file" name="default_og_image_file" accept="image/*" style="font-size: 0.82rem; color: #94a3b8;" />
          <?php if (!empty($s['default_og_image'])): ?>
            <div style="margin-top: 8px; border-radius: 6px; overflow: hidden; height: 110px; background: #0f172a; border: 1px solid #334155; text-align: center;">
              <img src="<?= htmlspecialchars($s['default_og_image']) ?>" style="height: 100%; width: 100%; object-fit: contain;" alt="OG Preview" />
            </div>
          <?php endif; ?>
        </div>

        <!-- Org Logo -->
        <div>
          <label style="display: block; font-size: 0.82rem; font-weight: 700; color: #cbd5e1; margin-bottom: 6px;">
            Organization Schema Logo URL
          </label>
          <input type="text" name="org_logo" value="<?= htmlspecialchars($s['org_logo'] ?? '/logo.webp') ?>" style="width: 100%; box-sizing: border-box; padding: 9px 12px; background: #0f172a; border: 1px solid #475569; border-radius: 6px; color: #fff; font-size: 0.85rem; margin-bottom: 6px;" />
          <input type="file" name="org_logo_file" accept="image/*" style="font-size: 0.82rem; color: #94a3b8;" />
          <?php if (!empty($s['org_logo'])): ?>
            <div style="margin-top: 8px; border-radius: 6px; overflow: hidden; height: 110px; background: #0f172a; border: 1px solid #334155; text-align: center;">
              <img src="<?= htmlspecialchars($s['org_logo']) ?>" style="height: 100%; width: 100%; object-fit: contain;" alt="Logo Preview" />
            </div>
          <?php endif; ?>
        </div>

        <!-- Favicon -->
        <div>
          <label style="display: block; font-size: 0.82rem; font-weight: 700; color: #cbd5e1; margin-bottom: 6px;">
            Browser Favicon (SVG / PNG / ICO)
          </label>
          <input type="text" name="favicon" value="<?= htmlspecialchars($s['favicon'] ?? '/logo_svg.svg') ?>" style="width: 100%; box-sizing: border-box; padding: 9px 12px; background: #0f172a; border: 1px solid #475569; border-radius: 6px; color: #fff; font-size: 0.85rem; margin-bottom: 6px;" />
          <input type="file" name="favicon_file" accept=".svg,.png,.ico" style="font-size: 0.82rem; color: #94a3b8;" />
          <?php if (!empty($s['favicon'])): ?>
            <div style="margin-top: 8px; border-radius: 6px; overflow: hidden; height: 110px; background: #0f172a; border: 1px solid #334155; display: flex; align-items: center; justify-content: center;">
              <img src="<?= htmlspecialchars($s['favicon']) ?>" style="max-height: 48px; max-width: 48px;" alt="Favicon Preview" />
            </div>
          <?php endif; ?>
        </div>

      </div>
    </div>

    <!-- 3. SEARCH CONSOLE & ANALYTICS INTEGRATIONS -->
    <div style="background: #1e293b; border: 1px solid #334155; border-radius: 14px; padding: 24px; margin-bottom: 24px;">
      <h3 style="margin: 0 0 16px 0; font-size: 1.1rem; color: #38bdf8; font-weight: 800; border-bottom: 1px solid #334155; padding-bottom: 10px;">
        3. Webmaster Verifications &amp; Analytics Tags
      </h3>

      <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(280px, 1fr)); gap: 18px; margin-bottom: 18px;">
        <div>
          <label style="display: block; font-size: 0.82rem; font-weight: 700; color: #cbd5e1; margin-bottom: 6px;">
            Google Search Console Verification Code
          </label>
          <input type="text" name="google_verification" value="<?= htmlspecialchars($s['google_verification'] ?? '') ?>" placeholder="e.g. google-site-verification token" style="width: 100%; box-sizing: border-box; padding: 11px 14px; background: #0f172a; border: 1px solid #475569; border-radius: 8px; color: #fff; font-size: 0.9rem;" />
        </div>

        <div>
          <label style="display: block; font-size: 0.82rem; font-weight: 700; color: #cbd5e1; margin-bottom: 6px;">
            Bing Webmaster Verification Code
          </label>
          <input type="text" name="bing_verification" value="<?= htmlspecialchars($s['bing_verification'] ?? '') ?>" placeholder="e.g. BINGE08A6F4E26A911C8" style="width: 100%; box-sizing: border-box; padding: 11px 14px; background: #0f172a; border: 1px solid #475569; border-radius: 8px; color: #fff; font-size: 0.9rem;" />
        </div>
      </div>

      <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(280px, 1fr)); gap: 18px;">
        <div>
          <label style="display: block; font-size: 0.82rem; font-weight: 700; color: #cbd5e1; margin-bottom: 6px;">
            Google Analytics 4 (GA4) ID
          </label>
          <input type="text" name="ga4_id" value="<?= htmlspecialchars($s['ga4_id'] ?? '') ?>" placeholder="e.g. G-TN77884411" style="width: 100%; box-sizing: border-box; padding: 11px 14px; background: #0f172a; border: 1px solid #475569; border-radius: 8px; color: #fff; font-size: 0.9rem;" />
        </div>

        <div>
          <label style="display: block; font-size: 0.82rem; font-weight: 700; color: #cbd5e1; margin-bottom: 6px;">
            Google Tag Manager (GTM) Container ID
          </label>
          <input type="text" name="gtm_id" value="<?= htmlspecialchars($s['gtm_id'] ?? '') ?>" placeholder="e.g. GTM-TRUENORTH" style="width: 100%; box-sizing: border-box; padding: 11px 14px; background: #0f172a; border: 1px solid #475569; border-radius: 8px; color: #fff; font-size: 0.9rem;" />
        </div>

        <div>
          <label style="display: block; font-size: 0.82rem; font-weight: 700; color: #cbd5e1; margin-bottom: 6px;">
            Meta / Facebook Pixel ID
          </label>
          <input type="text" name="facebook_pixel_id" value="<?= htmlspecialchars($s['facebook_pixel_id'] ?? '') ?>" placeholder="e.g. 123456789012345" style="width: 100%; box-sizing: border-box; padding: 11px 14px; background: #0f172a; border: 1px solid #475569; border-radius: 8px; color: #fff; font-size: 0.9rem;" />
        </div>
      </div>
    </div>

    <!-- 4. ORGANIZATION SCHEMA DETAILS -->
    <div style="background: #1e293b; border: 1px solid #334155; border-radius: 14px; padding: 24px; margin-bottom: 24px;">
      <h3 style="margin: 0 0 16px 0; font-size: 1.1rem; color: #38bdf8; font-weight: 800; border-bottom: 1px solid #334155; padding-bottom: 10px;">
        4. Structured Organization Entity Information (Schema.org)
      </h3>

      <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(280px, 1fr)); gap: 18px; margin-bottom: 18px;">
        <div>
          <label style="display: block; font-size: 0.82rem; font-weight: 700; color: #cbd5e1; margin-bottom: 6px;">Legal Entity Name</label>
          <input type="text" name="org_legal_name" value="<?= htmlspecialchars($s['org_legal_name'] ?? 'TrueNorth Group Primary Packaging Limited') ?>" style="width: 100%; box-sizing: border-box; padding: 11px 14px; background: #0f172a; border: 1px solid #475569; border-radius: 8px; color: #fff; font-size: 0.9rem;" />
        </div>
        <div>
          <label style="display: block; font-size: 0.82rem; font-weight: 700; color: #cbd5e1; margin-bottom: 6px;">Sales / Support Telephone</label>
          <input type="text" name="org_phone" value="<?= htmlspecialchars($s['org_phone'] ?? '+91 9810220761') ?>" style="width: 100%; box-sizing: border-box; padding: 11px 14px; background: #0f172a; border: 1px solid #475569; border-radius: 8px; color: #fff; font-size: 0.9rem;" />
        </div>
        <div>
          <label style="display: block; font-size: 0.82rem; font-weight: 700; color: #cbd5e1; margin-bottom: 6px;">Official Email</label>
          <input type="email" name="org_email" value="<?= htmlspecialchars($s['org_email'] ?? 'info@wetruenorthgroup.com') ?>" style="width: 100%; box-sizing: border-box; padding: 11px 14px; background: #0f172a; border: 1px solid #475569; border-radius: 8px; color: #fff; font-size: 0.9rem;" />
        </div>
      </div>

      <div style="display: grid; grid-template-columns: 2fr 1fr 1fr 1fr; gap: 14px; margin-bottom: 18px;">
        <div>
          <label style="display: block; font-size: 0.82rem; font-weight: 700; color: #cbd5e1; margin-bottom: 6px;">Street Address</label>
          <input type="text" name="org_address_street" value="<?= htmlspecialchars($s['org_address_street'] ?? '#201, Vardhaman Trade Towers, Rohit Kunj, LSC Pitampura') ?>" style="width: 100%; box-sizing: border-box; padding: 11px 14px; background: #0f172a; border: 1px solid #475569; border-radius: 8px; color: #fff; font-size: 0.88rem;" />
        </div>
        <div>
          <label style="display: block; font-size: 0.82rem; font-weight: 700; color: #cbd5e1; margin-bottom: 6px;">City / Locality</label>
          <input type="text" name="org_address_locality" value="<?= htmlspecialchars($s['org_address_locality'] ?? 'Delhi') ?>" style="width: 100%; box-sizing: border-box; padding: 11px 14px; background: #0f172a; border: 1px solid #475569; border-radius: 8px; color: #fff; font-size: 0.88rem;" />
        </div>
        <div>
          <label style="display: block; font-size: 0.82rem; font-weight: 700; color: #cbd5e1; margin-bottom: 6px;">State / Region</label>
          <input type="text" name="org_address_region" value="<?= htmlspecialchars($s['org_address_region'] ?? 'DL') ?>" style="width: 100%; box-sizing: border-box; padding: 11px 14px; background: #0f172a; border: 1px solid #475569; border-radius: 8px; color: #fff; font-size: 0.88rem;" />
        </div>
        <div>
          <label style="display: block; font-size: 0.82rem; font-weight: 700; color: #cbd5e1; margin-bottom: 6px;">Postal Code</label>
          <input type="text" name="org_address_postal" value="<?= htmlspecialchars($s['org_address_postal'] ?? '110034') ?>" style="width: 100%; box-sizing: border-box; padding: 11px 14px; background: #0f172a; border: 1px solid #475569; border-radius: 8px; color: #fff; font-size: 0.88rem;" />
        </div>
      </div>

      <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(240px, 1fr)); gap: 14px;">
        <div>
          <label style="display: block; font-size: 0.82rem; font-weight: 700; color: #cbd5e1; margin-bottom: 6px;">LinkedIn Company Page URL</label>
          <input type="url" name="org_social_linkedin" value="<?= htmlspecialchars($s['org_social_linkedin'] ?? '') ?>" placeholder="https://linkedin.com/company/..." style="width: 100%; box-sizing: border-box; padding: 10px 12px; background: #0f172a; border: 1px solid #475569; border-radius: 6px; color: #fff; font-size: 0.85rem;" />
        </div>
        <div>
          <label style="display: block; font-size: 0.82rem; font-weight: 700; color: #cbd5e1; margin-bottom: 6px;">Facebook Page URL</label>
          <input type="url" name="org_social_facebook" value="<?= htmlspecialchars($s['org_social_facebook'] ?? '') ?>" placeholder="https://facebook.com/..." style="width: 100%; box-sizing: border-box; padding: 10px 12px; background: #0f172a; border: 1px solid #475569; border-radius: 6px; color: #fff; font-size: 0.85rem;" />
        </div>
        <div>
          <label style="display: block; font-size: 0.82rem; font-weight: 700; color: #cbd5e1; margin-bottom: 6px;">Default Currency</label>
          <input type="text" name="currency" value="<?= htmlspecialchars($s['currency'] ?? 'INR') ?>" style="width: 100%; box-sizing: border-box; padding: 10px 12px; background: #0f172a; border: 1px solid #475569; border-radius: 6px; color: #fff; font-size: 0.85rem;" />
        </div>
      </div>
    </div>

    <!-- 5. SUPER ADMIN CUSTOM SCRIPTS -->
    <div style="background: #1e293b; border: 1px solid <?= ($adminRole === 'Super Admin') ? '#0284c7' : '#334155' ?>; border-radius: 14px; padding: 24px; margin-bottom: 24px;">
      <div style="display: flex; justify-content: space-between; align-items: center; border-bottom: 1px solid #334155; padding-bottom: 10px; margin-bottom: 16px;">
        <h3 style="margin: 0; font-size: 1.1rem; color: #38bdf8; font-weight: 800;">
          5. Custom Header &amp; Footer Code Injections
        </h3>
        <span style="font-size: 0.74rem; background: rgba(56, 189, 248, 0.15); color: #38bdf8; padding: 3px 8px; border-radius: 4px; font-weight: 700;">
          <?= ($adminRole === 'Super Admin') ? 'Super Admin Access Active' : 'Read-Only (Super Admin Required)' ?>
        </span>
      </div>

      <p style="color: #94a3b8; font-size: 0.84rem; margin: 0 0 16px 0;">
        Use these fields to safely inject third-party tags, chat widgets, heatmaps, or schema scripts across the live website. Scripts are rendered raw inside &lt;head&gt; and before &lt;/body&gt;.
      </p>

      <div style="margin-bottom: 18px;">
        <label style="display: block; font-size: 0.82rem; font-weight: 700; color: #cbd5e1; margin-bottom: 6px;">
          Custom &lt;head&gt; Scripts
        </label>
        <textarea name="header_scripts" rows="5" <?= ($adminRole !== 'Super Admin') ? 'readonly disabled' : '' ?> placeholder="<script>/* custom analytics or verification */</script>" style="width: 100%; box-sizing: border-box; padding: 12px 14px; background: #0f172a; border: 1px solid #475569; border-radius: 8px; color: #fff; font-family: monospace; font-size: 0.86rem;"><?= htmlspecialchars($s['header_scripts'] ?? '') ?></textarea>
      </div>

      <div>
        <label style="display: block; font-size: 0.82rem; font-weight: 700; color: #cbd5e1; margin-bottom: 6px;">
          Custom Footer Scripts (Before &lt;/body&gt;)
        </label>
        <textarea name="footer_scripts" rows="5" <?= ($adminRole !== 'Super Admin') ? 'readonly disabled' : '' ?> placeholder="<script>/* chat widget or deferred script */</script>" style="width: 100%; box-sizing: border-box; padding: 12px 14px; background: #0f172a; border: 1px solid #475569; border-radius: 8px; color: #fff; font-family: monospace; font-size: 0.86rem;"><?= htmlspecialchars($s['footer_scripts'] ?? '') ?></textarea>
      </div>
    </div>

    <!-- SUBMIT BUTTON -->
    <div style="text-align: right; margin-bottom: 40px;">
      <button type="submit" class="tn-btn-gold" style="padding: 13px 32px; border: none; border-radius: 8px; font-weight: 700; font-size: 0.98rem; cursor: pointer; box-shadow: 0 4px 14px rgba(184, 137, 46, 0.35);">
        SAVE GLOBAL SETTINGS
      </button>
    </div>

  </form>

</div>

<?php require_once __DIR__ . '/footer.php'; ?>

