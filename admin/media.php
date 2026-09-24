<?php
require_once __DIR__ . '/auth.php';
checkAdminAuth();

$uploadDir = PUBLIC_PATH . '/uploads/products';
if (!file_exists($uploadDir)) {
    @mkdir($uploadDir, 0755, true);
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
            $ext = strtolower(pathinfo($fileName, PATHINFO_EXTENSION));
            $allowedExts = ['jpg', 'jpeg', 'png', 'webp', 'svg'];

            if (!in_array($ext, $allowedExts)) {
                $error = "Invalid file type. Only JPG, PNG, WEBP, and SVG images are allowed.";
            } else {
                $cleanName = preg_replace('/[^a-zA-Z0-9_\.-]/', '_', pathinfo($fileName, PATHINFO_FILENAME));
                $targetFile = $cleanName . '_' . time() . '.' . $ext;
                $targetPath = $uploadDir . '/' . $targetFile;

                if (move_uploaded_file($_FILES['media_file']['tmp_name'], $targetPath)) {
                    redirectMedia("media.php?msg=" . urlencode("Image '{$targetFile}' uploaded successfully!"));
                } else {
                    $error = "Failed to upload image file. Please check directory permissions.";
                }
            }
        } else {
            $error = "Please select an image file to upload.";
        }
    } elseif ($action === 'rename_media') {
        $oldName = basename($_POST['old_filename'] ?? '');
        $newNameRaw = trim($_POST['new_filename'] ?? '');

        if (!empty($oldName) && !empty($newNameRaw)) {
            $oldPath = $uploadDir . '/' . $oldName;
            $ext = strtolower(pathinfo($oldName, PATHINFO_EXTENSION));
            $cleanName = preg_replace('/[^a-zA-Z0-9_\.-]/', '_', pathinfo($newNameRaw, PATHINFO_FILENAME));
            $newName = $cleanName . '.' . $ext;
            $newPath = $uploadDir . '/' . $newName;

            if (!file_exists($oldPath)) {
                $error = "Source image file not found.";
            } elseif (file_exists($newPath) && $oldName !== $newName) {
                $error = "A file with name '{$newName}' already exists.";
            } else {
                if (!empty($_FILES['replace_file']['name'])) {
                    $repExt = strtolower(pathinfo($_FILES['replace_file']['name'], PATHINFO_EXTENSION));
                    $allowedExts = ['jpg', 'jpeg', 'png', 'webp', 'svg'];
                    if (in_array($repExt, $allowedExts)) {
                        @unlink($oldPath);
                        $targetPath = $uploadDir . '/' . $newName;
                        move_uploaded_file($_FILES['replace_file']['tmp_name'], $targetPath);
                    }
                } else {
                    rename($oldPath, $newPath);
                }
                redirectMedia("media.php?msg=" . urlencode("Image updated successfully to '{$newName}'!"));
            }
        }
    } elseif ($action === 'delete_media') {
        $targetFile = basename($_POST['filename'] ?? '');
        if (!empty($targetFile)) {
            $filePath = $uploadDir . '/' . $targetFile;
            if (file_exists($filePath)) {
                @unlink($filePath);
                redirectMedia("media.php?msg=" . urlencode("Image '{$targetFile}' deleted successfully."));
            } else {
                $error = "File '{$targetFile}' not found or already deleted.";
            }
        }
    }
}

// NOW Include Header & Render Page
require_once __DIR__ . '/header.php';

// Fetch images list
$images = [];
if (file_exists($uploadDir)) {
    $files = scandir($uploadDir);
    foreach ($files as $file) {
        if ($file !== '.' && $file !== '..' && preg_match('/\.(jpg|jpeg|png|webp|svg)$/i', $file)) {
            $filePath = $uploadDir . '/' . $file;
            $images[] = [
                'filename' => $file,
                'url' => '/public/uploads/products/' . $file,
                'size' => round(filesize($filePath) / 1024, 1) . ' KB',
                'date' => date('M d, Y H:i', filemtime($filePath))
            ];
        }
    }
}
?>

