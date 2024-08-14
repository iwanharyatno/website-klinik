<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

class RoleAndPermissionSeeder extends Seeder
{
    private array $resources = ['obat', 'transaksi_obat', 'pasien', 'rekam_medis', 'resep_obat', 'supplier'];
    private array $operations = ['create', 'read', 'update', 'delete'];

    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        app()[\Spatie\Permission\PermissionRegistrar::class]->forgetCachedPermissions();
        foreach ($this->resources as $resource) {
            foreach($this->operations as $operation) {
                Permission::create(['name' => $operation . ' ' . $resource]);
            }
        }

        Role::create(['name' => 'Owner']);
        Role::create(['name' => 'Doctor']);
        $assistant = Role::create(['name' => 'Assistant']);

        $assistant->givePermissionTo(collect($this->operations)->map(fn ($item, $key) => $item . ' pasien'));
        $assistant->givePermissionTo(['create rekam_medis', 'update rekam_medis', 'read rekam_medis']);

        $pharmacist = Role::create(['name' => 'Pharmacist']);
        $pharmacist->givePermissionTo(['read resep_obat', 'read pasien', 'read obat', 'update resep_obat']);
    }
}
