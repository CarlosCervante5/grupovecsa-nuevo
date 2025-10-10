<?php
// Variables que pueden ser definidas antes de incluir este header:
// $page_title - Título de la página
// $breadcrumb_title - Título para el breadcrumb
// $meta_description - Meta descripción (opcional)

// Valores por defecto
if (!isset($page_title)) $page_title = "Grupo VECSA";
if (!isset($breadcrumb_title)) $breadcrumb_title = "";
if (!isset($meta_description)) $meta_description = "Grupo VECSA - Distribuidor autorizado BMW y MINI en México";
?>
<!DOCTYPE html>
<html lang="es">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title><?php echo $page_title; ?></title>
  <meta name="description" content="<?php echo $meta_description; ?>">
  <link href="assets/css/style.css" rel="stylesheet">
  <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800;900&display=swap" rel="stylesheet">
  <link href="https://fonts.googleapis.com/css2?family=Oswald:wght@300;400;500;600;700&display=swap" rel="stylesheet">
</head>
<body class="font-sans bg-white">

  <!-- Top Bar -->
  <div class="bg-gray-900 text-white py-2 text-sm hidden md:block">
      <div class="container mx-auto px-4 md:px-6 lg:px-8 flex justify-between items-center">
          <div class="flex items-center space-x-6">
              <!-- Contenido removido: teléfono y correo -->
          </div>
          <div class="flex items-center space-x-4">
              <!-- Facebook -->
              <a href="#" class="text-white hover:text-gray-300 transition-colors">
                  <svg class="w-5 h-5" fill="currentColor" viewBox="0 0 24 24">
                      <path d="M24 12.073c0-6.627-5.373-12-12-12s-12 5.373-12 12c0 5.99 4.388 10.954 10.125 11.854v-8.385H7.078v-3.47h3.047V9.43c0-3.007 1.792-4.669 4.533-4.669 1.312 0 2.686.235 2.686.235v2.953H15.83c-1.491 0-1.956.925-1.956 1.874v2.25h3.328l-.532 3.47h-2.796v8.385C19.612 23.027 24 18.062 24 12.073z"/>
                  </svg>
              </a>
              <!-- Instagram -->
              <a href="#" class="text-white hover:text-gray-300 transition-colors">
                  <svg class="w-5 h-5" fill="currentColor" viewBox="0 0 24 24">
                      <path d="M12 2.163c3.204 0 3.584.012 4.85.07 3.252.148 4.771 1.691 4.919 4.919.058 1.265.069 1.645.069 4.849 0 3.205-.012 3.584-.069 4.849-.149 3.225-1.664 4.771-4.919 4.919-1.266.058-1.644.07-4.85.07-3.204 0-3.584-.012-4.849-.07-3.26-.149-4.771-1.699-4.919-4.92-.058-1.265-.07-1.644-.07-4.849 0-3.204.013-3.583.07-4.849.149-3.227 1.664-4.771 4.919-4.919 1.266-.057 1.645-.069 4.849-.069zm0-2.163c-3.259 0-3.667.014-4.947.072-4.358.2-6.78 2.618-6.98 6.98-.059 1.281-.073 1.689-.073 4.948 0 3.259.014 3.668.072 4.948.2 4.358 2.618 6.78 6.98 6.98 1.281.058 1.689.072 4.948.072 3.259 0 3.668-.014 4.948-.072 4.354-.2 6.782-2.618 6.979-6.98.059-1.28.073-1.689.073-4.948 0-3.259-.014-3.667-.072-4.947-.196-4.354-2.617-6.78-6.979-6.98-1.281-.059-1.69-.073-4.949-.073zm0 5.838c-3.403 0-6.162 2.759-6.162 6.162s2.759 6.163 6.162 6.163 6.162-2.759 6.162-6.163c0-3.403-2.759-6.162-6.162-6.162zm0 10.162c-2.209 0-4-1.79-4-4 0-2.209 1.791-4 4-4s4 1.791 4 4c0 2.21-1.791 4-4 4zm6.406-11.845c-.796 0-1.441.645-1.441 1.44s.645 1.44 1.441 1.44c.795 0 1.439-.645 1.439-1.44s-.644-1.44-1.439-1.44z"/>
                  </svg>
              </a>
              <!-- LinkedIn -->
              <a href="#" class="text-white hover:text-gray-300 transition-colors">
                  <svg class="w-5 h-5" fill="currentColor" viewBox="0 0 24 24">
                      <path d="M20.447 20.452h-3.554v-5.569c0-1.328-.027-3.037-1.852-3.037-1.853 0-2.136 1.445-2.136 2.939v5.667H9.351V9h3.414v1.561h.046c.477-.9 1.637-1.85 3.37-1.85 3.601 0 4.267 2.37 4.267 5.455v6.286zM5.337 7.433c-1.144 0-2.063-.926-2.063-2.065 0-1.138.92-2.063 2.063-2.063 1.14 0 2.064.925 2.064 2.063 0 1.139-.925 2.065-2.064 2.065zm1.782 13.019H3.555V9h3.564v11.452zM22.225 0H1.771C.792 0 0 .774 0 1.729v20.542C0 23.227.792 24 1.771 24h20.451C23.2 24 24 23.227 24 22.271V1.729C24 .774 23.2 0 22.222 0h.003z"/>
                  </svg>
              </a>
          </div>
      </div>
  </div>

  <!-- Header -->
  <header id="main-header" class="fixed top-0 md:top-10 left-0 right-0 z-50 bg-white shadow-sm transition-all duration-300">
    <div class="container mx-auto px-4 md:px-6 lg:px-8">
        <div class="flex items-center justify-between h-16 lg:h-20">
            <!-- Logo -->
            <div class="flex-shrink-0">
                <a href="/" class="text-2xl lg:text-3xl font-bold text-gray-900 tracking-tight no-underline">GRUPO VECSA</a>
            </div>
            
            <!-- Desktop Navigation -->
            <nav class="hidden lg:flex items-center space-x-8">
                <div class="relative group">
                    <a href="#" class="text-gray-700 hover:text-gray-900 transition-colors text-sm font-medium flex items-center">
                        Vehículos
                        <svg class="w-4 h-4 ml-1" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"></path></svg>
                    </a>
                    <div class="absolute top-full left-0 mt-2 w-48 bg-white rounded-md shadow-lg opacity-0 group-hover:opacity-100 transition-opacity duration-300 invisible group-hover:visible z-50">
                        <a href="https://grupovecsa.com/inventory/compra-tu-auto/Nuevo-Demo/Bmw/sin-lineas/sin-modelos/sin-carrocerias/sin-versiones/sin-anios/10000/60000000/sin-estados/sin-busqueda/sin-transmisiones/sin-colores/sin-colores/ninguno/1" target="_blank" class="block px-4 py-2 text-sm text-gray-700 hover:bg-gray-100">BMW</a>
                        <a href="https://grupovecsa.com/inventory/compra-tu-auto/Nuevo-Demo/Mini/sin-lineas/sin-modelos/sin-carrocerias/sin-versiones/sin-anios/1000/60000000/sin-estados/sin-busqueda/sin-transmisiones/sin-colores/sin-colores/ninguno/1" target="_blank" class="block px-4 py-2 text-sm text-gray-700 hover:bg-gray-100">MINI</a>
                        <a href="https://grupovecsa.com/inventory/compra-tu-auto/Nuevo-Demo/Motorrad/sin-lineas/sin-modelos/sin-carrocerias/sin-versiones/sin-anios/10000/60000000/sin-estados/sin-busqueda/sin-transmisiones/sin-colores/sin-colores/ninguno/1" target="_blank" class="block px-4 py-2 text-sm text-gray-700 hover:bg-gray-100">Motorrad</a>
                        <a href="https://grupovecsa.com/inventory/compra-tu-auto/Seminuevo/sin-marcas/sin-lineas/sin-modelos/sin-carrocerias/sin-versiones/sin-anios/10000/60000000/sin-estados/sin-busqueda/sin-transmisiones/sin-colores/sin-colores/ninguno/1" target="_blank" class="block px-4 py-2 text-sm text-gray-700 hover:bg-gray-100">Seminuevos</a>
                    </div>
                </div>
                <a href="https://vecsaboutique.com/" target="_blank" class="text-gray-700 hover:text-gray-900 transition-colors text-sm font-medium">Boutique</a>
                <a href="https://grupovecsa.com/inventory/auth/login" target="_blank" class="text-gray-700 hover:text-gray-900 transition-colors text-sm font-medium">Rewards</a>
                <a href="https://vecsaexperience.com/" target="_blank" class="text-gray-700 hover:text-gray-900 transition-colors text-sm font-medium">Experience</a>
                <a href="https://grupovecsa.com/inventory/carcare" target="_blank" class="text-gray-700 hover:text-gray-900 transition-colors text-sm font-medium">Car Care</a>
                <a href="https://grupovecsa.com/inventory/promotions" target="_blank" class="text-gray-700 hover:text-gray-900 transition-colors text-sm font-medium">Promociones</a>
            </nav>
            
            <!-- Right Side Actions -->
            <div class="flex items-center space-x-4">
                <a href="https://grupovecsa.com/inventory/auth/iniciar-sesion" target="_blank" class="hidden lg:block text-gray-700 hover:text-gray-900 text-sm font-medium">
                    Iniciar Sesión
                </a>
                
                <!-- Mobile Menu Button -->
                <button id="mobile-menu-button" class="lg:hidden text-gray-700 p-2">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16"></path>
                    </svg>
                </button>
            </div>
        </div>
    </div>
  </header>

  <!-- Mobile Sidebar -->
  <div id="sidebar-overlay" class="fixed inset-0 bg-black/50 z-40 lg:hidden opacity-0 pointer-events-none transition-opacity duration-300"></div>
  <div id="mobile-sidebar" class="fixed top-0 right-0 h-full w-80 bg-black z-50 lg:hidden transform translate-x-full transition-transform duration-300">
      <div class="flex items-center justify-between p-6 border-b border-white/10">
          <span class="text-xl font-bold text-white">GRUPO VECSA</span>
          <button id="close-sidebar" class="text-white p-2">
              <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
              </svg>
          </button>
      </div>
      
      <!-- Contact Info from Top Bar -->
      <div class="p-6 border-b border-white/10">
          <h3 class="text-white font-medium mb-4">Contacto</h3>
          <div class="space-y-3">
              <div class="flex items-center text-white/80">
                  <svg class="w-4 h-4 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                      <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 5a2 2 0 012-2h3.28a1 1 0 01.948.684l1.498 4.493a1 1 0 01-.502 1.21l-2.257 1.13a11.042 11.042 0 005.516 5.516l1.13-2.257a1 1 0 011.21-.502l4.493 1.498a1 1 0 01.684.949V19a2 2 0 01-2 2h-1C9.716 21 3 14.284 3 6V5z"></path>
                  </svg>
                  <span class="text-sm">222-555-0123</span>
              </div>
              <div class="flex items-center text-white/80">
                  <svg class="w-4 h-4 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                      <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"></path>
                  </svg>
                  <span class="text-sm">contacto@grupovecsa.com</span>
              </div>
          </div>
      </div>
      
      <nav class="p-6">
          <div class="space-y-6">
              <a href="#vehículos" class="block text-white text-lg font-medium">Vehículos</a>
              <a href="#boutique" class="block text-white text-lg font-medium">Boutique</a>
              <a href="#rewards" class="block text-white text-lg font-medium">Rewards</a>
              <a href="#experience" class="block text-white text-lg font-medium">Experience</a>
              <a href="#carcare" class="block text-white text-lg font-medium">Car Care</a>
              <a href="#promociones" class="block text-white text-lg font-medium">Promociones</a>
              <div class="pt-6 border-t border-white/10">
                  <a href="https://grupovecsa.com/inventory/auth/iniciar-sesion" target="_blank" class="block w-full text-white border border-white/20 px-6 py-3 rounded-full font-medium transition-colors text-center">
                      Iniciar Sesión
                  </a>
              </div>
              
              <!-- Social Media from Top Bar -->
              <div class="pt-6 border-t border-white/10">
                  <h3 class="text-white font-medium mb-4">Síguenos</h3>
                  <div class="flex items-center space-x-4">
                      <!-- Facebook -->
                      <a href="#" class="text-white hover:text-white/80 transition-colors">
                          <svg class="w-5 h-5" fill="currentColor" viewBox="0 0 24 24">
                              <path d="M24 12.073c0-6.627-5.373-12-12-12s-12 5.373-12 12c0 5.99 4.388 10.954 10.125 11.854v-8.385H7.078v-3.47h3.047V9.43c0-3.007 1.792-4.669 4.533-4.669 1.312 0 2.686.235 2.686.235v2.953H15.83c-1.491 0-1.956.925-1.956 1.874v2.25h3.328l-.532 3.47h-2.796v8.385C19.612 23.027 24 18.062 24 12.073z"/>
                          </svg>
                      </a>
                      <!-- Instagram -->
                      <a href="#" class="text-white hover:text-white/80 transition-colors">
                          <svg class="w-5 h-5" fill="currentColor" viewBox="0 0 24 24">
                              <path d="M12 2.163c3.204 0 3.584.012 4.85.07 3.252.148 4.771 1.691 4.919 4.919.058 1.265.069 1.645.069 4.849 0 3.205-.012 3.584-.069 4.849-.149 3.225-1.664 4.771-4.919 4.919-1.266.058-1.644.07-4.85.07-3.204 0-3.584-.012-4.849-.07-3.26-.149-4.771-1.699-4.919-4.92-.058-1.265-.07-1.644-.07-4.849 0-3.204.013-3.583.07-4.849.149-3.227 1.664-4.771 4.919-4.919 1.266-.057 1.645-.069 4.849-.069zm0-2.163c-3.259 0-3.667.014-4.947.072-4.358.2-6.78 2.618-6.98 6.98-.059 1.281-.073 1.689-.073 4.948 0 3.259.014 3.668.072 4.948.2 4.358 2.618 6.78 6.98 6.98 1.281.058 1.689.072 4.948.072 3.259 0 3.668-.014 4.948-.072 4.354-.2 6.782-2.618 6.979-6.98.059-1.28.073-1.689.073-4.948 0-3.259-.014-3.667-.072-4.947-.196-4.354-2.617-6.78-6.979-6.98-1.281-.059-1.69-.073-4.949-.073zm0 5.838c-3.403 0-6.162 2.759-6.162 6.162s2.759 6.163 6.162 6.163 6.162-2.759 6.162-6.163c0-3.403-2.759-6.162-6.162-6.162zm0 10.162c-2.209 0-4-1.79-4-4 0-2.209 1.791-4 4-4s4 1.791 4 4c0 2.21-1.791 4-4 4zm6.406-11.845c-.796 0-1.441.645-1.441 1.44s.645 1.44 1.441 1.44c.795 0 1.439-.645 1.439-1.44s-.644-1.44-1.439-1.44z"/>
                          </svg>
                      </a>
                      <!-- LinkedIn -->
                      <a href="#" class="text-white hover:text-white/80 transition-colors">
                          <svg class="w-5 h-5" fill="currentColor" viewBox="0 0 24 24">
                              <path d="M20.447 20.452h-3.554v-5.569c0-1.328-.027-3.037-1.852-3.037-1.853 0-2.136 1.445-2.136 2.939v5.667H9.351V9h3.414v1.561h.046c.477-.9 1.637-1.85 3.37-1.85 3.601 0 4.267 2.37 4.267 5.455v6.286zM5.337 7.433c-1.144 0-2.063-.926-2.063-2.065 0-1.138.92-2.063 2.063-2.063 1.14 0 2.064.925 2.064 2.063 0 1.139-.925 2.065-2.064 2.065zm1.782 13.019H3.555V9h3.564v11.452zM22.225 0H1.771C.792 0 0 .774 0 1.729v20.542C0 23.227.792 24 1.771 24h20.451C23.2 24 24 23.227 24 22.271V1.729C24 .774 23.2 0 22.222 0h.003z"/>
                          </svg>
                      </a>
                  </div>
              </div>
          </div>
      </nav>
  </div>

  <main class="relative pt-16 md:pt-24">
</body>
</html> 