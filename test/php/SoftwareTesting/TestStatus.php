<?php
// 10 + 2 BDD
namespace Nominatim;

require_once(CONST_LibDir.'/DB.php');
require_once(CONST_LibDir.'/Status.php');

class TestStatus extends \PHPUnit\Framework\TestCase
{
    /** ---------------------------------------------------------------------------------
     * UT - 34
     * TESTING status() METHOD
     * 
     * Testing for No Database exception
    */
    public function testThrowNoDatabase()
    {
        $this->expectException(\Exception::class);
        $this->expectExceptionMessage('No database');
        $this->expectExceptionCode(700);

        $oDB = null;
        $oStatus = new Status($oDB);
        $this->assertEquals('No database', $oStatus->status());
    }

    /** ---------------------------------------------------------------------------------
     * UT - 35
     * TESTING status() METHOD
     * 
     * Testing for Database connection failed exception
    */
    public function testThrowDatabaseConnectionFailed()
    {
        $this->expectException(\Exception::class);
        $this->expectExceptionMessage('Database connection failed');
        $this->expectExceptionCode(700);

        $oDbStub = $this->getMockBuilder(Nominatim\DB::class)
                        ->setMethods(array('connect'))
                        ->getMock();

        $oDbStub->method('connect')
                ->will($this->returnCallback(function () {
                    throw new \Nominatim\DatabaseError('psql connection problem', 500, null, 'unknown database');
                }));


        $oStatus = new Status($oDbStub);
        $this->assertEquals('No database', $oStatus->status());
    }

    /** ---------------------------------------------------------------------------------
     * UT - 36
     * TESTING status() METHOD
     * 
     * Testing for Module failed exception
    */
    public function testThrowModuleFailed()
    {
        $this->expectException(\Exception::class);
        $this->expectExceptionMessage('Module failed');
        $this->expectExceptionCode(701);

        $oDbStub = $this->getMockBuilder(Nominatim\DB::class)
                        ->setMethods(array('connect', 'getOne'))
                        ->getMock();

        $oDbStub->method('getOne')
                ->willReturn(false);

        $oStatus = new Status($oDbStub);
        $this->assertNull($oStatus->status());
    }

    /** ---------------------------------------------------------------------------------
     * UT - 37
     * TESTING status() METHOD
     * 
     * Testing for Module call failed exception
    */
    public function testThrowModuleCallFailed()
    {
        $this->expectException(\Exception::class);
        $this->expectExceptionMessage('Module call failed');
        $this->expectExceptionCode(702);

        $oDbStub = $this->getMockBuilder(Nominatim\DB::class)
                        ->setMethods(array('connect', 'getOne'))
                        ->getMock();

        $oStatus = new Status($oDbStub);
        $this->assertNull($oStatus->status());
    }

    
    /** ---------------------------------------------------------------------------------
     * UT - 38
     * TESTING status() METHOD
     * 
     * Testing for Query Failed exception
    */
    public function testNoValue()
    {
        $this->expectException(\Exception::class);
        $this->expectExceptionCode(702);

        $oDbStub = $this->getMockBuilder(Nominatim\DB::class)
                        ->setMethods(array('connect', 'getOne'))
                        ->getMock();

        $oDbStub->method('getOne')
                ->will($this->returnCallback(function ($sql) {
                    if (preg_match('/SELECT word_id, word_token/', $sql)) return false;
                }));

        $oStatus = new Status($oDbStub);
        $this->assertNull($oStatus->status());
    }

    /** ---------------------------------------------------------------------------------
     * UT - 39
     * TESTING status() METHOD
     * 
     * Testing for No Value exception
    */
    public function testWordIdQueryFail()
    {
        $this->expectException(\Exception::class);
        $this->expectExceptionMessage('No value');
        $this->expectExceptionCode(704);

        $oDbStub = $this->getMockBuilder(Nominatim\DB::class)
                        ->setMethods(array('connect', 'getOne'))
                        ->getMock();

        $oDbStub->method('getOne')
                ->will($this->returnCallback(function ($sql) {
                    if (preg_match("/make_standard_name\('a'\)/", $sql)) return 'a';
                    if (preg_match('/SELECT word_id, word_token/', $sql)) return null;
                }));

        $oStatus = new Status($oDbStub);
        $this->assertNull($oStatus->status());
    }

    /** ---------------------------------------------------------------------------------
     * UT - 40
     * TESTING status() METHOD
     * 
     * Testing if correct status is returned
    */
    public function testStatusOK()
    {
        $oDbStub = $this->getMockBuilder(Nominatim\DB::class)
                        ->setMethods(array('connect', 'getOne'))
                        ->getMock();

        $oDbStub->method('getOne')
                ->will($this->returnCallback(function ($sql) {
                    if (preg_match("/make_standard_name\('(\w+)'\)/", $sql, $aMatch)) return $aMatch[1];
                    if (preg_match('/SELECT word_id, word_token/', $sql)) return 1234;
                }));

        $oStatus = new Status($oDbStub);
        $this->assertNull($oStatus->status());
    }





    /** ---------------------------------------------------------------------------------
     * UT - 41
     * TESTING dataDate() METHOD
     * 
     * Testing for Data date query failed exception 
    */
    public function testDataDateException()
    {
        $this->expectException(\Exception::class);
        $this->expectExceptionCode(705);

        $oDbStub = $this->getMockBuilder(Nominatim\DB::class)
                        ->setMethods(array('getOne'))
                        ->getMock();
     
        $oDbStub->method('getOne')
                ->willReturn(false);

        $oStatus = new Status($oDbStub);
        $this->assertNull($oStatus->dataDate());
    }


    /** ---------------------------------------------------------------------------------
     * UT - 42
     * TESTING dataDate() METHOD
     * 
     * Testing if correct data date is returned
    */
    public function testDataDateOk()
    {
        $oDbStub = $this->getMockBuilder(Nominatim\DB::class)
                        ->setMethods(array('getOne'))
                        ->getMock();
     
        $oDbStub->method('getOne')
                ->willReturn(1519430221);

        $oStatus = new Status($oDbStub);
        $this->assertEquals(1519430221, $oStatus->dataDate());
    }


    /** ---------------------------------------------------------------------------------
     * UT - 43
     * TESTING databaseVersion() METHOD
     * 
     * Testing if correct database version date is returned
    */
    public function testDatabaseVersion()
    {
        $oDbStub = $this->getMockBuilder(Nominatim\DB::class)
                        ->setMethods(array('getOne'))
                        ->getMock();
     
        $oDbStub->method('getOne')
                ->willReturn(12);

        $oStatus = new Status($oDbStub);
        $this->assertEquals(12, $oStatus->databaseVersion());
    }
}