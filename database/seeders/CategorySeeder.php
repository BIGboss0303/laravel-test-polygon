<?php

namespace Database\Seeders;

use App\Models\Category;
use Illuminate\Database\Seeder;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;

class CategorySeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $movies = Category::firstOrCreate(['name' => 'Фильмы']);
        $series = Category::firstOrCreate(['name' => 'Сериалы']);

        Category::upsert([
            ['name' => 'ТВ-программы', 'parent_id' => null],
            ['name' => 'Мультфильмы', 'parent_id' => $movies->id],
            ['name' => 'Аниме', 'parent_id' => $series->id]
        ], 'name');
    }
}
