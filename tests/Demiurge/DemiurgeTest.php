<?php
namespace Demiurge\Test;

use Demiurge\Demiurge;

/**
 * Generated by PHPUnit_SkeletonGenerator 1.2.0 on 2012-10-14 at 10:02:10.
 */
class DemiurgeTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var Demiurge
     */
    protected $object;

    /**
     * Sets up the fixture, for example, opens a network connection.
     * This method is called before a test is executed.
     */
    protected function setUp()
    {
        $this->demiurge = new Demiurge;
    }

    /**
     * Tears down the fixture, for example, closes a network connection.
     * This method is called after a test is executed.
     */
    protected function tearDown()
    {
    }

    public function testSetConstant()
    {
        $this->demiurge->constant = 'a';

        $this->assertEquals('a', $this->demiurge->constant);
    }

    /**
     * @expectedException OutOfBoundsException
     */
    public function testGetNonexistentService()
    {
        $this->demiurge->xxx;
    }

    public function testServiceCalledAsProperty()
    {
        $testCase = $this;
        $this->demiurge->service = function(Demiurge $demiurge) use($testCase) {
            $testCase->assertInstanceOf('Demiurge\Demiurge', $demiurge, 'First argument has to be an instance of Demiurge class');

            return 'returnValue';
        };

        $this->assertEquals('returnValue', $this->demiurge->service, 'Service is resolved');
    }

    public function testServiceCalledAsMethod()
    {
        $v1 = 'a';
        $v2 = 100;
        $testCase = $this;

        $this->demiurge->serviceAsMethod = function(Demiurge $demiurge, $arg1, $arg2) use ($testCase, $v1, $v2) {
            $testCase->assertEquals($v1, $arg1);
            $testCase->assertEquals($v2, $arg2);

            return 'returnValue';
        };

        $this->assertEquals('returnValue', $this->demiurge->serviceAsMethod($v1, $v2));
    }

}