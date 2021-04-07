<?php

namespace Nominatim;

require_once(CONST_LibDir.'/ParameterParser.php');
require_once(CONST_LibDir.'/lib.php');
require_once(CONST_LibDir.'/DB.php');
require_once(CONST_LibDir.'/PlaceLookup.php');
require_once(CONST_LibDir.'/ReverseGeocode.php');


require_once(CONST_LibDir.'/DebugHtml.php');
require_once(CONST_LibDir.'/Result.php');

function userError($sError)
{
    throw new \Exception($sError);
}

class TestReverse extends \PHPUnit\Framework\TestCase
{
    /** ---------------------------------------------------------------------------------
     * UT - 24
     * TESTING getSet() METHOD
     * 
     * Testing for True/False Outputs
    */
    public function testGetSet()
    {
        $oParams = new ParameterParser(array(
                                        'val1' => 'foo',
                                        'val2' => '',
                                        'val3' => 0
                                       ));

        $this->assertSame(false, $oParams->getSet('non-exists', array('foo', 'bar')));
        $this->assertSame('default', $oParams->getSet('non-exists', array('foo', 'bar'), 'default'));
        $this->assertSame('foo', $oParams->getSet('val1', array('foo', 'bar')));

        $this->assertSame(false, $oParams->getSet('val2', array('foo', 'bar')));
        $this->assertSame(0, $oParams->getSet('val3', array('foo', 'bar')));
    }

    /**
     * UT - 25
     * Testing For Exceptions
     */
    public function testGetSetWithValueNotInSet()
    {
        $this->expectException(\Exception::class);
        $this->expectExceptionMessage("Parameter 'val4' must be one of: foo, bar");

        (new ParameterParser(array('val4' => 'faz')))->getSet('val4', array('foo', 'bar'));
    }

    /** ---------------------------------------------------------------------------------
     * UT - 26
     * TESTING getBool() METHOD
     * 
     * Testing for True Outputs
    */
    public function testGetBoolTrue()
    {
        $oParams = new ParameterParser(array(
                                        'bool1' => '1',
                                        'bool2' => 'true',
                                        'bool3' => 'false'
                                       ));

        $this->assertSame(true, $oParams->getBool('non-exists', true));

        $this->assertSame(true, $oParams->getBool('bool1'));
        $this->assertSame(true, $oParams->getBool('bool2'));
        $this->assertSame(true, $oParams->getBool('bool3'));
    }

    /**
     * UT - 27
     * Testing For Exceptions
     */
    public function testGetBoolFalse()
    {
        $oParams = new ParameterParser(array(
                                        'bool1' => '0',
                                        'bool2' => ''
                                       ));

        $this->assertSame(false, $oParams->getBool('non-exists'));

        $this->assertSame(false, $oParams->getBool('bool1'));
        $this->assertSame(false, $oParams->getBool('bool2'));
    }


    /** ---------------------------------------------------------------------------------
     * UT - 28
     * TESTING getInt() METHOD
     * 
     * Testing for True/False Outputs
    */
    public function testGetInt()
    {
        $oParams = new ParameterParser(array(
                                        'int1' => '5',
                                        'int2' => '-1',
                                        'int3' => 0
                                       ));

        $this->assertSame(false, $oParams->getInt('non-exists'));
        $this->assertSame(999, $oParams->getInt('non-exists', 999));

        $this->assertSame(5, $oParams->getInt('int1'));
        $this->assertSame(-1, $oParams->getInt('int2'));
        $this->assertSame(0, $oParams->getInt('int3'));
    }

    /**
     * UT - 29
     * Testing For Exceptions
     */
    public function testGetIntWithNonNumber()
    {
        $this->expectException(\Exception::class);
        $this->expectExceptionMessage("Integer number expected for parameter 'int4'");

        $oParams = new ParameterParser(array('int4' => 'a'));
        $oParams->getInt('int4');
    }


    public function testGetIntWithEmpytString()
    {
        $this->expectException(\Exception::class);
        $this->expectExceptionMessage("Integer number expected for parameter 'int5'");

        $oParams = new ParameterParser(array('int5' => ''));
        $oParams->getInt('int5');
    }


    /** ---------------------------------------------------------------------------------
     * UT - 30
     * TESTING getFloat() METHOD
     * 
     * Testing for True/False Outputs
    */
    public function testGetFloat()
    {

        $oParams = new ParameterParser(array(
                                        'float1' => '1.0',
                                        'float2' => '-5',
                                        'float3' => 0
                                       ));

        $this->assertSame(false, $oParams->getFloat('non-exists'));
        $this->assertSame(999, $oParams->getFloat('non-exists', 999));
        $this->assertSame(1.0, $oParams->getFloat('float1'));
        $this->assertSame(-5.0, $oParams->getFloat('float2'));
        $this->assertSame(0.0, $oParams->getFloat('float3'));
    }

