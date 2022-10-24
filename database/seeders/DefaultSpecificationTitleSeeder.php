<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class DefaultSpecificationTitleSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        DB::table('default_specification_titles')->insert([
            'category_id'=>1,
            'title'=>'品牌',
        ]);
        DB::table('default_specification_titles')->insert([
            'category_id'=>1,
            'title'=>'型號',
        ]);
        DB::table('default_specification_titles')->insert([
            'category_id'=>1,
            'title'=>'參考編號',
        ]);
        DB::table('default_specification_titles')->insert([
            'category_id'=>1,
            'title'=>'性別',
        ]);
        DB::table('default_specification_titles')->insert([
            'category_id'=>1,
            'title'=>'表盤材質',
        ]);
        DB::table('default_specification_titles')->insert([
            'category_id'=>1,
            'title'=>'機芯',
        ]);
    }
}
