<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class ImageSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        DB::table('images')->insert([
            'url'=>'/storage/images/category/8587ac3a6761e9ec6e93f561670a87ee.jpg',
            'path'=>'public/images/category/8587ac3a6761e9ec6e93f561670a87ee.jpg',
            'alt'=>'手錶',
            'imageable_id'=>1,
            'imageable_type'=>'App\Models\Category'
        ]);
    }
}
