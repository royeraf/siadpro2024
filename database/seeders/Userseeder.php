<?php

namespace Database\Seeders;

use App\Models\User; 

use Illuminate\Database\Seeder;


class Userseeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {   User::create([
            'name' => 'Zevallos Galarza Brian Alan',
            'email' => '2019210132@udh.edu.pe',
            'password' => bcrypt('12345678'),
            'cargo' => 'No',
            'provincia' => 'HUÁNUCO',
            'estado' => '1',
            'distrito' => 'HUÁNUCO',
            'dni' => '12345678',
        ])->assignRole('Admin');

    }
}