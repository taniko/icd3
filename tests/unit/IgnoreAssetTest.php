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
}
