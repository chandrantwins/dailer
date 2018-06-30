<?php
use App\Setting;
use Illuminate\Database\Seeder;

class CloserSettingSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $payout_closer = new Setting();
        $payout_closer->key = 'payout_closer';
        $payout_closer->name = 'Payout closer';
        $payout_closer->value = '50';
        $payout_closer->save();
    }
}
