<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        // Limpa dados respeitando FKs (MySQL)
        Schema::disableForeignKeyConstraints();
        DB::table('transactions')->truncate();
        DB::table('users')->truncate();
        Schema::enableForeignKeyConstraints();

        $this->call([
            UserSeeder::class,
            TransactionSeeder::class,
        ]);
    }
}
