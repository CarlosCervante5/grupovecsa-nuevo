<?php
require_once __DIR__ . '/slides/functions.php';
require_once __DIR__ . '/testimonials/functions.php';

$slides = getSlides();
$testimonials = getTestimonials();
$tab = $_GET['tab'] ?? 'slides';
?>
<!DOCTYPE html>
<html lang="es">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Manager - Grupo VECSA</title>
  <script src="https://cdn.tailwindcss.com"></script>
  <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
  <style>body { font-family: 'Inter', sans-serif; }</style>
</head>
<body class="bg-gray-100">
  <div class="min-h-screen">
    <!-- Header -->
    <header class="bg-gray-900 text-white">
      <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-4 flex justify-between items-center">
        <div>
          <h1 class="text-xl font-bold">GRUPO VECSA · Manager</h1>
        </div>
        <a href="../index.php" class="text-sm bg-white/10 px-4 py-2 rounded-lg hover:bg-white/20 transition-colors">Ver Sitio</a>
      </div>
    </header>

    <!-- Tabs -->
    <div class="bg-white border-b">
      <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 flex gap-0">
        <a href="?tab=slides" class="px-6 py-4 text-sm font-medium border-b-2 <?php echo $tab === 'slides' ? 'border-blue-600 text-blue-600' : 'border-transparent text-gray-500 hover:text-gray-700'; ?>">
          Slides / Promociones
        </a>
        <a href="?tab=testimonials" class="px-6 py-4 text-sm font-medium border-b-2 <?php echo $tab === 'testimonials' ? 'border-blue-600 text-blue-600' : 'border-transparent text-gray-500 hover:text-gray-700'; ?>">
          Success Day
        </a>
      </div>
    </div>

    <?php if (isset($_GET['success'])): ?>
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 mt-4">
      <div class="bg-green-100 text-green-700 px-4 py-3 rounded-lg text-sm">Cambios guardados correctamente.</div>
    </div>
    <?php endif; ?>
    <?php if (isset($_GET['error'])): ?>
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 mt-4">
      <div class="bg-red-100 text-red-700 px-4 py-3 rounded-lg text-sm">Error al guardar. Verifica los datos e intenta de nuevo.</div>
    </div>
    <?php endif; ?>

    <main class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">

    <?php if ($tab === 'slides'): ?>
    <!-- ==================== SLIDES TAB ==================== -->
    <div class="flex justify-between items-center mb-6">
      <h2 class="text-lg font-bold text-gray-900">Slides del Home (<?php echo count($slides); ?>)</h2>
      <button onclick="openSlideModal()" class="bg-blue-600 text-white px-4 py-2 rounded-lg text-sm hover:bg-blue-700 transition-colors">+ Nuevo Slide</button>
    </div>

    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
      <?php foreach ($slides as $slide): ?>
      <div class="bg-white rounded-xl shadow-sm overflow-hidden border">
        <div class="relative h-44 bg-gray-200">
          <img src="slides/../../assets/images/slides/slide<?php echo $slide['order']; ?>/desktop/<?php echo $slide['desktop_image']; ?>" 
               alt="<?php echo htmlspecialchars($slide['title']); ?>" class="w-full h-full object-cover"
               onerror="this.src='data:image/svg+xml,<svg xmlns=%22http://www.w3.org/2000/svg%22 width=%22400%22 height=%22200%22><rect fill=%22%23ddd%22 width=%22400%22 height=%22200%22/><text x=%2250%%22 y=%2250%%22 text-anchor=%22middle%22 fill=%22%23999%22 font-size=%2214%22>Sin imagen</text></svg>'">
          <span class="absolute top-2 right-2 px-2 py-1 rounded-full text-xs font-medium <?php echo $slide['active'] ? 'bg-green-500 text-white' : 'bg-gray-400 text-white'; ?>">
            <?php echo $slide['active'] ? 'Activo' : 'Inactivo'; ?>
          </span>
        </div>
        <div class="p-4">
          <h3 class="font-semibold text-gray-900 mb-1"><?php echo htmlspecialchars($slide['title']); ?></h3>
          <p class="text-xs text-gray-500 mb-3"><?php echo htmlspecialchars($slide['subtitle']); ?></p>
          <?php if ($slide['offer_main']): ?>
          <p class="text-sm text-blue-600 font-bold mb-3"><?php echo $slide['offer_main']; ?> <?php echo $slide['offer_main_text']; ?> <?php echo $slide['offer_sub']; ?></p>
          <?php endif; ?>
          <div class="flex gap-2">
            <button onclick='editSlide(<?php echo json_encode($slide); ?>)' class="flex-1 bg-blue-600 text-white px-3 py-2 rounded text-xs hover:bg-blue-700">Editar</button>
            <form method="POST" action="slides/functions.php" class="inline">
              <input type="hidden" name="action" value="toggle"><input type="hidden" name="id" value="<?php echo $slide['id']; ?>">
              <button type="submit" class="px-3 py-2 rounded text-xs text-white <?php echo $slide['active'] ? 'bg-orange-500 hover:bg-orange-600' : 'bg-green-500 hover:bg-green-600'; ?>">
                <?php echo $slide['active'] ? 'Desactivar' : 'Activar'; ?>
              </button>
            </form>
            <form method="POST" action="slides/functions.php" onsubmit="return confirm('¿Eliminar este slide?')" class="inline">
              <input type="hidden" name="action" value="delete"><input type="hidden" name="id" value="<?php echo $slide['id']; ?>">
              <button type="submit" class="px-3 py-2 bg-red-600 text-white rounded text-xs hover:bg-red-700">Eliminar</button>
            </form>
          </div>
        </div>
      </div>
      <?php endforeach; ?>
    </div>

    <!-- Slide Modal -->
    <div id="slideModal" class="fixed inset-0 bg-black/50 hidden z-50 flex items-center justify-center p-4">
      <div class="bg-white rounded-xl max-w-2xl w-full max-h-[90vh] overflow-y-auto">
        <form method="POST" action="slides/functions.php" enctype="multipart/form-data">
          <input type="hidden" name="action" id="slide_action" value="create">
          <input type="hidden" name="id" id="slide_id">
          <div class="p-6">
            <div class="flex justify-between items-center mb-6">
              <h2 id="slideModalTitle" class="text-lg font-bold">Nuevo Slide</h2>
              <button type="button" onclick="closeSlideModal()" class="text-gray-400 hover:text-gray-600">✕</button>
            </div>
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
              <div>
                <label class="block text-xs font-medium text-gray-700 mb-1">Título *</label>
                <input type="text" name="title" id="s_title" required class="w-full px-3 py-2 border rounded-lg text-sm">
              </div>
              <div>
                <label class="block text-xs font-medium text-gray-700 mb-1">Subtítulo</label>
                <input type="text" name="subtitle" id="s_subtitle" class="w-full px-3 py-2 border rounded-lg text-sm">
              </div>
              <div>
                <label class="block text-xs font-medium text-gray-700 mb-1">Oferta Principal</label>
                <div class="flex gap-2">
                  <input type="text" name="offer_main" id="s_offer_main" placeholder="36" class="w-20 px-3 py-2 border rounded-lg text-sm">
                  <input type="text" name="offer_main_text" id="s_offer_main_text" placeholder="meses" class="flex-1 px-3 py-2 border rounded-lg text-sm">
                </div>
              </div>
              <div>
                <label class="block text-xs font-medium text-gray-700 mb-1">Oferta Sub</label>
                <input type="text" name="offer_sub" id="s_offer_sub" placeholder="sin intereses" class="w-full px-3 py-2 border rounded-lg text-sm">
              </div>
              <div>
                <label class="block text-xs font-medium text-gray-700 mb-1">Oferta Secundaria</label>
                <div class="flex gap-2">
                  <input type="text" name="offer_secondary" id="s_offer_secondary" placeholder="$50,000" class="w-24 px-3 py-2 border rounded-lg text-sm">
                  <input type="text" name="offer_secondary_text" id="s_offer_secondary_text" placeholder="Bono" class="flex-1 px-3 py-2 border rounded-lg text-sm">
                </div>
              </div>
              <div>
                <label class="block text-xs font-medium text-gray-700 mb-1">Texto Botón</label>
                <input type="text" name="button_text" id="s_button_text" value="Más Información" class="w-full px-3 py-2 border rounded-lg text-sm">
              </div>
              <div class="md:col-span-2">
                <label class="block text-xs font-medium text-gray-700 mb-1">Enlace Botón</label>
                <input type="url" name="button_link" id="s_button_link" class="w-full px-3 py-2 border rounded-lg text-sm">
              </div>
              <div>
                <label class="block text-xs font-medium text-gray-700 mb-1">Imagen Desktop</label>
                <input type="file" name="desktop_image" accept="image/*" class="w-full text-sm">
              </div>
              <div>
                <label class="block text-xs font-medium text-gray-700 mb-1">Imagen Mobile</label>
                <input type="file" name="mobile_image" accept="image/*" class="w-full text-sm">
              </div>
              <div class="md:col-span-2">
                <label class="block text-xs font-medium text-gray-700 mb-1">Disclaimer</label>
                <textarea name="disclaimer" id="s_disclaimer" rows="2" class="w-full px-3 py-2 border rounded-lg text-sm"></textarea>
              </div>
              <div>
                <label class="flex items-center gap-2">
                  <input type="checkbox" name="active" id="s_active" checked class="rounded">
                  <span class="text-sm text-gray-700">Activo</span>
                </label>
              </div>
            </div>
          </div>
          <div class="bg-gray-50 px-6 py-4 flex justify-end gap-3 rounded-b-xl">
            <button type="button" onclick="closeSlideModal()" class="px-4 py-2 text-gray-700 bg-gray-200 rounded-lg text-sm">Cancelar</button>
            <button type="submit" class="px-4 py-2 bg-blue-600 text-white rounded-lg text-sm hover:bg-blue-700">Guardar</button>
          </div>
        </form>
      </div>
    </div>
    <?php endif; ?>

    <?php if ($tab === 'testimonials'): ?>
    <!-- ==================== TESTIMONIALS TAB ==================== -->
    <div class="flex justify-between items-center mb-6">
      <h2 class="text-lg font-bold text-gray-900">Success Day - Testimoniales (<?php echo count($testimonials); ?>)</h2>
      <button onclick="openTestimonialModal()" class="bg-blue-600 text-white px-4 py-2 rounded-lg text-sm hover:bg-blue-700 transition-colors">+ Nueva Imagen</button>
    </div>

    <div class="grid grid-cols-2 md:grid-cols-3 lg:grid-cols-4 gap-4">
      <?php foreach ($testimonials as $t): ?>
      <div class="bg-white rounded-xl shadow-sm overflow-hidden border group relative">
        <div class="aspect-[4/3] bg-gray-200">
          <img src="../assets/images/<?php echo htmlspecialchars($t['image']); ?>" 
               alt="<?php echo htmlspecialchars($t['alt']); ?>" class="w-full h-full object-cover"
               onerror="this.parentElement.innerHTML='<div class=\'flex items-center justify-center h-full text-gray-400 text-xs\'>Sin imagen</div>'">
        </div>
        <div class="absolute top-2 right-2">
          <span class="px-2 py-1 rounded-full text-xs font-medium <?php echo $t['active'] ? 'bg-green-500 text-white' : 'bg-gray-400 text-white'; ?>">
            <?php echo $t['active'] ? 'Activo' : 'Oculto'; ?>
          </span>
        </div>
        <div class="p-3">
          <p class="text-xs text-gray-500 mb-2 truncate"><?php echo htmlspecialchars($t['alt']); ?></p>
          <div class="flex gap-1">
            <button onclick='editTestimonial(<?php echo json_encode($t); ?>)' class="flex-1 bg-blue-600 text-white px-2 py-1.5 rounded text-xs hover:bg-blue-700">Editar</button>
            <form method="POST" action="testimonials/functions.php" class="inline">
              <input type="hidden" name="action" value="toggle"><input type="hidden" name="id" value="<?php echo $t['id']; ?>">
              <button type="submit" class="px-2 py-1.5 rounded text-xs text-white <?php echo $t['active'] ? 'bg-orange-500' : 'bg-green-500'; ?>">
                <?php echo $t['active'] ? 'Ocultar' : 'Mostrar'; ?>
              </button>
            </form>
            <form method="POST" action="testimonials/functions.php" onsubmit="return confirm('¿Eliminar?')" class="inline">
              <input type="hidden" name="action" value="delete"><input type="hidden" name="id" value="<?php echo $t['id']; ?>">
              <button type="submit" class="px-2 py-1.5 bg-red-600 text-white rounded text-xs">✕</button>
            </form>
          </div>
        </div>
      </div>
      <?php endforeach; ?>
    </div>

    <!-- Testimonial Modal -->
    <div id="testimonialModal" class="fixed inset-0 bg-black/50 hidden z-50 flex items-center justify-center p-4">
      <div class="bg-white rounded-xl max-w-md w-full">
        <form method="POST" action="testimonials/functions.php" enctype="multipart/form-data">
          <input type="hidden" name="action" id="t_action" value="create">
          <input type="hidden" name="id" id="t_id">
          <div class="p-6">
            <div class="flex justify-between items-center mb-6">
              <h2 id="testimonialModalTitle" class="text-lg font-bold">Nueva Imagen</h2>
              <button type="button" onclick="closeTestimonialModal()" class="text-gray-400 hover:text-gray-600">✕</button>
            </div>
            <div class="space-y-4">
              <div>
                <label class="block text-xs font-medium text-gray-700 mb-1">Imagen *</label>
                <input type="file" name="image" id="t_image" accept="image/*" class="w-full text-sm">
                <p id="t_current_image" class="text-xs text-gray-400 mt-1 hidden"></p>
              </div>
              <div>
                <label class="block text-xs font-medium text-gray-700 mb-1">Descripción (alt)</label>
                <input type="text" name="alt" id="t_alt" value="Entrega VECSA" class="w-full px-3 py-2 border rounded-lg text-sm">
              </div>
              <label class="flex items-center gap-2">
                <input type="checkbox" name="active" id="t_active" checked class="rounded">
                <span class="text-sm text-gray-700">Activo</span>
              </label>
            </div>
          </div>
          <div class="bg-gray-50 px-6 py-4 flex justify-end gap-3 rounded-b-xl">
            <button type="button" onclick="closeTestimonialModal()" class="px-4 py-2 text-gray-700 bg-gray-200 rounded-lg text-sm">Cancelar</button>
            <button type="submit" class="px-4 py-2 bg-blue-600 text-white rounded-lg text-sm hover:bg-blue-700">Guardar</button>
          </div>
        </form>
      </div>
    </div>
    <?php endif; ?>

    </main>
  </div>

  <script>
    // ===== SLIDES =====
    function openSlideModal() {
      document.getElementById('slide_action').value = 'create';
      document.getElementById('slide_id').value = '';
      document.getElementById('slideModalTitle').textContent = 'Nuevo Slide';
      ['s_title','s_subtitle','s_offer_main','s_offer_main_text','s_offer_sub','s_offer_secondary','s_offer_secondary_text','s_button_text','s_button_link','s_disclaimer'].forEach(id => {
        const el = document.getElementById(id);
        if (el) el.value = id === 's_button_text' ? 'Más Información' : '';
      });
      document.getElementById('s_active').checked = true;
      document.getElementById('slideModal').classList.remove('hidden');
    }

    function editSlide(slide) {
      document.getElementById('slide_action').value = 'update';
      document.getElementById('slide_id').value = slide.id;
      document.getElementById('slideModalTitle').textContent = 'Editar Slide';
      document.getElementById('s_title').value = slide.title || '';
      document.getElementById('s_subtitle').value = slide.subtitle || '';
      document.getElementById('s_offer_main').value = slide.offer_main || '';
      document.getElementById('s_offer_main_text').value = slide.offer_main_text || '';
      document.getElementById('s_offer_sub').value = slide.offer_sub || '';
      document.getElementById('s_offer_secondary').value = slide.offer_secondary || '';
      document.getElementById('s_offer_secondary_text').value = slide.offer_secondary_text || '';
      document.getElementById('s_button_text').value = slide.button_text || '';
      document.getElementById('s_button_link').value = slide.button_link || '';
      document.getElementById('s_disclaimer').value = slide.disclaimer || '';
      document.getElementById('s_active').checked = slide.active;
      document.getElementById('slideModal').classList.remove('hidden');
    }

    function closeSlideModal() { document.getElementById('slideModal').classList.add('hidden'); }

    // ===== TESTIMONIALS =====
    function openTestimonialModal() {
      document.getElementById('t_action').value = 'create';
      document.getElementById('t_id').value = '';
      document.getElementById('testimonialModalTitle').textContent = 'Nueva Imagen';
      document.getElementById('t_alt').value = 'Entrega VECSA';
      document.getElementById('t_active').checked = true;
      document.getElementById('t_image').required = true;
      document.getElementById('t_current_image').classList.add('hidden');
      document.getElementById('testimonialModal').classList.remove('hidden');
    }

    function editTestimonial(t) {
      document.getElementById('t_action').value = 'update';
      document.getElementById('t_id').value = t.id;
      document.getElementById('testimonialModalTitle').textContent = 'Editar Imagen';
      document.getElementById('t_alt').value = t.alt || '';
      document.getElementById('t_active').checked = t.active;
      document.getElementById('t_image').required = false;
      const cur = document.getElementById('t_current_image');
      cur.textContent = 'Actual: ' + t.image;
      cur.classList.remove('hidden');
      document.getElementById('testimonialModal').classList.remove('hidden');
    }

    function closeTestimonialModal() { document.getElementById('testimonialModal').classList.add('hidden'); }

    // Close modals on backdrop click
    document.querySelectorAll('[id$="Modal"]').forEach(modal => {
      modal.addEventListener('click', e => { if (e.target === modal) modal.classList.add('hidden'); });
    });
  </script>
</body>
</html>
