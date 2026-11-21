<?php
// ==============================================================================
// TRUENORTH CMS — MEDIA LIBRARY & ASSETS (WORDPRESS ATTACHMENT SUITE)
// Comprehensive recursive media browser, uploader, replacer & Image SEO manager
// ==============================================================================

require_once dirname(__DIR__) . '/includes/upload_guard.php';
require_once __DIR__ . '/auth.php';
require_once dirname(__DIR__) . '/app/Models/SeoModel.php';

checkAdminAuth();
SeoModel::init();

$baseUploadDir = PUBLIC_PATH . '/uploads';
$productsUploadDir = $baseUploadDir . '/products';
if (!file_exists($productsUploadDir)) {
    @mkdir($productsUploadDir, 0755, true);
}

$message = $_GET['msg'] ?? '';
$error = $_GET['err'] ?? '';

function redirectMedia($url) {
    if (!headers_sent()) {
        header("Location: " . $url);
    } else {
        echo "<script>window.location.href = " . json_encode($url) . ";</script>";
    }
    exit();
}

// Handle Actions (Upload, Rename/Replace, Delete) BEFORE HTML output
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';
    $csrfToken = $_POST['csrf_token'] ?? '';

    if (!verifyCsrfToken($csrfToken)) {
        $error = "Invalid security token. Please refresh and try again.";
    } elseif ($action === 'upload_media') {
        if (!empty($_FILES['media_file']['name'])) {
            $fileName = basename($_FILES['media_file']['name']);
            $ext = tn_upload_safe_image_ext($_FILES['media_file']['tmp_name'], $fileName, true, ['jpg', 'jpeg', 'png', 'webp']);

            if ($ext === false) {
                $error = "Invalid or unsafe file. Only real JPG, PNG, WEBP and script-free SVG images (max 10 MB) are allowed.";
            } else {
                $cleanName = preg_replace('/[^a-zA-Z0-9_\.-]/', '_', pathinfo($fileName, PATHINFO_FILENAME));
                $targetFile = $cleanName . '_' . time() . '.' . $ext;
                $targetPath = $productsUploadDir . '/' . $targetFile;

                if (move_uploaded_file($_FILES['media_file']['tmp_name'], $targetPath)) {
                    $url = '/uploads/products/' . $targetFile;
                    // Register in SeoModel
                    SeoModel::saveImageSeo($url, [
                        'alt_text' => ucwords(str_replace(['_', '-'], ' ', $cleanName)),
                        'title_text' => ucwords(str_replace(['_', '-'], ' ', $cleanName)),
                        'uploaded_by' => $_SESSION['admin_user'] ?? 'admin'
                    ]);
                    redirectMedia("/admin/media?msg=" . urlencode("Image '{$targetFile}' uploaded successfully!"));
                } else {
                    $error = "Failed to upload image file. Please check directory permissions.";
                }
            }
        } else {
            $error = "Please select an image file to upload.";
        }
    } elseif ($action === 'rename_media') {
        $oldUrl = trim($_POST['old_filename'] ?? '');
        $newNameRaw = trim($_POST['new_filename'] ?? '');

        if (!empty($oldUrl) && !empty($newNameRaw)) {
            $cleanUrl = ltrim($oldUrl, '/');
            // Resolve the source to a real, PUBLIC_PATH-contained image (blocks ../ traversal).
            $srcPath = null;
            foreach (['uploads/' . basename($cleanUrl), 'uploads/products/' . basename($cleanUrl), $cleanUrl] as $src) {
                $resolved = function_exists('tn_public_asset_path') ? tn_public_asset_path($src) : null;
                if ($resolved !== null) { $srcPath = $resolved; break; }
            }

            if ($srcPath) {
                $dir = dirname($srcPath);
                $ext = strtolower(pathinfo($srcPath, PATHINFO_EXTENSION));
                $cleanName = preg_replace('/[^a-zA-Z0-9_\.-]/', '_', pathinfo($newNameRaw, PATHINFO_FILENAME));
                $newFilename = $cleanName . '.' . $ext;
                $destPath = $dir . '/' . $newFilename;

                if (!empty($_FILES['replace_file']['name'])) {
                    $repExt = tn_upload_safe_image_ext($_FILES['replace_file']['tmp_name'], $_FILES['replace_file']['name'], true, ['jpg', 'jpeg', 'png', 'webp']);
                    if ($repExt !== false && ($repExt === $ext || ($repExt === 'jpg' && $ext === 'jpeg'))) {
                        @unlink($srcPath);
                        move_uploaded_file($_FILES['replace_file']['tmp_name'], $destPath);
                    }
                } else {
                    rename($srcPath, $destPath);
                }
                redirectMedia("/admin/media?msg=" . urlencode("Image updated successfully!"));
            } else {
                $error = "Source image file not found.";
            }
        }
    } elseif ($action === 'delete_media') {
        $targetFile = trim($_POST['filename'] ?? ($_POST['url'] ?? ''));
        if (!empty($targetFile)) {
            $cleanUrl = ltrim($targetFile, '/');
            // Resolve to a PUBLIC_PATH-contained image file; traversal is rejected.
            $deleted = false;
            foreach (['uploads/' . basename($cleanUrl), 'uploads/products/' . basename($cleanUrl), $cleanUrl] as $src) {
                $cand = function_exists('tn_public_asset_path') ? tn_public_asset_path($src) : null;
                if ($cand !== null) {
                    @unlink($cand);
                    $deleted = true;
                    break;
                }
            }
            if ($deleted) {
                redirectMedia("/admin/media?msg=" . urlencode("Image deleted successfully."));
            } else {
                $error = "File not found or already deleted.";
            }
        }
    }
}

