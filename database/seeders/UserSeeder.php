<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class UserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
    $owner = User::create([
            'nama' => 'Lorem Ipsum',
            'username' => 'lorem',
            'email' => 'lorem@lorem.com',
            'password' => bcrypt('lorem123'),
        ]);
        $owner->assignRole('Owner');
        $doctor = User::create([
            'nama' => 'Dolore Amet',
            'username' => 'dolor',
            'email' => 'dolore@lorem.com',
            'password' => bcrypt('dolor123'),
        ]);
        $doctor->assignRole('Doctor');
        
        $assistant = User::create([
            'nama' => 'Aliquam Nulla',
            'username' => 'nulla',
            'email' => 'nulla@lorem.com',
            'password' => bcrypt('nulla123'),
        ]);
        $assistant->assignRole('Assistant');
        
        $pharmacist = User::create([
            'nama' => 'Consetetur Elit',
            'username' => 'consetetur',
            'email' => 'consetetur@lorem.com',
            'password' => bcrypt('consetetur123'),
        ]);
        $pharmacist->assignRole('Pharmacist');
    }
}
