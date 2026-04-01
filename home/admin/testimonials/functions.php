<?php
define('TESTIMONIALS_FILE', __DIR__ . '/testimonials.json');
define('TESTIMONIALS_UPLOADS_DIR', __DIR__ . '/../uploads/testimonials/');
define('TESTIMONIALS_IMAGES_DIR', __DIR__ . '/../../assets/images/testimonials/');

foreach ([TESTIMONIALS_UPLOADS_DIR, TESTIMONIALS_IMAGES_DIR] as $dir) {
    if (!file_exists($dir)) mkdir($dir, 0755, true);
}

function getTestimonials() {
    if (!file_exists(TESTIMONIALS_FILE)) return [];
    $data = json_decode(file_get_contents(TESTIMONIALS_FILE), true);
    return $data['testimonials'] ?? [];
}

function saveTestimonials($items) {
    return file_put_contents(TESTIMONIALS_FILE, json_encode(['testimonials' => $items], JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE));
}

function createTestimonial($data) {
    $items = getTestimonials();
    $newId = empty($items) ? 1 : max(array_column($items, 'id')) + 1;

    $item = [
        'id' => $newId,
        'image' => $data['image'] ?? '',
        'alt' => $data['alt'] ?? 'Entrega VECSA',
        'active' => isset($data['active']) ? (bool)$data['active'] : true,
        'order' => count($items) + 1
    ];

    $items[] = $item;
    return saveTestimonials($items);
}

function updateTestimonial($id, $data) {
    $items = getTestimonials();
    foreach ($items as &$item) {
        if ($item['id'] == $id) {
            if (isset($data['image']) && $data['image']) $item['image'] = $data['image'];
            $item['alt'] = $data['alt'] ?? $item['alt'];
            $item['active'] = isset($data['active']) ? (bool)$data['active'] : $item['active'];
            break;
        }
    }
    return saveTestimonials($items);
}

function deleteTestimonial($id) {
    $items = getTestimonials();
    $items = array_values(array_filter($items, fn($i) => $i['id'] != $id));
    foreach ($items as $idx => &$item) $item['order'] = $idx + 1;
    return saveTestimonials($items);
}

function toggleTestimonial($id) {
    $items = getTestimonials();
    foreach ($items as &$item) {
        if ($item['id'] == $id) { $item['active'] = !$item['active']; break; }
    }
    return saveTestimonials($items);
}

function uploadTestimonialImage($file) {
    if (!isset($file) || $file['error'] !== UPLOAD_ERR_OK) return false;
    $allowed = ['image/jpeg', 'image/jpg', 'image/png', 'image/gif', 'image/webp'];
    if (!in_array($file['type'], $allowed)) return false;

    $ext = pathinfo($file['name'], PATHINFO_EXTENSION);
    $filename = uniqid('tst_') . '.' . $ext;

    // Guardar en uploads y copiar a images/testimonials
    if (move_uploaded_file($file['tmp_name'], TESTIMONIALS_UPLOADS_DIR . $filename)) {
        copy(TESTIMONIALS_UPLOADS_DIR . $filename, TESTIMONIALS_IMAGES_DIR . $filename);
        return $filename;
    }
    return false;
}

function processTestimonialForm() {
    if ($_SERVER['REQUEST_METHOD'] !== 'POST') return false;

    $action = $_POST['action'] ?? '';
    $data = ['alt' => $_POST['alt'] ?? 'Entrega VECSA', 'active' => isset($_POST['active'])];

    if (isset($_FILES['image']) && $_FILES['image']['error'] === UPLOAD_ERR_OK) {
        $img = uploadTestimonialImage($_FILES['image']);
        if ($img) $data['image'] = $img;
    }

    switch ($action) {
        case 'create': return $data['image'] ? createTestimonial($data) : false;
        case 'update': return updateTestimonial($_POST['id'] ?? 0, $data);
        case 'delete': return deleteTestimonial($_POST['id'] ?? 0);
        case 'toggle': return toggleTestimonial($_POST['id'] ?? 0);
    }
    return false;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $result = processTestimonialForm();
    header('Location: ../index.php?tab=testimonials&' . ($result ? 'success=1' : 'error=1'));
    exit;
}
?>
