<?php

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

//use database\seeds\MessageSeeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     *
     * @return void
     */
    public function run()
    {
        DB::beginTransaction();
        DB::table('message')->truncate();
        try {
            foreach (range(0, rand(10, 20)) as $_) {
                $message = new \App\Message([
                    'name' => self::quickRandom(),
                    'message' => self::quickRandom() . (rand(0, 1) ? ('<br/>' . self::quickRandom()) : ''),
                    'page_uid' => 'uid' . rand(1, 3),
                ]);
                $message->save();
            }
        } catch (\Exception $e) {
            DB::rollBack();
            return;
        }
        DB::commit();
    }

    public static function quickRandom($length = 16)
    {
        $pool = '0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ';

        return substr(str_shuffle(str_repeat($pool, 5)), 0, $length);
    }
}
