<?php
namespace Tests\Unit;

use App\Database\PostgresBooleanConnection;
use App\Models\AdmissionPeriod;
use Illuminate\Database\{PostgresConnection,MySqlConnection};
use Illuminate\Support\Facades\{DB,Event};
use Tests\TestCase;

class PostgresBooleanBindingTest extends TestCase
{
    public function test_pgsql_resolver_is_registered_before_lazy_connection_creation():void
    {
        $this->assertInstanceOf(PostgresBooleanConnection::class,DB::connection('pgsql'));
        $this->assertInstanceOf(MySqlConnection::class,DB::connection('mysql'));
    }
    public function test_boolean_strings_preserve_other_types_keys_dates_and_resources():void
    {
        $connection=new PostgresBooleanConnection(fn()=>null,'test');$stream=fopen('php://memory','r+');
        try {
            $values=$connection->prepareBindings(['yes'=>true,'no'=>false,'id'=>1,'zero'=>0,'text'=>'1','null'=>null,'date'=>new \DateTimeImmutable('2026-11-15 12:34:56'),'stream'=>$stream]);
            $this->assertSame(['yes'=>'true','no'=>'false','id'=>1,'zero'=>0,'text'=>'1','null'=>null,'date'=>'2026-11-15 12:34:56','stream'=>$stream],$values);
        }finally{fclose($stream);}
    }
    public function test_pgsql_boolean_bindings_are_sent_as_strings_not_integers():void
    {
        $connection=new PostgresBooleanConnection(fn()=>null,'test');
        $statement=\Mockery::mock(\PDOStatement::class);
        $statement->shouldReceive('bindValue')->once()->with(1,'true',\PDO::PARAM_STR);
        $statement->shouldReceive('bindValue')->once()->with(2,'false',\PDO::PARAM_STR);
        $statement->shouldReceive('bindValue')->once()->with(3,17,\PDO::PARAM_INT);
        $connection->bindValues($statement,$connection->prepareBindings([true,false,17]));
        $this->assertTrue(true);
    }
    public function test_mysql_and_stock_laravel_bindings_remain_integer():void
    {
        $this->assertSame([1,0],(new MySqlConnection(fn()=>null,'test'))->prepareBindings([true,false]));
        $this->assertSame([1,0],(new PostgresConnection(fn()=>null,'test'))->prepareBindings([true,false]));
        $this->assertSame([1,0],(new PostgresBooleanConnection(fn()=>null,'test'))->prepareBindings([1,0]));
    }
    public function test_only_pgsql_declared_boolean_model_attributes_are_normalized():void
    {
        $model=(new AdmissionPeriod(['is_active'=>1,'quota'=>1]))->setConnection('pgsql');
        Event::dispatch('eloquent.saving: '.AdmissionPeriod::class,[$model]);
        $this->assertTrue($model->getAttributes()['is_active']);$this->assertSame(1,$model->getAttributes()['quota']);
        $model->is_active='0';Event::dispatch('eloquent.saving: '.AdmissionPeriod::class,[$model]);$this->assertFalse($model->getAttributes()['is_active']);
        $mysql=(new AdmissionPeriod(['is_active'=>1,'quota'=>1]))->setConnection('mysql');
        Event::dispatch('eloquent.saving: '.AdmissionPeriod::class,[$mysql]);$this->assertSame(1,$mysql->getAttributes()['is_active']);
    }
}