    /**
     * UT - 31
     * Testing For Exceptions
     */
    public function testGetFloatWithEmptyString()
    {
        $this->expectException(\Exception::class);
        $this->expectExceptionMessage("Floating-point number expected for parameter 'dummyFloat'");

        $oParams = new ParameterParser(array('dummyFloat' => ''));
        $oParams->getFloat('dummyFloat');
    }


    public function testGetFloatWithInvalidNumber()
    {
        $this->expectException(\Exception::class);
        $this->expectExceptionMessage("Floating-point number expected for parameter 'invalidFloat'");

        $oParams = new ParameterParser(array('invalidFloat' => '-21.'));
        $oParams->getFloat('invalidFloat');
    }


    /** ---------------------------------------------------------------------------------
     * UT - 32 | UT - 33
     * TESTING getString() METHOD
     * 
     * Testing for True/False Outputs
    */
    public function testGetString()
    {
        $oParams = new ParameterParser(array(
                                        'str1' => 'abc',
                                        'str2' => '',
                                        'str3' => '0'
                                       ));

        $this->assertSame(false, $oParams->getString('non-exists'));
        $this->assertSame('default', $oParams->getString('non-exists', 'default'));

        $this->assertSame('abc', $oParams->getString('str1'));
        $this->assertSame(false, $oParams->getStringList('str2'));
        $this->assertSame(false, $oParams->getStringList('str3')); // PHP magic treats 0 as false when returned
    }



    /** ---------------------------------------------------------------------------------
     * UT - 38
     * TESTING lookupOSMID() METHOD
     * 
     * Testing for NULL Outputs
    */
    public function testlookupOSMID(){
        $oDbStub = $this->getMockBuilder(Nominatim\DB::class)
        ->setMethods(array('connect', 'getOne'))
        ->getMock();

        $oDbStub->method('getOne')
        ->willReturn(false);
        $oLookUp = new PlaceLookup($oDbStub);

        $this->assertNull($oLookUp->lookupOSMID('', 0));

    }
    

    /** ---------------------------------------------------------------------------------
     * UT - 39 | UT - 40
     * TESTING getRow() METHOD
     * 
     * Testing for True/False Outputs
    */
    public function testgetRow()
    {
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

        $this->assertTrue(
            $oDB->checkConnection($sDbname)
        );
        
        $oDB->exec('CREATE TABLE table1 (id integer, city varchar, country varchar)');
        $oDB->exec("INSERT INTO table1 VALUES (1, 'Berlin', 'Germany'), (2, 'Paris', 'France')");

        #UT-39
        $this->assertEquals(
            array('id' => 1, 'city' => 'Berlin', 'country' => 'Germany'),
            $oDB->getRow('SELECT * FROM table1 WHERE id=1')
        );

        #UT-40
        $this->assertEquals(
            false,
            $oDB->getRow('SELECT * FROM table1 WHERE id=999')
        );
    }



    /** ---------------------------------------------------------------------------------
     * 
     */
    public function testLookupOne()
    {
        $oDbStub = $this->getMockBuilder(Nominatim\DB::class)
                        ->setMethods(array('connect', 'getOne', 'getRow'))
                        ->getMock();

        $oDbStub->method('getOne')
                ->willReturn(false);

        $oDbStub->method('getRow')
                ->willReturn(false);

        $oRevGeocode = new ReverseGeocode($oDbStub);

        $this->assertNull($oRevGeocode->lookup(1, 1, false));

    }


    /** ---------------------------------------------------------------------------------
     * 
     */
    public function testLookupTwo()
    {
        $oDbStub = $this->getMockBuilder(Nominatim\DB::class)
                        ->setMethods(array('connect', 'getOne', 'getRow'))
                        ->getMock();

        $aPlace = array(
            'parent_place_id' => 123123,
            'rank_address' => 28,
            'rank_search' => 12,
            'place_id'=> 155814
        );
        $oResult = new Result($aPlace['place_id']);
        
        $oDbStub->method('getOne')
                ->willReturn(true);

        $oDbStub->method('getRow')
                ->will($this->returnCallback(function ($sql) {
                    if (preg_match('/and \(name is not null or housenumber is not null/', $sql)) return false;
                    if (preg_match("/WHERE ST_GeometryType(geometry) in (\'ST_Polygon\', \'ST_MultiPolygon\')/", $sql)) return false;
                    if (preg_match("/WHERE distance <= reverse_place_diameter(rank_search)/", $sql)) return $aPlace;
                }));

        $oRevGeocode = new ReverseGeocode($oDbStub);

        $this->assertEquals($oResult, $oRevGeocode->lookup(1, 1, false));

    }



