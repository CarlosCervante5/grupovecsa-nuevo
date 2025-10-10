<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Role;
use Spatie\Permission\PermissionRegistrar;

class ChevroletSellersSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Reset cached roles and permissions
        app()[PermissionRegistrar::class]->forgetCachedPermissions();

        $role = Role::findByName('seller');

        // Create demo users
        $user = User::factory()->create([
            'nickname' => 'miguel_aguilar',
            'email' => 'miguel_aguilar@vecsa.com',
            'password' => 'MiguelAguilar%2024%%'
        ]);
        
        $user->assignRole($role);
        
        $user->userProfile()->create([
            'name' => 'Miguel Angel Eleazar',
            'last_name' => 'Aguilar Alonso'
        ]);


        $user = User::factory()->create([
            'nickname' => 'alan_pacheco',
            'email' => 'alan_pacheco@vecsa.com',
            'password' => 'AlanPacheco%2024%%'
        ]);
        
        $user->assignRole($role);
        
        $user->userProfile()->create([
            'name' => 'Alan Cruz',
            'last_name' => 'Pacheco Jimenez'
        ]);


        $user = User::factory()->create([
            'nickname' => 'alberto_rivera',
            'email' => 'alberto_rivera@vecsa.com',
            'password' => 'AlbertoRivera%2024%%'
        ]);
        
        $user->assignRole($role);
        
        $user->userProfile()->create([
            'name' => 'Alberto',
            'last_name' => 'Rivera Contreras'
        ]);


        $user = User::factory()->create([
            'nickname' => 'alejandra_mendoza',
            'email' => 'alejandra_mendoza@vecsa.com',
            'password' => 'AlejandraMendoza%2024%%'
        ]);
        
        $user->assignRole($role);
        
        $user->userProfile()->create([
            'name' => 'Alejandra Leticia',
            'last_name' => 'Mendoza Marquez'
        ]);


        $user = User::factory()->create([
            'nickname' => 'antonio_angeles',
            'email' => 'antonio_angeles@vecsa.com',
            'password' => 'AntonioAngeles%2024%%'
        ]);
        
        $user->assignRole($role);
        
        $user->userProfile()->create([
            'name' => 'Antonio De Jesus',
            'last_name' => 'Angeles Hernandez'
        ]);


        $user = User::factory()->create([
            'nickname' => 'armando_santander',
            'email' => 'armando_santander@vecsa.com',
            'password' => 'ArmandoSantander%2024%%'
        ]);
        
        $user->assignRole($role);
        
        $user->userProfile()->create([
            'name' => 'Armando',
            'last_name' => 'Santander Colin'
        ]);


        $user = User::factory()->create([
            'nickname' => 'eduardo_bardesi',
            'email' => 'eduardo_bardesi@vecsa.com',
            'password' => 'EduardoBardesi%2024%%'
        ]);
        
        $user->assignRole($role);
        
        $user->userProfile()->create([
            'name' => 'Eduardo',
            'last_name' => 'Bardesi Zamora'
        ]);


        $user = User::factory()->create([
            'nickname' => 'edgar_barranco',
            'email' => 'edgar_barranco@vecsa.com',
            'password' => 'EdgarBarranco%2024%%'
        ]);
        
        $user->assignRole($role);
        
        $user->userProfile()->create([
            'name' => 'Edgar',
            'last_name' => 'Barranco Nahuacatl'
        ]);


        $user = User::factory()->create([
            'nickname' => 'marco_calixto',
            'email' => 'marco_calixto@vecsa.com',
            'password' => 'MarcoCalixto%2024%%'
        ]);
        
        $user->assignRole($role);
        
        $user->userProfile()->create([
            'name' => 'Marco Antonio',
            'last_name' => 'Calixto Romero'
        ]);


        $user = User::factory()->create([
            'nickname' => 'cecilia_cardenas',
            'email' => 'cecilia_cardenas@vecsa.com',
            'password' => 'CeciliaCardenas%2024%%'
        ]);
        
        $user->assignRole($role);
        
        $user->userProfile()->create([
            'name' => 'Cecilia',
            'last_name' => 'Cardenas De La Cerda'
        ]);


        $user = User::factory()->create([
            'nickname' => 'aurora_cazares',
            'email' => 'aurora_cazares@vecsa.com',
            'password' => 'AuroraCazares%2024%%'
        ]);
        
        $user->assignRole($role);
        
        $user->userProfile()->create([
            'name' => 'Aurora',
            'last_name' => 'Cazares Cruz'
        ]);


        $user = User::factory()->create([
            'nickname' => 'gabriel_chacon',
            'email' => 'gabriel_chacon@vecsa.com',
            'password' => 'GabrielChacon%2024%%'
        ]);
        
        $user->assignRole($role);
        
        $user->userProfile()->create([
            'name' => 'Gabriel',
            'last_name' => 'Chacon Orozco'
        ]);


        $user = User::factory()->create([
            'nickname' => 'daniela_salinas',
            'email' => 'daniela_salinas@vecsa.com',
            'password' => 'DanielaSalinas%2024%%'
        ]);
        
        $user->assignRole($role);
        
        $user->userProfile()->create([
            'name' => 'Daniela',
            'last_name' => 'Salinas'
        ]);


        $user = User::factory()->create([
            'nickname' => 'flor_botton',
            'email' => 'flor_botton@vecsa.com',
            'password' => 'FlorBotton%2024%%'
        ]);
        
        $user->assignRole($role);
        
        $user->userProfile()->create([
            'name' => 'Flor Susana',
            'last_name' => 'De Botton Orue'
        ]);


        $user = User::factory()->create([
            'nickname' => 'eduardo_dominguez',
            'email' => 'eduardo_dominguez@vecsa.com',
            'password' => 'EduardoDominguez%2024%%'
        ]);
        
        $user->assignRole($role);
        
        $user->userProfile()->create([
            'name' => 'Eduardo Gaudencio',
            'last_name' => 'Dominguez Escamilla'
        ]);


        $user = User::factory()->create([
            'nickname' => 'elvia_reyes',
            'email' => 'elvia_reyes@vecsa.com',
            'password' => 'ElviaReyes%2024%%'
        ]);
        
        $user->assignRole($role);
        
        $user->userProfile()->create([
            'name' => 'Elvia',
            'last_name' => 'Reyes'
        ]);


        $user = User::factory()->create([
            'nickname' => 'enrique_garduño',
            'email' => 'enrique_garduño@vecsa.com',
            'password' => 'EnriqueGarduño%2024%%'
        ]);
        
        $user->assignRole($role);
        
        $user->userProfile()->create([
            'name' => 'Enrique Guillermo',
            'last_name' => 'Garduño Maldonado'
        ]);


        $user = User::factory()->create([
            'nickname' => 'erick_rasgado',
            'email' => 'erick_rasgado@vecsa.com',
            'password' => 'ErickRasgado%2024%%'
        ]);
        
        $user->assignRole($role);
        
        $user->userProfile()->create([
            'name' => 'Erick Christian',
            'last_name' => 'Rasgado Marroquin'
        ]);


        $user = User::factory()->create([
            'nickname' => 'ernesto_barrera',
            'email' => 'ernesto_barrera@vecsa.com',
            'password' => 'ErnestoBarrera%2024%%'
        ]);
        
        $user->assignRole($role);
        
        $user->userProfile()->create([
            'name' => 'Ernesto',
            'last_name' => 'Barrera Osorio'
        ]);


        $user = User::factory()->create([
            'nickname' => 'cirilio_flores',
            'email' => 'cirilio_flores@vecsa.com',
            'password' => 'CirilioFlores%2024%%'
        ]);
        
        $user->assignRole($role);
        
        $user->userProfile()->create([
            'name' => 'Cirilo',
            'last_name' => 'Flores Vera'
        ]);


        $user = User::factory()->create([
            'nickname' => 'maria_fonseca',
            'email' => 'maria_fonseca@vecsa.com',
            'password' => 'MariaFonseca%2024%%'
        ]);
        
        $user->assignRole($role);
        
        $user->userProfile()->create([
            'name' => 'Maria Belen',
            'last_name' => 'Fonseca Flores'
        ]);


        $user = User::factory()->create([
            'nickname' => 'montserrat_fuentes',
            'email' => 'montserrat_fuentes@vecsa.com',
            'password' => 'MontserratFuentes%2024%%'
        ]);
        
        $user->assignRole($role);
        
        $user->userProfile()->create([
            'name' => 'Monserrat',
            'last_name' => 'Fuentes Flores'
        ]);


        $user = User::factory()->create([
            'nickname' => 'jose_gomez',
            'email' => 'jose_gomez@vecsa.com',
            'password' => 'JoseGomez%2024%%'
        ]);
        
        $user->assignRole($role);
        
        $user->userProfile()->create([
            'name' => 'Jose Ramon',
            'last_name' => 'Gomez Ramirez'
        ]);


        $user = User::factory()->create([
            'nickname' => 'maria_gonzales',
            'email' => 'maria_gonzales@vecsa.com',
            'password' => 'MariaGonzales%2024%%'
        ]);
        
        $user->assignRole($role);
        
        $user->userProfile()->create([
            'name' => 'Maria Fernanda',
            'last_name' => 'Gonzalez Lezama'
        ]);


        $user = User::factory()->create([
            'nickname' => 'gonzalo_quintana',
            'email' => 'gonzalo_quintana@vecsa.com',
            'password' => 'GonzaloQuintana%2024%%'
        ]);
        
        $user->assignRole($role);
        
        $user->userProfile()->create([
            'name' => 'Gonzalo',
            'last_name' => 'Quintana Morales'
        ]);


        $user = User::factory()->create([
            'nickname' => 'fidel_guillen',
            'email' => 'fidel_guillen@vecsa.com',
            'password' => 'FidelGuillen%2024%%'
        ]);
        
        $user->assignRole($role);
        
        $user->userProfile()->create([
            'name' => 'Fidel Eduardo',
            'last_name' => 'Guillen Aguilar'
        ]);


        $user = User::factory()->create([
            'nickname' => 'gerardo_herrera',
            'email' => 'gerardo_herrera@vecsa.com',
            'password' => 'GerardoHerrera%2024%%'
        ]);
        
        $user->assignRole($role);
        
        $user->userProfile()->create([
            'name' => 'Gerardo',
            'last_name' => 'Herrera Hernandez'
        ]);


        $user = User::factory()->create([
            'nickname' => 'israel_rojas',
            'email' => 'israel_rojas@vecsa.com',
            'password' => 'IsraelRojas%2024%%'
        ]);
        
        $user->assignRole($role);
        
        $user->userProfile()->create([
            'name' => 'Israel',
            'last_name' => 'Rojas Lazcano'
        ]);


        $user = User::factory()->create([
            'nickname' => 'carlos_jimenez',
            'email' => 'carlos_jimenez@vecsa.com',
            'password' => 'CarlosJimenez%2024%%'
        ]);
        
        $user->assignRole($role);
        
        $user->userProfile()->create([
            'name' => 'Carlos',
            'last_name' => 'Jimenez Gonzalez'
        ]);


        $user = User::factory()->create([
            'nickname' => 'jorge_rojas',
            'email' => 'jorge_rojas@vecsa.com',
            'password' => 'JorgeRojas%2024%%'
        ]);
        
        $user->assignRole($role);
        
        $user->userProfile()->create([
            'name' => 'Jorge Fernando',
            'last_name' => 'Rojas Rosas'
        ]);


        $user = User::factory()->create([
            'nickname' => 'juan_luis',
            'email' => 'juan_luis@vecsa.com',
            'password' => 'JuanLuis%2024%%'
        ]);
        
        $user->assignRole($role);
        
        $user->userProfile()->create([
            'name' => 'Juan Luis',
            'last_name' => 'Ayala Valdez'
        ]);


        $user = User::factory()->create([
            'nickname' => 'karen_padilla',
            'email' => 'karen_padilla@vecsa.com',
            'password' => 'KarenPadilla%2024%%'
        ]);
        
        $user->assignRole($role);
        
        $user->userProfile()->create([
            'name' => 'Karen',
            'last_name' => 'Padilla Gonzales'
        ]);


        $user = User::factory()->create([
            'nickname' => 'kevin_rojas',
            'email' => 'kevin_rojas@vecsa.com',
            'password' => 'KevinRojas%2024%%'
        ]);
        
        $user->assignRole($role);
        
        $user->userProfile()->create([
            'name' => 'Kevin Jesus',
            'last_name' => 'Rojas Navarrete'
        ]);


        $user = User::factory()->create([
            'nickname' => 'omar_lopez',
            'email' => 'omar_lopez@vecsa.com',
            'password' => 'OmarLopez%2024%%'
        ]);
        
        $user->assignRole($role);
        
        $user->userProfile()->create([
            'name' => 'Omar',
            'last_name' => 'Lopez Rojas'
        ]);


        $user = User::factory()->create([
            'nickname' => 'hugo_luna',
            'email' => 'hugo_luna@vecsa.com',
            'password' => 'HugoLuna%2024%%'
        ]);
        
        $user->assignRole($role);
        
        $user->userProfile()->create([
            'name' => 'Hugo Emilio',
            'last_name' => 'Luna Aguilar'
        ]);


        $user = User::factory()->create([
            'nickname' => 'alberto_marcial',
            'email' => 'alberto_marcial@vecsa.com',
            'password' => 'AlbertoMarcial%2024%%'
        ]);
        
        $user->assignRole($role);
        
        $user->userProfile()->create([
            'name' => 'Alberto',
            'last_name' => 'Marcial Osorio'
        ]);


        $user = User::factory()->create([
            'nickname' => 'maria_herrera',
            'email' => 'maria_herrera@vecsa.com',
            'password' => 'MariaHerrera%2024%%'
        ]);
        
        $user->assignRole($role);
        
        $user->userProfile()->create([
            'name' => 'Maria Ailin Carolina',
            'last_name' => 'Herrera Hernandez'
        ]);


        $user = User::factory()->create([
            'nickname' => 'mario_lopez',
            'email' => 'mario_lopez@vecsa.com',
            'password' => 'MarioLopez%2024%%'
        ]);
        
        $user->assignRole($role);
        
        $user->userProfile()->create([
            'name' => 'Mario Alberto',
            'last_name' => 'Lopez Soto'
        ]);


        // $user = User::factory()->create([
        //     'nickname' => 'mario_lopez',
        //     'email' => 'mario_lopez@vecsa.com',
        //     'password' => 'MarioLopez%2024%%'
        // ]);
        
        // $user->assignRole($role);
        
        // $user->userProfile()->create([
        //     'name' => 'Mario Alberto',
        //     'last_name' => 'Lopez Soto'
        // ]);


        $user = User::factory()->create([
            'nickname' => 'hector_marquez',
            'email' => 'hector_marquez@vecsa.com',
            'password' => 'HectorMarquez%2024%%'
        ]);
        
        $user->assignRole($role);
        
        $user->userProfile()->create([
            'name' => 'Hector Alberto',
            'last_name' => 'Marquez Acosta'
        ]);


        $user = User::factory()->create([
            'nickname' => 'max_junco',
            'email' => 'max_junco@vecsa.com',
            'password' => 'MaxJunco%2024%%'
        ]);
        
        $user->assignRole($role);
        
        $user->userProfile()->create([
            'name' => 'Max',
            'last_name' => 'Junco'
        ]);


        $user = User::factory()->create([
            'nickname' => 'angel_mendoza',
            'email' => 'angel_mendoza@vecsa.com',
            'password' => 'AngelMendoza%2024%%'
        ]);
        
        $user->assignRole($role);
        
        $user->userProfile()->create([
            'name' => 'Angel',
            'last_name' => 'Mendoza Hernandez'
        ]);


        $user = User::factory()->create([
            'nickname' => 'miguel_peralta',
            'email' => 'miguel_peralta@vecsa.com',
            'password' => 'MiguelPeralta%2024%%'
        ]);
        
        $user->assignRole($role);
        
        $user->userProfile()->create([
            'name' => 'Miguel Angel',
            'last_name' => 'Peralta Vazquez'
        ]);


        $user = User::factory()->create([
            'nickname' => 'hector_nauhuacatl',
            'email' => 'hector_nauhuacatl@vecsa.com',
            'password' => 'HectorNauhuacatl%2024%%'
        ]);
        
        $user->assignRole($role);
        
        $user->userProfile()->create([
            'name' => 'Hector Uriel',
            'last_name' => 'Nauhuacatl Hernandez'
        ]);


        $user = User::factory()->create([
            'nickname' => 'pablo_ruiz',
            'email' => 'pablo_ruiz@vecsa.com',
            'password' => 'PabloRuiz%2024%%'
        ]);
        
        $user->assignRole($role);
        
        $user->userProfile()->create([
            'name' => 'Pablo Isaac',
            'last_name' => 'Ruiz Herrera'
        ]);

        $user = User::factory()->create([
            'nickname' => 'rafael_peralta',
            'email' => 'rafael_peralta@vecsa.com',
            'password' => 'RafaelPeralta%2024%%'
        ]);
        
        $user->assignRole($role);
        
        $user->userProfile()->create([
            'name' => 'Rafael',
            'last_name' => 'Peralta Diaz'
        ]);

        $user = User::factory()->create([
            'nickname' => 'cesar_perez',
            'email' => 'cesar_perez@vecsa.com',
            'password' => 'CesarPerez%2024%%'
        ]);
        
        $user->assignRole($role);
        
        $user->userProfile()->create([
            'name' => 'Cesar Augusto',
            'last_name' => 'Perez Fuentes'
        ]);


        $user = User::factory()->create([
            'nickname' => 'carlos_perez',
            'email' => 'carlos_perez@vecsa.com',
            'password' => 'CarlosPerez%2024%%'
        ]);
        
        $user->assignRole($role);
        
        $user->userProfile()->create([
            'name' => 'Carlos Ivan',
            'last_name' => 'Perez Hernandez'
        ]);


        $user = User::factory()->create([
            'nickname' => 'diana_portillo',
            'email' => 'diana_portillo@vecsa.com',
            'password' => 'DianaPortillo%2024%%'
        ]);
        
        $user->assignRole($role);
        
        $user->userProfile()->create([
            'name' => 'Diana',
            'last_name' => 'Portillo Diaz'
        ]);


        $user = User::factory()->create([
            'nickname' => 'salvador_ramirez',
            'email' => 'salvador_ramirez@vecsa.com',
            'password' => 'SalvadorRamirez%2024%%'
        ]);
        
        $user->assignRole($role);
        
        $user->userProfile()->create([
            'name' => 'Salvador',
            'last_name' => 'Ramirez Ramirez'
        ]);

        $user = User::factory()->create([
            'nickname' => 'jacob_ramirez',
            'email' => 'jacob_ramirez@vecsa.com',
            'password' => 'JacobRamirez%2024%%'
        ]);
        
        $user->assignRole($role);
        
        $user->userProfile()->create([
            'name' => 'Jacob Noel',
            'last_name' => 'Ramirez Torres'
        ]);
    

        $user = User::factory()->create([
            'nickname' => 'ramon_uribe',
            'email' => 'ramon_uribe@vecsa.com',
            'password' => 'RamonUribe%2024%%'
        ]);
        
        $user->assignRole($role);
        
        $user->userProfile()->create([
            'name' => 'Ramon',
            'last_name' => 'Uribe Villegas'
        ]);
    

        $user = User::factory()->create([
            'nickname' => 'rebeca_lopez',
            'email' => 'rebeca_lopez@vecsa.com',
            'password' => 'RebecaLopez%2024%%'
        ]);
        
        $user->assignRole($role);
        
        $user->userProfile()->create([
            'name' => 'Rebeca',
            'last_name' => 'Lopez Rodriguez'
        ]);
    

        $user = User::factory()->create([
            'nickname' => 'rocio_martinez',
            'email' => 'rocio_martinez@vecsa.com',
            'password' => 'RocioMartinez%2024%%'
        ]);
        
        $user->assignRole($role);
        
        $user->userProfile()->create([
            'name' => 'Rocio',
            'last_name' => 'Martinez'
        ]);
    
        $user = User::factory()->create([
            'nickname' => 'carlos_rojas',
            'email' => 'carlos_rojas@vecsa.com',
            'password' => 'CarlosRojas%2024%%'
        ]);
        
        $user->assignRole($role);
        
        $user->userProfile()->create([
            'name' => 'Carlos Ariel',
            'last_name' => 'Rojas Portillo'
        ]);
    

        $user = User::factory()->create([
            'nickname' => 'marcos_romero',
            'email' => 'marcos_romero@vecsa.com',
            'password' => 'MarcosRomero%2024%%'
        ]);
        
        $user->assignRole($role);
        
        $user->userProfile()->create([
            'name' => 'Marcos',
            'last_name' => 'Romero Ceron'
        ]);
    

        $user = User::factory()->create([
            'nickname' => 'rosalva_sanchez',
            'email' => 'rosalva_sanchez@vecsa.com',
            'password' => 'RosalvaSanchez%2024%%'
        ]);
        
        $user->assignRole($role);
        
        $user->userProfile()->create([
            'name' => 'Rosalva',
            'last_name' => 'Sanchez Rodriguez'
        ]);
    

        $user = User::factory()->create([
            'nickname' => 'jesus_sanchez',
            'email' => 'jesus_sanchez@vecsa.com',
            'password' => 'JesusSanchez%2024%%'
        ]);
        
        $user->assignRole($role);
        
        $user->userProfile()->create([
            'name' => 'Jesus Raul',
            'last_name' => 'Sanchez Loyola'
        ]);

        $user = User::factory()->create([
            'nickname' => 'sergio_trujillo',
            'email' => 'sergio_trujillo@vecsa.com',
            'password' => 'SergioTrujillo%2024%%'
        ]);
        
        $user->assignRole($role);
        
        $user->userProfile()->create([
            'name' => 'Sergio Saul',
            'last_name' => 'Trujillo Sanchez'
        ]);
    

        $user = User::factory()->create([
            'nickname' => 'shamanta_jorge',
            'email' => 'shamanta_jorge@vecsa.com',
            'password' => 'ShamantaJorge%2024%%'
        ]);
        
        $user->assignRole($role);
        
        $user->userProfile()->create([
            'name' => 'Shamanta Yanin',
            'last_name' => 'Jorge Rodriguez'
        ]);
    

        // $user = User::factory()->create([
        //     'nickname' => 'fabian_tapia',
        //     'email' => 'fabian_tapia@vecsa.com',
        //     'password' => 'FabianTapia%2024%%'
        // ]);
        
        // $user->assignRole($role);
        
        // $user->userProfile()->create([
        //     'name' => 'Fabian Alonso',
        //     'last_name' => 'Tapia Noe'
        // ]);
    

        $user = User::factory()->create([
            'nickname' => 'jorge_torres',
            'email' => 'jorge_torres@vecsa.com',
            'password' => 'JorgeTorres%2024%%'
        ]);
        
        $user->assignRole($role);
        
        $user->userProfile()->create([
            'name' => 'Jorge Ambrosio',
            'last_name' => 'Torres Cruz'
        ]);
    

        $user = User::factory()->create([
            'nickname' => 'jorge_vazquez',
            'email' => 'jorge_vazquez@vecsa.com',
            'password' => 'JorgeVazquez%2024%%'
        ]);
        
        $user->assignRole($role);
        
        $user->userProfile()->create([
            'name' => 'Jorge Luis',
            'last_name' => 'Vazquez Morales'
        ]);
    

        $user = User::factory()->create([
            'nickname' => 'gilberto_vidal',
            'email' => 'gilberto_vidal@vecsa.com',
            'password' => 'GilbertoVidal%2024%%'
        ]);
        
        $user->assignRole($role);
        
        $user->userProfile()->create([
            'name' => 'Gilberto',
            'last_name' => 'Vidal Andrade'
        ]);
    

        $user = User::factory()->create([
            'nickname' => 'yovana_barquera',
            'email' => 'yovana_barquera@vecsa.com',
            'password' => 'YovanaBarquera%2024%%'
        ]);
        
        $user->assignRole($role);
        
        $user->userProfile()->create([
            'name' => 'Yovana',
            'last_name' => 'Barquera Cardoso'
        ]);
    

        $user = User::factory()->create([
            'nickname' => 'javier_zavala',
            'email' => 'javier_zavala@vecsa.com',
            'password' => 'JavierZavala%2024%%'
        ]);
        
        $user->assignRole($role);
        
        $user->userProfile()->create([
            'name' => 'Javier Jesus',
            'last_name' => 'Zavala Acevedo'
        ]);

    }
}
