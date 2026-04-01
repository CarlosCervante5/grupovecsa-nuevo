<?php
// Configuración
define('SLIDES_FILE', __DIR__ . '/slides.json');
define('UPLOADS_DIR', __DIR__ . '/../uploads/slides/');
define('SLIDES_IMAGES_DIR', __DIR__ . '/../../assets/images/slides/');

// Crear directorios si no existen
if (!file_exists(UPLOADS_DIR)) {
    mkdir(UPLOADS_DIR, 0755, true);
}

/**
 * Obtener todos los slides
 */
function getSlides() {
    if (!file_exists(SLIDES_FILE)) {
        return [];
    }
    
    $content = file_get_contents(SLIDES_FILE);
    $data = json_decode($content, true);
    
    return $data['slides'] ?? [];
}

/**
 * Guardar slides
 */
function saveSlides($slides) {
    $data = ['slides' => $slides];
    return file_put_contents(SLIDES_FILE, json_encode($data, JSON_PRETTY_PRINT));
}

/**
 * Obtener un slide por ID
 */
function getSlideById($id) {
    $slides = getSlides();
    foreach ($slides as $slide) {
        if ($slide['id'] == $id) {
            return $slide;
        }
    }
    return null;
}

/**
 * Crear nuevo slide
 */
function createSlide($slideData) {
    $slides = getSlides();
    
    // Generar nuevo ID
    $newId = 1;
    if (!empty($slides)) {
        $newId = max(array_column($slides, 'id')) + 1;
    }
    
    // Generar nuevo orden
    $newOrder = count($slides) + 1;
    
    $newSlide = [
        'id' => $newId,
        'title' => $slideData['title'],
        'subtitle' => $slideData['subtitle'] ?? '',
        'offer_main' => $slideData['offer_main'] ?? '',
        'offer_main_text' => $slideData['offer_main_text'] ?? '',
        'offer_sub' => $slideData['offer_sub'] ?? '',
        'offer_secondary' => $slideData['offer_secondary'] ?? '',
        'offer_secondary_text' => $slideData['offer_secondary_text'] ?? '',
        'button_text' => $slideData['button_text'] ?? 'Más Información',
        'button_link' => $slideData['button_link'] ?? '',
        'disclaimer' => $slideData['disclaimer'] ?? '',
        'desktop_image' => $slideData['desktop_image'] ?? '',
        'mobile_image' => $slideData['mobile_image'] ?? '',
        'active' => isset($slideData['active']) ? (bool)$slideData['active'] : true,
        'order' => $newOrder
    ];
    
    $slides[] = $newSlide;
    return saveSlides($slides);
}

/**
 * Actualizar slide
 */
function updateSlide($id, $slideData) {
    $slides = getSlides();
    
    foreach ($slides as &$slide) {
        if ($slide['id'] == $id) {
            $slide['title'] = $slideData['title'];
            $slide['subtitle'] = $slideData['subtitle'] ?? $slide['subtitle'];
            $slide['offer_main'] = $slideData['offer_main'] ?? $slide['offer_main'];
            $slide['offer_main_text'] = $slideData['offer_main_text'] ?? $slide['offer_main_text'];
            $slide['offer_sub'] = $slideData['offer_sub'] ?? $slide['offer_sub'];
            $slide['offer_secondary'] = $slideData['offer_secondary'] ?? $slide['offer_secondary'];
            $slide['offer_secondary_text'] = $slideData['offer_secondary_text'] ?? $slide['offer_secondary_text'];
            $slide['button_text'] = $slideData['button_text'] ?? $slide['button_text'];
            $slide['button_link'] = $slideData['button_link'] ?? $slide['button_link'];
            $slide['disclaimer'] = $slideData['disclaimer'] ?? $slide['disclaimer'];
            $slide['desktop_image'] = $slideData['desktop_image'] ?? $slide['desktop_image'];
            $slide['mobile_image'] = $slideData['mobile_image'] ?? $slide['mobile_image'];
            $slide['active'] = isset($slideData['active']) ? (bool)$slideData['active'] : $slide['active'];
            break;
        }
    }
    
    return saveSlides($slides);
}

/**
 * Eliminar slide
 */
