<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\User;

class UserSeeder extends Seeder
{
    public function run()
    {
        /* ----------------------------
            ログイン可能な管理者
        ----------------------------- */

        User::factory()->admin()->create([
            'name'     => '管理者',
            'email'    => 'admin@example.com',
            'password' => bcrypt('test4343'),
        ]);

        /* ----------------------------
            ログイン可能なスタッフ
        ----------------------------- */

        User::factory()->staff()->create([
            'name'     => '西 玲奈',
            'email'    => 'reina.n@coachtech.com',
            'password' => bcrypt('test4343'),
        ]);

        /* ----------------------------
            ダミースタッフ（ログイン不要）
            【理由】勤怠データ生成用のスタッフであり、ログインテストには使用しない。
        ----------------------------- */

        $dummyStaffs = [
            ['name' => '山田 太郎',   'email' => 'taro.y@coachtech.com'],
            ['name' => '増田 一世',   'email' => 'issei.m@coachtech.com'],
            ['name' => '山本 敬吉',   'email' => 'keikichi.y@coachtech.com'],
            ['name' => '秋田 朋美',   'email' => 'tomomi.a@coachtech.com'],
            ['name' => '中西 教夫',   'email' => 'norio.n@coachtech.com'],
        ];

        foreach ($dummyStaffs as $staff) {
            User::factory()->staff()->create($staff);
        }
    }
}
