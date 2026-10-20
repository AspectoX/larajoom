<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Schema;

/**
 * Datos de usuario de LaraJoom.
 *
 * - Borra los datos de usuarios (perfil, mapa de roles y usuarios).
 * - Quita la columna `avatar` de la tabla `users` si quedó de una migración
 *   de agregados que ya no existe (el avatar vive en el perfil).
 * - Crea el administrador con su perfil y el rol SuperAdmin (9).
 */
class LaraJoomUserSeeder extends Seeder
{
    public function run(): void
    {
        $this->removeLegacyAvatarColumn();
        $this->clean();
        $this->createAdmin();
    }

    /**
     * Quita la columna avatar que dejaron las migraciones de agregados (ya no existen).
     */
    protected function removeLegacyAvatarColumn(): void
    {
        if (Schema::hasTable('users') && Schema::hasColumn('users', 'avatar')) {
            Schema::table('users', function (Blueprint $table) {
                $table->dropColumn('avatar');
            });
        }
    }

    /**
     * Borra los datos de usuarios.
     */
    protected function clean(): void
    {
        foreach (['larajoom_user_profile', 'larajoom_user_usergroup_map', 'larajoom_users', 'users'] as $table) {
            if (Schema::hasTable($table)) {
                DB::table($table)->truncate();
            }
        }
    }

    /**
     * Administrador con su perfil y el rol SuperAdmin.
     */
    protected function createAdmin(): void
    {
        $now  = now();
        $pass = Hash::make('password');

        if (Schema::hasTable('larajoom_users')) {
            DB::table('larajoom_users')->insert([
                'id'           => 1,
                'name'         => 'Administrator',
                'username'     => 'admin',
                'email'        => 'test@test.ax',
                'password'     => $pass,
                'group_id'     => 9,
                'activated'    => 1,
                'params'       => json_encode([]),
                'registerDate' => $now,
                'created_at'   => $now,
                'updated_at'   => $now,
            ]);
        }

        if (Schema::hasTable('larajoom_user_profile')) {
            DB::table('larajoom_user_profile')->insert([
                'user_id'       => 1,
                'name'          => 'Administrator',
                'username'      => 'admin',
                'email'         => 'test@test.ax',
                'password'      => $pass,
                'block'         => 0,
                'activated'     => 1,
                'sendEmail'     => 1,
                'params'        => null,
                'registerDate'  => $now,
                'lastvisitDate' => $now,
                'lastResetTime' => null,
                'resetCount'    => 0,
                'avatar'        => null,
                'created_at'    => $now,
                'updated_at'    => $now,
            ]);
        }

        if (Schema::hasTable('larajoom_user_usergroup_map')) {
            DB::table('larajoom_user_usergroup_map')->insert([
                'user_id'  => 1,
                'group_id' => 9,
            ]);
        }
    }
}