// NOW Include Header & Render Page
require_once __DIR__ . '/header.php';

// 1. RECURSIVELY FETCH ALL MEDIA ASSETS FROM PUBLIC/UPLOADS
$allFiles = [];
$foldersList = [];

if (file_exists($baseUploadDir)) {
    $rdi = new RecursiveDirectoryIterator($baseUploadDir, RecursiveDirectoryIterator::SKIP_DOTS);
    $rii = new RecursiveIteratorIterator($rdi, RecursiveIteratorIterator::SELF_FIRST);

    foreach ($rii as $file) {
        if ($file->isFile() && preg_match('/\.(jpg|jpeg|png|webp|svg|gif)$/i', $file->getFilename())) {
            $fullPath = str_replace('\\', '/', $file->getPathname());
            $relPath = str_replace(str_replace('\\', '/', PUBLIC_PATH), '', $fullPath);
            $parentFolder = basename(dirname($fullPath));
            if ($parentFolder && !in_array($parentFolder, $foldersList) && $parentFolder !== 'uploads') {
                $foldersList[] = $parentFolder;
            }

            $allFiles[] = [
                'filename' => $file->getFilename(),
                'url' => $relPath,
                'path' => $fullPath,
                'folder' => $parentFolder,
                'size' => round($file->getSize() / 1024, 1) . ' KB',
                'raw_size' => $file->getSize(),
                'mtime' => $file->getMTime(),
                'date' => date('M d, Y', $file->getMTime())
            ];
        }
    }
}

// Include seeded root banners
$rootAssets = ['/hero_bottles_banner.webp', '/logo.webp', '/factory.webp', '/manufacturing.webp'];
foreach ($rootAssets as $ra) {
    $p = PUBLIC_PATH . $ra;
    if (file_exists($p) && is_file($p)) {
        $allFiles[] = [
            'filename' => basename($ra),
            'url' => $ra,
            'path' => str_replace('\\', '/', $p),
            'folder' => 'site-assets',
            'size' => round(filesize($p) / 1024, 1) . ' KB',
            'raw_size' => filesize($p),
            'mtime' => filemtime($p),
            'date' => date('M d, Y', filemtime($p))
        ];
    }
}

// Sort by newest first
usort($allFiles, function($a, $b) {
    return ($b['mtime'] ?? 0) <=> ($a['mtime'] ?? 0);
});

// Filters: Search & Folder
$search = strtolower(trim($_GET['q'] ?? ''));
$folderFilter = trim($_GET['folder'] ?? 'all');

