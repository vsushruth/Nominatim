<?php
// 11 + 1 BDD
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

class TestDetails extends \PHPUnit\Framework\TestCase
{
    public function testdbTestDataProvider() {
        $unit_test_dsn = getenv('UNIT_TEST_DSN') != false ? getenv('UNIT_TEST_DSN') : 'pgsql:dbname=nominatim_unit_tests';

        {
            $aDSNParsed = \Nominatim\DB::parseDSN($unit_test_dsn);
            $sDbname = $aDSNParsed['database'];
            $aDSNParsed['database'] = 'postgres';

            $oDB = new \Nominatim\DB(\Nominatim\DB::generateDSN($aDSNParsed));
            $oDB->connect();
            $oDB->exec('DROP DATABASE IF EXISTS ' . $sDbname);
            $oDB->exec('CREATE DATABASE ' . $sDbname);
        }

        $oDB = new \Nominatim\DB($unit_test_dsn);
        $oDB->connect();

        $oDB->exec('CREATE TABLE table1 (id integer, firstName varchar, gender varchar)');
        $oDB->exec("INSERT INTO table1 VALUES (1, 'Tom', 'Male'), (2, 'Mary', 'Female'), (3, 'Jacob', 'Male')");

        /** -------------------------------------------------------------------------------------------
         * UT - 46
         * Test getDBQuoted() for correct return value
         */
        $this->assertEquals(
            "'HelloWorld'",
            $oDB->getDBQuoted('HelloWorld')
        );
        
        
        /** -------------------------------------------------------------------------------------------
         * UT - 47
         * Test getArraySQL() for correct return value
         */
        $this->assertEquals(
            "ARRAY[a,3,7.1,X]",
            $oDB->getArraySQL(['a', 3, 7.1, 'X'])
        );

        $this->assertEquals(
            "ARRAY[Hello,World,2077]",
            $oDB->getArraySQL(['Hello', 'World', 2077])
        );


        /** -------------------------------------------------------------------------------------------
         * UT - 48
         * Test tableExists() for True/False
         */
        $this->assertTrue($oDB->tableExists('table1'));
        $this->assertFalse($oDB->tableExists('table99'));
        $this->assertFalse($oDB->tableExists(null));


        /** -------------------------------------------------------------------------------------------
         * UT - 50
         * Test getAssoc() for True condition
         */

        $this->assertEquals(
            array('Tom' => 'Male', 'Mary' => 'Female', 'Jacob' => 'Male'),
            $oDB->getAssoc('SELECT firstName, gender FROM table1')
        );

        $this->assertEquals(
            array(),
            $oDB->getAssoc('SELECT firstName, gender FROM table1 WHERE id=999')
        );



        /** -------------------------------------------------------------------------------------------
         * UT - 51
         * Test getRow() for True condition
         */
        $this->assertEquals(
            array('id' => 1, 'firstname' => 'Tom', 'gender' => 'Male'),
            $oDB->getRow('SELECT * FROM table1 WHERE id=1')
        );

        $this->assertEquals(
            false,
            $oDB->getRow('SELECT * FROM table1 WHERE id=999')
        );



        /** -------------------------------------------------------------------------------------------
         * UT - 53
         * Test getCol() for True condition
         */
        $this->assertEquals(
            array('Tom', 'Mary', 'Jacob'),
            $oDB->getCol('SELECT firstName FROM table1')
        );
        $this->assertEquals(
            array(),
            $oDB->getCol('SELECT firstName FROM table1 WHERE id=999')
        );



        /** -------------------------------------------------------------------------------------------
         * UT - 55
         * Test getOne() for True condition
         * 
         */
        $this->assertEquals(
            'Jacob',
            $oDB->getOne('SELECT firstName FROM table1 WHERE id=3')
        );
        $this->assertEquals(
            null,
            $oDB->getOne('SELECT firstName FROM table1 WHERE id=999')
        );

    }


    /** -------------------------------------------------------------------------------------------
     * UT - 49
     * Test getAssoc() for Exception
     * 
     */
    public function testGetAssocException(){
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
        $oDB->exec('CREATE TABLE table1 (id integer, firstName varchar, gender varchar)');

        $this->assertNull($oDB->getAssoc('Hello'));
    }


    /** -------------------------------------------------------------------------------------------
    * UT - 52
    * Test getRow() for Exception
    */
    public function testGetRowException(){
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
        $oDB->exec('CREATE TABLE table1 (id integer, firstName varchar, gender varchar)');

        $this->assertNull($oDB->getRow('Hello'));
    }


    /** -------------------------------------------------------------------------------------------
     * UT - 54
     * Test getCol() for Exception
     * 
     */
    public function testGetColException(){
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
        $oDB->exec('CREATE TABLE table1 (id integer, firstName varchar, gender varchar)');


        $this->assertNull($oDB->getCol(' Hello '));
    }


    
    /** -------------------------------------------------------------------------------------------
     * UT - 56
     * Test getOne() for Exception
     * 
     */
    public function testGetOneException(){

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
        $oDB->exec('CREATE TABLE table1 (id integer, firstName varchar, gender varchar)');

        $this->assertNull($oDB->getOne(' Hello '));
    }
}