function deleteSlide($id) {
    $slides = getSlides();
    $slides = array_filter($slides, function($slide) use ($id) {
        return $slide['id'] != $id;
    });
    
    // Reordenar
    $slides = array_values($slides);
    foreach ($slides as $index => &$slide) {
        $slide['order'] = $index + 1;
    }
    
    return saveSlides($slides);
}

/**
 * Toggle estado del slide
 */
function toggleSlide($id) {
    $slides = getSlides();
    
    foreach ($slides as &$slide) {
        if ($slide['id'] == $id) {
            $slide['active'] = !$slide['active'];
            break;
        }
    }
    
    return saveSlides($slides);
}

/**
 * Subir imagen
 */
function uploadImage($file, $type = 'desktop') {
    if (!isset($file) || $file['error'] !== UPLOAD_ERR_OK) {
        return false;
    }
    
    $allowedTypes = ['image/jpeg', 'image/jpg', 'image/png', 'image/gif'];
    if (!in_array($file['type'], $allowedTypes)) {
        return false;
    }
    
    $extension = pathinfo($file['name'], PATHINFO_EXTENSION);
    $filename = uniqid() . '_' . $type . '.' . $extension;
    $uploadPath = UPLOADS_DIR . $filename;
    
    if (move_uploaded_file($file['tmp_name'], $uploadPath)) {
        return $filename;
    }
    
    return false;
}

/**
 * Copiar imagen a la estructura de slides
 */
function copyImageToSlideStructure($filename, $slideOrder, $type = 'desktop') {
    $sourcePath = UPLOADS_DIR . $filename;
    $slideDir = SLIDES_IMAGES_DIR . 'slide' . $slideOrder . '/' . $type . '/';
    
    // Crear directorio si no existe
    if (!file_exists($slideDir)) {
        mkdir($slideDir, 0755, true);
    }
    
    $extension = pathinfo($filename, PATHINFO_EXTENSION);
    $newFilename = ($type === 'desktop') ? 'desktop.' . $extension : 'mobile.' . $extension;
    $destinationPath = $slideDir . $newFilename;
    
    if (copy($sourcePath, $destinationPath)) {
        return $newFilename;
    }
    
    return false;
}

/**
 * Procesar formulario de slide
 */
function processSlideForm() {
    if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
        return false;
    }
    
    $action = $_POST['action'] ?? '';
    $slideData = [
        'title' => $_POST['title'] ?? '',
        'subtitle' => $_POST['subtitle'] ?? '',
        'offer_main' => $_POST['offer_main'] ?? '',
        'offer_main_text' => $_POST['offer_main_text'] ?? '',
        'offer_sub' => $_POST['offer_sub'] ?? '',
        'offer_secondary' => $_POST['offer_secondary'] ?? '',
        'offer_secondary_text' => $_POST['offer_secondary_text'] ?? '',
        'button_text' => $_POST['button_text'] ?? 'Más Información',
        'button_link' => $_POST['button_link'] ?? '',
        'disclaimer' => $_POST['disclaimer'] ?? '',
        'active' => isset($_POST['active'])
    ];
    
    // Procesar imágenes
    if (isset($_FILES['desktop_image']) && $_FILES['desktop_image']['error'] === UPLOAD_ERR_OK) {
        $desktopImage = uploadImage($_FILES['desktop_image'], 'desktop');
        if ($desktopImage) {
            $slideData['desktop_image'] = $desktopImage;
        }
    }
    
    if (isset($_FILES['mobile_image']) && $_FILES['mobile_image']['error'] === UPLOAD_ERR_OK) {
        $mobileImage = uploadImage($_FILES['mobile_image'], 'mobile');
        if ($mobileImage) {
            $slideData['mobile_image'] = $mobileImage;
        }
    }
    
    switch ($action) {
        case 'create':
            return createSlide($slideData);
            
        case 'update':
            $id = $_POST['id'] ?? 0;
            return updateSlide($id, $slideData);
            
        case 'delete':
            $id = $_POST['id'] ?? 0;
            return deleteSlide($id);
            
        case 'toggle':
            $id = $_POST['id'] ?? 0;
            return toggleSlide($id);
    }
    
    return false;
}

// Procesar formulario si es POST
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $result = processSlideForm();
    
    if ($result) {
        header('Location: ../index.php?tab=slides&success=1');
        exit;
    } else {
        header('Location: ../index.php?tab=slides&error=1');
        exit;
    }
}
?>

