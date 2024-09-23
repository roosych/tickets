<?php

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddLdapColumnsToUsersTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        $driver = Schema::getConnection()->getDriverName();

        Schema::table('users', function (Blueprint $table) use ($driver) {
            $table->string('guid')->nullable();
            $table->string('domain')->nullable();
            $table->string('department')->nullable();
            $table->string('manager')->nullable();
            $table->string('is_manager')->nullable();
            $table->string('distinguishedname')->nullable();
            $table->string('position')->nullable();
            $table->string('mailnickname')->unique();
            $table->string('mobile')->nullable();
            $table->string('pager')->nullable();
            $table->string('status')->nullable();

            if ($driver !== 'sqlsrv') {
                $table->unique('guid');
            }
        });

        if ($driver === 'sqlsrv') {
            DB::statement(
                $this->compileUniqueSqlServerIndexStatement('users', 'guid')
            );
        }
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn(['guid', 'domain']);
        });
    }

    /**
     * Compile a compatible "unique" SQL Server index constraint.
     *
     * @param string $table
     * @param string $column
     *
     * @return string
     */
    protected function compileUniqueSqlServerIndexStatement($table, $column)
    {
        return sprintf('create unique index %s on %s (%s) where %s is not null',
            implode('_', [$table, $column, 'unique']),
            $table,
            $column,
            $column
        );
    }
}
