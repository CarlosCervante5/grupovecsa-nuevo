<?php
// API base URL - configurable per environment
$api_base_url = 'http://localhost:8000';

// Frontend base URL - local Angular dev vs production
$is_local = in_array($_SERVER['SERVER_NAME'], ['localhost', '127.0.0.1']);
$frontend_base = $is_local ? 'http://localhost:4200' : '';
$frontend_login = $frontend_base . ($is_local ? '/auth/iniciar-sesion' : '/inventory/auth/iniciar-sesion');

/**
 * Helper: fetch data from the Laravel API via POST
 * Returns decoded response data or empty array on failure
 */
function fetchFromApi($endpoint, $api_base_url) {
    $url = rtrim($api_base_url, '/') . $endpoint;
    $context = stream_context_create([
        'http' => [
            'method' => 'POST',
            'header' => "Content-Type: application/json\r\n",
            'timeout' => 5,
            'ignore_errors' => true,
        ],
    ]);
    $response = @file_get_contents($url, false, $context);
    if ($response === false) {
        return [];
    }
    $data = json_decode($response, true);
    if (!is_array($data) || !isset($data['data'])) {
        return [];
    }
    return $data['data'];
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Grupo VECSA - El Placer de Conducir</title>
  <link href="assets/css/style.css?v=<?php echo time(); ?>" rel="stylesheet">
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
    <header id="main-header" class="header-transparent fixed top-0 md:top-10 left-0 right-0 z-50 bg-transparent backdrop-blur-md transition-all duration-300">
      <div class="container mx-auto px-4 md:px-6 lg:px-8">
          <div class="flex items-center justify-between h-16 lg:h-20">
              <!-- Logo -->
              <div class="flex-shrink-0">
                  <span class="text-2xl lg:text-3xl font-bold text-white tracking-tight">GRUPO VECSA</span>
              </div>
              
              <!-- Desktop Navigation -->
              <nav class="hidden lg:flex items-center space-x-8">
                  <div class="relative group">
                      <a href="#" class="text-white/80 hover:text-white transition-colors text-sm font-medium flex items-center">
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
                  <a href="https://vecsaboutique.com/" target="_blank" class="text-white/80 hover:text-white transition-colors text-sm font-medium">VECSA Boutique</a>
                  <a href="https://grupovecsa.com/inventory/auth/login" target="_blank" class="text-white/80 hover:text-white transition-colors text-sm font-medium">Rewards</a>
                  <a href="https://vecsaexperience.com/" target="_blank" class="text-white/80 hover:text-white transition-colors text-sm font-medium">Experience</a>
                  <a href="https://grupovecsa.com/inventory/carcare" target="_blank" class="text-white/80 hover:text-white transition-colors text-sm font-medium">Car Care</a>
                  <a href="https://grupovecsa.com/inventory/promotions" target="_blank" class="text-white/80 hover:text-white transition-colors text-sm font-medium">Promociones</a>
              </nav>
              
              <!-- Right Side Actions -->
              <div class="flex items-center space-x-4">
                  <a href="<?php echo $frontend_login; ?>" class="hidden lg:block text-white/80 hover:text-white text-sm font-medium">
                      Iniciar Sesión
                  </a>
                  
                  <!-- Mobile Menu Button -->
                  <button id="mobile-menu-button" class="lg:hidden text-white p-2">
                      <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16"></path>
                      </svg>
                  </button>
              </div>
          </div>
      </div>
  </header>

  <!-- Mobile Sidebar -->
  <div id="sidebar-overlay" class="fixed inset-0 bg-[#111827]/50 z-40 lg:hidden opacity-0 pointer-events-none transition-opacity duration-300"></div>
  <div id="mobile-sidebar" class="fixed top-0 right-0 h-full w-80 bg-[#111827] z-50 lg:hidden transform translate-x-full transition-transform duration-300">
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
              <a href="#boutique" class="block text-white text-lg font-medium">VECSA Boutique</a>
              <a href="#rewards" class="block text-white text-lg font-medium">Rewards</a>
              <a href="#experience" class="block text-white text-lg font-medium">Experience</a>
              <a href="#carcare" class="block text-white text-lg font-medium">Car Care</a>
              <a href="#promociones" class="block text-white text-lg font-medium">Promociones</a>
              <div class="pt-6 border-t border-white/10">
                  <a href="<?php echo $frontend_login; ?>" class="block w-full text-white border border-white/20 px-6 py-3 rounded-full font-medium transition-colors text-center">
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

  <main class="relative">
    <?php include 'includes/dynamic-slider.php'; ?>

    <!-- Brands Section -->
    <section class="py-20 bg-gray-100">
      <div class="container mx-auto px-4 md:px-6 lg:px-8 text-center">
        <h2 class="text-4xl font-bold text-gray-900 mb-4 font-condensed">Da clic en nuestras marcas</h2>
        <p class="text-xl text-gray-600 max-w-3xl mx-auto mb-16">Encuentra el inventario con disponibilidad inmediata</p>
        
        <div class="mt-12 grid gap-8 grid-cols-2 sm:grid-cols-3 md:grid-cols-4 lg:grid-cols-6">
          <!-- BMW -->
          <a href="https://grupovecsa.com/inventory/compra-tu-auto/Nuevo-Demo/Bmw/sin-lineas/sin-modelos/sin-carrocerias/sin-versiones/sin-anios/10000/60000000/sin-estados/sin-busqueda/sin-transmisiones/sin-colores/sin-colores/ninguno/1" target="_blank" class="flex justify-center items-center p-4 bg-gray-50 rounded-lg hover:shadow-lg transition-shadow">
            <img class="logo-brand" src="assets/images/BMW.png" alt="BMW">
          </a>
          <!-- MINI -->
          <a href="https://grupovecsa.com/inventory/compra-tu-auto/Nuevo-Demo/Mini/sin-lineas/sin-modelos/sin-carrocerias/sin-versiones/sin-anios/1000/60000000/sin-estados/sin-busqueda/sin-transmisiones/sin-colores/sin-colores/ninguno/1" target="_blank" class="flex justify-center items-center p-4 bg-gray-50 rounded-lg hover:shadow-lg transition-shadow">
            <img class="logo-brand" src="assets/images/MINI.png" alt="MINI">
          </a>
          <!-- Motorrad -->
          <a href="https://grupovecsa.com/inventory/compra-tu-auto/Nuevo-Demo/Motorrad/sin-lineas/sin-modelos/sin-carrocerias/sin-versiones/sin-anios/10000/60000000/sin-estados/sin-busqueda/sin-transmisiones/sin-colores/sin-colores/ninguno/1" target="_blank" class="flex justify-center items-center p-4 bg-gray-50 rounded-lg hover:shadow-lg transition-shadow">
            <img class="logo-brand" src="assets/images/MOTO.png" alt="Motorrad">
          </a>
          <!-- BMW Premium Selection -->
          <a href="https://grupovecsa.com/inventory/compra-tu-auto/Seminuevo/sin-marcas/sin-lineas/sin-modelos/sin-carrocerias/sin-versiones/sin-anios/10000/60000000/sin-estados/sin-busqueda/sin-transmisiones/sin-colores/sin-colores/ninguno/1" target="_blank" class="flex justify-center items-center p-4 bg-gray-50 rounded-lg hover:shadow-lg transition-shadow">
            <img class="logo-brand" src="assets/images/BMW_PREMIUM_SELECITION.png" alt="BMW Premium Selection">
          </a>
          <!-- Abc Cars -->
          <a href="https://abcars.mx/compra-tu-auto/sin-marcas/sin-modelos/sin-anios/100000/5000000/sin-carrocerias/sin-estados/sin-busqueda/sin-transmisiones/1" target="_blank" class="flex justify-center items-center p-4 bg-gray-50 rounded-lg hover:shadow-lg transition-shadow">
            <img class="logo-brand" src="assets/images/ABCARS_new.png" alt="Abc Cars">
          </a>
          <!-- Chevrolet -->
          <a href="https://www.chevroletbalderrama.com.mx/" target="_blank" class="flex justify-center items-center p-4 bg-gray-50 rounded-lg hover:shadow-lg transition-shadow">
            <img class="logo-brand" src="assets/images/CHEVROLET_new.png" alt="Chevrolet Balderrama">
          </a>
        </div>
      </div>
    </section>

    <!-- Services Section -->
    <section class="py-20 bg-white">
        <div class="container mx-auto px-4 md:px-6 lg:px-8">
            <div class="text-center mb-16">
                <h2 class="text-4xl font-bold text-gray-900 mb-4 font-condensed">Nuestras Secciones</h2>
                <p class="text-xl text-gray-600 max-w-3xl mx-auto">Descubre todo lo que Grupo VECSA tiene para ofrecerte</p>
            </div>
            
            <!-- Services Grid - Layout optimizado sin espacios en blanco -->
            <div class="grid grid-cols-1 md:grid-cols-4 md:grid-rows-2 gap-6 auto-rows-fr">
                <!-- VECSA Boutique - Takes 1 column -->
                <a href="https://vecsaboutique.com/" target="_blank" class="group relative overflow-hidden rounded-2xl shadow-lg hover:shadow-2xl transition-all duration-500 md:col-span-1 md:row-span-1 aspect-[4/3] md:aspect-auto">
                    <img src="assets/images/BOUTIQUE.jpg" alt="VECSA Boutique" class="absolute inset-0 w-full h-full object-cover object-center group-hover:scale-110 transition-transform duration-700">
                    <div class="absolute inset-0 card-gradient-overlay"></div>
                    <div class="absolute inset-0 p-6 flex flex-col justify-end text-white">
                        <h3 class="text-2xl font-bold mb-2 text-white drop-shadow-lg">VECSA Boutique</h3>
                        <p class="text-sm text-white/90 mb-4 drop-shadow-md">Accesorios y productos exclusivos BMW y MINI</p>
                        <div class="w-full border-2 border-white text-white py-3 px-6 rounded-full font-medium hover:bg-white hover:text-[#111827] transition-colors text-center">
                            Ver catálogo
                        </div>
                    </div>
                </a>

                <!-- VECSA Rewards - Takes 1 column -->
                <a href="https://grupovecsa.com/inventory/auth/login" target="_blank" class="group relative overflow-hidden rounded-2xl shadow-lg hover:shadow-2xl transition-all duration-500 md:col-span-1 md:row-span-1 aspect-[4/3] md:aspect-auto">
                    <img src="assets/images/REWARDS.jpg" alt="VECSA Rewards" class="absolute inset-0 w-full h-full object-cover object-center group-hover:scale-110 transition-transform duration-700">
                    <div class="absolute inset-0 card-gradient-overlay"></div>
                    <div class="absolute inset-0 p-6 flex flex-col justify-end text-white">
                        <h3 class="text-2xl font-bold mb-2 text-white drop-shadow-lg">VECSA Rewards</h3>
                        <p class="text-sm text-white/90 mb-4 drop-shadow-md">Programa de lealtad exclusivo con beneficios únicos</p>
                        <div class="w-full border-2 border-white text-white py-3 px-6 rounded-full font-medium hover:bg-white hover:text-[#111827] transition-colors text-center">
                            Registrarse
                        </div>
                    </div>
                </a>

                <!-- VECSA Experience - Takes 2 columns, 2 rows para llenar todo el espacio -->
                <a href="https://vecsaexperience.com/" target="_blank" class="group relative overflow-hidden rounded-2xl shadow-lg hover:shadow-2xl transition-all duration-500 md:col-span-2 md:row-span-2 aspect-[4/3] md:aspect-auto">
                    <img src="assets/images/COMUNIDAD-VECSA.jpg" alt="VECSA Experience" class="absolute inset-0 w-full h-full object-cover object-center group-hover:scale-110 transition-transform duration-700">
                    <div class="absolute inset-0 card-gradient-overlay"></div>
                    <div class="absolute inset-0 p-6 flex flex-col justify-end text-white">
                        <h3 class="text-3xl font-bold mb-4 text-white drop-shadow-lg">VECSA Experience</h3>
                        <p class="text-base text-white/90 mb-6 drop-shadow-md">Disfruta de eventos especiales, experiencias únicas y beneficios exclusivos.</p>
                        <div class="w-full border-2 border-white text-white py-4 px-8 rounded-full font-medium hover:bg-white hover:text-[#111827] transition-colors text-center">
                            Explorar
                        </div>
                    </div>
                </a>

                <!-- Car Care - Takes 1 column -->
                <a href="https://grupovecsa.com/inventory/carcare" target="_blank" class="group relative overflow-hidden rounded-2xl shadow-lg hover:shadow-2xl transition-all duration-500 md:col-span-1 md:row-span-1 aspect-[4/3] md:aspect-auto">
                    <img src="assets/images/CARCARE.jpg" alt="Car Care" class="absolute inset-0 w-full h-full object-cover object-center group-hover:scale-110 transition-transform duration-700">
                    <div class="absolute inset-0 card-gradient-overlay"></div>
                    <div class="absolute inset-0 p-6 flex flex-col justify-end text-white">
                        <h3 class="text-2xl font-bold mb-2 text-white drop-shadow-lg">Car Care</h3>
                        <p class="text-sm text-white/90 mb-4 drop-shadow-md">Mantenimiento y cuidado profesional para tu vehículo</p>
                        <div class="w-full border-2 border-white text-white py-3 px-6 rounded-full font-medium hover:bg-white hover:text-[#111827] transition-colors text-center">
                            Agendar cita
                        </div>
                    </div>
                </a>

                <!-- Promociones - Takes 1 column -->
                <a href="https://grupovecsa.com/inventory/promotions" target="_blank" class="group relative overflow-hidden rounded-2xl shadow-lg hover:shadow-2xl transition-all duration-500 md:col-span-1 md:row-span-1 aspect-[4/3] md:aspect-auto">
                    <img src="assets/images/PROMOCIONES.jpg" alt="Promociones" class="absolute inset-0 w-full h-full object-cover object-center group-hover:scale-110 transition-transform duration-700">
                    <div class="absolute inset-0 card-gradient-overlay"></div>
                    <div class="absolute inset-0 p-6 flex flex-col justify-end text-white">
                        <h3 class="text-2xl font-bold mb-2 text-white drop-shadow-lg">Promociones</h3>
                        <p class="text-sm text-white/90 mb-4 drop-shadow-md">Descubre las mejores ofertas y promociones especiales</p>
                        <div class="w-full border-2 border-white text-white py-3 px-6 rounded-full font-medium hover:bg-white hover:text-[#111827] transition-colors text-center">
                            Ver ofertas
                        </div>
                    </div>
                </a>
            </div>
        </div>
    </section>

    <!-- Success Day Section -->
    <?php
    $testimonialsData = fetchFromApi('/api/home/testimonials', $api_base_url);
    $activeTestimonials = $testimonialsData['testimonials'] ?? [];
    // Map Angular asset paths to PHP asset paths
    foreach ($activeTestimonials as &$t) {
        if (isset($t['image_path'])) {
            $t['image_path'] = str_replace('assets/images/home/', 'assets/images/', $t['image_path']);
        }
    }
    unset($t);
    ?>
    <?php if (!empty($activeTestimonials)): ?>
    <section id="success-day" class="py-20 bg-gray-900 overflow-hidden scroll-mt-20">
      <div class="container mx-auto px-4 md:px-6 lg:px-8">
        <div class="text-center mb-12">
          <span class="inline-block bg-blue-600/20 text-blue-400 text-sm font-semibold px-4 py-1.5 rounded-full mb-4 tracking-wide uppercase">Success Day</span>
          <h2 class="text-4xl font-bold text-white mb-4 font-condensed">Momentos que nos llenan de orgullo</h2>
          <p class="text-xl text-gray-400 max-w-3xl mx-auto">Cada entrega es una historia de éxito. Conoce a nuestros clientes y sus nuevos compañeros de viaje.</p>
        </div>

        <div class="relative">
          <button id="sd-prev" class="absolute left-0 top-1/2 -translate-y-1/2 z-10 bg-white/10 hover:bg-white/20 backdrop-blur-sm text-white w-10 h-10 rounded-full flex items-center justify-center transition-colors -ml-2 md:ml-0" aria-label="Anterior">
            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/></svg>
          </button>
          <button id="sd-next" class="absolute right-0 top-1/2 -translate-y-1/2 z-10 bg-white/10 hover:bg-white/20 backdrop-blur-sm text-white w-10 h-10 rounded-full flex items-center justify-center transition-colors -mr-2 md:mr-0" aria-label="Siguiente">
            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/></svg>
          </button>

          <div id="sd-track" class="flex gap-6 transition-transform duration-500 ease-out px-6 md:px-12">
            <?php foreach ($activeTestimonials as $t): ?>
            <div class="sd-card flex-shrink-0 w-72 md:w-80 rounded-2xl overflow-hidden group">
              <img src="<?php echo htmlspecialchars($t['image_path'] ?? ''); ?>" alt="<?php echo htmlspecialchars($t['alt'] ?? ''); ?>" class="w-full aspect-[4/3] object-cover rounded-2xl group-hover:scale-105 transition-transform duration-500">
            </div>
            <?php endforeach; ?>
          </div>
        </div>

        <div class="flex justify-center mt-12 gap-2" id="sd-dots"></div>
      </div>
    </section>
    <?php endif; ?>

    <!-- Locations Section -->
    <section id="sucursales" class="py-20 bg-white scroll-mt-20">
      <div class="container mx-auto px-4 md:px-6 lg:px-8">
        <div class="text-center mb-16">
          <h2 class="text-4xl font-bold text-gray-900 mb-4 font-condensed">Sucursales</h2>
          <p class="text-xl text-gray-600 max-w-3xl mx-auto">Encuentra tu agencia más cercana y vive la experiencia premium que solo Grupo VECSA te puede ofrecer</p>
        </div>

        <div class="grid grid-cols-1 lg:grid-cols-2 gap-8">
          <!-- Locations List -->
          <div class="space-y-4">
            <div class="flex flex-col sm:flex-row sm:items-center justify-between mb-6">
              <!-- Mobile Filter Dropdown -->
              <div class="mobile-only">
                <select id="mobile-location-select" class="w-full p-3 border border-gray-300 rounded-lg bg-white text-gray-900 focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                  <option value="">Selecciona una sucursal</option>
                  <option value="hub-serdan">HUB Serdán - Puebla</option>
                  <option value="vecsa-puebla">VECSA Angelópolis - Puebla</option>
                  <option value="vecsa-pachuca">VECSA Pachuca - Hidalgo</option>
                  <option value="vecsa-veracruz">VECSA Veracruz</option>
                  <option value="vecsa-oaxaca">VECSA Oaxaca</option>
                  <option value="vecsa-balderrama">Chevrolet Balderrama - Puebla</option>
                  <option value="abcars-puebla">ABCars - Puebla</option>
                </select>
              </div>
              <!-- Desktop Filter Tabs -->
              <div class="desktop-only flex bg-gray-100 rounded-lg p-1">
                <button class="location-filter-btn active px-3 py-1 rounded text-sm font-medium transition-colors" data-filter="all">
                  Todas
                </button>
                <button class="location-filter-btn px-3 py-1 rounded text-sm font-medium transition-colors" data-filter="puebla">
                  Puebla
                </button>
                <button class="location-filter-btn px-3 py-1 rounded text-sm font-medium transition-colors" data-filter="otros">
                  Otros Estados
                </button>
              </div>
            </div>
            
            <!-- Desktop Location Cards -->
            <div class="desktop-only space-y-3 max-h-96 overflow-y-auto custom-scrollbar">
              <!-- HUB Serdán -->
              <div class="location-item bg-white rounded-xl p-4 shadow-md hover:shadow-xl transition-all duration-300 cursor-pointer border border-gray-100 hover:border-blue-200 group" 
                   data-location="hub-serdan" data-lat="19.0414" data-lng="-98.2063" data-filter="puebla">
                <div class="flex items-start space-x-3">
                  <div class="relative">
                    <img src="assets/images/hub-serdan.jpg" alt="HUB Serdán" class="w-16 h-16 object-cover rounded-lg flex-shrink-0 group-hover:scale-105 transition-transform">
                    <div class="absolute -top-1 -right-1 w-4 h-4 bg-blue-600 rounded-full border-2 border-white"></div>
                  </div>
                  <div class="flex-1 min-w-0">
                    <div class="flex items-center justify-between mb-1">
                      <h4 class="text-lg font-bold text-gray-900 group-hover:text-blue-600 transition-colors">HUB Serdán</h4>
                      <span class="text-xs bg-blue-100 text-blue-700 px-2 py-1 rounded-full font-medium">Puebla</span>
                    </div>
                    <p class="text-sm text-gray-600 mb-2 line-clamp-1">Blvd. Hermanos Serdán 788, esquina Francisco Villa</p>
                    <div class="flex items-center justify-between text-xs text-gray-500">
                      <div class="flex items-center">
                        <svg class="w-3 h-3 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 5a2 2 0 012-2h3.28a1 1 0 01.948.684l1.498 4.493a1 1 0 01-.502 1.21l-2.257 1.13a11.042 11.042 0 005.516 5.516l1.13-2.257a1 1 0 011.21-.502l4.493 1.498a1 1 0 01.684.949V19a2 2 0 01-2 2h-1C9.716 21 3 14.284 3 6V5z"></path>
                        </svg>
                        222-309-0700
                      </div>
                      <div class="flex items-center text-green-600">
                        <div class="w-2 h-2 bg-green-500 rounded-full mr-1"></div>
                        Abierto
                      </div>
                    </div>
                  </div>
                </div>
              </div>

              <!-- VECSA Puebla -->
              <div class="location-item bg-white rounded-xl p-4 shadow-md hover:shadow-xl transition-all duration-300 cursor-pointer border border-gray-100 hover:border-blue-200 group" 
                   data-location="vecsa-puebla" data-lat="19.0319" data-lng="-98.2442" data-filter="puebla">
                <div class="flex items-start space-x-3">
                  <div class="relative">
                    <img src="assets/images/puebla-angelopolis.jpg" alt="VECSA Puebla" class="w-16 h-16 object-cover rounded-lg flex-shrink-0 group-hover:scale-105 transition-transform">
                    <div class="absolute -top-1 -right-1 w-4 h-4 bg-blue-600 rounded-full border-2 border-white"></div>
                  </div>
                  <div class="flex-1 min-w-0">
                    <div class="flex items-center justify-between mb-1">
                      <h4 class="text-lg font-bold text-gray-900 group-hover:text-blue-600 transition-colors">VECSA Angelópolis</h4>
                      <span class="text-xs bg-blue-100 text-blue-700 px-2 py-1 rounded-full font-medium">Puebla</span>
                    </div>
                    <p class="text-sm text-gray-600 mb-2 line-clamp-1">Blvd. Atlixcayotl No. 5316, Angelópolis</p>
                    <div class="flex items-center justify-between text-xs text-gray-500">
                      <div class="flex items-center">
                        <svg class="w-3 h-3 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 5a2 2 0 012-2h3.28a1 1 0 01.948.684l1.498 4.493a1 1 0 01-.502 1.21l-2.257 1.13a11.042 11.042 0 005.516 5.516l1.13-2.257a1 1 0 011.21-.502l4.493 1.498a1 1 0 01.684.949V19a2 2 0 01-2 2h-1C9.716 21 3 14.284 3 6V5z"></path>
                        </svg>
                        222-309-0800
                      </div>
                      <div class="flex items-center text-green-600">
                        <div class="w-2 h-2 bg-green-500 rounded-full mr-1"></div>
                        Abierto
                      </div>
                    </div>
                  </div>
                </div>
              </div>

              <!-- VECSA Pachuca -->
              <div class="location-item bg-white rounded-xl p-4 shadow-md hover:shadow-xl transition-all duration-300 cursor-pointer border border-gray-100 hover:border-blue-200 group" 
                   data-location="vecsa-pachuca" data-lat="20.1011" data-lng="-98.7591" data-filter="otros">
                <div class="flex items-start space-x-3">
                  <div class="relative">
                    <img src="assets/images/pachuca.jpg" alt="VECSA Pachuca" class="w-16 h-16 object-cover rounded-lg flex-shrink-0 group-hover:scale-105 transition-transform">
                    <div class="absolute -top-1 -right-1 w-4 h-4 bg-green-500 rounded-full border-2 border-white"></div>
                  </div>
                  <div class="flex-1 min-w-0">
                    <div class="flex items-center justify-between mb-1">
                      <h4 class="text-lg font-bold text-gray-900 group-hover:text-blue-600 transition-colors">VECSA Pachuca</h4>
                      <span class="text-xs bg-green-100 text-green-700 px-2 py-1 rounded-full font-medium">Hidalgo</span>
                    </div>
                    <p class="text-sm text-gray-600 mb-2 line-clamp-1">Vial La Paz 113, Col. Adolfo López Mateos</p>
                    <div class="flex items-center justify-between text-xs text-gray-500">
                      <div class="flex items-center">
                        <svg class="w-3 h-3 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 5a2 2 0 012-2h3.28a1 1 0 01.948.684l1.498 4.493a1 1 0 01-.502 1.21l-2.257 1.13a11.042 11.042 0 005.516 5.516l1.13-2.257a1 1 0 011.21-.502l4.493 1.498a1 1 0 01.684.949V19a2 2 0 01-2 2h-1C9.716 21 3 14.284 3 6V5z"></path>
                        </svg>
                        771-717-2554
                      </div>
                      <div class="flex items-center text-green-600">
                        <div class="w-2 h-2 bg-green-500 rounded-full mr-1"></div>
                        Abierto
                      </div>
                    </div>
                  </div>
                </div>
              </div>

              <!-- VECSA Veracruz -->
              <div class="location-item bg-white rounded-xl p-4 shadow-md hover:shadow-xl transition-all duration-300 cursor-pointer border border-gray-100 hover:border-blue-200 group" 
                   data-location="vecsa-veracruz" data-lat="19.1738" data-lng="-96.1342" data-filter="otros">
                <div class="flex items-start space-x-3">
                  <div class="relative">
                    <img src="assets/images/veracruz.jpg" alt="VECSA Veracruz" class="w-16 h-16 object-cover rounded-lg flex-shrink-0 group-hover:scale-105 transition-transform">
                    <div class="absolute -top-1 -right-1 w-4 h-4 bg-orange-500 rounded-full border-2 border-white"></div>
                  </div>
                  <div class="flex-1 min-w-0">
                    <div class="flex items-center justify-between mb-1">
                      <h4 class="text-lg font-bold text-gray-900 group-hover:text-blue-600 transition-colors">VECSA Veracruz</h4>
                      <span class="text-xs bg-orange-100 text-orange-700 px-2 py-1 rounded-full font-medium">Veracruz</span>
                    </div>
                    <p class="text-sm text-gray-600 mb-2 line-clamp-1">Carretera Federal Boca del Río – Antón de Lizardo No. 4450</p>
                    <div class="flex items-center justify-between text-xs text-gray-500">
                      <div class="flex items-center">
                        <svg class="w-3 h-3 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 5a2 2 0 012-2h3.28a1 1 0 01.948.684l1.498 4.493a1 1 0 01-.502 1.21l-2.257 1.13a11.042 11.042 0 005.516 5.516l1.13-2.257a1 1 0 011.21-.502l4.493 1.498a1 1 0 01.684.949V19a2 2 0 01-2 2h-1C9.716 21 3 14.284 3 6V5z"></path>
                        </svg>
                        229-923-6030
                      </div>
                      <div class="flex items-center text-green-600">
                        <div class="w-2 h-2 bg-green-500 rounded-full mr-1"></div>
                        Abierto
                      </div>
                    </div>
                  </div>
                </div>
              </div>

              <!-- VECSA Oaxaca -->
              <div class="location-item bg-white rounded-xl p-4 shadow-md hover:shadow-xl transition-all duration-300 cursor-pointer border border-gray-100 hover:border-blue-200 group" 
                   data-location="vecsa-oaxaca" data-lat="17.0732" data-lng="-96.7266" data-filter="otros">
                <div class="flex items-start space-x-3">
                  <div class="relative">
                    <img src="assets/images/oaxaca.jpg" alt="VECSA Oaxaca" class="w-16 h-16 object-cover rounded-lg flex-shrink-0 group-hover:scale-105 transition-transform">
                    <div class="absolute -top-1 -right-1 w-4 h-4 bg-purple-500 rounded-full border-2 border-white"></div>
                  </div>
                  <div class="flex-1 min-w-0">
                    <div class="flex items-center justify-between mb-1">
                      <h4 class="text-lg font-bold text-gray-900 group-hover:text-blue-600 transition-colors">VECSA Oaxaca</h4>
                      <span class="text-xs bg-purple-100 text-purple-700 px-2 py-1 rounded-full font-medium">Oaxaca</span>
                    </div>
                    <p class="text-sm text-gray-600 mb-2 line-clamp-1">Av. Universidad No. 400, Col. Ex hacienda Candiani</p>
                    <div class="flex items-center justify-between text-xs text-gray-500">
                      <div class="flex items-center">
                        <svg class="w-3 h-3 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 5a2 2 0 012-2h3.28a1 1 0 01.948.684l1.498 4.493a1 1 0 01-.502 1.21l-2.257 1.13a11.042 11.042 0 005.516 5.516l1.13-2.257a1 1 0 011.21-.502l4.493 1.498a1 1 0 01.684.949V19a2 2 0 01-2 2h-1C9.716 21 3 14.284 3 6V5z"></path>
                        </svg>
                        951-144-7955
                      </div>
                      <div class="flex items-center text-green-600">
                        <div class="w-2 h-2 bg-green-500 rounded-full mr-1"></div>
                        Abierto
                      </div>
                    </div>
                  </div>
                </div>
              </div>

              <!-- VECSA Balderrama -->
              <div class="location-item bg-white rounded-xl p-4 shadow-md hover:shadow-xl transition-all duration-300 cursor-pointer border border-gray-100 hover:border-blue-200 group" 
                   data-location="vecsa-balderrama" data-lat="19.0326" data-lng="-98.2280" data-filter="puebla">
                <div class="flex items-start space-x-3">
                  <div class="relative">
                    <img src="assets/images/puebla-balderrma.jpg" alt="VECSA Balderrama" class="w-16 h-16 object-cover rounded-lg flex-shrink-0 group-hover:scale-105 transition-transform">
                    <div class="absolute -top-1 -right-1 w-4 h-4 bg-blue-600 rounded-full border-2 border-white"></div>
                  </div>
                  <div class="flex-1 min-w-0">
                    <div class="flex items-center justify-between mb-1">
                      <h4 class="text-lg font-bold text-gray-900 group-hover:text-blue-600 transition-colors">Chevrolet Balderrama</h4>
                      <span class="text-xs bg-blue-100 text-blue-700 px-2 py-1 rounded-full font-medium">Puebla</span>
                    </div>
                    <p class="text-sm text-gray-600 mb-2 line-clamp-1">Av. Hermanos Serdán No. 241, Col. Aquiles Serdán</p>
                    <div class="flex items-center justify-between text-xs text-gray-500">
                      <div class="flex items-center">
                        <svg class="w-3 h-3 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 5a2 2 0 012-2h3.28a1 1 0 01.948.684l1.498 4.493a1 1 0 01-.502 1.21l-2.257 1.13a11.042 11.042 0 005.516 5.516l1.13-2.257a1 1 0 011.21-.502l4.493 1.498a1 1 0 01.684.949V19a2 2 0 01-2 2h-1C9.716 21 3 14.284 3 6V5z"></path>
                        </svg>
                        222-303-9900
                      </div>
                      <div class="flex items-center text-green-600">
                        <div class="w-2 h-2 bg-green-500 rounded-full mr-1"></div>
                        Abierto
                      </div>
                    </div>
                  </div>
                </div>
              </div>

              <!-- ABCars Puebla -->
              <div class="location-item bg-white rounded-xl p-4 shadow-md hover:shadow-xl transition-all duration-300 cursor-pointer border border-gray-100 hover:border-blue-200 group" 
                   data-location="abcars-puebla" data-lat="19.0414" data-lng="-98.2063" data-filter="puebla">
                <div class="flex items-start space-x-3">
                  <div class="relative">
                    <img src="assets/images/abcars.jpeg" alt="ABCars Puebla" class="w-16 h-16 object-cover rounded-lg flex-shrink-0 group-hover:scale-105 transition-transform">
                    <div class="absolute -top-1 -right-1 w-4 h-4 bg-blue-600 rounded-full border-2 border-white"></div>
                  </div>
                  <div class="flex-1 min-w-0">
                    <div class="flex items-center justify-between mb-1">
                      <h4 class="text-lg font-bold text-gray-900 group-hover:text-blue-600 transition-colors">ABCars Puebla</h4>
                      <span class="text-xs bg-blue-100 text-blue-700 px-2 py-1 rounded-full font-medium">Puebla</span>
                    </div>
                    <p class="text-sm text-gray-600 mb-2 line-clamp-1">Blvrd Esteban de Antuñano 1314, Obrera Textil José Abascal</p>
                    <div class="flex items-center text-xs text-gray-500 mb-1">
                      <div class="flex items-center">
                        <svg class="w-3 h-3 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"></path>
                        </svg>
                        contacto@abcars.mx
                      </div>
                    </div>
                    <div class="flex items-center justify-between text-xs text-gray-500">
                      <div class="flex items-center">
                        <svg class="w-3 h-3 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 5a2 2 0 012-2h3.28a1 1 0 01.948.684l1.498 4.493a1 1 0 01-.502 1.21l-2.257 1.13a11.042 11.042 0 005.516 5.516l1.13-2.257a1 1 0 011.21-.502l4.493 1.498a1 1 0 01.684.949V19a2 2 0 01-2 2h-1C9.716 21 3 14.284 3 6V5z"></path>
                        </svg>
                        222-303-9910
                      </div>
                      <div class="flex items-center text-green-600">
                        <div class="w-2 h-2 bg-green-500 rounded-full mr-1"></div>
                        Abierto
                      </div>
                    </div>
                  </div>
                </div>
              </div>
            </div>
            
            <!-- Mobile Location Info Cards -->
            <div class="mobile-only" id="mobile-location-info">
              <!-- This will be populated by JavaScript based on selection -->
            </div>
          </div>

          <!-- Map -->
          <div class="space-y-4">
            <h3 class="text-2xl font-bold text-gray-900 mb-6">Ubicación</h3>
            <div class="bg-gray-200 h-96 flex items-center justify-center relative overflow-hidden">
              <div id="map" class="w-full h-full"></div>
              <div class="absolute inset-0 bg-gray-200 flex items-center justify-center" id="map-placeholder">
                <div class="text-center">
                  <svg class="w-16 h-16 text-gray-400 mx-auto mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"></path>
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z"></path>
                  </svg>
                  <p class="text-gray-600">Selecciona una sucursal para ver su ubicación</p>
                </div>
              </div>
            </div>
            
            <!-- Selected Location Info -->
            <div id="selected-location-info" class="bg-gray-900 rounded-xl p-6 hidden border border-gray-700">
              <div class="flex items-start space-x-4 mb-4">
                <div class="w-12 h-12 bg-gray-800 rounded-full flex items-center justify-center flex-shrink-0 border border-gray-600">
                  <svg class="w-6 h-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"></path>
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z"></path>
                  </svg>
                </div>
                <div class="flex-1">
                  <h4 class="text-xl font-bold text-white mb-1" id="selected-name">HUB Serdán</h4>
                  <p class="text-gray-300 mb-2" id="selected-address">Blvd. Hermanos Serdán 241, Puebla</p>
                  <div class="flex items-center text-sm text-gray-400 mb-2">
                    <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                      <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 5a2 2 0 012-2h3.28a1 1 0 01.948.684l1.498 4.493a1 1 0 01-.502 1.21l-2.257 1.13a11.042 11.042 0 005.516 5.516l1.13-2.257a1 1 0 011.21-.502l4.493 1.498a1 1 0 01.684.949V19a2 2 0 01-2 2h-1C9.716 21 3 14.284 3 6V5z"></path>
                    </svg>
                    <span id="selected-phone">222-555-0123</span>
                  </div>
                  <div class="flex items-center text-sm text-green-400">
                    <div class="w-2 h-2 bg-green-400 rounded-full mr-2"></div>
                    <span>Abierto • Lun - Sáb: 9:00 AM - 7:00 PM</span>
                  </div>
                </div>
              </div>
              <div class="grid grid-cols-1 sm:grid-cols-3 gap-3">
                <a href="#" id="directions-link" target="_blank" class="border border-gray-600 text-white py-3 px-4 rounded-lg text-center hover:bg-gray-800 hover:border-gray-500 transition-colors font-medium flex items-center justify-center">
                  <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 20l-5.447-2.724A1 1 0 013 16.382V5.618a1 1 0 011.447-.894L9 7m0 13l6-3m-6 3V7m6 10l4.553 2.276A1 1 0 0021 18.382V7.618a1 1 0 00-.553-.894L15 4m0 13V4m0 0L9 7"></path>
                  </svg>
                  Cómo llegar
                </a>
                <a href="#" id="call-link" class="border border-gray-600 text-white py-3 px-4 rounded-lg text-center hover:bg-gray-800 hover:border-gray-500 transition-colors font-medium flex items-center justify-center">
                  <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 5a2 2 0 012-2h3.28a1 1 0 01.948.684l1.498 4.493a1 1 0 01-.502 1.21l-2.257 1.13a11.042 11.042 0 005.516 5.516l1.13-2.257a1 1 0 011.21-.502l4.493 1.498a1 1 0 01.684.949V19a2 2 0 01-2 2h-1C9.716 21 3 14.284 3 6V5z"></path>
                  </svg>
                  Llamar
                </a>
                <a href="https://wa.me/522214316725" target="_blank" class="border border-gray-600 text-white py-3 px-4 rounded-lg text-center hover:bg-gray-800 hover:border-gray-500 transition-colors font-medium flex items-center justify-center">
                  <svg class="w-4 h-4 mr-2" fill="currentColor" viewBox="0 0 24 24">
                    <path d="M17.472 14.382c-.297-.149-1.758-.867-2.03-.967-.273-.099-.471-.148-.67.15-.197.297-.767.966-.94 1.164-.173.199-.347.223-.644.075-.297-.15-1.255-.463-2.39-1.475-.883-.788-1.48-1.761-1.653-2.059-.173-.297-.018-.458.13-.606.134-.133.298-.347.446-.52.149-.174.198-.298.298-.497.099-.198.05-.371-.025-.52-.075-.149-.669-1.612-.916-2.207-.242-.579-.487-.5-.669-.51-.173-.008-.371-.01-.57-.01-.198 0-.52.074-.792.372-.272.297-1.04 1.016-1.04 2.479 0 1.462 1.065 2.875 1.213 3.074.149.198 2.096 3.2 5.077 4.487.709.306 1.262.489 1.694.625.712.227 1.36.195 1.871.118.571-.085 1.758-.719 2.006-1.413.248-.694.248-1.289.173-1.413-.074-.124-.272-.198-.57-.347m-5.421 7.403h-.004a9.87 9.87 0 01-5.031-1.378l-.361-.214-3.741.982.998-3.648-.235-.374a9.86 9.86 0 01-1.51-5.26c.001-5.45 4.436-9.884 9.888-9.884 2.64 0 5.122 1.03 6.988 2.898a9.825 9.825 0 012.893 6.994c-.003 5.45-4.437 9.884-9.885 9.884m8.413-18.297A11.815 11.815 0 0012.05 0C5.495 0 .16 5.335.157 11.892c0 2.096.547 4.142 1.588 5.945L.057 24l6.305-1.654a11.882 11.882 0 005.683 1.448h.005c6.554 0 11.89-5.335 11.893-11.893A11.821 11.821 0 0020.885 3.515"/>
                  </svg>
                  WhatsApp
                </a>
              </div>
            </div>
          </div>
        </div>
      </div>
    </section>
  </main>

  <!-- Floating Action Buttons (Mobile Only) -->
  <div class="fixed bottom-6 right-4 z-50 md:hidden">
    <div class="flex flex-col space-y-3">
      <!-- WhatsApp Button -->
      <a href="https://wa.me/522214316725" target="_blank" 
         class="floating-btn bg-green-500 hover:bg-green-600 text-white transition-all duration-300 group"
         title="Contactar por WhatsApp">
        <svg class="w-6 h-6 group-hover:scale-110 transition-transform" fill="currentColor" viewBox="0 0 24 24">
          <path d="M17.472 14.382c-.297-.149-1.758-.867-2.03-.967-.273-.099-.471-.148-.67.15-.197.297-.767.966-.94 1.164-.173.199-.347.223-.644.075-.297-.15-1.255-.463-2.39-1.475-.883-.788-1.48-1.761-1.653-2.059-.173-.297-.018-.458.13-.606.134-.133.298-.347.446-.52.149-.174.198-.298.298-.497.099-.198.05-.371-.025-.52-.075-.149-.669-1.612-.916-2.207-.242-.579-.487-.5-.669-.51-.173-.008-.371-.01-.57-.01-.198 0-.52.074-.792.372-.272.297-1.04 1.016-1.04 2.479 0 1.462 1.065 2.875 1.213 3.074.149.198 2.096 3.2 5.077 4.487.709.306 1.262.489 1.694.625.712.227 1.36.195 1.871.118.571-.085 1.758-.719 2.006-1.413.248-.694.248-1.289.173-1.413-.074-.124-.272-.198-.57-.347m-5.421 7.403h-.004a9.87 9.87 0 01-5.031-1.378l-.361-.214-3.741.982.998-3.648-.235-.374a9.86 9.86 0 01-1.51-5.26c.001-5.45 4.436-9.884 9.888-9.884 2.64 0 5.122 1.03 6.988 2.898a9.825 9.825 0 012.893 6.994c-.003 5.45-4.437 9.884-9.885 9.884m8.413-18.297A11.815 11.815 0 0012.05 0C5.495 0 .16 5.335.157 11.892c0 2.096.547 4.142 1.588 5.945L.057 24l6.305-1.654a11.882 11.882 0 005.683 1.448h.005c6.554 0 11.89-5.335 11.893-11.893A11.821 11.821 0 0020.885 3.515"/>
        </svg>
      </a>

      <!-- Phone Button -->
      <a href="tel:+522214316725" 
         class="floating-btn bg-blue-600 hover:bg-blue-700 text-white transition-all duration-300 group"
         title="Llamar ahora">
        <svg class="w-5 h-5 group-hover:scale-110 transition-transform" fill="none" stroke="currentColor" viewBox="0 0 24 24">
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 5a2 2 0 012-2h3.28a1 1 0 01.948.684l1.498 4.493a1 1 0 01-.502 1.21l-2.257 1.13a11.042 11.042 0 005.516 5.516l1.13-2.257a1 1 0 011.21-.502l4.493 1.498a1 1 0 01.684.949V19a2 2 0 01-2 2h-1C9.716 21 3 14.284 3 6V5z"></path>
        </svg>
      </a>

      <!-- Email Button -->
      <a href="mailto:contacto@grupovecsa.com" 
         class="floating-btn bg-gray-700 hover:bg-gray-800 text-white transition-all duration-300 group"
         title="Enviar email">
        <svg class="w-5 h-5 group-hover:scale-110 transition-transform" fill="none" stroke="currentColor" viewBox="0 0 24 24">
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"></path>
        </svg>
      </a>
    </div>
  </div>

  <!-- Floating Action Buttons (Desktop Only) -->
  <div class="fixed bottom-6 right-4 z-50 hidden md:block">
    <div class="flex flex-col space-y-3">
      <!-- WhatsApp Button -->
      <a href="https://wa.me/522214316725" target="_blank" 
         class="floating-btn bg-green-500 hover:bg-green-600 text-white transition-all duration-300 group"
         title="Contactar por WhatsApp">
        <svg class="w-6 h-6 group-hover:scale-110 transition-transform" fill="currentColor" viewBox="0 0 24 24">
          <path d="M17.472 14.382c-.297-.149-1.758-.867-2.03-.967-.273-.099-.471-.148-.67.15-.197.297-.767.966-.94 1.164-.173.199-.347.223-.644.075-.297-.15-1.255-.463-2.39-1.475-.883-.788-1.48-1.761-1.653-2.059-.173-.297-.018-.458.13-.606.134-.133.298-.347.446-.52.149-.174.198-.298.298-.497.099-.198.05-.371-.025-.52-.075-.149-.669-1.612-.916-2.207-.242-.579-.487-.5-.669-.51-.173-.008-.371-.01-.57-.01-.198 0-.52.074-.792.372-.272.297-1.04 1.016-1.04 2.479 0 1.462 1.065 2.875 1.213 3.074.149.198 2.096 3.2 5.077 4.487.709.306 1.262.489 1.694.625.712.227 1.36.195 1.871.118.571-.085 1.758-.719 2.006-1.413.248-.694.248-1.289.173-1.413-.074-.124-.272-.198-.57-.347m-5.421 7.403h-.004a9.87 9.87 0 01-5.031-1.378l-.361-.214-3.741.982.998-3.648-.235-.374a9.86 9.86 0 01-1.51-5.26c.001-5.45 4.436-9.884 9.888-9.884 2.64 0 5.122 1.03 6.988 2.898a9.825 9.825 0 012.893 6.994c-.003 5.45-4.437 9.884-9.885 9.884m8.413-18.297A11.815 11.815 0 0012.05 0C5.495 0 .16 5.335.157 11.892c0 2.096.547 4.142 1.588 5.945L.057 24l6.305-1.654a11.882 11.882 0 005.683 1.448h.005c6.554 0 11.89-5.335 11.893-11.893A11.821 11.821 0 0020.885 3.515"/>
        </svg>
      </a>
    </div>
  </div>

  <!-- Disclaimer Section -->
  <section class="bg-gray-900 py-8">
    <div class="container mx-auto px-4 md:px-6 lg:px-8">
      <div class="text-center mb-6">
        <h3 class="text-lg font-semibold text-white mb-4">Aviso Importante</h3>
      </div>
      <div class="text-xs text-gray-300 leading-relaxed space-y-3">
        <p>
          <strong>1. Ofertas y Promociones:</strong> Las ofertas incluyen descuentos especiales válidos únicamente durante el período promocional indicado. Aplican restricciones y condiciones específicas. Los precios mostrados no incluyen impuestos, gastos de escrituración, placas, verificación ni seguros. Las ofertas de financiamiento están sujetas a aprobación crediticia.
        </p>
        <p>
          <strong>2. Disponibilidad:</strong> Los vehículos mostrados están sujetos a disponibilidad. Las imágenes pueden no corresponder exactamente al modelo disponible. Los colores, equipamiento y características pueden variar según el modelo y año.
        </p>
        <p>
          <strong>3. Garantías:</strong> Todos los vehículos BMW y MINI incluyen garantía de fábrica. Los términos específicos de garantía varían según el modelo. Los vehículos seminuevos incluyen garantía limitada sujeta a términos y condiciones específicas.
        </p>
        <p>
          <strong>4. Información Legal:</strong> Grupo VECSA es distribuidor autorizado BMW, MINI, Motorrad, Chevrolet y ABCars en México. Todos los servicios están regulados por las leyes mexicanas aplicables. Para más información sobre términos, condiciones y políticas, contacte a nuestro equipo de ventas.
        </p>
      </div>
    </div>
  </section>

  <!-- Footer -->
  <footer class="bg-gray-900 text-white">
    <div class="container mx-auto px-4 md:px-6 lg:px-8 py-12">
      <!-- Main Footer Content -->
      <div class="grid grid-cols-1 lg:grid-cols-4 gap-8 mb-8">
        <!-- Company Info -->
        <div class="lg:col-span-1">
          <h3 class="text-xl font-bold mb-4">GRUPO VECSA</h3>
          <p class="text-gray-300 text-sm mb-6 leading-relaxed">
            Más de 20 años distribuyendo vehículos premium BMW y MINI en México con excelencia y compromiso.
          </p>
          <!-- Social Media -->
          <div class="flex space-x-4">
            <!-- Facebook -->
            <a href="https://www.facebook.com/grupovecsa" target="_blank" class="w-10 h-10 bg-gray-800 rounded-full flex items-center justify-center text-gray-400 hover:text-white hover:bg-blue-600 transition-all duration-300">
              <svg class="w-5 h-5" fill="currentColor" viewBox="0 0 24 24">
                <path d="M24 12.073c0-6.627-5.373-12-12-12s-12 5.373-12 12c0 5.99 4.388 10.954 10.125 11.854v-8.385H7.078v-3.47h3.047V9.43c0-3.007 1.792-4.669 4.533-4.669 1.312 0 2.686.235 2.686.235v2.953H15.83c-1.491 0-1.956.925-1.956 1.874v2.25h3.328l-.532 3.47h-2.796v8.385C19.612 23.027 24 18.062 24 12.073z"/>
              </svg>
            </a>
            <!-- Instagram -->
            <a href="https://www.instagram.com/grupovecsa" target="_blank" class="w-10 h-10 bg-gray-800 rounded-full flex items-center justify-center text-gray-400 hover:text-white hover:bg-gradient-to-r hover:from-purple-500 hover:to-pink-500 transition-all duration-300">
              <svg class="w-5 h-5" fill="currentColor" viewBox="0 0 24 24">
                <path d="M12 2.163c3.204 0 3.584.012 4.85.07 3.252.148 4.771 1.691 4.919 4.919.058 1.265.069 1.645.069 4.849 0 3.205-.012 3.584-.069 4.849-.149 3.225-1.664 4.771-4.919 4.919-1.266.058-1.644.07-4.85.07-3.204 0-3.584-.012-4.849-.07-3.26-.149-4.771-1.699-4.919-4.92-.058-1.265-.07-1.644-.07-4.849 0-3.204.013-3.583.07-4.849.149-3.227 1.664-4.771 4.919-4.919 1.266-.057 1.645-.069 4.849-.069zm0-2.163c-3.259 0-3.667.014-4.947.072-4.358.2-6.78 2.618-6.98 6.98-.059 1.281-.073 1.689-.073 4.948 0 3.259.014 3.668.072 4.948.2 4.358 2.618 6.78 6.98 6.98 1.281.058 1.689.072 4.948.072 3.259 0 3.668-.014 4.948-.072 4.354-.2 6.782-2.618 6.979-6.98.059-1.28.073-1.689.073-4.948 0-3.259-.014-3.667-.072-4.947-.196-4.354-2.617-6.78-6.979-6.98-1.281-.059-1.69-.073-4.949-.073zm0 5.838c-3.403 0-6.162 2.759-6.162 6.162s2.759 6.163 6.162 6.163 6.162-2.759 6.162-6.163c0-3.403-2.759-6.162-6.162-6.162zm0 10.162c-2.209 0-4-1.79-4-4 0-2.209 1.791-4 4-4s4 1.791 4 4c0 2.21-1.791 4-4 4zm6.406-11.845c-.796 0-1.441.645-1.441 1.44s.645 1.44 1.441 1.44c.795 0 1.439-.645 1.439-1.44s-.644-1.44-1.439-1.44z"/>
              </svg>
            </a>
            <!-- LinkedIn -->
            <a href="https://www.linkedin.com/company/grupovecsa" target="_blank" class="w-10 h-10 bg-gray-800 rounded-full flex items-center justify-center text-gray-400 hover:text-white hover:bg-blue-700 transition-all duration-300">
              <svg class="w-5 h-5" fill="currentColor" viewBox="0 0 24 24">
                <path d="M20.447 20.452h-3.554v-5.569c0-1.328-.027-3.037-1.852-3.037-1.853 0-2.136 1.445-2.136 2.939v5.667H9.351V9h3.414v1.561h.046c.477-.9 1.637-1.85 3.37-1.85 3.601 0 4.267 2.37 4.267 5.455v6.286zM5.337 7.433c-1.144 0-2.063-.926-2.063-2.065 0-1.138.92-2.063 2.063-2.063 1.14 0 2.064.925 2.064 2.063 0 1.139-.925 2.065-2.064 2.065zm1.782 13.019H3.555V9h3.564v11.452zM22.225 0H1.771C.792 0 0 .774 0 1.729v20.542C0 23.227.792 24 1.771 24h20.451C23.2 24 24 23.227 24 22.271V1.729C24 .774 23.2 0 22.222 0h.003z"/>
              </svg>
            </a>
          </div>
        </div>

        <!-- Vehículos -->
        <div>
          <h4 class="text-lg font-semibold mb-4 text-white">Vehículos</h4>
          <ul class="space-y-3 text-sm">
            <li><a href="https://grupovecsa.com/inventory/compra-tu-auto/Nuevo/Bmw/sin-lineas/sin-modelos/sin-carrocerias/sin-versiones/sin-anios/10000/60000000/sin-estados/sin-busqueda/sin-transmisiones/sin-colores/sin-colores/ninguno/1" target="_blank" class="text-gray-300 hover:text-blue-400 transition-colors flex items-center group">
              <span class="w-1 h-1 bg-blue-400 rounded-full mr-3 opacity-0 group-hover:opacity-100 transition-opacity"></span>
              Vehículos BMW
            </a></li>
            <li><a href="https://grupovecsa.com/inventory/compra-tu-auto/Nuevo-Demo/Mini/sin-lineas/sin-modelos/sin-carrocerias/sin-versiones/sin-anios/1000/60000000/sin-estados/sin-busqueda/sin-transmisiones/sin-colores/sin-colores/ninguno/1" target="_blank" class="text-gray-300 hover:text-blue-400 transition-colors flex items-center group">
              <span class="w-1 h-1 bg-blue-400 rounded-full mr-3 opacity-0 group-hover:opacity-100 transition-opacity"></span>
              Vehículos MINI
            </a></li>
            <li><a href="https://grupovecsa.com/inventory/compra-tu-auto/Nuevo-Demo/Motorrad/sin-lineas/sin-modelos/sin-carrocerias/sin-versiones/sin-anios/10000/60000000/sin-estados/sin-busqueda/sin-transmisiones/sin-colores/sin-colores/ninguno/1" target="_blank" class="text-gray-300 hover:text-blue-400 transition-colors flex items-center group">
              <span class="w-1 h-1 bg-blue-400 rounded-full mr-3 opacity-0 group-hover:opacity-100 transition-opacity"></span>
              BMW Motorrad
            </a></li>
            <li><a href="https://grupovecsa.com/inventory/compra-tu-auto/Seminuevo/sin-marcas/sin-lineas/sin-modelos/sin-carrocerias/sin-versiones/sin-anios/10000/60000000/sin-estados/sin-busqueda/sin-transmisiones/sin-colores/sin-colores/ninguno/1" target="_blank" class="text-gray-300 hover:text-blue-400 transition-colors flex items-center group">
              <span class="w-1 h-1 bg-blue-400 rounded-full mr-3 opacity-0 group-hover:opacity-100 transition-opacity"></span>
              Seminuevos Ejecutivos
            </a></li>
            <li><a href="https://abcars.mx/compra-tu-auto/sin-marcas/sin-modelos/sin-anios/100000/5000000/sin-carrocerias/sin-estados/sin-busqueda/sin-transmisiones/1" target="_blank" class="text-gray-300 hover:text-blue-400 transition-colors flex items-center group">
              <span class="w-1 h-1 bg-blue-400 rounded-full mr-3 opacity-0 group-hover:opacity-100 transition-opacity"></span>
              ABCars Seminuevos
            </a></li>
          </ul>
        </div>

        <!-- Secciones -->
        <div>
          <h4 class="text-lg font-semibold mb-4 text-white">Secciones</h4>
          <ul class="space-y-3 text-sm">
            <li><a href="https://vecsaboutique.com/" target="_blank" class="text-gray-300 hover:text-blue-400 transition-colors flex items-center group">
              <span class="w-1 h-1 bg-blue-400 rounded-full mr-3 opacity-0 group-hover:opacity-100 transition-opacity"></span>
              VECSA Boutique
            </a></li>
            <li><a href="https://grupovecsa.com/inventory/auth/login" target="_blank" class="text-gray-300 hover:text-blue-400 transition-colors flex items-center group">
              <span class="w-1 h-1 bg-blue-400 rounded-full mr-3 opacity-0 group-hover:opacity-100 transition-opacity"></span>
              VECSA Rewards
            </a></li>
            <li><a href="https://vecsaexperience.com/" target="_blank" class="text-gray-300 hover:text-blue-400 transition-colors flex items-center group">
              <span class="w-1 h-1 bg-blue-400 rounded-full mr-3 opacity-0 group-hover:opacity-100 transition-opacity"></span>
              VECSA Experience
            </a></li>
            <li><a href="https://grupovecsa.com/inventory/carcare" target="_blank" class="text-gray-300 hover:text-blue-400 transition-colors flex items-center group">
              <span class="w-1 h-1 bg-blue-400 rounded-full mr-3 opacity-0 group-hover:opacity-100 transition-opacity"></span>
              Car Care
            </a></li>
            <li><a href="https://grupovecsa.com/inventory/promotions" target="_blank" class="text-gray-300 hover:text-blue-400 transition-colors flex items-center group">
              <span class="w-1 h-1 bg-blue-400 rounded-full mr-3 opacity-0 group-hover:opacity-100 transition-opacity"></span>
              Promociones
            </a></li>
          </ul>
        </div>

        <!-- Legales -->
        <div>
          <h4 class="text-lg font-semibold mb-4 text-white">Legales</h4>
          <ul class="space-y-3 text-sm">
            <li><a href="/aviso-privacidad" class="text-gray-300 hover:text-blue-400 transition-colors flex items-center group">
              <span class="w-1 h-1 bg-blue-400 rounded-full mr-3 opacity-0 group-hover:opacity-100 transition-opacity"></span>
              Aviso de Privacidad
            </a></li>
            <li><a href="/condiciones-uso" class="text-gray-300 hover:text-blue-400 transition-colors flex items-center group">
              <span class="w-1 h-1 bg-blue-400 rounded-full mr-3 opacity-0 group-hover:opacity-100 transition-opacity"></span>
              Condiciones de Uso
            </a></li>
            <li><a href="/politicas-devolucion" class="text-gray-300 hover:text-blue-400 transition-colors flex items-center group">
              <span class="w-1 h-1 bg-blue-400 rounded-full mr-3 opacity-0 group-hover:opacity-100 transition-opacity"></span>
              Políticas de Devolución
            </a></li>
            <li><a href="/uso-cookies" class="text-gray-300 hover:text-blue-400 transition-colors flex items-center group">
              <span class="w-1 h-1 bg-blue-400 rounded-full mr-3 opacity-0 group-hover:opacity-100 transition-opacity"></span>
              Uso de Cookies
            </a></li>
          </ul>
        </div>
      </div>

      <!-- Bottom Bar -->
      <div class="border-t border-gray-800 pt-6 flex flex-col md:flex-row justify-between items-center text-sm">
        <p class="text-gray-400 mb-4 md:mb-0">
          &copy; <?php echo date("Y"); ?> Grupo VECSA. Todos los derechos reservados.
        </p>
        <div class="flex flex-wrap justify-center md:justify-end gap-4">
          <span class="text-gray-500">Distribuidor autorizado BMW, MINI, Motorrad, Chevrolet y ABCars en México</span>
        </div>
      </div>
    </div>
  </footer>

  <script src="assets/js/scripts.js"></script>
  
  <!-- Google Maps API -->
  <script async defer src="https://maps.googleapis.com/maps/api/js?key=AIzaSyDGiPm--l8ZaCMWG9P02M_ZkPqgLCFTHhg&callback=initMap&libraries=marker"></script>
  <script>
    // Location Selector Functionality
    document.addEventListener('DOMContentLoaded', function() {
      // Location data
      const locationData = {
        'hub-serdan': {
          name: 'HUB Serdán',
          address: 'Blvd. Hermanos Serdán 241, Puebla',
          phone: '222-555-0123',
          state: 'Puebla',
          filter: 'puebla'
        },
        'vecsa-puebla': {
          name: 'VECSA Puebla',
          address: 'Vía Atlixcáyotl 5719, Angelópolis',
          phone: '222-555-0124',
          state: 'Puebla',
          filter: 'puebla'
        },
        'vecsa-pachuca': {
          name: 'VECSA Pachuca',
          address: 'Blvd. Luis Donaldo Colosio 1234',
          phone: '771-555-0125',
          state: 'Hidalgo',
          filter: 'otros'
        },
        'vecsa-veracruz': {
          name: 'VECSA Veracruz',
          address: 'Av. Ruiz Cortines 1500',
          phone: '229-555-0126',
          state: 'Veracruz',
          filter: 'otros'
        },
        'vecsa-oaxaca': {
          name: 'VECSA Oaxaca',
          address: 'Carretera Internacional 2000',
          phone: '951-555-0127',
          state: 'Oaxaca',
          filter: 'otros'
        },
        'vecsa-balderrama': {
          name: 'VECSA Balderrama',
          address: 'Blvd. Forjadores 3000',
          phone: '222-555-0128',
          state: 'Puebla',
          filter: 'puebla'
        }
      };

      // Initialize filter functionality
      const filterButtons = document.querySelectorAll('.location-filter-btn');
      const locationItems = document.querySelectorAll('.location-item');
      const mobileSelect = document.getElementById('mobile-location-select');
      const mobileLocationInfo = document.getElementById('mobile-location-info');
      const selectedLocationInfo = document.getElementById('selected-location-info');

      // Filter functionality
      filterButtons.forEach(button => {
        button.addEventListener('click', function() {
          const filter = this.dataset.filter;
          
          // Update active button
          filterButtons.forEach(btn => btn.classList.remove('active'));
          this.classList.add('active');
          
          // Filter locations
          locationItems.forEach(item => {
            const itemFilter = item.dataset.filter;
            if (filter === 'all' || itemFilter === filter) {
              item.style.display = 'block';
              setTimeout(() => {
                item.style.opacity = '1';
                item.style.transform = 'translateY(0)';
              }, 50);
            } else {
              item.style.opacity = '0';
              item.style.transform = 'translateY(-10px)';
              setTimeout(() => {
                item.style.display = 'none';
              }, 300);
            }
          });
        });
      });

      // Mobile select functionality
      mobileSelect.addEventListener('change', function() {
        const selectedValue = this.value;
        if (selectedValue) {
          selectLocation(selectedValue);
          showMobileLocationCard(selectedValue);
        } else {
          mobileLocationInfo.innerHTML = '';
          selectedLocationInfo.classList.add('hidden');
        }
      });

      // Desktop location item clicks
      locationItems.forEach(item => {
        item.addEventListener('click', function() {
          const locationId = this.dataset.location;
          const lat = parseFloat(this.dataset.lat);
          const lng = parseFloat(this.dataset.lng);
          
          // Update selected state
          locationItems.forEach(loc => loc.classList.remove('selected'));
          this.classList.add('selected');
          
          // Update map
          if (window.updateMapLocation && lat && lng) {
            window.updateMapLocation({ lat, lng }, locationId);
          }
          
          // Update location info
          updateSelectedLocationInfo(locationId);
        });
      });

      // Function to select location programmatically
      function selectLocation(locationId) {
        const locationItem = document.querySelector(`[data-location="${locationId}"]`);
        if (locationItem) {
          const lat = parseFloat(locationItem.dataset.lat);
          const lng = parseFloat(locationItem.dataset.lng);
          
          // Update selected state
          locationItems.forEach(loc => loc.classList.remove('selected'));
          locationItem.classList.add('selected');
          
          // Update map
          if (window.updateMapLocation && lat && lng) {
            window.updateMapLocation({ lat, lng }, locationId);
          }
          
          // Update location info
          updateSelectedLocationInfo(locationId);
        }
      }

      // Function to show mobile location card
      function showMobileLocationCard(locationId) {
        const data = locationData[locationId];
        if (!data) return;

        const cardHTML = `
          <div class="mobile-location-card bg-white rounded-xl p-4 shadow-lg border border-gray-200 mt-4">
            <div class="flex items-start space-x-3">
              <div class="w-12 h-12 bg-blue-600 rounded-full flex items-center justify-center flex-shrink-0">
                <svg class="w-6 h-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"></path>
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z"></path>
                </svg>
              </div>
              <div class="flex-1">
                <h4 class="text-lg font-bold text-gray-900 mb-1">${data.name}</h4>
                <p class="text-sm text-gray-600 mb-2">${data.address}</p>
                <div class="flex items-center text-sm text-gray-500 mb-2">
                  <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 5a2 2 0 012-2h3.28a1 1 0 01.948.684l1.498 4.493a1 1 0 01-.502 1.21l-2.257 1.13a11.042 11.042 0 005.516 5.516l1.13-2.257a1 1 0 011.21-.502l4.493 1.498a1 1 0 01.684.949V19a2 2 0 01-2 2h-1C9.716 21 3 14.284 3 6V5z"></path>
                  </svg>
                  ${data.phone}
                </div>
                <div class="flex items-center text-sm text-green-600 mb-3">
                  <div class="w-2 h-2 bg-green-500 rounded-full mr-2"></div>
                  <span>Abierto • Lun - Sáb: 9:00 AM - 7:00 PM</span>
                </div>
                <div class="grid grid-cols-2 gap-2">
                  <a href="https://maps.google.com/?q=${encodeURIComponent(data.address)}" target="_blank" 
                     class="bg-blue-600 text-white py-2 px-3 rounded-lg text-center hover:bg-blue-700 transition-colors font-medium text-sm flex items-center justify-center">
                    <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                      <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 20l-5.447-2.724A1 1 0 013 16.382V5.618a1 1 0 011.447-.894L9 7m0 13l6-3m-6 3V7m6 10l4.553 2.276A1 1 0 0021 18.382V7.618a1 1 0 00-.553-.894L15 4m0 13V4m0 0L9 7"></path>
                    </svg>
                    Cómo llegar
                  </a>
                  <a href="tel:${data.phone}" 
                     class="bg-green-600 text-white py-2 px-3 rounded-lg text-center hover:bg-green-700 transition-colors font-medium text-sm flex items-center justify-center">
                    <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                      <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 5a2 2 0 012-2h3.28a1 1 0 01.948.684l1.498 4.493a1 1 0 01-.502 1.21l-2.257 1.13a11.042 11.042 0 005.516 5.516l1.13-2.257a1 1 0 011.21-.502l4.493 1.498a1 1 0 01.684.949V19a2 2 0 01-2 2h-1C9.716 21 3 14.284 3 6V5z"></path>
                    </svg>
                    Llamar
                  </a>
                </div>
              </div>
            </div>
          </div>
        `;

        mobileLocationInfo.innerHTML = cardHTML;
      }

      // Function to update selected location info
      function updateSelectedLocationInfo(locationId) {
        const data = locationData[locationId];
        if (!data) return;

        const nameElement = document.getElementById('selected-name');
        const addressElement = document.getElementById('selected-address');
        const phoneElement = document.getElementById('selected-phone');
        const directionsLink = document.getElementById('directions-link');
        const callLink = document.getElementById('call-link');

        if (nameElement) nameElement.textContent = data.name;
        if (addressElement) addressElement.textContent = data.address;
        if (phoneElement) phoneElement.textContent = data.phone;
        if (directionsLink) directionsLink.href = `https://maps.google.com/?q=${encodeURIComponent(data.address)}`;
        if (callLink) callLink.href = `tel:${data.phone}`;

        selectedLocationInfo.classList.remove('hidden');
      }

      // Initialize with first location
      const firstLocation = document.querySelector('.location-item');
      if (firstLocation) {
        const locationId = firstLocation.dataset.location;
        selectLocation(locationId);
      }
    });

    // Initialize Google Map
    function initMap() {
      try {
        // Check if map element exists
        const mapElement = document.getElementById('map');
        if (!mapElement) {
          console.error('Map element not found');
          return;
        }

        // Default center (Puebla, Mexico)
        const defaultCenter = { lat: 19.0414, lng: -98.2063 };
        
        // Create map with mapId for Advanced Markers
        const map = new google.maps.Map(mapElement, {
          zoom: 12,
          center: defaultCenter,
          mapTypeId: 'roadmap',
          mapId: 'VECSA_MAP_ID', // Required for Advanced Markers
        styles: [
          {
            "featureType": "all",
            "elementType": "geometry.fill",
            "stylers": [{"weight": "2.00"}]
          },
          {
            "featureType": "all",
            "elementType": "geometry.stroke",
            "stylers": [{"color": "#9c9c9c"}]
          },
          {
            "featureType": "all",
            "elementType": "labels.text",
            "stylers": [{"visibility": "on"}]
          },
          {
            "featureType": "landscape",
            "elementType": "all",
            "stylers": [{"color": "#f2f2f2"}]
          },
          {
            "featureType": "landscape",
            "elementType": "geometry.fill",
            "stylers": [{"color": "#ffffff"}]
          },
          {
            "featureType": "landscape.man_made",
            "elementType": "geometry.fill",
            "stylers": [{"color": "#ffffff"}]
          },
          {
            "featureType": "poi",
            "elementType": "all",
            "stylers": [{"visibility": "off"}]
          },
          {
            "featureType": "road",
            "elementType": "all",
            "stylers": [{"saturation": -100}, {"lightness": 45}]
          },
          {
            "featureType": "road",
            "elementType": "geometry.fill",
            "stylers": [{"color": "#eeeeee"}]
          },
          {
            "featureType": "road",
            "elementType": "labels.text.fill",
            "stylers": [{"color": "#7b7b7b"}]
          },
          {
            "featureType": "road",
            "elementType": "labels.text.stroke",
            "stylers": [{"color": "#ffffff"}]
          },
          {
            "featureType": "road.highway",
            "elementType": "all",
            "stylers": [{"visibility": "simplified"}]
          },
          {
            "featureType": "road.arterial",
            "elementType": "labels.icon",
            "stylers": [{"visibility": "off"}]
          },
          {
            "featureType": "transit",
            "elementType": "all",
            "stylers": [{"visibility": "off"}]
          },
          {
            "featureType": "water",
            "elementType": "all",
            "stylers": [{"color": "#46bcec"}, {"visibility": "on"}]
          },
          {
            "featureType": "water",
            "elementType": "geometry.fill",
            "stylers": [{"color": "#c8d7d4"}]
          },
          {
            "featureType": "water",
            "elementType": "labels.text.fill",
            "stylers": [{"color": "#070707"}]
          },
          {
            "featureType": "water",
            "elementType": "labels.text.stroke",
            "stylers": [{"color": "#ffffff"}]
          }
        ]
      });

      // Store map globally for access from other functions
      window.vecsaMap = map;
      window.vecsaMarker = null;

      // Hide placeholder
      const placeholder = document.getElementById('map-placeholder');
      if (placeholder) {
        placeholder.style.display = 'none';
      }

      // Initialize with first location if available
      const firstLocationItem = document.querySelector('.location-item');
      if (firstLocationItem) {
        const locationId = firstLocationItem.dataset.location;
        const lat = parseFloat(firstLocationItem.dataset.lat);
        const lng = parseFloat(firstLocationItem.dataset.lng);
        
        if (lat && lng) {
          updateMapLocation({ lat, lng }, locationId);
        }
      }
    } catch (error) {
      console.error('Error initializing Google Maps:', error);
      // Show fallback message
      const mapElement = document.getElementById('map');
      if (mapElement) {
        mapElement.innerHTML = `
          <div class="flex items-center justify-center h-full bg-gray-100 rounded-lg">
            <div class="text-center p-8">
              <div class="text-gray-500 mb-2">
                <svg class="w-12 h-12 mx-auto" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"></path>
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z"></path>
                </svg>
              </div>
              <p class="text-gray-600">Mapa no disponible temporalmente</p>
              <p class="text-sm text-gray-500 mt-1">Selecciona una sucursal para ver su información</p>
            </div>
          </div>
        `;
      }
    }
    }

    // Function to update map location
    function updateMapLocation(position, locationId) {
      if (!window.vecsaMap) return;

      // Remove existing marker
      if (window.vecsaMarker) {
        window.vecsaMarker.map = null;
      }

      // Create marker content
      const markerContent = document.createElement('div');
      markerContent.innerHTML = `
        <div style="
          width: 40px; 
          height: 40px; 
          background: #2563eb; 
          border: 4px solid white; 
          border-radius: 50%; 
          display: flex; 
          align-items: center; 
          justify-content: center;
          box-shadow: 0 2px 8px rgba(0,0,0,0.3);
        ">
          <div style="
            width: 16px; 
            height: 16px; 
            background: white; 
            border-radius: 50%;
          "></div>
        </div>
      `;

      // Create new advanced marker
      try {
        window.vecsaMarker = new google.maps.marker.AdvancedMarkerElement({
          position: position,
          map: window.vecsaMap,
          title: getLocationName(locationId),
          content: markerContent
        });
      } catch (error) {
        // Fallback to classic marker if AdvancedMarkerElement is not available
        window.vecsaMarker = new google.maps.Marker({
          position: position,
          map: window.vecsaMap,
          title: getLocationName(locationId),
          icon: {
            url: 'data:image/svg+xml;charset=UTF-8,' + encodeURIComponent(`
              <svg width="40" height="40" viewBox="0 0 40 40" xmlns="http://www.w3.org/2000/svg">
                <circle cx="20" cy="20" r="18" fill="#2563eb" stroke="#ffffff" stroke-width="4"/>
                <circle cx="20" cy="20" r="8" fill="#ffffff"/>
              </svg>
            `),
            scaledSize: new google.maps.Size(40, 40),
            anchor: new google.maps.Point(20, 20)
          }
        });
      }

      // Center map on new location
      window.vecsaMap.setCenter(position);
      window.vecsaMap.setZoom(15);

      // Add info window
      const infoWindow = new google.maps.InfoWindow({
        content: `
          <div class="p-2">
            <h4 class="font-bold text-gray-900 mb-1">${getLocationName(locationId)}</h4>
            <p class="text-sm text-gray-600">${getLocationAddress(locationId)}</p>
          </div>
        `
      });

      window.vecsaMarker.addListener('click', () => {
        infoWindow.open(window.vecsaMap, window.vecsaMarker);
      });
    }

    // Helper functions to get location data
    function getLocationName(locationId) {
      const names = {
        'hub-serdan': 'HUB Serdán',
        'vecsa-puebla': 'VECSA Puebla',
        'vecsa-pachuca': 'VECSA Pachuca',
        'vecsa-veracruz': 'VECSA Veracruz',
        'vecsa-oaxaca': 'VECSA Oaxaca',
        'vecsa-balderrama': 'VECSA Balderrama'
      };
      return names[locationId] || 'VECSA Location';
    }

    function getLocationAddress(locationId) {
      const addresses = {
        'hub-serdan': 'Blvd. Hermanos Serdán 241, Puebla',
        'vecsa-puebla': 'Vía Atlixcáyotl 5719, Puebla',
        'vecsa-pachuca': 'Blvd. Luis Donaldo Colosio 1234, Pachuca',
        'vecsa-veracruz': 'Av. Ruiz Cortines 1500, Veracruz',
        'vecsa-oaxaca': 'Carretera Internacional 2000, Oaxaca',
        'vecsa-balderrama': 'Blvd. Forjadores 3000, Puebla'
      };
      return addresses[locationId] || 'VECSA Address';
    }

    // Make function available globally for location clicks
    window.updateMapLocation = updateMapLocation;
  </script>
</body>
</html> 