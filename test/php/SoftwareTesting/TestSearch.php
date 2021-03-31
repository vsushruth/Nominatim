<?php

namespace Nominatim;

require_once(CONST_LibDir.'/ParameterParser.php');
require_once(CONST_LibDir.'/lib.php');
require_once(CONST_LibDir.'/DB.php');


function userError($sError)
{
    throw new \Exception($sError);
}

// subclassing so we can set the protected connection variable
class NominatimSubClassedDB extends \Nominatim\DB
{
    public function setConnection($oConnection)
    {
        $this->connection = $oConnection;
    }

    public function exec($sSQL, $aInputVars = null, $sErrMessage = 'Database query failed')
    {
        if ($sSQL == "Select * from placex")
            $val = 10; // simulating the number of rows affecting as 10
        else
            $val = 5;
        return $val;
    }
}
class TestReverse extends \PHPUnit\Framework\TestCase
{
     /** ---------------------------------------------------------------------------------
     * UT - 1-3
     * TESTING DBConnect() METHOD
     * 
     * Testing for True/False Outputs
    */
    public function testDBConnect()
    {
        $this->expectException(DatabaseError::class);
        $oDB = new NominatimSubClassedDB('');
        $oDB->setConnection('db');
        $this->assertTrue($oDB->connect(false));
        $this->assertTrue($oDB->connect(true));
        $this->assertTrue($oDB->connect(true, 'asd'));
    }
         /** ---------------------------------------------------------------------------------
     * UT - 4-5
     * TESTING exec() METHOD
     * 
     * Testing for True/False Outputs
    */
    public function testExec()
    {
        $oDB = new NominatimSubClassedDB('');
        $oDB->setConnection('db');
        $this->assertEquals(10, $oDB->exec('Select * from placex'));

        $this->assertNotEquals(10, $oDB->exec('Select * from country_code'));

    }

    public function test_set_exception_handler_by_format()
    {

    }
}