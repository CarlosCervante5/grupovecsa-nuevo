<?php
// Incluir funciones de manejo de slides
require_once 'functions.php';

// Obtener todos los slides
$slides = getSlides();
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Administración de Slides - Grupo VECSA</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800;900&display=swap" rel="stylesheet">
    <style>
        body { font-family: 'Inter', sans-serif; }
    </style>
</head>
<body class="bg-gray-100">
    <div class="min-h-screen">
        <!-- Header -->
        <header class="bg-white shadow-sm border-b">
            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
                <div class="flex justify-between items-center py-6">
                    <div>
                        <h1 class="text-2xl font-bold text-gray-900">Administración de Slides</h1>
                        <p class="text-sm text-gray-600">Gestiona los banners del home</p>
                    </div>
                    <a href="../index.php" class="bg-blue-600 text-white px-4 py-2 rounded-lg hover:bg-blue-700 transition-colors">
                        Ver Sitio Web
                    </a>
                </div>
            </div>
        </header>

        <!-- Main Content -->
        <main class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
            <!-- Add New Slide Button -->
            <div class="mb-8">
                <button onclick="openSlideModal()" class="bg-green-600 text-white px-6 py-3 rounded-lg hover:bg-green-700 transition-colors flex items-center gap-2">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"></path>
                    </svg>
                    Agregar Nuevo Slide
                </button>
            </div>

            <!-- Slides Grid -->
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
                <?php foreach ($slides as $slide): ?>
                <div class="bg-white rounded-lg shadow-md overflow-hidden">
                    <!-- Slide Preview -->
                    <div class="relative h-48 bg-gray-200">
                        <img src="../assets/images/slides/slide<?php echo $slide['order']; ?>/desktop/<?php echo $slide['desktop_image']; ?>" 
                             alt="<?php echo htmlspecialchars($slide['title']); ?>" 
                             class="w-full h-full object-cover">
                        <div class="absolute top-2 right-2">
                            <span class="bg-<?php echo $slide['active'] ? 'green' : 'red'; ?>-500 text-white px-2 py-1 rounded-full text-xs">
                                <?php echo $slide['active'] ? 'Activo' : 'Inactivo'; ?>
                            </span>
                        </div>
                    </div>
                    
                    <!-- Slide Info -->
                    <div class="p-6">
                        <h3 class="text-lg font-semibold text-gray-900 mb-2"><?php echo htmlspecialchars($slide['title']); ?></h3>
                        <p class="text-sm text-gray-600 mb-4"><?php echo htmlspecialchars($slide['subtitle']); ?></p>
                        
                        <!-- Offer Info -->
                        <div class="mb-4">
                            <div class="flex items-center gap-2">
                                <span class="text-2xl font-bold text-blue-600"><?php echo $slide['offer_main']; ?></span>
                                <span class="text-sm text-gray-600"><?php echo $slide['offer_main_text']; ?></span>
                            </div>
                            <?php if ($slide['offer_secondary']): ?>
                            <div class="flex items-center gap-2 mt-2">
                                <span class="text-lg font-bold text-gray-800"><?php echo $slide['offer_secondary']; ?></span>
                                <span class="text-sm text-gray-600"><?php echo $slide['offer_secondary_text']; ?></span>
                            </div>
                            <?php endif; ?>
                        </div>
                        
                        <!-- Actions -->
                        <div class="flex gap-2">
                            <button onclick="editSlide(<?php echo $slide['id']; ?>)" 
                                    class="flex-1 bg-blue-600 text-white px-3 py-2 rounded text-sm hover:bg-blue-700 transition-colors">
                                Editar
                            </button>
                            <button onclick="toggleSlide(<?php echo $slide['id']; ?>)" 
                                    class="px-3 py-2 rounded text-sm <?php echo $slide['active'] ? 'bg-red-600 hover:bg-red-700' : 'bg-green-600 hover:bg-green-700'; ?> text-white transition-colors">
                                <?php echo $slide['active'] ? 'Desactivar' : 'Activar'; ?>
                            </button>
                            <button onclick="deleteSlide(<?php echo $slide['id']; ?>)" 
                                    class="px-3 py-2 bg-red-600 text-white rounded text-sm hover:bg-red-700 transition-colors">
                                Eliminar
                            </button>
                        </div>
                    </div>
                </div>
                <?php endforeach; ?>
            </div>
        </main>
    </div>

    <!-- Slide Modal -->
    <div id="slideModal" class="fixed inset-0 bg-black bg-opacity-50 hidden z-50">
        <div class="flex items-center justify-center min-h-screen p-4">
            <div class="bg-white rounded-lg max-w-2xl w-full max-h-[90vh] overflow-y-auto">
                <form id="slideForm" enctype="multipart/form-data">
                    <div class="p-6">
                        <div class="flex justify-between items-center mb-6">
                            <h2 id="modalTitle" class="text-xl font-bold">Agregar Nuevo Slide</h2>
                            <button type="button" onclick="closeSlideModal()" class="text-gray-400 hover:text-gray-600">
                                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                                </svg>
                            </button>
                        </div>

                        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                            <!-- Basic Info -->
                            <div class="space-y-4">
                                <div>
                                    <label class="block text-sm font-medium text-gray-700 mb-2">Título</label>
                                    <input type="text" name="title" id="title" required 
                                           class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                                </div>
                                
                                <div>
                                    <label class="block text-sm font-medium text-gray-700 mb-2">Subtítulo</label>
                                    <input type="text" name="subtitle" id="subtitle" 
                                           class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                                </div>
                                
                                <div>
                                    <label class="block text-sm font-medium text-gray-700 mb-2">Texto del Botón</label>
                                    <input type="text" name="button_text" id="button_text" value="Más Información" 
                                           class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                                </div>
                                
                                <div>
                                    <label class="block text-sm font-medium text-gray-700 mb-2">Enlace del Botón</label>
                                    <input type="url" name="button_link" id="button_link" 
                                           class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                                </div>
                            </div>

                            <!-- Offer Info -->
                            <div class="space-y-4">
                                <div>
                                    <label class="block text-sm font-medium text-gray-700 mb-2">Oferta Principal</label>
                                    <div class="flex gap-2">
                                        <input type="text" name="offer_main" id="offer_main" placeholder="36" 
                                               class="w-20 px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                                        <input type="text" name="offer_main_text" id="offer_main_text" placeholder="meses" 
                                               class="flex-1 px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                                    </div>
                                </div>
                                
                                <div>
                                    <label class="block text-sm font-medium text-gray-700 mb-2">Oferta Secundaria</label>
                                    <div class="flex gap-2">
                                        <input type="text" name="offer_secondary" id="offer_secondary" placeholder="$50,000" 
                                               class="w-20 px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                                        <input type="text" name="offer_secondary_text" id="offer_secondary_text" placeholder="Bono Cashback" 
                                               class="flex-1 px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                                    </div>
                                </div>
                                
                                <div>
                                    <label class="block text-sm font-medium text-gray-700 mb-2">Texto de Oferta Sub</label>
                                    <input type="text" name="offer_sub" id="offer_sub" placeholder="sin intereses" 
                                           class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                                </div>
                            </div>
                        </div>

                        <!-- Images -->
                        <div class="mt-6 space-y-4">
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-2">Imagen Desktop</label>
                                <input type="file" name="desktop_image" id="desktop_image" accept="image/*" 
                                       class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                            </div>
                            
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-2">Imagen Mobile</label>
                                <input type="file" name="mobile_image" id="mobile_image" accept="image/*" 
                                       class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                            </div>
                        </div>

                        <!-- Disclaimer -->
                        <div class="mt-6">
                            <label class="block text-sm font-medium text-gray-700 mb-2">Disclaimer</label>
                            <textarea name="disclaimer" id="disclaimer" rows="3" 
                                      class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500"></textarea>
                        </div>

                        <!-- Status -->
                        <div class="mt-6">
                            <label class="flex items-center">
                                <input type="checkbox" name="active" id="active" checked 
                                       class="rounded border-gray-300 text-blue-600 shadow-sm focus:border-blue-300 focus:ring focus:ring-blue-200 focus:ring-opacity-50">
                                <span class="ml-2 text-sm text-gray-700">Slide Activo</span>
                            </label>
                        </div>
                    </div>

                    <!-- Modal Footer -->
                    <div class="bg-gray-50 px-6 py-4 flex justify-end gap-3">
                        <button type="button" onclick="closeSlideModal()" 
                                class="px-4 py-2 text-gray-700 bg-gray-200 rounded-lg hover:bg-gray-300 transition-colors">
                            Cancelar
                        </button>
                        <button type="submit" 
                                class="px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700 transition-colors">
                            Guardar Slide
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <script>
        let currentSlideId = null;

        function openSlideModal(slideId = null) {
            currentSlideId = slideId;
            const modal = document.getElementById('slideModal');
            const title = document.getElementById('modalTitle');
            
            if (slideId) {
                title.textContent = 'Editar Slide';
                // Aquí cargarías los datos del slide
            } else {
                title.textContent = 'Agregar Nuevo Slide';
                document.getElementById('slideForm').reset();
            }
            
            modal.classList.remove('hidden');
        }

        function closeSlideModal() {
            document.getElementById('slideModal').classList.add('hidden');
            currentSlideId = null;
        }

        function editSlide(slideId) {
            openSlideModal(slideId);
        }

        function toggleSlide(slideId) {
            if (confirm('¿Estás seguro de cambiar el estado de este slide?')) {
                // Aquí implementarías la lógica para cambiar el estado
                location.reload();
            }
        }

        function deleteSlide(slideId) {
            if (confirm('¿Estás seguro de eliminar este slide? Esta acción no se puede deshacer.')) {
                // Aquí implementarías la lógica para eliminar
                location.reload();
            }
        }

        // Form submission
        document.getElementById('slideForm').addEventListener('submit', function(e) {
            e.preventDefault();
            
            const formData = new FormData(this);
            if (currentSlideId) {
                formData.append('action', 'update');
                formData.append('id', currentSlideId);
            } else {
                formData.append('action', 'create');
            }
            
            // Aquí implementarías la lógica para guardar
            console.log('Guardando slide...', Object.fromEntries(formData));
            closeSlideModal();
        });
    </script>
</body>
</html>