$filtered = $allFiles;
if (!empty($search)) {
    $filtered = array_filter($filtered, function($f) use ($search) {
        return strpos(strtolower($f['filename'] . ' ' . $f['folder']), $search) !== false;
    });
}
if ($folderFilter !== 'all' && !empty($folderFilter)) {
    $filtered = array_filter($filtered, function($f) use ($folderFilter) {
        return ($f['folder'] ?? '') === $folderFilter;
    });
}
$filtered = array_values($filtered);

// Pagination
$perPage = 36;
$totalItems = count($filtered);
$totalPages = max(1, ceil($totalItems / $perPage));
$page = max(1, min($totalPages, (int)($_GET['p'] ?? 1)));
$offset = ($page - 1) * $perPage;
$displayImages = array_slice($filtered, $offset, $perPage);

// Enrich display images with SEO metadata
$enrichedList = [];
foreach ($displayImages as $item) {
    $seo = SeoModel::getImageSeo($item['url']);
    $enrichedList[] = array_merge($item, $seo, [
        'url' => $item['url'],
        'file_name' => $item['filename'],
        'file_size' => $item['size'],
        'date' => $item['date']
    ]);
}

$csrfToken = generateCsrfToken();
?>

<style>
  .media-header {
    display: flex;
    align-items: center;
    justify-content: space-between;
    margin-bottom: 22px;
    flex-wrap: wrap;
    gap: 14px;
  }
  .media-card {
    background: #1e293b;
    border: 1px solid #334155;
    border-radius: 12px;
    padding: 12px;
    display: flex;
    flex-direction: column;
    justify-content: space-between;
    transition: transform 0.15s ease, border-color 0.15s ease;
  }
  .media-card:hover {
    border-color: #38bdf8;
    transform: translateY(-2px);
  }
  .media-preview-box {
    background: #090e1a;
    border-radius: 8px;
    padding: 10px;
    height: 140px;
    display: flex;
    align-items: center;
    justify-content: center;
    margin-bottom: 10px;
    position: relative;
    overflow: hidden;
  }
  .media-preview-img {
    max-width: 100%;
    max-height: 125px;
    object-fit: contain;
  }
  .media-title {
    font-size: 0.84rem;
    font-weight: 700;
    color: #e2e8f0;
    text-overflow: ellipsis;
    overflow: hidden;
    white-space: nowrap;
    margin-bottom: 2px;
  }
  .media-meta {
    font-size: 0.73rem;
    color: #64748b;
    margin-bottom: 10px;
    display: flex;
    justify-content: space-between;
  }
  .media-actions {
    display: flex;
    gap: 6px;
  }
  .btn-media-act {
    flex: 1;
    min-height: 34px;
    padding: 5px 8px;
    border-radius: 6px;
    font-size: 0.76rem;
    font-weight: 700;
    border: 1px solid #475569;
    background: #0f172a;
    color: #cbd5e1;
    cursor: pointer;
    display: inline-flex;
    align-items: center;
    justify-content: center;
    gap: 4px;
    text-decoration: none;
    transition: all 0.15s;
  }
  .btn-media-act:hover {
    border-color: #38bdf8;
    color: #ffffff;
  }
  .btn-media-danger {
    color: #ef4444;
    border-color: rgba(239, 68, 68, 0.4);
    background: rgba(239, 68, 68, 0.1);
  }
  .btn-media-danger:hover {
    background: rgba(239, 68, 68, 0.25);
    color: #ef4444;
  }

  /* MODAL DIALOGS */
  .media-modal-overlay {
    position: fixed;
    inset: 0;
    background: rgba(15, 23, 42, 0.85);
    backdrop-filter: blur(4px);
    z-index: 2000;
    display: none;
    align-items: center;
    justify-content: center;
    padding: 16px;
  }
  .media-modal-overlay.open {
    display: flex;
  }
  .media-modal-box {
    background: #1e293b;
    border: 1px solid #334155;
    border-radius: 16px;
    width: 100%;
    max-width: 480px;
    padding: 24px;
    box-shadow: 0 25px 50px -12px rgba(0, 0, 0, 0.6);
  }
</style>