<style>
  .media-header {
    display: flex;
    align-items: center;
    justify-content: space-between;
    margin-bottom: 24px;
    flex-wrap: wrap;
    gap: 12px;
  }
  .media-card {
    background: #1e293b;
    border: 1px solid #334155;
    border-radius: 12px;
    padding: 14px;
    display: flex;
    flex-direction: column;
    justify-content: space-between;
    transition: transform 0.15s ease, border-color 0.15s ease;
  }
  .media-card:hover {
    border-color: #38bdf8;
  }
  .media-preview-box {
    background: #0f172a;
    border-radius: 8px;
    padding: 10px;
    height: 140px;
    display: flex;
    align-items: center;
    justify-content: center;
    margin-bottom: 12px;
    position: relative;
    overflow: hidden;
  }
  .media-preview-img {
    max-width: 100%;
    max-height: 120px;
    object-fit: contain;
  }
  .media-title {
    font-size: 0.85rem;
    font-weight: 700;
    color: #e2e8f0;
    text-overflow: ellipsis;
    overflow: hidden;
    white-space: nowrap;
    margin-bottom: 4px;
  }
  .media-meta {
    font-size: 0.75rem;
    color: #64748b;
    margin-bottom: 12px;
  }
  .media-actions {
    display: flex;
    gap: 6px;
  }
  .btn-media-act {
    flex: 1;
    min-height: 38px;
    padding: 6px 8px;
    border-radius: 6px;
    font-size: 0.78rem;
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

<div class="media-header">
  <div>
    <h1 style="font-size: 1.8rem; font-weight: 800; margin: 0; color: #f8fafc;">Media Library &amp; Assets</h1>
    <p style="color: #94a3b8; margin: 4px 0 0 0; font-size: 0.88rem;">
      Upload, view, edit, replace, and delete product image assets (Total: <?= count($images) ?> files)
    </p>
  </div>
  <button onclick="openUploadModal()" class="frapak-btn-gold" style="display: inline-flex; align-items: center; gap: 8px; border: none; padding: 12px 20px; border-radius: 8px; font-weight: 700; background: #0284c7; color: #fff; cursor: pointer; min-height: 46px;">
    <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><line x1="12" y1="5" x2="12" y2="19"/><line x1="5" y1="12" x2="19" y2="12"/></svg>
    Upload New Image
  </button>
</div>

<?php if (!empty($message)): ?>
  <div style="background: rgba(52, 211, 153, 0.15); border: 1px solid rgba(52, 211, 153, 0.4); color: #6ee7b7; padding: 14px 18px; border-radius: 10px; font-size: 0.9rem; margin-bottom: 24px;">
    <?= htmlspecialchars($message) ?>
  </div>
<?php endif; ?>

<?php if (!empty($error)): ?>
  <div style="background: rgba(239, 68, 68, 0.15); border: 1px solid rgba(239, 68, 68, 0.4); color: #fca5a5; padding: 14px 18px; border-radius: 10px; font-size: 0.9rem; margin-bottom: 24px;">
    <?= htmlspecialchars($error) ?>
  </div>
<?php endif; ?>

<?php if (empty($images)): ?>
  <div style="background: #1e293b; border: 1px solid #334155; border-radius: 12px; padding: 48px; text-align: center; color: #64748b;">
    <p style="margin: 0 0 16px 0; font-size: 1rem;">No custom media assets uploaded yet.</p>
    <button onclick="openUploadModal()" style="background: #0284c7; color: #fff; border: none; padding: 12px 22px; border-radius: 8px; font-weight: 700; cursor: pointer;">
      Upload First Image Asset &rarr;
    </button>
  </div>
<?php else: ?>
  <div class="media-grid" style="display: grid; grid-template-columns: repeat(auto-fill, minmax(200px, 1fr)); gap: 16px;">
    <?php foreach ($images as $img): ?>
      <div class="media-card">
        <div>
          <div class="media-preview-box">
            <img src="<?= htmlspecialchars($img['url']) ?>" class="media-preview-img" alt="Asset" />
          </div>
          <div class="media-title" title="<?= htmlspecialchars($img['filename']) ?>">
            <?= htmlspecialchars($img['filename']) ?>
          </div>
          <div class="media-meta"><?= $img['size'] ?> • <?= $img['date'] ?></div>
        </div>

        <div class="media-actions">
          <button type="button" class="btn-media-act" onclick="openEditModal('<?= htmlspecialchars(addslashes($img['filename'])) ?>', '<?= htmlspecialchars(addslashes($img['url'])) ?>')">
            <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M11 4H4a2 2 0 0 0-2 2v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2v-7"/><path d="M18.5 2.5a2.121 2.121 0 0 1 3 3L12 15l-4 1 1-4 9.5-9.5z"/></svg>
            Edit
          </button>

          <form method="POST" action="media.php" style="flex: 1;" onsubmit="return confirm('Are you sure you want to permanently delete \'<?= htmlspecialchars(addslashes($img['filename'])) ?>\'?');">
            <input type="hidden" name="action" value="delete_media" />
            <input type="hidden" name="filename" value="<?= htmlspecialchars($img['filename']) ?>" />
            <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($csrfToken) ?>" />
            <button type="submit" class="btn-media-act btn-media-danger" style="width: 100%;">
              <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><polyline points="3 6 5 6 21 6"/><path d="M19 6v14a2 2 0 0 1-2 2H7a2 2 0 0 1-2-2V6m3 0V4a2 2 0 0 1 2-2h4a2 2 0 0 1 2 2v2"/></svg>
              Delete
            </button>
          </form>
        </div>
      </div>
    <?php endforeach; ?>
  </div>
<?php endif; ?>

<!-- UPLOAD MEDIA MODAL -->
<div class="media-modal-overlay" id="uploadModal">
  <div class="media-modal-box">
    <div style="display: flex; align-items: center; justify-content: space-between; margin-bottom: 20px; border-bottom: 1px solid #334155; padding-bottom: 12px;">
      <h3 style="margin: 0; font-size: 1.2rem; color: #f8fafc;">Upload New Media Asset</h3>
      <button onclick="closeUploadModal()" style="background: transparent; border: none; color: #94a3b8; font-size: 1.6rem; cursor: pointer;">&times;</button>
    </div>

    <form method="POST" action="media.php" enctype="multipart/form-data">
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

<!-- EDIT / RENAME / REPLACE MEDIA MODAL -->
<div class="media-modal-overlay" id="editModal">
  <div class="media-modal-box">
    <div style="display: flex; align-items: center; justify-content: space-between; margin-bottom: 20px; border-bottom: 1px solid #334155; padding-bottom: 12px;">
      <h3 style="margin: 0; font-size: 1.2rem; color: #f8fafc;">Edit Image Asset</h3>
      <button onclick="closeEditModal()" style="background: transparent; border: none; color: #94a3b8; font-size: 1.6rem; cursor: pointer;">&times;</button>
    </div>

    <form method="POST" action="media.php" enctype="multipart/form-data">
      <input type="hidden" name="action" value="rename_media" />
      <input type="hidden" name="old_filename" id="modalOldFilename" value="" />
      <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($csrfToken) ?>" />

      <div style="text-align: center; background: #0f172a; border-radius: 8px; padding: 12px; margin-bottom: 18px;">
        <img id="modalPreviewImg" src="" style="max-width: 100%; max-height: 120px; object-fit: contain;" alt="Preview" />
      </div>

      <div style="margin-bottom: 16px;">
        <label style="display: block; font-size: 0.85rem; font-weight: 700; color: #cbd5e1; margin-bottom: 6px;">Filename *</label>
        <input type="text" name="new_filename" id="modalNewFilename" required class="form-control-smart" style="width: 100%; padding: 12px; background: #0f172a; border: 1px solid #475569; border-radius: 8px; color: #fff; font-size: 0.9rem;" />
      </div>

      <div style="margin-bottom: 20px;">
        <label style="display: block; font-size: 0.85rem; font-weight: 700; color: #cbd5e1; margin-bottom: 6px;">Replace Image File (Optional)</label>
        <input type="file" name="replace_file" accept="image/*" style="width: 100%; padding: 10px; background: #0f172a; border: 1px solid #475569; border-radius: 8px; color: #fff;" />
        <span style="font-size: 0.75rem; color: #64748b; margin-top: 4px; display: block;">Select new image file to replace existing artwork</span>
      </div>

      <div style="display: flex; justify-content: flex-end; gap: 10px;">
        <button type="button" onclick="closeEditModal()" style="background: transparent; border: 1px solid #475569; color: #cbd5e1; padding: 10px 18px; border-radius: 8px; font-weight: 600; cursor: pointer; min-height: 44px;">Cancel</button>
        <button type="submit" style="background: #0284c7; color: #fff; border: none; padding: 10px 22px; border-radius: 8px; font-weight: 700; cursor: pointer; min-height: 44px;">Save Changes &rarr;</button>
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

function openEditModal(filename, url) {
  document.getElementById('modalOldFilename').value = filename;
  document.getElementById('modalNewFilename').value = filename;
  document.getElementById('modalPreviewImg').src = url;
  document.getElementById('editModal').classList.add('open');
}
function closeEditModal() {
  document.getElementById('editModal').classList.remove('open');
}
</script>

<?php require_once __DIR__ . '/footer.php'; ?>