    /** ---------------------------------------------------------------------------------
     * 
     */
    public function testLookupThree()
    {
        $oDbStub = $this->getMockBuilder(Nominatim\DB::class)
                        ->setMethods(array('connect', 'getOne', 'getRow'))
                        ->getMock();

        $aPoly = array(
            'parent_place_id' => 123123,
            'rank_address' => 28,
            'rank_search' => 12,
            'place_id'=> 155814
        );
        $oResult = new Result($aPoly['place_id']);

        $oDbStub->method('getRow')
                ->will($this->returnCallback(function ($sql) {
                    if (preg_match("/WHERE ST_GeometryType(geometry) in (\'ST_Polygon\', \'ST_MultiPolygon\')/", $sql)) return $aPoly;
                    if (preg_match('/and \(name is not null or housenumber is not null/', $sql)) return false;
                }));

        $oRevGeocode = new ReverseGeocode($oDbStub);

        $this->assertEquals($oResult, $oRevGeocode->lookup(1, 1, false));

    }


    /** ---------------------------------------------------------------------------------
     * 
     */
    public function testLookupFour()
    {
        $oDbStub = $this->getMockBuilder(Nominatim\DB::class)
                        ->setMethods(array('connect', 'getOne', 'getRow'))
                        ->getMock();

        $aPoly = array(
            'parent_place_id' => 123123,
            'rank_address' => 20,
            'rank_search' => 12,
            'place_id'=> 155814
        );
        $aPlaceNode = array(
            'parent_place_id' => 134573,
            'rank_address' => 20,
            'rank_search' => 12,
            'place_id'=> 856630
        );
        $oResult = new Result(856630);
        

        $oDbStub->method('getRow')
                ->will($this->returnCallback(function ($sql) {
                    if (preg_match("/AND rank_address Between 5 AND 25/", $sql)) return $aPoly;
                    if (preg_match("/AND rank_address > 0/", $sql)) return $aPlaceNode;
                    if (preg_match('/or rank_address between 26 and 27/', $sql)) return false;
                }));

        $oRevGeocode = new ReverseGeocode($oDbStub);

        $this->assertEquals($oResult, $oRevGeocode->lookup(1, 1, false));

    }


    /** ---------------------------------------------------------------------------------
     * 
     */
    public function testLookupFive()
    {
        $oDbStub = $this->getMockBuilder(Nominatim\DB::class)
                        ->setMethods(array('connect', 'getOne', 'getRow'))
                        ->getMock();

        $aPlace = array(
            'parent_place_id' => 123123,
            'rank_address' => 30,
            'rank_search' => 12,
            'place_id'=> 155814
        );
        $oResult = new Result(155814);

        $oDbStub->method('getOne')
                ->willReturn(false);

        $oDbStub->method('getRow')
                ->will($this->returnCallback(function ($sql) {
                    if (preg_match("/or rank_address between 26 and 27/", $sql)) return $aPlace;
                }));

        $oRevGeocode = new ReverseGeocode($oDbStub);

        $this->assertEquals($oResult, $oRevGeocode->lookup(1, 1, false));

    }


    
    /** ---------------------------------------------------------------------------------
     * 
     */
    public function testLookupSix()
    {
        $oDbStub = $this->getMockBuilder(Nominatim\DB::class)
                        ->setMethods(array('connect', 'getOne', 'getRow'))
                        ->getMock();

        $aPlace = array(
            'parent_place_id' => 123123,
            'rank_address' => 20,
            'rank_search' => 12,
            'place_id'=> 155814,
            'country_code' => "ind"
        );
        $aStreet = array(
            'parent_place_id' => 123123,
            'rank_address' => 20,
            'rank_search' => 12,
            'place_id'=> 253814,
            'country_code' => "ind"
        );
        $oResult = new Result(253814);

        $oDbStub->method('getOne')
                ->willReturn(false);

        $oDbStub->method('getRow')
                ->will($this->returnCallback(function ($sql) {
                    if (preg_match("/or rank_address between 26 and 27/", $sql)) return $aPlace;
                    if (preg_match('/and rank_address > 28/', $sql)) return $aStreet;
                }));

        $oRevGeocode = new ReverseGeocode($oDbStub);

        $this->assertEquals($oResult, $oRevGeocode->lookup(1, 1, false));

    }



}

