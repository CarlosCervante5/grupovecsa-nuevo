<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\HomeSlide;
use App\Models\HomeTestimonial;

class HomeContentSeeder extends Seeder
{
    public function run()
    {
        // Slides - image paths relative to Angular assets folder
        $slides = [
            [
                'title' => 'MINI 3 Puertas E',
                'subtitle' => 'Tecnología con actitud. Conduce el cambio con MINI.',
                'offer_main' => '36',
                'offer_main_text' => 'meses',
                'offer_sub' => 'sin intereses.',
                'offer_secondary' => '',
                'offer_secondary_text' => '',
                'button_text' => 'Más Información',
                'button_link' => 'https://grupovecsa.com/inventory/compra-tu-auto/Nuevo-Demo/Mini/sin-lineas/sin-modelos/sin-carrocerias/sin-versiones/sin-anios/1000/60000000/sin-estados/sin-busqueda/sin-transmisiones/sin-colores/sin-colores/ninguno/1',
                'disclaimer' => 'Sujeto a aprobación de crédito. Financiamiento otorgado y operado por MINI Financial Services de México, S.A. de C.V., SOFOM, E.N.R. Para más información sobre requisitos, condiciones y comisiones, consulta con tu asesor de ventas MINI VECSA Hidalgo. Vigencia al 31 de octubre 2025.',
                'desktop_image_path' => 'assets/images/home/Mini1.jpg',
                'mobile_image_path' => 'assets/images/home/oct3_mov.jpg',
                'active' => true,
                'sort_id' => 1,
            ],
            [
                'title' => 'BMW F 900 GS',
                'subtitle' => '',
                'offer_main' => '24',
                'offer_main_text' => 'MSI + 0% CXA',
                'offer_sub' => '',
                'offer_secondary' => '$50,000',
                'offer_secondary_text' => 'Bono Cashback.',
                'button_text' => 'Más Información',
                'button_link' => 'https://grupovecsa.com/inventory/compra-tu-auto/Nuevo/Motorrad/sin-lineas/sin-modelos/sin-carrocerias/sin-versiones/sin-anios/10000/60000000/sin-estados/sin-busqueda/sin-transmisiones/sin-colores/sin-colores/ninguno/1',
                'disclaimer' => 'Para más información, términos y condiciones consulta con tu asesor especializado BMW MOTORRAD VECSA Hidalgo. Sujeto a aprobación por parte de BMW Financial Services. Vigencia 31 de octubre 2025.',
                'desktop_image_path' => 'assets/images/home/Moto2.jpg',
                'mobile_image_path' => 'assets/images/home/oct1_mov.jpg',
                'active' => true,
                'sort_id' => 2,
            ],
            [
                'title' => 'BMW X5 xDrive50e',
                'subtitle' => 'Híbrido Conectable.',
                'offer_main' => '30',
                'offer_main_text' => 'meses',
                'offer_sub' => 'sin intereses.',
                'offer_secondary' => 'Bono',
                'offer_secondary_text' => 'Tasa preferencial.',
                'button_text' => 'Más Información',
                'button_link' => 'https://grupovecsa.com/inventory/compra-tu-auto/Nuevo-Demo/Bmw/sin-lineas/sin-modelos/sin-carrocerias/sin-versiones/sin-anios/10000/60000000/sin-estados/sin-busqueda/sin-transmisiones/sin-colores/sin-colores/ninguno/1',
                'disclaimer' => 'Sujeto a aprobación de crédito por parte de BMW Financial Services de México, S.A. de C.V., SOFOM, E.N.R. Para conocer términos, condiciones, comisiones y requisitos de contratación, consulta con tu asesor de ventas en BMW VECSA Hidalgo. Vigencia al 31 de diciembre 2025.',
                'desktop_image_path' => 'assets/images/home/Bmw3.jpg',
                'mobile_image_path' => 'assets/images/home/oct2_mov.jpg',
                'active' => true,
                'sort_id' => 3,
            ],
        ];

        foreach ($slides as $slide) {
            HomeSlide::create($slide);
        }

        // Testimoniales - image paths relative to Angular assets folder
        $testimonials = [
            ['image_path' => 'assets/images/home/BMW_st.jpg', 'alt' => 'Entrega BMW', 'active' => true, 'sort_id' => 1],
            ['image_path' => 'assets/images/home/MINI_st.jpg', 'alt' => 'Entrega MINI', 'active' => true, 'sort_id' => 2],
            ['image_path' => 'assets/images/home/MOTO_st.jpg', 'alt' => 'Entrega Motorrad', 'active' => true, 'sort_id' => 3],
            ['image_path' => 'assets/images/home/Bmw3.jpg', 'alt' => 'Entrega BMW', 'active' => true, 'sort_id' => 4],
            ['image_path' => 'assets/images/home/Mini1.jpg', 'alt' => 'Entrega MINI', 'active' => true, 'sort_id' => 5],
            ['image_path' => 'assets/images/home/Moto2.jpg', 'alt' => 'Entrega Motorrad', 'active' => true, 'sort_id' => 6],
        ];

        foreach ($testimonials as $testimonial) {
            HomeTestimonial::create($testimonial);
        }

        $this->command->info('Home slides y testimoniales creados exitosamente!');
        $this->command->info('Slides: ' . HomeSlide::count());
        $this->command->info('Testimoniales: ' . HomeTestimonial::count());
    }
}
