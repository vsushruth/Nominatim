<?php

namespace Nominatim;

require_once(CONST_LibDir.'/lib.php');
require_once(CONST_LibDir.'/DB.php');

class NominatimSubClassedDB extends \Nominatim\DB
{
    public function setConnection($oConnection)
    {
        $this->connection = $oConnection;
    }
}

class TestDeletable extends \PHPUnit\Framework\TestCase
{
    /** ---------------------------------------------------------------------------------
     * UT - 67 | UT - 68 
     * TESTING status() METHOD
     * 
     * Testing for Empty array or array of results
    */
    public function testGetAll()
    {
        $unit_test_dsn = getenv('UNIT_TEST_DSN') != false ? getenv('UNIT_TEST_DSN') : 'pgsql:dbname=nominatim_unit_tests';

        $aDSNParsed = \Nominatim\DB::parseDSN($unit_test_dsn);
        $sDbname = $aDSNParsed['database'];
        $aDSNParsed['database'] = 'postgres';

        $oDB = new \Nominatim\DB(\Nominatim\DB::generateDSN($aDSNParsed));
        $oDB->connect();
        $oDB->exec('DROP DATABASE IF EXISTS ' . $sDbname);
        $oDB->exec('CREATE DATABASE ' . $sDbname);

        $oDB = new \Nominatim\DB($unit_test_dsn);
        $oDB->connect();

        $oDB->exec('CREATE TABLE table1 (id integer, city varchar, country varchar)');
        $oDB->exec("INSERT INTO table1 VALUES (1, 'Berlin', 'Germany'), (2, 'Paris', 'France')");

        $this->assertEquals(
            array(
                array('city' => 'Berlin'),
                array('city' => 'Paris')
            ),
            $oDB->getAll('SELECT city FROM table1')
        );

        $this->assertEquals(
            array(),
            $oDB->getAll('SELECT city FROM table1 WHERE id=999')
        );
    }

    /** ---------------------------------------------------------------------------------
     * UT - 69
     * TESTING status() METHOD
     * 
     * Testing for Exception
    */
    public function testThrowExceptionGetAll()
    {
        $this->expectException(\Exception::class);
        $this->expectExceptionMessage('Database query failed');
        $this->expectExceptionCode(500);

        $unit_test_dsn = getenv('UNIT_TEST_DSN') != false ? getenv('UNIT_TEST_DSN') : 'pgsql:dbname=nominatim_unit_tests';

        $aDSNParsed = \Nominatim\DB::parseDSN($unit_test_dsn);
        $sDbname = $aDSNParsed['database'];
        $aDSNParsed['database'] = 'postgres';

        $oDB = new \Nominatim\DB(\Nominatim\DB::generateDSN($aDSNParsed));
        $oDB->connect();
        $oDB->exec('DROP DATABASE IF EXISTS ' . $sDbname);
        $oDB->exec('CREATE DATABASE ' . $sDbname);

        $oDB = new \Nominatim\DB($unit_test_dsn);
        $oDB->connect();

        $this->assertNull($oDB->getAll(null));
    }
}