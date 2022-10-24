<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class CategorySeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        DB::table('categories')->insert([
            '_lft'=>1,
            '_rgt'=>2,
            'name'=>'手錶',
            'url_name'=>'watch',
            'color_hex'=>'#003a6c'
        ]);

        DB::table('categories')->insert([
            '_lft'=>2,
            '_rgt'=>3,
            'parent_id'=>1,
            'name'=>'勞力士',
            'url_name'=>'rolex',
            'color_hex'=>'#003a6c'
        ]);
    }
}
