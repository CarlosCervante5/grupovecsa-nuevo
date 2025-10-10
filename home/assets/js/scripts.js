document.addEventListener('DOMContentLoaded', function() {
    console.log('Grupo VECSA website loaded');

    // Header scroll behavior
    const header = document.getElementById('main-header');
    const topBar = document.querySelector('.bg-gray-900');
    let lastScrollTop = 0;
    
    if (header) {
        // Check if we're on mobile or desktop
        function updateHeaderPosition() {
            const isMobile = window.innerWidth < 768; // md breakpoint
            const scrollTop = window.pageYOffset || document.documentElement.scrollTop;
            
            if (isMobile) {
                // On mobile, header is always at top but changes background on scroll
                header.style.top = '0';
                        if (scrollTop > 100) {
            header.classList.add('header-scrolled');
            header.classList.remove('bg-transparent');
        } else {
            header.classList.remove('header-scrolled');
            header.classList.add('bg-transparent');
        }
            } else if (topBar) {
                // On desktop, position below top bar
                const topBarHeight = topBar.offsetHeight;
                
                if (scrollTop > 100) {
                    header.style.top = '0';
                    header.classList.add('header-scrolled');
                    header.classList.remove('bg-transparent');
                } else {
                    header.style.top = topBarHeight + 'px';
                    header.classList.remove('header-scrolled');
                    header.classList.add('bg-transparent');
                }
            }
        }
        
        // Initial setup
        updateHeaderPosition();
        
        window.addEventListener('scroll', updateHeaderPosition);
        window.addEventListener('resize', updateHeaderPosition);
    }

    // Hero Slider functionality
    const slides = document.querySelectorAll('.slide');
    const indicators = document.querySelectorAll('.slide-indicator');
    const prevButton = document.getElementById('prev-slide');
    const nextButton = document.getElementById('next-slide');
    let currentSlide = 0;
    let slideInterval;

    if (slides.length > 0) {
        // Function to show specific slide
        function showSlide(index) {
            // Hide all slides
            slides.forEach((slide, i) => {
                if (i === index) {
                    slide.style.opacity = '1';
                    slide.style.zIndex = '20';
                    slide.classList.add('active');
                } else {
                    slide.style.opacity = '0';
                    slide.style.zIndex = '10';
                    slide.classList.remove('active');
                }
            });
            
            // Update indicators
            indicators.forEach((indicator, i) => {
                if (i === index) {
                    indicator.style.opacity = '1';
                    indicator.style.backgroundColor = 'white';
                } else {
                    indicator.style.opacity = '0.6';
                    indicator.style.backgroundColor = 'rgba(255, 255, 255, 0.4)';
                }
            });
            
            currentSlide = index;
        }

        // Function to go to next slide
        function nextSlide() {
            const next = (currentSlide + 1) % slides.length;
            showSlide(next);
        }

        // Function to go to previous slide
        function prevSlide() {
            const prev = (currentSlide - 1 + slides.length) % slides.length;
            showSlide(prev);
        }

        // Auto-play functionality
        function startAutoPlay() {
            slideInterval = setInterval(nextSlide, 8000); // Change slide every 8 seconds
        }

        function stopAutoPlay() {
            clearInterval(slideInterval);
        }

        // Event listeners for navigation buttons
        if (nextButton) {
            nextButton.addEventListener('click', () => {
                stopAutoPlay();
                nextSlide();
                startAutoPlay();
            });
        }

        if (prevButton) {
            prevButton.addEventListener('click', () => {
                stopAutoPlay();
                prevSlide();
                startAutoPlay();
            });
        }

        // Event listeners for indicators
        indicators.forEach((indicator, index) => {
            indicator.addEventListener('click', () => {
                stopAutoPlay();
                showSlide(index);
                startAutoPlay();
            });
        });

        // Pause auto-play on hover
        const heroSlider = document.getElementById('hero-slider');
        if (heroSlider) {
            heroSlider.addEventListener('mouseenter', stopAutoPlay);
            heroSlider.addEventListener('mouseleave', startAutoPlay);
        }

        // Initialize first slide
        showSlide(0);
        
        // Start auto-play
        startAutoPlay();

        // Keyboard navigation
        document.addEventListener('keydown', (e) => {
            if (e.key === 'ArrowLeft') {
                stopAutoPlay();
                prevSlide();
                startAutoPlay();
            } else if (e.key === 'ArrowRight') {
                stopAutoPlay();
                nextSlide();
                startAutoPlay();
            }
        });
    }

    // Mobile Sidebar Menu functionality
    const mobileMenuButton = document.getElementById('mobile-menu-button');
    const mobileSidebar = document.getElementById('mobile-sidebar');
    const closeSidebar = document.getElementById('close-sidebar');
    const sidebarOverlay = document.getElementById('sidebar-overlay');

    // Function to open sidebar
    function openSidebar() {
        if (mobileSidebar && sidebarOverlay) {
            mobileSidebar.classList.remove('translate-x-full');
            sidebarOverlay.classList.remove('opacity-0', 'pointer-events-none');
            // Prevent body scroll when sidebar is open
            document.body.style.overflow = 'hidden';
        }
    }

    // Function to close sidebar
    function closeSidebarMenu() {
        if (mobileSidebar && sidebarOverlay) {
            mobileSidebar.classList.add('translate-x-full');
            sidebarOverlay.classList.add('opacity-0', 'pointer-events-none');
            // Restore body scroll
            document.body.style.overflow = '';
        }
    }

    // Event listeners for sidebar
    if (mobileMenuButton) {
        mobileMenuButton.addEventListener('click', openSidebar);
    }

    if (closeSidebar) {
        closeSidebar.addEventListener('click', closeSidebarMenu);
    }

    if (sidebarOverlay) {
        sidebarOverlay.addEventListener('click', closeSidebarMenu);
    }

    // Close sidebar on escape key
    document.addEventListener('keydown', (e) => {
        if (e.key === 'Escape') {
            closeSidebarMenu();
        }
    });

    // Close sidebar when clicking on nav links (mobile)
    const sidebarNavLinks = document.querySelectorAll('#mobile-sidebar nav a');
    sidebarNavLinks.forEach(link => {
        link.addEventListener('click', closeSidebarMenu);
    });

    // Handle window resize - close sidebar on desktop view
    window.addEventListener('resize', () => {
        if (window.innerWidth >= 1024) { // lg breakpoint
            closeSidebarMenu();
        }
    });

    // Location Selection and Map functionality
    const locationItems = document.querySelectorAll('.location-item');
    const mapPlaceholder = document.getElementById('map-placeholder');
    const selectedLocationInfo = document.getElementById('selected-location-info');
    const selectedName = document.getElementById('selected-name');
    const selectedAddress = document.getElementById('selected-address');
    const directionsLink = document.getElementById('directions-link');
    const callLink = document.getElementById('call-link');

    // Location data
    const locations = {
        'hub-serdan': {
            name: 'HUB Serdán',
            address: 'Blvd. Hermanos Serdán 788, esquina Francisco Villa, Col. San Rafael Oriente, Puebla',
            phone: '222-309-0700',
            lat: 19.0904203,
            lng: -98.2316285
        },
        'vecsa-puebla': {
            name: 'VECSA Puebla Angelópolis',
            address: 'Blvd. Atlixcayotl No. 5316, Col. Reserva Territorial Atlixcayotl, Puebla',
            phone: '222-309-0800',
            lat: 19.0261353,
            lng: -98.2387406
        },
        'vecsa-pachuca': {
            name: 'VECSA Pachuca',
            address: 'Vial La Paz 113, Col. Adolfo López Mateos, Pachuca, Hidalgo',
            phone: '771-717-2554',
            lat: 20.0896006,
            lng: -98.7476267
        },
        'vecsa-veracruz': {
            name: 'VECSA Veracruz',
            address: 'Carretera Federal Boca del Río – Antón de Lizardo No. 4450, Col. Punta Tiburón',
            phone: '229-923-6030',
            lat: 19.0857766,
            lng: -96.1097188
        },
        'vecsa-oaxaca': {
            name: 'VECSA Oaxaca',
            address: 'Av. Universidad No. 400, Col. Ex hacienda Candiani, Oaxaca',
            phone: '951-144-7955',
            lat: 17.035929,
            lng: -96.7158026
        },
                    'vecsa-balderrama': {
                name: 'Chevrolet Balderrama',
                address: 'Av. Hermanos Serdán No. 241, Col. Aquiles Serdán, Puebla',
                phone: '222-303-9900',
                lat: 19.0684357,
                lng: -98.2298084
            },
                    'abcars-puebla': {
                name: 'ABCars Puebla',
                address: 'Blvrd Esteban de Antuñano 1314, Obrera Textil José Abascal, Puebla',
                phone: '222-303-9910',
                lat: 19.0834563,
                lng: -98.2383
            }
    };

    let currentMap = null;
    let currentMarker = null;

    // Function to update active location
    function updateActiveLocation(locationId) {
        // Remove active state from all items
        locationItems.forEach(item => {
            item.classList.remove('border-blue-600');
            item.classList.add('border-transparent');
        });

        // Add active state to selected item
        const selectedItem = document.querySelector(`[data-location="${locationId}"]`);
        if (selectedItem) {
            selectedItem.classList.add('border-blue-600');
            selectedItem.classList.remove('border-transparent');
        }

        // Update location info
        const location = locations[locationId];
        if (location && selectedLocationInfo) {
            selectedName.textContent = location.name;
            selectedAddress.textContent = location.address;
            directionsLink.href = `https://www.google.com/maps/dir/?api=1&destination=${location.lat},${location.lng}`;
            callLink.href = `tel:+52${location.phone.replace(/-/g, '')}`;
            
            selectedLocationInfo.classList.remove('hidden');
            
            // Hide placeholder and show map
            if (mapPlaceholder) {
                mapPlaceholder.style.display = 'none';
            }
            
            // Update map
            updateMap(location);
        }
    }

    // Function to update map
    function updateMap(location) {
        // If Google Maps is available, use it
        if (window.updateMapLocation) {
            window.updateMapLocation({ lat: location.lat, lng: location.lng }, location.name.toLowerCase().replace(/\s+/g, '-'));
            return;
        }

        // Fallback to static map representation
        const mapContainer = document.getElementById('map');
        if (!mapContainer) return;

        mapContainer.innerHTML = `
            <div class="w-full h-full bg-blue-100 flex items-center justify-center relative">
                <div class="absolute inset-0 bg-gradient-to-br from-blue-200 to-blue-300"></div>
                <div class="relative z-10 text-center p-8">
                    <div class="bg-red-500 w-8 h-8 rounded-full mx-auto mb-4 flex items-center justify-center shadow-lg">
                        <svg class="w-5 h-5 text-white" fill="currentColor" viewBox="0 0 24 24">
                            <path d="M12 2C8.13 2 5 5.13 5 9c0 5.25 7 13 7 13s7-7.75 7-13c0-3.87-3.13-7-7-7zm0 9.5c-1.38 0-2.5-1.12-2.5-2.5s1.12-2.5 2.5-2.5 2.5 1.12 2.5 2.5-1.12 2.5-2.5 2.5z"/>
                        </svg>
                    </div>
                    <h4 class="text-lg font-bold text-gray-800 mb-2">${location.name}</h4>
                    <p class="text-sm text-gray-600">${location.address}</p>
                    <div class="mt-4 text-xs text-gray-500">
                        <p>Lat: ${location.lat}</p>
                        <p>Lng: ${location.lng}</p>
                    </div>
                    <div class="mt-4 text-xs text-blue-600">
                        <p>Cargando Google Maps...</p>
                    </div>
                </div>
                <!-- Decorative elements -->
                <div class="absolute top-4 left-4 w-2 h-2 bg-white rounded-full opacity-50"></div>
                <div class="absolute top-8 right-6 w-1 h-1 bg-white rounded-full opacity-30"></div>
                <div class="absolute bottom-6 left-8 w-1 h-1 bg-white rounded-full opacity-40"></div>
                <div class="absolute bottom-4 right-4 w-2 h-2 bg-white rounded-full opacity-20"></div>
            </div>
        `;
    }

    // Add click listeners to location items
    locationItems.forEach(item => {
        item.addEventListener('click', () => {
            const locationId = item.dataset.location;
            updateActiveLocation(locationId);
        });
    });

    // Initialize with first location selected
    if (locationItems.length > 0) {
        const firstLocationId = locationItems[0].dataset.location;
        updateActiveLocation(firstLocationId);
    }

    // Smooth scrolling for anchor links
    document.querySelectorAll('a[href^="#"]').forEach(anchor => {
        anchor.addEventListener('click', function (e) {
            e.preventDefault();
            const target = document.querySelector(this.getAttribute('href'));
            if (target) {
                target.scrollIntoView({
                    behavior: 'smooth',
                    block: 'start'
                });
            }
        });
    });

    console.log('Mobile sidebar functionality initialized');
    console.log('Location selection functionality initialized');
    console.log('Map functionality initialized');
}); 