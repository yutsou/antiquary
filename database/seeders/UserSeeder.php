<?php

namespace Database\Seeders;

use Carbon\Carbon;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

class UserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        DB::table('users')->insert([
            'name' => 'Jason',
            'email' => 'auctioneer@mail.com',
            'password' => Hash::make('eagle111'),
            'phone' => '0912123123',
            'role' => 0,
        ]);

        DB::table('users')->insert([
            'name' => 'John',
            'email' => 'expert@mail.com',
            'password' => Hash::make('eagle111'),
            'phone' => '0912123123',
            'role' => 1,
        ]);

        DB::table('users')->insert([
            'name' => '鄒瑜',
            'email' => 'evilfishcoco@hotmail.com',
            'password' => Hash::make('eagle111'),
            'phone' => '0912649739',
            'role' => 3,
            'email_verified_at'=>Carbon::now(),
            'phone_verified_at'=>Carbon::now(),
            'bank_name'=>'台灣銀行',
            'bank_branch_name'=>'桃園分行',
            'bank_account_number'=>'0000145623',
            'bank_account_name'=>'鄒瑜',
            'premium_rate'=> 0.02,
            'commission_rate'=> 0.03
        ]);

        DB::table('users')->insert([
            'name' => '小明',
            'email' => 'evilfishcoco@gmail.com',
            'password' => Hash::make('eagle111'),
            'phone' => '0912649739',
            'role' => 3,
            'email_verified_at'=>Carbon::now(),
            'phone_verified_at'=>Carbon::now(),
            'premium_rate'=> 0.02,
            'commission_rate'=> 0.03
        ]);

    }
}
