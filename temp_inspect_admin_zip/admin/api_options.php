<?php
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

if (empty($group) && in_array($action, ['add', 'edit', 'toggle_status', 'delete', 'quick_add'])) {
    echo json_encode(['success' => false, 'message' => 'Option group is required']);
    exit();
}

switch ($action) {
    case 'add':
    case 'edit':
    case 'quick_add':
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
