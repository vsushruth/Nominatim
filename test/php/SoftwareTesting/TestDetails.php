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
    public function dbTestDataProvider() {
        $unit_test_dsn = getenv('UNIT_TEST_DSN') != false ?
                            getenv('UNIT_TEST_DSN') :
                            'pgsql:dbname=nominatim_unit_tests';

        $this->assertRegExp(
            '/unit_test/',
            $unit_test_dsn,
            'Test database will get destroyed, thus should have a name like unit_test to be safe'
        );

        ## Create the database.
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
         * UT - 55
         * Test getDBQuoted() for correct return value
         */
        $this->assertEquals(
            "'HelloWorld'",
            $oDB->getDBQuoted('HelloWorld')
        );
        
        
        /** -------------------------------------------------------------------------------------------
         * UT - 56
         * Test getArraySQL() for correct return value
         */
        $this->assertEquals(
            "ARRAY['a', 3, 7.1, 'X']",
            $oDB->getArraySQL(['a', 3, 7.1, 'X'])
        );

        $this->assertEquals(
            "ARRAY['Hello', 'World', 2077]",
            $oDB->getArraySQL(['Hello', 'World', 2077])
        );


        /** -------------------------------------------------------------------------------------------
         * UT - 57
         * Test tableExists() for True/False
         */
        $this->assertTrue($this->$oDB->tableExists('table1'));
        $this->assertFalse($this->$oDB->tableExists('table99'));
        $this->assertFalse($this->$oDB->tableExists(null));


        /** -------------------------------------------------------------------------------------------
         * UT - 58
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
         * UT - 59
         * Test getAssoc() for Exception
         */
        $this->expectException(\Exception::class);
        $this->expectExceptionMessage('Database query failed');
        $this->expectExceptionCode(500);

        $this->assertNull($oDB->getAssoc(' Hello '));
    

        /** -------------------------------------------------------------------------------------------
         * UT - 60
         * Test getRow() for True condition
         */
        $this->assertEquals(
            array('id' => 1, 'firstName' => 'Tom', 'gender' => 'Male'),
            $this->$oDB->getRow('SELECT * FROM table1 WHERE id=1')
        );

        $this->assertEquals(
            false,
            $this->$oDB->getRow('SELECT * FROM table1 WHERE id=999')
        );

        /** -------------------------------------------------------------------------------------------
         * UT - 61
         * Test getRow() for Exception
         */
        $this->expectException(\Exception::class);
        $this->expectExceptionMessage('Database query failed');
        $this->expectExceptionCode(500);

        $this->assertNull($oDB->getRow(' Hello '));


        /** -------------------------------------------------------------------------------------------
         * UT - 62
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
         * UT - 63
         * Test getCol() for Exception
         */
        $this->expectException(\Exception::class);
        $this->expectExceptionMessage('Database query failed');
        $this->expectExceptionCode(500);

        $this->assertNull($oDB->getCol(' Hello '));


        /** -------------------------------------------------------------------------------------------
         * UT - 64
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

        /** -------------------------------------------------------------------------------------------
         * UT - 65
         * Test getOne() for Exception
         * 
         */
        // $this->expectException(\Exception::class);
        // $this->expectExceptionMessage('Database query failed');
        // $this->expectExceptionCode(500);

        // $this->assertNull($oDB->getOne(' Hello '));



    }



    
    /** -------------------------------------------------------------------------------------------
     * UT - 65
     * Test getOne() for Exception
     * 
     */
    public function testGetOneException(){


        $this->expectException(\Exception::class);
        $this->expectExceptionMessage('Database query failed');
        $this->expectExceptionCode(500);


        $unit_test_dsn = getenv('UNIT_TEST_DSN') != false ?
                            getenv('UNIT_TEST_DSN') :
                            'pgsql:dbname=nominatim_unit_tests';

        $this->assertRegExp(
            '/unit_test/',
            $unit_test_dsn,
            'Test database will get destroyed, thus should have a name like unit_test to be safe'
        );

        ## Create the database.
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

        
        $this->assertNull($oDB->getOne('Hello'));
    }
}

    
    // public function testGetDBQuoted()
    // {
    //     $this->assertEquals(
    //         "'HelloWorld'",
    //         $this->$oDB->getDBQuoted('HelloWorld')
    //     );
    // }

    // /** -------------------------------------------------------------------------------------------
    //  * UT - 56
    //  * Test getArraySQL() for correct return value
    //  * 
    //  */
    // public function testGetArraySQL()
    // {

    //     // $oDB = $this->dbTestDataProvider();
    //     $this->assertEquals(
    //         "ARRAY['a', 3, 7.1, 'X']",
    //         $this->$oDB->getArraySQL(['a', 3, 7.1, 'X'])
    //     );

    //     $this->assertEquals(
    //         "ARRAY['Hello', 'World', 2077]",
    //         $this->$oDB->getArraySQL(['Hello', 'World', 2077])
    //     );
    // }

    // /** -------------------------------------------------------------------------------------------
    //  * UT - 57
    //  * Test tableExists() for True/False
    //  * 
    //  */
    // public function testTableExists(){
    //     $this->assertTrue($this->$oDB->tableExists('table1'));
    //     $this->assertFalse($this->$oDB->tableExists('table99'));
    //     $this->assertFalse($this->$oDB->tableExists(null));
    // }

    // /** -------------------------------------------------------------------------------------------
    //  * UT - 58
    //  * Test getAssoc() for True condition
    //  * 
    //  */
    // public function testGetAssocOk(){
        
    // }

    
    // /** -------------------------------------------------------------------------------------------
    //  * UT - 59
    //  * Test getAssoc() for Exception
    //  * 
    //  */
    // public function testGetAssocException(){
    //     $this->expectException(\Exception::class);
    //     $this->expectExceptionMessage('Database query failed');
    //     $this->expectExceptionCode(500);

    //     $this->assertNull($this->$oDB->getAssoc(' Hello '));
    // }

    // /** -------------------------------------------------------------------------------------------
    //  * UT - 60
    //  * Test getRow() for True condition
    //  * 
    //  */
    // public function testGetRowOk(){
    //     $this->assertEquals(
    //         array('id' => 1, 'firstName' => 'Tom', 'gender' => 'Male'),
    //         $this->$oDB->getRow('SELECT * FROM table1 WHERE id=1')
    //     );

    //     $this->assertEquals(
    //         false,
    //         $this->$oDB->getRow('SELECT * FROM table1 WHERE id=999')
    //     );
    // }

    // /** -------------------------------------------------------------------------------------------
    //  * UT - 61
    //  * Test getRow() for Exception
    //  * 
    //  */
    // public function testGetRowException(){
    //     $this->expectException(\Exception::class);
    //     $this->expectExceptionMessage('Database query failed');
    //     $this->expectExceptionCode(500);

    //     $this->assertNull($this->$oDB->getRow(' Hello '));
    // }


    // /** -------------------------------------------------------------------------------------------
    //  * UT - 62
    //  * Test getCol() for True condition
    //  * 
    //  */
    // public function testGetColOk(){
    //     $this->assertEquals(
    //         array('Tom', 'Mary', 'Jacob'),
    //         $this->$oDB->getCol('SELECT firstName FROM table1')
    //     );
    //     $this->assertEquals(
    //         array(),
    //         $this->$oDB->getCol('SELECT firstName FROM table1 WHERE id=999')
    //     );
    // }

    // /** -------------------------------------------------------------------------------------------
    //  * UT - 63
    //  * Test getCol() for Exception
    //  * 
    //  */
    // public function testGetColException(){
    //     $this->expectException(\Exception::class);
    //     $this->expectExceptionMessage('Database query failed');
    //     $this->expectExceptionCode(500);

    //     $this->assertNull($this->$oDB->getCol(' Hello '));
    // }


    // /** -------------------------------------------------------------------------------------------
    //  * UT - 64
    //  * Test getOne() for True condition
    //  * 
    //  */
    // public function testGetOneOk(){
    //     $this->assertEquals(
    //         'Jacob',
    //         $this->$oDB->getOne('SELECT firstName FROM table1 WHERE id=3')
    //     );
    //     $this->assertEquals(
    //         null,
    //         $this->$oDB->getOne('SELECT firstName FROM table1 WHERE id=999')
    //     );
    // }





