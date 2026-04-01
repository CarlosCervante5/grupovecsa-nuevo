<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Ramsey\Uuid\Uuid;

class QuizzesSeeder extends Seeder
{
    public function run(): void
    {
        $table = env('DB_TABLE_PREFIX', '') . 'quizzes';

        if (DB::table($table)->count() > 0) {
            $this->command->info('Quizzes already exist, skipping.');
            return;
        }

        $quizzes = [
            // 1 - Gender (clothes_gender - index 0)
            ['name' => 'Género de ropa', 'description' => 'Selecciona tu género para recomendaciones de ropa', 'sort_id' => 1, 'values' => 'H,M', 'status' => 'active', 'question_type' => 'single', 'element_type' => 'chip', 'group_name' => 'profile_gender'],
            // 2-11 - Profile affinities (accesories)
            ['name' => 'Deportes', 'description' => 'Deportes al aire libre', 'sort_id' => 2, 'values' => 'Sí,No', 'status' => 'active', 'question_type' => 'single', 'element_type' => 'card', 'group_name' => 'profile_affinities'],
            ['name' => 'Viajes', 'description' => 'Viajes y aventuras', 'sort_id' => 3, 'values' => 'Sí,No', 'status' => 'active', 'question_type' => 'single', 'element_type' => 'card', 'group_name' => 'profile_affinities'],
            ['name' => 'Tecnología', 'description' => 'Gadgets y tecnología', 'sort_id' => 4, 'values' => 'Sí,No', 'status' => 'active', 'question_type' => 'single', 'element_type' => 'card', 'group_name' => 'profile_affinities'],
            ['name' => 'Gastronomía', 'description' => 'Comida y restaurantes', 'sort_id' => 5, 'values' => 'Sí,No', 'status' => 'active', 'question_type' => 'single', 'element_type' => 'card', 'group_name' => 'profile_affinities'],
            ['name' => 'Música', 'description' => 'Conciertos y festivales', 'sort_id' => 6, 'values' => 'Sí,No', 'status' => 'active', 'question_type' => 'single', 'element_type' => 'card', 'group_name' => 'profile_affinities'],
            ['name' => 'Arte', 'description' => 'Exposiciones y museos', 'sort_id' => 7, 'values' => 'Sí,No', 'status' => 'active', 'question_type' => 'single', 'element_type' => 'card', 'group_name' => 'profile_affinities'],
            ['name' => 'Moda', 'description' => 'Tendencias y estilo', 'sort_id' => 8, 'values' => 'Sí,No', 'status' => 'active', 'question_type' => 'single', 'element_type' => 'card', 'group_name' => 'profile_affinities'],
            ['name' => 'Bienestar', 'description' => 'Salud y bienestar', 'sort_id' => 9, 'values' => 'Sí,No', 'status' => 'active', 'question_type' => 'single', 'element_type' => 'card', 'group_name' => 'profile_affinities'],
            ['name' => 'Lectura', 'description' => 'Libros y literatura', 'sort_id' => 10, 'values' => 'Sí,No', 'status' => 'active', 'question_type' => 'single', 'element_type' => 'card', 'group_name' => 'profile_affinities'],
            ['name' => 'Cine', 'description' => 'Películas y series', 'sort_id' => 11, 'values' => 'Sí,No', 'status' => 'active', 'question_type' => 'single', 'element_type' => 'card', 'group_name' => 'profile_affinities'],
            // 12 - Brand quiz (brand_quiz - index 11)
            ['name' => 'Marca actual', 'description' => 'Tu marca de auto actual', 'sort_id' => 12, 'values' => 'BMW,MOTORRAD,MINI,CHEVROLET', 'status' => 'active', 'question_type' => 'single', 'element_type' => 'chip', 'group_name' => 'brand_preference'],
            // 13-18 - Motorrad event preferences
            ['name' => 'Track Day', 'description' => 'Día de pista Motorrad', 'sort_id' => 13, 'values' => 'Sí,No', 'status' => 'active', 'question_type' => 'single', 'element_type' => 'card', 'group_name' => 'event_preferences'],
            ['name' => 'Road Trip', 'description' => 'Viaje en carretera', 'sort_id' => 14, 'values' => 'Sí,No', 'status' => 'active', 'question_type' => 'single', 'element_type' => 'card', 'group_name' => 'event_preferences'],
            ['name' => 'Off Road', 'description' => 'Aventura off road', 'sort_id' => 15, 'values' => 'Sí,No', 'status' => 'active', 'question_type' => 'single', 'element_type' => 'card', 'group_name' => 'event_preferences'],
            ['name' => 'Café Racer', 'description' => 'Encuentro café racer', 'sort_id' => 16, 'values' => 'Sí,No', 'status' => 'active', 'question_type' => 'single', 'element_type' => 'card', 'group_name' => 'event_preferences'],
            ['name' => 'Taller Mecánico', 'description' => 'Taller de mecánica', 'sort_id' => 17, 'values' => 'Sí,No', 'status' => 'active', 'question_type' => 'single', 'element_type' => 'card', 'group_name' => 'event_preferences'],
            ['name' => 'Lanzamiento', 'description' => 'Lanzamiento de modelos', 'sort_id' => 18, 'values' => 'Sí,No', 'status' => 'active', 'question_type' => 'single', 'element_type' => 'card', 'group_name' => 'event_preferences'],
            // 19-24 - BMW event preferences
            ['name' => 'Track Day BMW', 'description' => 'Día de pista BMW', 'sort_id' => 19, 'values' => 'Sí,No', 'status' => 'active', 'question_type' => 'single', 'element_type' => 'card', 'group_name' => 'bmw_event_preferences'],
            ['name' => 'Golf BMW', 'description' => 'Torneo de golf', 'sort_id' => 20, 'values' => 'Sí,No', 'status' => 'active', 'question_type' => 'single', 'element_type' => 'card', 'group_name' => 'bmw_event_preferences'],
            ['name' => 'Cena Exclusiva', 'description' => 'Cena exclusiva BMW', 'sort_id' => 21, 'values' => 'Sí,No', 'status' => 'active', 'question_type' => 'single', 'element_type' => 'card', 'group_name' => 'bmw_event_preferences'],
            ['name' => 'Test Drive', 'description' => 'Prueba de manejo', 'sort_id' => 22, 'values' => 'Sí,No', 'status' => 'active', 'question_type' => 'single', 'element_type' => 'card', 'group_name' => 'bmw_event_preferences'],
            ['name' => 'Lanzamiento BMW', 'description' => 'Lanzamiento de modelos BMW', 'sort_id' => 23, 'values' => 'Sí,No', 'status' => 'active', 'question_type' => 'single', 'element_type' => 'card', 'group_name' => 'bmw_event_preferences'],
            ['name' => 'Experiencia M', 'description' => 'Experiencia BMW M', 'sort_id' => 24, 'values' => 'Sí,No', 'status' => 'active', 'question_type' => 'single', 'element_type' => 'card', 'group_name' => 'bmw_event_preferences'],
            // 25-30 - MINI event preferences
            ['name' => 'MINI Track Day', 'description' => 'Día de pista MINI', 'sort_id' => 25, 'values' => 'Sí,No', 'status' => 'active', 'question_type' => 'single', 'element_type' => 'card', 'group_name' => 'mini_event_preferences'],
            ['name' => 'MINI Road Trip', 'description' => 'Road trip MINI', 'sort_id' => 26, 'values' => 'Sí,No', 'status' => 'active', 'question_type' => 'single', 'element_type' => 'card', 'group_name' => 'mini_event_preferences'],
            ['name' => 'MINI Lifestyle', 'description' => 'Evento lifestyle MINI', 'sort_id' => 27, 'values' => 'Sí,No', 'status' => 'active', 'question_type' => 'single', 'element_type' => 'card', 'group_name' => 'mini_event_preferences'],
            ['name' => 'MINI Lanzamiento', 'description' => 'Lanzamiento MINI', 'sort_id' => 28, 'values' => 'Sí,No', 'status' => 'active', 'question_type' => 'single', 'element_type' => 'card', 'group_name' => 'mini_event_preferences'],
            ['name' => 'MINI Picnic', 'description' => 'Picnic MINI', 'sort_id' => 29, 'values' => 'Sí,No', 'status' => 'active', 'question_type' => 'single', 'element_type' => 'card', 'group_name' => 'mini_event_preferences'],
            ['name' => 'MINI Test Drive', 'description' => 'Test drive MINI', 'sort_id' => 30, 'values' => 'Sí,No', 'status' => 'active', 'question_type' => 'single', 'element_type' => 'card', 'group_name' => 'mini_event_preferences'],
            // 31-36 - Chevrolet event preferences
            ['name' => 'Chevrolet Track', 'description' => 'Pista Chevrolet', 'sort_id' => 31, 'values' => 'Sí,No', 'status' => 'active', 'question_type' => 'single', 'element_type' => 'card', 'group_name' => 'chevrolet_event_preferences'],
            ['name' => 'Chevrolet Off Road', 'description' => 'Off road Chevrolet', 'sort_id' => 32, 'values' => 'Sí,No', 'status' => 'active', 'question_type' => 'single', 'element_type' => 'card', 'group_name' => 'chevrolet_event_preferences'],
            ['name' => 'Chevrolet Familia', 'description' => 'Evento familiar', 'sort_id' => 33, 'values' => 'Sí,No', 'status' => 'active', 'question_type' => 'single', 'element_type' => 'card', 'group_name' => 'chevrolet_event_preferences'],
            ['name' => 'Chevrolet Lanzamiento', 'description' => 'Lanzamiento Chevrolet', 'sort_id' => 34, 'values' => 'Sí,No', 'status' => 'active', 'question_type' => 'single', 'element_type' => 'card', 'group_name' => 'chevrolet_event_preferences'],
            ['name' => 'Chevrolet Test Drive', 'description' => 'Test drive Chevrolet', 'sort_id' => 35, 'values' => 'Sí,No', 'status' => 'active', 'question_type' => 'single', 'element_type' => 'card', 'group_name' => 'chevrolet_event_preferences'],
            ['name' => 'Chevrolet Servicio', 'description' => 'Clínica de servicio', 'sort_id' => 36, 'values' => 'Sí,No', 'status' => 'active', 'question_type' => 'single', 'element_type' => 'card', 'group_name' => 'chevrolet_event_preferences'],
            // 37-42 - Chevrolet questions
            ['name' => '¿Tienes Chevrolet?', 'description' => '¿Actualmente tienes un Chevrolet?', 'sort_id' => 37, 'values' => 'Sí,No', 'status' => 'active', 'question_type' => 'single', 'element_type' => 'question', 'group_name' => 'chevrolet_questions'],
            ['name' => '¿Modelo favorito?', 'description' => '¿Cuál es tu modelo favorito?', 'sort_id' => 38, 'values' => 'Camaro,Corvette,Silverado,Tahoe,Suburban,Otro', 'status' => 'active', 'question_type' => 'single', 'element_type' => 'question', 'group_name' => 'chevrolet_questions'],
            ['name' => '¿Año del vehículo?', 'description' => '¿De qué año es tu vehículo?', 'sort_id' => 39, 'values' => '2020,2021,2022,2023,2024,2025', 'status' => 'active', 'question_type' => 'single', 'element_type' => 'question', 'group_name' => 'chevrolet_questions'],
            ['name' => '¿Servicio frecuente?', 'description' => '¿Con qué frecuencia llevas tu auto a servicio?', 'sort_id' => 40, 'values' => 'Mensual,Trimestral,Semestral,Anual', 'status' => 'active', 'question_type' => 'single', 'element_type' => 'question', 'group_name' => 'chevrolet_questions'],
            ['name' => '¿Interés en eléctricos?', 'description' => '¿Te interesan los vehículos eléctricos?', 'sort_id' => 41, 'values' => 'Sí,No,Tal vez', 'status' => 'active', 'question_type' => 'single', 'element_type' => 'question', 'group_name' => 'chevrolet_questions'],
            ['name' => '¿Recomendarías Chevrolet?', 'description' => '¿Recomendarías Chevrolet?', 'sort_id' => 42, 'values' => 'Sí,No', 'status' => 'active', 'question_type' => 'single', 'element_type' => 'question', 'group_name' => 'chevrolet_questions'],
            // 43-48 - Default questions
            ['name' => 'Talla playera', 'description' => '¿Cuál es tu talla de playera?', 'sort_id' => 43, 'values' => 'XS,S,M,L,XL,XXL', 'status' => 'active', 'question_type' => 'single', 'element_type' => 'question', 'group_name' => 'default_questions'],
            ['name' => 'Talla pantalón', 'description' => '¿Cuál es tu talla de pantalón?', 'sort_id' => 44, 'values' => '28,30,32,34,36,38', 'status' => 'active', 'question_type' => 'single', 'element_type' => 'question', 'group_name' => 'default_questions'],
            ['name' => 'Talla calzado', 'description' => '¿Cuál es tu talla de calzado?', 'sort_id' => 45, 'values' => '24,25,26,27,28,29,30', 'status' => 'active', 'question_type' => 'single', 'element_type' => 'question', 'group_name' => 'default_questions'],
            ['name' => 'Talla gorra', 'description' => '¿Cuál es tu talla de gorra?', 'sort_id' => 46, 'values' => 'S,M,L,Unitalla', 'status' => 'active', 'question_type' => 'single', 'element_type' => 'question', 'group_name' => 'default_questions'],
            ['name' => 'Talla chamarra', 'description' => '¿Cuál es tu talla de chamarra?', 'sort_id' => 47, 'values' => 'XS,S,M,L,XL,XXL', 'status' => 'active', 'question_type' => 'single', 'element_type' => 'question', 'group_name' => 'default_questions'],
            ['name' => 'Color favorito', 'description' => '¿Cuál es tu color favorito?', 'sort_id' => 48, 'values' => 'Negro,Blanco,Azul,Rojo,Gris', 'status' => 'active', 'question_type' => 'single', 'element_type' => 'question', 'group_name' => 'default_questions'],
        ];

        $now = now();
        foreach ($quizzes as $quiz) {
            DB::table($table)->insert(array_merge($quiz, [
                'uuid' => (string) \Ramsey\Uuid\Uuid::uuid4(),
                'created_at' => $now,
                'updated_at' => $now,
            ]));
        }

        $this->command->info('✓ 48 quizzes created');
    }
}
