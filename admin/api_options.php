<?php
require_once dirname(__DIR__) . '/includes/upload_guard.php';
require_once __DIR__ . '/auth.php';
checkAdminAuth();
require_once dirname(__DIR__) . '/config/config.php';
require_once dirname(__DIR__) . '/app/Models/ProductModel.php';

header('Content-Type: application/json; charset=UTF-8');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['success' => false, 'message' => 'Method not allowed']);
    exit();
}

$action = $_POST['action'] ?? '';
$group = $_POST['group'] ?? '';

if (empty($group) && in_array($action, ['add', 'edit', 'toggle_status', 'delete', 'quick_add', 'save_option'])) {
    echo json_encode(['success' => false, 'message' => 'Option group is required']);
    exit();
}

switch ($action) {
    case 'add':
    case 'edit':
    case 'quick_add':
    case 'save_option':
        $id = (int)($_POST['id'] ?? 0);
        $name = trim($_POST['name'] ?? ($_POST['display_label'] ?? ''));
        if (empty($name)) {
            echo json_encode(['success' => false, 'message' => 'Name or display label is required']);
            exit();
        }

        $data = [
            'name' => $name,
            'status' => $_POST['status'] ?? 'active'
        ];

        if ($id > 0) $data['id'] = $id;
        if (isset($_POST['short_label'])) $data['short_label'] = trim($_POST['short_label']);
        if (isset($_POST['display_label'])) $data['display_label'] = trim($_POST['display_label']);
        if (isset($_POST['value'])) $data['value'] = trim($_POST['value']);
        if (isset($_POST['unit'])) $data['unit'] = trim($_POST['unit']);
        if (isset($_POST['hex_code'])) $data['hex_code'] = trim($_POST['hex_code']);
        if (isset($_POST['display_order'])) $data['display_order'] = (int)$_POST['display_order'];
        if (isset($_POST['slug'])) $data['slug'] = trim($_POST['slug']);
        if (isset($_POST['headline'])) $data['headline'] = trim($_POST['headline']);
        if (isset($_POST['desc'])) $data['desc'] = trim($_POST['desc']);
        if (isset($_POST['image'])) $data['image'] = trim($_POST['image']);
        if (isset($_POST['coverImage'])) $data['coverImage'] = trim($_POST['coverImage']);

        // Handle multipart image upload
        if (!empty($_FILES['image_file']['name']) && $_FILES['image_file']['error'] === UPLOAD_ERR_OK) {
            $tmpFile = $_FILES['image_file']['tmp_name'];
            $origName = $_FILES['image_file']['name'];
            $ext = tn_upload_safe_image_ext($tmpFile, $origName);
            if ($ext !== false) {
                $subDir = ($group === 'industries') ? 'industries' : 'categories';
                $destFolder = dirname(__DIR__) . "/public/uploads/{$subDir}";
                if (!is_dir($destFolder)) {
                    mkdir($destFolder, 0755, true);
                }
                $safeName = preg_replace('/[^a-zA-Z0-9_-]/', '_', pathinfo($origName, PATHINFO_FILENAME));
                $newFileName = time() . '_' . $safeName . '.' . $ext;
                $destPath = "{$destFolder}/{$newFileName}";
                if (move_uploaded_file($tmpFile, $destPath)) {
                    $uploadedUrl = "/uploads/{$subDir}/{$newFileName}";
                    $data['image'] = $uploadedUrl;
                    if (empty($data['coverImage'])) {
                        $data['coverImage'] = $uploadedUrl;
                    }
                }
            }
        }

        $saved = ProductModel::saveOption($group, $data);
        echo json_encode(['success' => true, 'message' => 'Option saved successfully', 'option' => $saved]);
        break;

    case 'toggle_status':
        $id = (int)($_POST['id'] ?? 0);
        $status = trim($_POST['status'] ?? 'active');
        $success = ProductModel::toggleOptionStatus($group, $id, $status);
        echo json_encode(['success' => $success, 'message' => "Option status updated to {$status}"]);
        break;

    case 'delete':
        $id = (int)($_POST['id'] ?? 0);
        $force = !empty($_POST['force_deactivate']);
        
        if ($force) {
            ProductModel::toggleOptionStatus($group, $id, 'inactive');
            echo json_encode(['success' => true, 'deactivated' => true, 'message' => 'Option deactivated successfully']);
        } else {
            $result = ProductModel::deleteOption($group, $id);
            echo json_encode($result);
        }
        break;

    default:
        echo json_encode(['success' => false, 'message' => 'Invalid action']);
        break;
}
