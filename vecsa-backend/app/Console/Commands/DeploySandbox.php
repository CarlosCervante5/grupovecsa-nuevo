<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;

class DeploySandbox extends Command
{
    protected $signature = 'deploy:sandbox {--fresh : Drop all tables and re-run migrations} {--seed : Run seeders after migrations}';
    protected $description = 'Run migrations and seeders for sandbox deployment';

    public function handle(): int
    {
        $this->info('🚀 Starting sandbox deployment...');

        // Step 1: Run migrations
        if ($this->option('fresh')) {
            $this->info('⚠️  Running fresh migrations (dropping all tables)...');
            $this->call('migrate:fresh', ['--force' => true]);
        } else {
            $this->info('📦 Running pending migrations...');
            $this->call('migrate', ['--force' => true]);
        }

        // Step 2: Run seeders if requested
        if ($this->option('seed') || $this->option('fresh')) {
            $this->info('🌱 Running seeders...');

            // Core seeders (order matters)
            $seeders = [
                'DeveloperUserSeeder',       // Roles, permissions, test users (including gerente)
                'VehicleDataSeeder',         // Vehicle brands, lines, models
                'VehicleInventorySeeder',    // Vehicle inventory
                'BoutiqueCategoriesSeeder',  // Boutique categories
                'CategoryHierarchySeeder',   // Category parent-child relationships
                'BoutiqueProductsSeeder',    // Boutique products
                'MigrateVariantsToAttributesSeeder', // Product attributes & variants
                'BoutiqueBannerSeeder',      // Boutique banners (3 default)
                'HomeContentSeeder',         // Home slides & testimonials
                'TestCustomersSeeder',       // Test customers for rewards
                'CampaignsSeeder',           // Marketing campaigns
            ];

            foreach ($seeders as $seeder) {
                $class = "Database\\Seeders\\{$seeder}";
                if (class_exists($class)) {
                    $this->info("  → Running {$seeder}...");
                    $this->call('db:seed', ['--class' => $seeder, '--force' => true]);
                } else {
                    $this->warn("  ⚠ Skipping {$seeder} (class not found)");
                }
            }
        }

        $this->info('');
        $this->info('✅ Sandbox deployment complete!');
        $this->info('');
        $this->info('Test users:');
        $this->info('  dev@vecsa.com / Developer%2024%%');
        $this->info('  admin@vecsa.com / TestUser%2024%%');
        $this->info('  gerente@vecsa.com / TestUser%2024%%');
        $this->info('  marketing@vecsa.com / TestUser%2024%%');
        $this->info('  client@vecsa.com / TestUser%2024%%');

        return Command::SUCCESS;
    }
}
