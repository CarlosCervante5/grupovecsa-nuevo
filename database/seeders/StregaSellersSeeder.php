<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Role;
use Spatie\Permission\PermissionRegistrar;

class StregaSellersSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Reset cached roles and permissions
        app()[PermissionRegistrar::class]->forgetCachedPermissions();
        
        $role1 = Role::findByName('seller');
        $role2 = Role::findByName('strega-seller');

        $user = User::factory()->create([
            'nickname' => 'Alan_Cruz_Pacheco_Jimenez',
            'email' => 'alan_pacheco@vecsa.com',
            'password' => 'AlanPacheco%2024%%'
        ]);
        $user->assignRole([$role1, $role2]);
        $user->userProfile()->create([
            'name' => 'Alan Cruz Pacheco Jimenez',
            'last_name' => '_'
        ]);

        $user = User::factory()->create([
            'nickname' => 'Alejandra_Leticia_Mendoza_Marquez',
            'email' => 'alejandra_mendoza@vecsa.com',
            'password' => 'AlejandraMendoza%2024%%'
        ]);
        $user->assignRole([$role1, $role2]);
        $user->userProfile()->create([
            'name' => 'Alejandra Leticia Mendoza Marquez',
            'last_name' => '_'
        ]);
        
        $user = User::factory()->create([
            'nickname' => 'Alexa_Margarita_Fernandez_Lopez',
            'email' => 'alexa_fernandez@vecsa.com',
            'password' => 'AlexaFernandez%2024%%'
        ]);
        $user->assignRole([$role1, $role2]);
        $user->userProfile()->create([
            'name' => 'Alexa Margarita Fernandez Lopez',
            'last_name' => '_'
        ]);
        
        $user = User::factory()->create([
            'nickname' => 'Alfredo_Díaz_Ávila',
            'email' => 'alfredo_diaz@vecsa.com',
            'password' => 'AlfredoDiaz%2024%%'
        ]);
        $user->assignRole([$role1, $role2]);
        $user->userProfile()->create([
            'name' => 'Alfredo Díaz Ávila',
            'last_name' => '_'
        ]);
        
        $user = User::factory()->create([
            'nickname' => 'Antonio_Angeles',
            'email' => 'antonio_angeles@vecsa.com',
            'password' => 'AntonioAngeles%2024%%'
        ]);
        $user->assignRole([$role1, $role2]);
        $user->userProfile()->create([
            'name' => 'Antonio Angeles',
            'last_name' => '_'
        ]);
        
        $user = User::factory()->create([
            'nickname' => 'Armando_Santander_Colin',
            'email' => 'armando_santander@vecsa.com',
            'password' => 'ArmandoSantander%2024%%'
        ]);
        $user->assignRole([$role1, $role2]);
        $user->userProfile()->create([
            'name' => 'Armando Santander Colin',
            'last_name' => '_'
        ]);
        
        $user = User::factory()->create([
            'nickname' => 'Blanca_Merari_Vega_Lozada',
            'email' => 'blanca_vega@vecsa.com',
            'password' => 'BlancaVega%2024%%'
        ]);
        $user->assignRole([$role1, $role2]);
        $user->userProfile()->create([
            'name' => 'Blanca Merari Vega Lozada',
            'last_name' => '_'
        ]);
        
        $user = User::factory()->create([
            'nickname' => 'Brenda_Lozano_Grosso',
            'email' => 'brenda_lozano@vecsa.com',
            'password' => 'BrendaLozano%2024%%'
        ]);
        $user->assignRole([$role1, $role2]);
        $user->userProfile()->create([
            'name' => 'Brenda Lozano Grosso',
            'last_name' => '_'
        ]);
        
        $user = User::factory()->create([
            'nickname' => 'Emmanuel_Martinez',
            'email' => 'emmanuel_martinez@vecsa.com',
            'password' => 'EmmanuelMartinez%2024%%'
        ]);
        $user->assignRole([$role1, $role2]);
        $user->userProfile()->create([
            'name' => 'Emmanuel Martinez',
            'last_name' => '_'
        ]);
        
        $user = User::factory()->create([
            'nickname' => 'Enrique_Guillermo_Garduño_Maldonado',
            'email' => 'enrique_garduno@vecsa.com',
            'password' => 'EnrigueGarduno%2024%%'
        ]);
        $user->assignRole([$role1, $role2]);
        $user->userProfile()->create([
            'name' => 'Enrique Guillermo Garduño Maldonado',
            'last_name' => '_'
        ]);
        
        $user = User::factory()->create([
            'nickname' => 'Erick_Christian_Rasgado_Marroquin',
            'email' => 'erick_rasgado@vecsa.com',
            'password' => 'ErickRasgado%2024%%'
        ]);
        $user->assignRole([$role1, $role2]);
        $user->userProfile()->create([
            'name' => 'Erick Christian Rasgado Marroquin',
            'last_name' => '_'
        ]);
        
        $user = User::factory()->create([
            'nickname' => 'Ernesto_Barrera_Osorio',
            'email' => 'ernesto_barrera@vecsa.com',
            'password' => 'ErnestoBarrera%2024%%'
        ]);
        $user->assignRole([$role1, $role2]);
        $user->userProfile()->create([
            'name' => 'Ernesto Barrera Osorio',
            'last_name' => '_'
        ]);
        
        $user = User::factory()->create([
            'nickname' => 'Gonzalo_Quintana_Morales',
            'email' => 'gonzalo_quintana@vecsa.com',
            'password' => 'GonzaloQuintana%2024%%'
        ]);
        $user->assignRole([$role1, $role2]);
        $user->userProfile()->create([
            'name' => 'Gonzalo Quintana Morales',
            'last_name' => '_'
        ]);
                
        $user = User::factory()->create([
            'nickname' => 'Israel_Rojas_Lazcano',
            'email' => 'israel_rojas@vecsa.com',
            'password' => 'IsraelRojas%2024%%'
        ]);
        $user->assignRole([$role1, $role2]);
        $user->userProfile()->create([
            'name' => 'Israel Rojas Lazcano',
            'last_name' => '_'
        ]);
                
        $user = User::factory()->create([
            'nickname' => 'Jesus_Fidel_Silva_Moreno',
            'email' => 'jesus_silva@vecsa.com',
            'password' => 'JesusSilva%2024%%'
        ]);
        $user->assignRole([$role1, $role2]);
        $user->userProfile()->create([
            'name' => 'Jesus Fidel Silva Moreno',
            'last_name' => '_'
        ]);
                
        $user = User::factory()->create([
            'nickname' => 'Jorge_Fernando_Rojas_Rosas',
            'email' => 'jorge_rojas@vecsa.com',
            'password' => 'JesusSilva%2024%%'
        ]);
        $user->assignRole([$role1, $role2]);
        $user->userProfile()->create([
            'name' => 'Jorge Fernando Rojas Rosas',
            'last_name' => '_'
        ]);
                
        $user = User::factory()->create([
            'nickname' => 'Juan_Luis_Ayala_Valdez',
            'email' => 'juan_ayala@vecsa.com',
            'password' => 'JuanAyala%2024%%'
        ]);
        $user->assignRole([$role1, $role2]);
        $user->userProfile()->create([
            'name' => 'Juan Luis Ayala Valdez',
            'last_name' => '_'
        ]);
                
        $user = User::factory()->create([
            'nickname' => 'Kevin_Jesus_Rojas_Navarrete',
            'email' => 'kevin_rojas@vecsa.com',
            'password' => 'KevinRojas%2024%%'
        ]);
        $user->assignRole([$role1, $role2]);
        $user->userProfile()->create([
            'name' => 'Kevin Jesus Rojas Navarrete',
            'last_name' => '_'
        ]);
                
        $user = User::factory()->create([
            'nickname' => 'Luis_Escamilla_Morgado',
            'email' => 'luis_escamilla@vecsa.com',
            'password' => 'LuisEscamilla%2024%%'
        ]);
        $user->assignRole([$role1, $role2]);
        $user->userProfile()->create([
            'name' => 'Luis Escamilla Morgado',
            'last_name' => '_'
        ]);
                
        $user = User::factory()->create([
            'nickname' => 'Maria_Ailin_Carolina_Herrera_Hernandez',
            'email' => 'maria_herrera@vecsa.com',
            'password' => 'MariaHerrera%2024%%'
        ]);
        $user->assignRole([$role1, $role2]);
        $user->userProfile()->create([
            'name' => 'Maria Ailin Carolina Herrera Hernandez',
            'last_name' => '_'
        ]);
                
        $user = User::factory()->create([
            'nickname' => 'Mario_Alberto_Lopez_Soto',
            'email' => 'mario_lopez@vecsa.com',
            'password' => 'MarioLopez%2024%%'
        ]);
        $user->assignRole([$role1, $role2]);
        $user->userProfile()->create([
            'name' => 'Mario Alberto Lopez Soto',
            'last_name' => '_'
        ]);
                
        $user = User::factory()->create([
            'nickname' => 'Miguel_Angel_Peralta_Vazquez',
            'email' => 'miguel_peralta@vecsa.com',
            'password' => 'MiguelPeralta%2024%%'
        ]);
        $user->assignRole([$role1, $role2]);
        $user->userProfile()->create([
            'name' => 'Miguel Angel Peralta Vazquez',
            'last_name' => '_'
        ]);
                
        $user = User::factory()->create([
            'nickname' => 'Norberto_Remos_Valdez',
            'email' => 'norberto_remos@vecsa.com',
            'password' => 'NorbertoRemos%2024%%'
        ]);
        $user->assignRole([$role1, $role2]);
        $user->userProfile()->create([
            'name' => 'Norberto Remos Valdez',
            'last_name' => '_'
        ]);

        $user = User::factory()->create([
            'nickname' => 'Pablo_Ruiz_Herrera',
            'email' => 'pablo_ruiz@vecsa.com',
            'password' => 'PabloRuiz%2024%%'
        ]);
        $user->assignRole([$role1, $role2]);
        $user->userProfile()->create([
            'name' => 'Pablo Ruiz Herrera',
            'last_name' => '_'
        ]);

        $user = User::factory()->create([
            'nickname' => 'Raul_Rojas_Navarrete',
            'email' => 'raul_rojas@vecsa.com',
            'password' => 'RaulRojas%2024%%'
        ]);
        $user->assignRole([$role1, $role2]);
        $user->userProfile()->create([
            'name' => 'Raul Rojas Navarrete',
            'last_name' => '_'
        ]);

        $user = User::factory()->create([
            'nickname' => 'Rebeca_Lopez_Rodriguez',
            'email' => 'rebeca_lopez@vecsa.com',
            'password' => 'RebecaLopez%2024%%'
        ]);
        $user->assignRole([$role1, $role2]);
        $user->userProfile()->create([
            'name' => 'Rebeca Lopez Rodriguez',
            'last_name' => '_'
        ]);

        $user = User::factory()->create([
            'nickname' => 'Sergio_Saul_Trujillo_Sanchez',
            'email' => 'sergio_trujillo@vecsa.com',
            'password' => 'ServioTrujillo%2024%%'
        ]);
        $user->assignRole([$role1, $role2]);
        $user->userProfile()->create([
            'name' => 'Sergio Saul Trujillo Sanchez',
            'last_name' => '_'
        ]);

        $user = User::factory()->create([
            'nickname' => 'Shamanta_Yanin_Jorge_Rodriguez',
            'email' => 'shamanta_jorge@vecsa.com',
            'password' => 'ShamantaJorge%2024%%'
        ]);
        $user->assignRole([$role1, $role2]);
        $user->userProfile()->create([
            'name' => 'Shamanta Yanin Jorge Rodriguez',
            'last_name' => '_'
        ]);
        
        $user = User::factory()->create([
            'nickname' => 'Victor_Antonio_Calva_Lopez',
            'email' => 'victor_calva@vecsa.com',
            'password' => 'VictorCalva%2024%%'
        ]);
        $user->assignRole([$role1, $role2]);
        $user->userProfile()->create([
            'name' => 'Victor Antonio Calva Lopez',
            'last_name' => '_'
        ]);
        
        $user = User::factory()->create([
            'nickname' => 'Yovana_Barquera_Cardoso',
            'email' => 'yovana_barquera@vecsa.com',
            'password' => 'YovanaBarquera%2024%%'
        ]);
        $user->assignRole([$role1, $role2]);
        $user->userProfile()->create([
            'name' => 'Yovana Barquera Cardoso',
            'last_name' => '_'
        ]);
    }
}
