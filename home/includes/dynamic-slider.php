<?php
// Incluir funciones de slides
require_once __DIR__ . '/../admin/slides/functions.php';

// Obtener slides activos
$slides = getSlides();
$activeSlides = array_filter($slides, function($slide) {
    return $slide['active'];
});

// Ordenar por orden
usort($activeSlides, function($a, $b) {
    return $a['order'] - $b['order'];
});
?>

<!-- Hero Section with Dynamic Slider -->
<section class="relative h-[80vh] md:h-[85vh] w-full overflow-hidden">
  <!-- Slider Container -->
  <div id="hero-slider" class="relative w-full h-full">
    <?php foreach ($activeSlides as $index => $slide): ?>
    <div class="slide absolute inset-0 w-full h-full transition-opacity duration-1000 <?php echo $index === 0 ? 'opacity-100' : 'opacity-0'; ?>">
      <div class="absolute inset-0 slide-mobile-gradient z-10"></div>
      
      <!-- Desktop Image -->
      <img src="assets/images/slides/slide<?php echo $slide['order']; ?>/desktop/<?php echo htmlspecialchars($slide['desktop_image']); ?>" 
           alt="<?php echo htmlspecialchars($slide['title']); ?>" 
           class="hidden md:block w-full h-full object-cover object-right md:object-center">
      
      <!-- Mobile Image -->
      <img src="assets/images/slides/slide<?php echo $slide['order']; ?>/mobile/<?php echo htmlspecialchars($slide['mobile_image']); ?>" 
           alt="<?php echo htmlspecialchars($slide['title']); ?>" 
           class="block md:hidden w-full h-full object-cover object-center">
      
      <div class="absolute inset-0 z-20 slide-content-container">
        <!-- Mobile Layout -->
        <div class="h-full flex flex-col justify-between items-center text-center py-12 text-white md:hidden">
          <div class="container mx-auto px-4">
            <h1 class="text-4xl font-semibold [text-shadow:_0_2px_10px_rgb(0_0_0_/_60%)] mobile-title-spacing">
              <?php echo htmlspecialchars($slide['title']); ?>
            </h1>
          </div>
          <div class="container mx-auto px-4">
            <?php if ($slide['subtitle']): ?>
            <p class="text-lg mb-6 [text-shadow:_0_2px_8px_rgb(0_0_0_/_50%)]">
              <?php echo htmlspecialchars($slide['subtitle']); ?>
            </p>
            <?php endif; ?>
            
            <!-- Offer Display -->
            <div class="mb-6">
              <?php if ($slide['offer_main']): ?>
              <p class="text-lg mb-4 [text-shadow:_0_2px_8px_rgb(0_0_0_/_50%)]">
                <span class="font-bold"><?php echo htmlspecialchars($slide['offer_main']); ?></span>
                <?php if ($slide['offer_main_text']): ?>
                <span class="font-bold"><?php echo htmlspecialchars($slide['offer_main_text']); ?></span>
                <?php endif; ?>
                <?php if ($slide['offer_sub']): ?>
                <span class="font-bold"><?php echo htmlspecialchars($slide['offer_sub']); ?></span>
                <?php endif; ?>
              </p>
              <?php endif; ?>
              
              <?php if ($slide['offer_secondary']): ?>
              <p class="text-lg mb-4 [text-shadow:_0_2px_8px_rgb(0_0_0_/_50%)]">
                <span class="font-bold"><?php echo htmlspecialchars($slide['offer_secondary']); ?></span>
                <?php if ($slide['offer_secondary_text']): ?>
                <span class="font-bold"><?php echo htmlspecialchars($slide['offer_secondary_text']); ?></span>
                <?php endif; ?>
              </p>
              <?php endif; ?>
            </div>
            
            <div class="flex flex-col sm:flex-row justify-center items-center gap-3">
              <a href="<?php echo htmlspecialchars($slide['button_link']); ?>" target="_blank" 
                 class="inline-block w-full sm:w-auto bg-white/10 backdrop-blur-sm border border-white/50 text-white px-8 py-3 rounded-full font-bold hover:bg-white/20 hover:border-white/80 transition-all duration-300 shadow-lg text-center">
                <?php echo htmlspecialchars($slide['button_text']); ?>
              </a>
            </div>
            
            <?php if ($slide['disclaimer']): ?>
            <p class="text-xs text-white/50 mt-6 max-w-2xl mx-auto [text-shadow:_0_1px_4px_rgb(0_0_0_/_80%)]">
              <?php echo htmlspecialchars($slide['disclaimer']); ?>
            </p>
            <?php endif; ?>
          </div>
        </div>

        <!-- Desktop Layout -->
        <div class="md:flex justify-center h-full">
          <div class="container mx-auto px-4 md:px-6 lg:px-8 flex items-center h-full">
            <div class="max-w-3xl">
              <h1 class="text-3xl md:text-4xl lg:text-5xl font-bold text-white mb-6 leading-tight">
                <?php echo htmlspecialchars($slide['title']); ?>
              </h1>
              
              <?php if ($slide['subtitle']): ?>
              <p class="text-xl text-white mb-4"><?php echo htmlspecialchars($slide['subtitle']); ?></p>
              <?php endif; ?>
              
              <!-- Offer Display -->
              <div class="flex items-center gap-8 mb-6">
                <?php if ($slide['offer_main']): ?>
                <div>
                  <span class="text-5xl md:text-6xl font-bold text-white"><?php echo htmlspecialchars($slide['offer_main']); ?></span>
                  <?php if ($slide['offer_main_text']): ?>
                  <span class="text-xl md:text-2xl text-white ml-2"><?php echo htmlspecialchars($slide['offer_main_text']); ?></span>
                  <?php endif; ?>
                  <?php if ($slide['offer_sub']): ?>
                  <div class="text-sm text-white/80"><?php echo htmlspecialchars($slide['offer_sub']); ?></div>
                  <?php endif; ?>
                </div>
                <?php endif; ?>
                
                <?php if ($slide['offer_secondary']): ?>
                <div class="text-center"><span class="text-lg text-white/80">ó</span></div>
                <div>
                  <span class="text-4xl md:text-5xl font-bold text-white"><?php echo htmlspecialchars($slide['offer_secondary']); ?></span>
                  <?php if ($slide['offer_secondary_text']): ?>
                  <div class="text-sm text-white/80"><?php echo htmlspecialchars($slide['offer_secondary_text']); ?></div>
                  <?php endif; ?>
                </div>
                <?php endif; ?>
              </div>
              
              <div class="mb-6">
                <a href="<?php echo htmlspecialchars($slide['button_link']); ?>" target="_blank" 
                   class="inline-block border-2 border-white text-white px-4 sm:px-8 py-4 rounded-full font-medium hover:bg-white hover:text-[#111827] transition-colors text-sm sm:text-base">
                  <?php echo htmlspecialchars($slide['button_text']); ?>
                </a>
              </div>
              
              <?php if ($slide['disclaimer']): ?>
              <p class="text-xs text-white/60 mt-6 max-w-2xl">
                <?php echo htmlspecialchars($slide['disclaimer']); ?>
              </p>
              <?php endif; ?>
            </div>
          </div>
        </div>
      </div>
    </div>
    <?php endforeach; ?>

    <!-- Navigation Arrows -->
    <button id="prev-slide" class="absolute left-4 md:left-8 top-1/2 -translate-y-1/2 z-30 w-12 h-12 bg-[#111827]/20 hover:bg-[#111827]/40 text-white rounded-full flex items-center justify-center transition-colors backdrop-blur-sm">
      <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"></path>
      </svg>
    </button>
    <button id="next-slide" class="absolute right-4 md:right-8 top-1/2 -translate-y-1/2 z-30 w-12 h-12 bg-[#111827]/20 hover:bg-[#111827]/40 text-white rounded-full flex items-center justify-center transition-colors backdrop-blur-sm">
      <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"></path>
      </svg>
    </button>

    <!-- Slide Indicators -->
    <div class="absolute bottom-8 left-1/2 -translate-x-1/2 z-30 flex space-x-3">
      <?php foreach ($activeSlides as $index => $slide): ?>
      <button class="slide-indicator w-3 h-3 bg-white/40 rounded-full opacity-60 transition-opacity" data-slide="<?php echo $index; ?>"></button>
      <?php endforeach; ?>
    </div>
  </div>
</section>

