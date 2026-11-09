<?php
namespace Tests\Integration;

use Illuminate\Support\Facades\DB;
use Tests\Feature\SpmbRegressionTest;

/**
 * Executes the existing SPMB regression against an isolated PostgreSQL test DB.
 * Never point this at Supabase production.
 */
class PostgresDeploymentTest extends SpmbRegressionTest
{
    protected function setUp(): void {
        if (getenv('RUN_POSTGRES_TESTS') !== '1') {
            parent::setUp();
            $this->markTestSkipped('PostgreSQL integration requires an isolated *_test database.');
        }
        parent::setUp();
        $database=config('database.connections.pgsql.database');
        if (!is_string($database) || !str_ends_with($database,'_test')) {
            throw new \RuntimeException('PostgreSQL integration is restricted to a *_test database.');
        }
        config(['database.default'=>'pgsql']);
        DB::purge('pgsql');
        $this->artisan('migrate',['--force'=>true])->assertExitCode(0);
        DB::connection()->beginTransaction();
    }

    protected function tearDown(): void {
        if (getenv('RUN_POSTGRES_TESTS')==='1' && config('database.default')==='pgsql') {
            while(DB::connection()->transactionLevel()>0) DB::connection()->rollBack();
        }
        parent::tearDown();
    }
}
