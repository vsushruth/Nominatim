<?php

namespace Nominatim;

require_once(CONST_LibDir.'/ClassTypes.php');
require_once(CONST_LibDir.'/init-website.php');

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

        $this->assertNull(user_error('My Exception'));
    }

    /** -------------------------------------------------------------------------------------------
     * UT - 76
     * Test getDefRadius() for 'village' type
     */
    public function testGetDefRadius()
    {
        $aPlace = array('class' => 'place', 'type' => 'village');
        $this->assertEquals(0.02, getDefRadius($aPlace));
    }

    /** -------------------------------------------------------------------------------------------
     * UT - 77
     * Test getDefRadius() for invalid place type
     */
    public function testGetDefRadius()
    {
        $aPlace = array('class' => 'Invalid', 'type' => 'Invalid');
        $this->assertEquals(0.00005, getDefRadius($aPlace));
    }
}