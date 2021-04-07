<?php

namespace Nominatim;

require_once(CONST_LibDir.'/ClassTypes.php');
require_once(CONST_LibDir.'/init-website.php');
require_once(CONST_LibDir.'/PlaceLookup.php');

class NominatimSubClassedDB extends \Nominatim\DB
{
    public function setConnection($oConnection)
    {
        $this->connection = $oConnection;
    }
}

class TestLookup extends \PHPUnit\Framework\TestCase
{
    /** -------------------------------------------------------------------------------------------
     * UT - 70
     * Test userError() for correct return value
     */
    public function testUserError()
    {
        $this->expectException(\Exception::class);
        $this->expectExceptionMessage('My Exception');
        $this->expectExceptionCode(400);

        $this->assertNull(userError('My Exception'));
    }

    /** -------------------------------------------------------------------------------------------
     * UT - 76
     * Test getDefRadius() for 'village' type
     */
    public function testGetDefRadius()
    {
        $aPlace = array('class' => 'place', 'type' => 'village');
        $this->assertEquals(0.02, ClassTypes\getDefRadius($aPlace));
    }

    /** -------------------------------------------------------------------------------------------
     * UT - 77
     * Test getDefRadius() for invalid place type
     */
    public function testInvalidGetDefRadius()
    {
        $aPlace = array('class' => 'Invalid', 'type' => 'Invalid');
        $this->assertEquals(0.00005, ClassTypes\getDefRadius($aPlace));
    }

    /** -------------------------------------------------------------------------------------------
     * UT - 77
     * Test getDefRadius() for invalid place type
     */
    public function testGetOutlines()
    {
        $oDbStub = $this->getMockBuilder(Nominatim\DB::class)
        ->setMethods(array('connect', 'getRow'))
        ->getMock();

        $aPlace = array(
            'place_id'=> 155814,
            'numfeatures' => 0,
            'area' => 0,
            'centrelat' => 51.0791006, 
            'centrelon' => 0.3878748,  
            'minlat' => 51.0788777,    
            'maxlat' => 51.0794884,
            'minlon' => 0.3869586,
            'maxlon' => 0.3885547
        );
        $aOutline = array(
            'lat' => 51.0791006,
            'lon' => 0.3878748,
            'aBoundingBox' => array
                (
                    '0' => 51.0788777,
                    '1' => 51.0794884,
                    '2' => 0.3869586,
                    '3' => 0.3885547
                )
        );
        $oDbStub->method('getRow')
        ->willReturn($aPlace);
        
        $oPL = new PlaceLookup($oDbStub);
        $this->assertEquals($aOutline, $oPL->getOutlines('155814'));
        // $this->assertEmpty($oPL->getOutlines('1', 1));
        // $this->assertEmpty($oPL->getOutlines('1'));
        // $this->assertEmpty($oPL->getOutlines('1'));
        // $this->assertEmpty($oPL->getOutlines('1'));
    }
    // place_id | numfeatures | area | centrelat  | centrelon |   minlat   |   maxlat   |  minlon   |  maxlon   
    // ----------+-------------+------+------------+-----------+------------+------------+-----------+-----------
    //    155814 |           0 |    0 | 51.0791006 | 0.3878748 | 51.0788777 | 51.0794884 | 0.3869586 | 0.3885547
}