<div style="max-width: 1360px; margin: 0 auto;">

  <!-- PAGE HEADER -->
  <div class="media-header">
    <div>
      <div style="display: flex; align-items: center; gap: 8px; margin-bottom: 6px; font-size: 0.88rem; color: #94a3b8;">
        <a href="/admin" style="color: #94a3b8; text-decoration: none;">Dashboard</a>
        <span>/</span>
        <span style="color: #38bdf8; font-weight: 600;">Media Library</span>
      </div>
      <h1 style="font-size: 1.8rem; font-weight: 800; margin: 0; color: #f8fafc;">
        Media Library &amp; Assets
      </h1>
      <p style="color: #94a3b8; margin: 4px 0 0 0; font-size: 0.88rem;">
        Browsing <?= number_format($totalItems) ?> image assets across product catalog and content directories.
      </p>
    </div>
    
    <div style="display: flex; gap: 10px; align-items: center; flex-wrap: wrap;">
      <a href="/admin/seo_images" class="tn-btn-outline" style="padding: 10px 18px; border-radius: 8px; font-weight: 700; font-size: 0.86rem; background: #1e293b; color: #38bdf8; border: 1px solid #334155; text-decoration: none; display: inline-flex; align-items: center; gap: 6px;">
        <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="11" cy="11" r="8"/><path d="m21 21-4.3-4.3"/><path d="m8 11 2 2 4-4"/></svg>
        Image SEO &amp; Alt Tags
      </a>
      <button onclick="openUploadModal()" class="tn-btn-gold" style="display: inline-flex; align-items: center; gap: 8px; border: none; padding: 11px 20px; border-radius: 8px; font-weight: 700; background: #0284c7; color: #fff; cursor: pointer; min-height: 44px; box-shadow: 0 4px 14px rgba(2, 132, 199, 0.35);">
        <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><line x1="12" y1="5" x2="12" y2="19"/><line x1="5" y1="12" x2="19" y2="12"/></svg>
        Upload New Image
      </button>
    </div>
  </div>

  <?php if (!empty($message)): ?>
    <div style="background: rgba(52, 211, 153, 0.15); border: 1px solid rgba(52, 211, 153, 0.4); color: #6ee7b7; padding: 14px 18px; border-radius: 10px; font-size: 0.9rem; margin-bottom: 22px;">
      <?= htmlspecialchars($message) ?>
    </div>
  <?php endif; ?>

  <?php if (!empty($error)): ?>
    <div style="background: rgba(239, 68, 68, 0.15); border: 1px solid rgba(239, 68, 68, 0.4); color: #fca5a5; padding: 14px 18px; border-radius: 10px; font-size: 0.9rem; margin-bottom: 22px;">
      <?= htmlspecialchars($error) ?>
    </div>
  <?php endif; ?>

  <!-- SEARCH & FOLDER FILTER STRIP -->
  <div style="background: #1e293b; border: 1px solid #334155; border-radius: 12px; padding: 16px 20px; margin-bottom: 24px; display: flex; justify-content: space-between; align-items: center; flex-wrap: wrap; gap: 14px;">
    
    <!-- Folder Filter Dropdown -->
    <form method="GET" action="/admin/media" style="display: flex; gap: 10px; align-items: center; flex-wrap: wrap;">
      <select name="folder" onchange="this.form.submit()" style="padding: 8px 12px; background: #0f172a; border: 1px solid #475569; border-radius: 6px; color: #fff; font-size: 0.84rem;">
        <option value="all">All Folders (<?= count($allFiles) ?>)</option>
        <?php foreach ($foldersList as $fld): ?>
          <option value="<?= htmlspecialchars($fld) ?>" <?= $folderFilter === $fld ? 'selected' : '' ?>>
            <?= htmlspecialchars(ucwords(str_replace('-', ' ', $fld))) ?>
          </option>
        <?php endforeach; ?>
      </select>

      <input type="text" name="q" value="<?= htmlspecialchars($search) ?>" placeholder="Filter by image name..." style="padding: 8px 14px; background: #0f172a; border: 1px solid #475569; border-radius: 6px; color: #fff; font-size: 0.84rem; min-width: 200px;" />
      <button type="submit" style="padding: 8px 14px; background: #334155; color: #fff; border: none; border-radius: 6px; font-weight: 600; font-size: 0.82rem; cursor: pointer;">
        Filter
      </button>
      <?php if (!empty($search) || $folderFilter !== 'all'): ?>
        <a href="/admin/media" style="color: #94a3b8; font-size: 0.8rem; text-decoration: none; margin-left: 4px;">Reset</a>
      <?php endif; ?>
    </form>

    <div style="font-size: 0.82rem; color: #94a3b8;">
      Showing <?= count($enrichedList) ?> of <?= number_format($totalItems) ?> images &bull; Click any image for <strong style="color: #38bdf8;">WordPress Attachment Details</strong>
    </div>

  </div>

  <!-- MEDIA GRID -->
  <?php if (empty($enrichedList)): ?>
    <div style="background: #1e293b; border: 1px solid #334155; border-radius: 12px; padding: 48px; text-align: center; color: #64748b;">
      <p style="margin: 0 0 16px 0; font-size: 1rem; color: #94a3b8;">No media assets found matching your criteria.</p>
      <button onclick="openUploadModal()" style="background: #0284c7; color: #fff; border: none; padding: 12px 22px; border-radius: 8px; font-weight: 700; cursor: pointer;">
        Upload Image Asset &rarr;
      </button>
    </div>
  <?php else: ?>
    <div class="media-grid" style="display: grid; grid-template-columns: repeat(auto-fill, minmax(200px, 1fr)); gap: 16px; margin-bottom: 30px;">
      <?php foreach ($enrichedList as $idx => $img): ?>
        <div class="media-card">
          <div>
            <div class="media-preview-box" onclick="openAttachmentModalByIndex(<?= $idx ?>)" style="cursor: pointer;" title="Click to view WordPress Attachment Details">
              <img src="<?= htmlspecialchars($img['url']) ?>" class="media-preview-img" alt="<?= htmlspecialchars($img['alt_text'] ?? 'Asset') ?>" onerror="this.src='/logo_svg.svg';" />
            </div>
            <div class="media-title" title="<?= htmlspecialchars($img['filename']) ?>" onclick="openAttachmentModalByIndex(<?= $idx ?>)" style="cursor: pointer;">
              <?= htmlspecialchars($img['filename']) ?>
            </div>
            <div class="media-meta">
              <span><?= htmlspecialchars($img['folder'] ?? 'uploads') ?></span>
              <span><?= htmlspecialchars($img['size']) ?></span>
            </div>
          </div>

          <div class="media-actions">
            <button type="button" class="btn-media-act" title="Copy relative URL" onclick="copyMediaUrl('<?= htmlspecialchars(addslashes($img['url'])) ?>', this)">
              <svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><rect x="9" y="9" width="13" height="13" rx="2" ry="2"/><path d="M5 15H4a2 2 0 0 1-2-2V4a2 2 0 0 1 2-2h9a2 2 0 0 1 2 2v1"/></svg>
              <span>Copy</span>
            </button>
            <button type="button" class="btn-media-act" onclick="openAttachmentModalByIndex(<?= $idx ?>)" title="Edit Alt Text, Title, Caption and SEO Details">
              <svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M11 4H4a2 2 0 0 0-2 2v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2v-7"/><path d="M18.5 2.5a2.121 2.121 0 0 1 3 3L12 15l-4 1 1-4 9.5-9.5z"/></svg>
              <span>Details</span>
            </button>

            <form method="POST" action="/admin/media" style="flex: 1;" onsubmit="return confirm('Are you sure you want to permanently delete \'<?= htmlspecialchars(addslashes($img['filename'])) ?>\'?');">
              <input type="hidden" name="action" value="delete_media" />
              <input type="hidden" name="filename" value="<?= htmlspecialchars($img['url']) ?>" />
              <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($csrfToken) ?>" />
              <button type="submit" class="btn-media-act btn-media-danger" style="width: 100%;" title="Delete permanently">
                <svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><polyline points="3 6 5 6 21 6"/><path d="M19 6v14a2 2 0 0 1-2 2H7a2 2 0 0 1-2-2V6m3 0V4a2 2 0 0 1 2-2h4a2 2 0 0 1 2 2v2"/></svg>
                <span>Delete</span>
              </button>
            </form>
          </div>
        </div>
      <?php endforeach; ?>
    </div>

    <!-- PAGINATION -->
    <?php if ($totalPages > 1): ?>
      <div style="display: flex; justify-content: center; gap: 6px; margin-bottom: 40px;">
        <?php for ($i = 1; $i <= min(10, $totalPages); $i++): ?>
          <a href="/admin/media?folder=<?= htmlspecialchars($folderFilter) ?>&p=<?= $i ?>&q=<?= urlencode($search) ?>" style="padding: 7px 12px; border-radius: 6px; font-size: 0.84rem; font-weight: 700; text-decoration: none; <?= $page === $i ? 'background: #0284c7; color: #fff;' : 'background: #1e293b; color: #94a3b8; border: 1px solid #334155;' ?>">
            <?= $i ?>
          </a>
        <?php endfor; ?>
      </div>
    <?php endif; ?>

  <?php endif; ?>

