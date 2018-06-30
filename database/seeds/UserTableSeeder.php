<?php

use App\User;
use Illuminate\Database\Seeder;

class UserTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $admin = new User();
        $admin->first_name = 'Admin';
        $admin->last_name = 'Admin';
        $admin->username = 'admin';
        $admin->email = 'admin@mail.com';
        $admin->affiliate = '00000000';
        $admin->password = bcrypt('admin');
        $admin->role = User::ADMIN;
        $admin->save();

    	$subadmin = new User();
        $subadmin->first_name = 'subAdmin';
        $subadmin->last_name = 'subAdmin';
        $subadmin->username = 'subadmin';
        $subadmin->email = 'subadmin@mail.com';
        $subadmin->affiliate = '1111111111';
        $subadmin->password = bcrypt('subadmin');
        $subadmin->role = User::SUBADMIN;
        $subadmin->save();

        $candidate = new User();
        $candidate->first_name = 'candidate';
        $candidate->last_name = 'candidate';
        $candidate->username = 'candidate';
        $candidate->email = 'candidate@mail.com';
        $candidate->affiliate = '22222222';
        $candidate->password = bcrypt('candidate');
        $candidate->role = User::CANDIDATE;
        $candidate->save();

        $company = new User();
        $company->first_name = 'company';
        $company->last_name = 'company';
        $company->username = 'company';
        $company->email = 'company@mail.com';
        $company->affiliate = '33333333';
        $company->password = bcrypt('company');
        $company->role = User::COMPANY;
        $company->save();
    }
}
