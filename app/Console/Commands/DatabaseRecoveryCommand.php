<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Artisan;
use App\Models\User;
use App\Models\AboutHeroSection;
use App\Models\HeaderLayanan;

class DatabaseRecoveryCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'db:recovery {--fresh : Fresh install}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Recovery database dengan setup lengkap About dan Services';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->info('🔄 Starting Database Recovery...');
        
        if ($this->option('fresh')) {
            $this->warn('⚠️  This will wipe ALL data!');
            if (!$this->confirm('Are you sure?')) {
                return;
            }
            
            $this->call('migrate:fresh', ['--force' => true]);
        }
        
        // Essential migrations untuk About
        $aboutMigrations = [
            '2025_10_26_000904_create_about_hero_sections_table.php',
            '2025_10_26_001122_create_about_content_sections_table.php', 
            '2025_10_26_001134_create_about_testimonial_sections_table.php',
            '2025_10_27_040033_add_hero_image_to_about_hero_sections_table.php',
            '2025_10_27_041249_create_hero_pages_table.php',
            '2025_10_27_043005_remove_hero_subtitle_from_hero_pages_table.php',
        ];
        
        // Essential migrations untuk Services
        $servicesMigrations = [
            '2025_10_27_131133_create_header_layanans_table.php',
            '2025_10_27_131149_create_fitur_utamas_table.php',
            '2025_10_27_131220_create_daftar_layanans_table.php',
            '2025_10_27_144159_update_header_layanans_table_add_missing_fields.php',
        ];
        
        $this->info('🏠 Setting up About system...');
        foreach ($aboutMigrations as $migration) {
            try {
                $this->call('migrate', [
                    '--path' => "database/migrations/{$migration}",
                    '--force' => true
                ]);
            } catch (\Exception $e) {
                $this->warn("Skipping {$migration}: " . $e->getMessage());
            }
        }
        
        $this->info('💼 Setting up Services system...');
        foreach ($servicesMigrations as $migration) {
            try {
                $this->call('migrate', [
                    '--path' => "database/migrations/{$migration}",
                    '--force' => true
                ]);
            } catch (\Exception $e) {
                $this->warn("Skipping {$migration}: " . $e->getMessage());
            }
        }
        
        // Create essential data
        $this->info('🌱 Creating default data...');
        
        // Admin user
        if (!User::where('email', 'admin@jasaibnu.id')->exists()) {
            User::create([
                'name' => 'Admin',
                'email' => 'admin@jasaibnu.id', 
                'password' => bcrypt('password123')
            ]);
            $this->info('✅ Admin user created');
        }
        
        // Basic About data
        if (!AboutHeroSection::exists()) {
            AboutHeroSection::create([
                'tagline' => 'Tentang CertiPro',
                'projects_completed' => 50,
                'satisfied_customers' => 25, 
                'awards_received' => 10,
                'years_experience' => 5,
            ]);
            $this->info('✅ About hero section created');
        }
        
        // Basic Services data
        if (!HeaderLayanan::exists()) {
            HeaderLayanan::create([
                'judul_halaman' => 'Layanan Kami',
                'judul_utama' => 'Layanan Berkualitas',
                'subjudul' => 'Solusi Terbaik untuk Bisnis Anda',
                'deskripsi' => 'Kami menyediakan berbagai layanan teknologi informasi terdepan.',
                'status_aktif' => true,
            ]);
            $this->info('✅ Services header created');
        }
        
        // Storage link
        try {
            $this->call('storage:link');
            $this->info('✅ Storage linked');
        } catch (\Exception $e) {
            $this->warn('Storage link already exists');
        }
        
        $this->info('');
        $this->info('🎉 Recovery completed successfully!');
        $this->info('🔑 Login: admin@jasaibnu.id / password123');
        $this->info('🌐 Admin URLs:');
        $this->info('   • http://localhost:8000/adminui/about');  
        $this->info('   • http://localhost:8000/adminui/services');
    }
}