</div>

<!-- UPLOAD MEDIA MODAL -->
<div class="media-modal-overlay" id="uploadModal">
  <div class="media-modal-box">
    <div style="display: flex; align-items: center; justify-content: space-between; margin-bottom: 20px; border-bottom: 1px solid #334155; padding-bottom: 12px;">
      <h3 style="margin: 0; font-size: 1.2rem; color: #f8fafc;">Upload New Media Asset</h3>
      <button onclick="closeUploadModal()" style="background: transparent; border: none; color: #94a3b8; font-size: 1.6rem; cursor: pointer;">&times;</button>
    </div>

    <form method="POST" action="/admin/media" enctype="multipart/form-data">
      <input type="hidden" name="action" value="upload_media" />
      <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($csrfToken) ?>" />

      <div style="margin-bottom: 20px;">
        <label style="display: block; font-size: 0.85rem; font-weight: 700; color: #cbd5e1; margin-bottom: 8px;">Select Image File *</label>
        <input type="file" name="media_file" accept="image/*" required style="width: 100%; padding: 12px; background: #0f172a; border: 1px solid #475569; border-radius: 8px; color: #fff;" />
        <span style="font-size: 0.75rem; color: #64748b; margin-top: 4px; display: block;">Allowed formats: JPG, PNG, WEBP, SVG</span>
      </div>

      <div style="display: flex; justify-content: flex-end; gap: 10px;">
        <button type="button" onclick="closeUploadModal()" style="background: transparent; border: 1px solid #475569; color: #cbd5e1; padding: 10px 18px; border-radius: 8px; font-weight: 600; cursor: pointer; min-height: 44px;">Cancel</button>
        <button type="submit" style="background: #0284c7; color: #fff; border: none; padding: 10px 22px; border-radius: 8px; font-weight: 700; cursor: pointer; min-height: 44px;">Upload File &rarr;</button>
      </div>
    </form>
  </div>
