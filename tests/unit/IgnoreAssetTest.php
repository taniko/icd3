<?php
namespace Hrgruri\Icd3\Model;

class IgnoreAssetTest extends \PHPUnit\Framework\TestCase
{
    protected $object;

    /**
     * Sets up the fixture, for example, opens a network connection.
     * This method is called before a test is executed.
     */
    protected function setUp()
    {
        $settings = (require __DIR__ . '/../../src/settings.php')['settings'];
        $capsule = new \Illuminate\Database\Capsule\Manager;
        $capsule->addConnection($settings['db']);
        $capsule->setAsGlobal();
        $capsule->bootEloquent();
        $this->object = new IgnoreAsset($capsule);
    }

    protected function tearDown()
    {
    }

    public function testGetIgnoreList()
    {
        $user = 43;

        $result = $this->object->getIgnoreList($user);
        $this->assertInternalType('array', $result);
    }

    public function testGetIgnoreListByYMD()
    {
        $user   = 43;

        $date = '2016-07-30';
        $result = $this->object->getIgnoreListByYMD($user, $date);
        $this->assertInternalType('array', $result);
    }

    public function testCorrectDateYMD()
    {
        $date = '2016-07-30';
        $result = $this->object->correctDateYMD($date);
        $this->assertInternalType('string', $result);
        $this->assertEquals($date, $result);

        $date = '2016-7-1';
        $result = $this->object->correctDateYMD($date);
        $this->assertInternalType('string', $result);
        $this->assertEquals('2016-07-01', $result);

        $date = '07-30';
        $result = $this->object->correctDateYMD($date);
        $this->assertInternalType('string', $result);
        $this->assertEquals(date('Y-').$date, $result);

        $date = '7-1';
        $result = $this->object->correctDateYMD($date);
        $this->assertInternalType('string', $result);
        $this->assertEquals(date('Y-').'07-01', $result);

        $date = 'bug';
        $result = $this->object->correctDateYMD($date);
        $this->assertNull($result);
    }

    public function testCorrectDateMD()
    {
        $date = '2016-07-30';
        $result = $this->object->correctDateMD($date);
        $this->assertInternalType('string', $result);
        $this->assertEquals('07-30', $result);

        $date = '2016-7-1';
        $result = $this->object->correctDateMD($date);
        $this->assertInternalType('string', $result);
        $this->assertEquals('07-01', $result);

        $date = '07-30';
        $result = $this->object->correctDateMD($date);
        $this->assertInternalType('string', $result);
        $this->assertEquals($date, $result);

        $date = 'bug';
        $result = $this->object->correctDateMD($date);
        $this->assertNull($result);
    }
}
