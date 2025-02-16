<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;

final class TeamworkSetupTables extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table(Config::get('teamwork.users_table'), function (Blueprint $table): void {
            $table->integer('current_team_id')->unsigned()->nullable();
        });

        Schema::create(Config::get('teamwork.teams_table'), function (Blueprint $table): void {
            $table->increments('id')->unsigned();
            $table->ulid('owner_id')->nullable();
            $table->string('name');
            $table->timestamps();
        });

        Schema::create(Config::get('teamwork.team_user_table'), function (Blueprint $table): void {
            $table->ulid('user_id');
            $table->integer('team_id')->unsigned();
            $table->timestamps();

            $table->foreign('user_id')
                ->references(Config::get('teamwork.user_foreign_key'))
                ->on(Config::get('teamwork.users_table'))
                ->onUpdate('cascade')
                ->onDelete('cascade');

            $table->foreign('team_id')
                ->references('id')
                ->on(Config::get('teamwork.teams_table'))
                ->onDelete('cascade');
        });

        Schema::create(Config::get('teamwork.team_invites_table'), function (Blueprint $table): void {
            $table->increments('id');
            $table->ulid('user_id');
            $table->integer('team_id')->unsigned();
            $table->enum('type', ['invite', 'request']);
            $table->string('email');
            $table->string('accept_token');
            $table->string('deny_token');
            $table->timestamps();
            $table->foreign('team_id')
                ->references('id')
                ->on(Config::get('teamwork.teams_table'))
                ->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table(Config::get('teamwork.users_table'), function (Blueprint $table): void {
            $table->dropColumn('current_team_id');
        });

        Schema::table(Config::get('teamwork.team_user_table'), function (Blueprint $table): void {
            if ('sqlite' !== DB::getDriverName()) {
                $table->dropForeign(Config::get('teamwork.team_user_table') . '_user_id_foreign');
            }
            if ('sqlite' !== DB::getDriverName()) {
                $table->dropForeign(Config::get('teamwork.team_user_table') . '_team_id_foreign');
            }
        });

        Schema::drop(Config::get('teamwork.team_user_table'));
        Schema::drop(Config::get('teamwork.team_invites_table'));
        Schema::drop(Config::get('teamwork.teams_table'));
    }
}
