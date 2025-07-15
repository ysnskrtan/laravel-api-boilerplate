<?php

namespace Database\Seeders;

use App\Models\User;
use App\Models\Post;
// use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        // Call the roles and permissions seeder first
        $this->call([
            RolesAndPermissionsSeeder::class,
        ]);

        // Create admin user
        $admin = User::factory()->create([
            'name' => 'Admin User',
            'email' => 'admin@example.com',
        ]);
        $admin->assignRole('admin');

        // Create test user
        $testUser = User::factory()->create([
            'name' => 'Test User',
            'email' => 'test@example.com',
        ]);
        $testUser->assignRole('user');

        // Create additional users
        $users = User::factory(8)->create();
        foreach ($users as $user) {
            $user->assignRole('user');
        }

        // Create posts for demo
        $allUsers = User::all();
        
        // Create published posts
        Post::factory(25)
            ->published()
            ->recycle($allUsers)
            ->create();

        // Create draft posts
        Post::factory(15)
            ->draft()
            ->recycle($allUsers)
            ->create();

        // Create archived posts
        Post::factory(10)
            ->archived()
            ->recycle($allUsers)
            ->create();

        // Create some featured posts with specific categories
        Post::factory(5)
            ->published()
            ->withFeaturedImage()
            ->withCategory('Technology')
            ->popular()
            ->forUser($admin)
            ->create();

        Post::factory(5)
            ->published()
            ->longForm()
            ->withCategory('Business')
            ->recent()
            ->forUser($testUser)
            ->create();

        // Create posts with specific tags
        Post::factory(3)
            ->published()
            ->withTags(['laravel', 'php', 'api', 'development'])
            ->withCategory('Technology')
            ->create();

        Post::factory(3)
            ->published()
            ->withTags(['business', 'entrepreneur', 'startup'])
            ->withCategory('Business')
            ->create();

        $this->command->info('Database seeded successfully!');
        $this->command->info('Admin user: admin@example.com');
        $this->command->info('Test user: test@example.com');
        $this->command->info('Password for all users: password');
    }
}
