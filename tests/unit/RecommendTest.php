<?php
namespace Hrgruri\Icd3\Model;

use Hrgruri\Rarcs\Asset\{
    Book,
    Nishikie
};

class RecommendTest extends \PHPUnit_Framework_TestCase
{
    protected $recommend;

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
        $this->recommend = new Recommend($capsule);
    }

    protected function tearDown()
    {
    }

    public function testGetRecommendByAsset()
    {
        $num    = 1;
        $target = [
            'id'    => 'arcUP2976',
            'db'    => 'nishikie',
        ];
        $result = $this->recommend->getRecommendByAsset($target['id'], $target['db'], $num);
        $this->assertInternalType('array', $result);
        $this->assertContainsOnly(Nishikie::class, $result);
        $this->assertTrue(count($result) <= $num);

        $target = [
            'id'    => 'arcBK01-0001',
            'db'    => 'books',
        ];
        $result = $this->recommend->getRecommendByAsset($target['id'], $target['db'], $num);
        $this->assertInternalType('array', $result);
        $this->assertContainsOnly(Book::class, $result);
        $this->assertTrue(count($result) <= $num);
    }

    public function testCorrectNumber()
    {
        $method = new \ReflectionMethod(get_class($this->recommend), 'correctNumber');
        $method->setAccessible(true);
        $this->assertTrue($method->invoke($this->recommend, 0) == 1);
        $this->assertTrue($method->invoke($this->recommend, 1) == 1);
        $this->assertTrue($method->invoke($this->recommend, 10) == 10);
        $this->assertTrue($method->invoke($this->recommend, 11) == 10);

        $result = $method->invoke($this->recommend, 999);
        $this->assertTrue($method->invoke($this->recommend, 100) == $result);
    }

    public function testGetAssetsInfo()
    {
        $method = new \ReflectionMethod(get_class($this->recommend), 'getAssetsInfo');
        $method->setAccessible(true);

        $assets_list = ['arcUP2976', 'arcUP2976', 'arcUP2977'];
        $db     =   'nishikie';
        $result = $method->invoke($this->recommend, $assets_list, $db);
        $this->assertInternalType('array', $result);
        $this->assertContainsOnly(Nishikie::class, $result);

        $assets_list = ['arcBK01-0001', 'arcBK01-0002', 'arcBK01-0003'];
        $db     =   'books';
        $result = $method->invoke($this->recommend, $assets_list, $db);
        $this->assertInternalType('array', $result);
        $this->assertContainsOnly(Book::class, $result);
    }

    public function testGetRecommendByUser()
    {
        $user   = 43;
        $num    = 4;

        $db     = 'nishikie';
        $result = $this->recommend->getRecommendByUser($user, $db, $num);
        $this->assertInternalType('array', $result);
        $this->assertTrue(count($result) <= $num);
        $this->assertContainsOnly(Nishikie::class, $result);

        $db     = 'books';
        $result = $this->recommend->getRecommendByUser($user, $db, $num);
        $this->assertInternalType('array', $result);
        $this->assertTrue(count($result) <= $num);
        $this->assertContainsOnly(Book::class, $result);
    }

    public function testGetDbId()
    {
        $method = new \ReflectionMethod(get_class($this->recommend), 'getDbId');
        $method->setAccessible(true);

        $this->assertInternalType('int', $method->invoke($this->recommend, 'nishikie'));
        $this->assertInternalType('int', $method->invoke($this->recommend, 'books'));
        $this->assertNull($method->invoke($this->recommend, 'dummy'));
    }

    public function testGetRecommendByPopular()
    {
        $user   = 43;
        $num    = 2;

        $db     = 'nishikie';
        $result = $this->recommend->getRecommendByPopular($user, $db, $num);
        $this->assertInternalType('array', $result);
        $this->assertTrue(count($result) <= $num);
        $this->assertContainsOnly(Nishikie::class, $result);

        $db     = 'books';
        $result = $this->recommend->getRecommendByPopular($user, $db, $num);
        $this->assertInternalType('array', $result);
        $this->assertTrue(count($result) <= $num);
        $this->assertContainsOnly(Book::class, $result);

        $db     = 'unknown';
        $result = $this->recommend->getRecommendByPopular($user, $db, $num);
        $this->assertNull($result);
    }

    public function testGetRecommendByDate()
    {
        $year   = date('Y');
        $month  = date('m');
        $day    = date('d');
        $user   = 43;
        $num    = 2;

        $db     = 'nishikie';
        $result = $this->recommend->getRecommendByDate($year, $month, $day, $user, $db, $num);
        $this->assertInternalType('array', $result);
        $this->assertContainsOnly(Nishikie::class, $result);
        $this->assertTrue(count($result) <= $num);

        $db     = 'books';
        $result = $this->recommend->getRecommendByDate($year, $month, $day, $user, $db, $num);
        $this->assertInternalType('array', $result);
        $this->assertContainsOnly(Book::class, $result);
        $this->assertTrue(count($result) <= $num);

        $db     = 'unknown';
        $result = $this->recommend->getRecommendByDate($year, $month, $day, $user, $db, $num);
        $this->assertNull($result);
    }
}