</div>

<script>
function openUploadModal() {
  document.getElementById('uploadModal').classList.add('open');
}
function closeUploadModal() {
  document.getElementById('uploadModal').classList.remove('open');
}

function copyMediaUrl(url, btn) {
  if (navigator.clipboard && navigator.clipboard.writeText) {
    const fullUrl = url.startsWith('http') ? url : (window.location.origin + (url.startsWith('/') ? '' : '/') + url);
    navigator.clipboard.writeText(fullUrl).then(function() {
      showCopiedFeedback(btn);
    }).catch(function() {
      promptFallback(url);
    });
  } else {
    promptFallback(url);
  }
}

function showCopiedFeedback(btn) {
  const origHtml = btn.innerHTML;
  btn.innerHTML = '<span style="color:#38bdf8;font-weight:700;">Copied!</span>';
  setTimeout(function() {
    btn.innerHTML = origHtml;
  }, 1800);
}

function promptFallback(url) {
  window.prompt('Copy Asset URL:', url);
}

// Initialize dataset for WordPress Attachment Details modal
document.addEventListener('DOMContentLoaded', function() {
  const mediaData = <?= json_encode($enrichedList, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE) ?>;
  setAttachmentDataset(mediaData);
});
</script>

<!-- WORDPRESS ATTACHMENT DETAILS MODAL -->
<?php include __DIR__ . '/partials/attachment_details_modal.php'; ?>

<?php require_once __DIR__ . '/footer.php'; ?>
