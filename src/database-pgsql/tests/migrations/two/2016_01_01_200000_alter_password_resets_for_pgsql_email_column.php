<?php

declare(strict_types=1);
/**
 * This file is part of Hyperf.
 *
 * @link     https://www.hyperf.io
 * @document https://hyperf.wiki
 * @contact  group@hyperf.io
 * @license  https://github.com/hyperf/hyperf/blob/master/LICENSE
 */
use Hyperf\Database\Migrations\Migration;
use Hyperf\Database\Schema\Blueprint;
use Hyperf\Database\Schema\Schema;

class AlterPasswordResetsForPgsqlEmailColumn extends Migration
{
    /**
     * Run the migrations.
     */
    public function up()
    {
        Schema::table('password_resets_for_pgsql', function (Blueprint $table) {
            $table->string('email')->comment('邮箱')->change();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down()
    {
        if (Schema::hasTable('password_resets_for_pgsql')) {
            Schema::table('password_resets_for_pgsql', function (Blueprint $table) {
                $table->string('email')->index()->change();
            });
        }
    }
}
