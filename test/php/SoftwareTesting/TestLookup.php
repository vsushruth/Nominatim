<?php
// 8 UT 70-77
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
     * UT - 61
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
     * UT - 67
     * Test getDefRadius() for 'village' type
     */
    public function testGetDefRadius()
    {
        $aPlace = array('class' => 'place', 'type' => 'village');
        $this->assertEquals(0.02, ClassTypes\getDefRadius($aPlace));
    }

    /** -------------------------------------------------------------------------------------------
     * UT - 68
     * Test getDefRadius() for invalid place type
     */
    public function testInvalidGetDefRadius()
    {
        $aPlace = array('class' => 'Invalid', 'type' => 'Invalid');
        $this->assertEquals(0.00005, ClassTypes\getDefRadius($aPlace));
    }

    /** -------------------------------------------------------------------------------------------
     * UT - 62 | UT - 66
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

        $map = [["select place_id,0 as numfeatures,st_area(geometry) as area, ST_Y(centroid) as centrelat, ST_X(centroid) as centrelon, ST_YMin(geometry) as minlat,ST_YMax(geometry) as maxlat, ST_XMin(geometry) as minlon,ST_XMax(geometry) as maxlon from placex where place_id = 155814", null, 'Could not get outline', $aPlace],["select place_id,0 as numfeatures,st_area(geometry) as area, ST_Y(centroid) as centrelat, ST_X(centroid) as centrelon, ST_YMin(geometry) as minlat,ST_YMax(geometry) as maxlat, ST_XMin(geometry) as minlon,ST_XMax(geometry) as maxlon from placex where place_id = 0", null, 'Could not get outline', array()]];
        $oDbStub->method('getRow')
        ->will($this->returnValueMap($map));
        
        $oPL = new PlaceLookup($oDbStub);
        // UT - 62
        $this->assertEquals($aOutline, $oPL->getOutlines('155814'));

        // UT - 63
        $this->assertEquals($aOutline, $oPL->getOutlines('155814', $fRadius=0));

        // UT - 64
        $this->assertEquals($aOutline, $oPL->getOutlines('155814', $fLat=51, $fLon=0));

        // UT - 65
        $this->assertEquals($aOutline, $oPL->getOutlines('155814', $fLatReverse=51, $fLonReverse=0));
        
        // UT - 66
        $this->assertEmpty($oPL->getOutlines('0'));
    }

}