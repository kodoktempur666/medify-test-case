<?php

namespace Database\Seeders;

use App\Models\Category;
use App\Models\MasterItem;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     *
     * @return void
     */
    public function run()
    {
        $catObat = Category::firstOrCreate(['kode' => 'KAT-0001'], ['nama' => 'Obat']);
        $catAlkes = Category::firstOrCreate(['kode' => 'KAT-0002'], ['nama' => 'Alkes']);
        $catMatkes = Category::firstOrCreate(['kode' => 'KAT-0003'], ['nama' => 'Matkes']);
        $catUmum = Category::firstOrCreate(['kode' => 'KAT-0004'], ['nama' => 'Umum']);

        $item1 = MasterItem::firstOrCreate(
            ['kode' => '00001'],
            [
                'nama' => 'Paracetamol 500mg',
                'harga_beli' => 5000,
                'laba' => 20,
                'supplier' => 'Tokopaedi',
                'jenis' => 'Obat',
            ]
        );
        $item1->categories()->sync([$catObat->id]);

        $item2 = MasterItem::firstOrCreate(
            ['kode' => '00002'],
            [
                'nama' => 'Spuit 3cc / Alat Suntik',
                'harga_beli' => 15000,
                'laba' => 15,
                'supplier' => 'Bukulapuk',
                'jenis' => 'Alkes',
            ]
        );
        $item2->categories()->sync([$catAlkes->id]);

        $item3 = MasterItem::firstOrCreate(
            ['kode' => '00003'],
            [
                'nama' => 'Kasa Steril Husada',
                'harga_beli' => 8500,
                'laba' => 25,
                'supplier' => 'TokoBagas',
                'jenis' => 'Matkes',
            ]
        );
        $item3->categories()->sync([$catMatkes->id, $catUmum->id]);
    }
}
