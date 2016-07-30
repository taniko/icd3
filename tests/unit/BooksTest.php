<?php
namespace Hrgruri\Icd3\Model;

use Hrgruri\Rarcs\BooksClient as Client;

class BooksTest extends \PHPUnit\Framework\TestCase
{
    protected $book;

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
        $this->book = new Book($capsule);
    }

    /**
     * Tears down the fixture, for example, closes a network connection.
     * This method is called after a test is executed.
     */
    protected function tearDown()
    {
    }

    public function testExistsAsset()
    {
        $target = 'arcBK01-0001';
        $method = new \ReflectionMethod(get_class($this->book), 'existsAsset');
        $method->setAccessible(true);
        $result = $method->invoke($this->book, $target);
        $this->assertInternalType('boolean', $result);
    }

    public function testExistsInfo()
    {
        $target = 'arcBK01-0001';
        $method = new \ReflectionMethod(get_class($this->book), 'existsInfo');
        $method->setAccessible(true);
        $result = $method->invoke($this->book, $target);
        $this->assertInternalType('boolean', $result);
    }

    public function testGetDB()
    {
        $method = new \ReflectionMethod(get_class($this->book), 'getDB');
        $method->setAccessible(true);
        $result = $method->invoke($this->book, 'books');
        $this->assertInternalType('int', $result);
    }

    public function testInsertAsset()
    {
        $target = 'arcBK01-0001';
        $method = new \ReflectionMethod(get_class($this->book), 'insertAsset');
        $method->setAccessible(true);
        $result = $method->invoke($this->book, $target);
        $this->assertInternalType('int', $result);
        $this->assertEquals(
            $method->invoke($this->book, $target),
            $result
        );
    }

    public function testInfoMethod()
    {
        $target = 'arcBK01-0001';

        /*  insertInfo() */
        $method = new \ReflectionMethod(get_class($this->book), 'insertInfo');
        $method->setAccessible(true);
        $insert_asset = $method->invoke($this->book, $target);

        /*  selectInfot() */
        $method = new \ReflectionMethod(get_class($this->book), 'selectInfo');
        $method->setAccessible(true);
        $select_asset = $method->invoke($this->book, $target);

        $this->assertInstanceOf(\Hrgruri\Rarcs\Asset\Book::class, $insert_asset);
        $this->assertInstanceOf(\Hrgruri\Rarcs\Asset\Book::class, $select_asset);
        $this->assertEquals($insert_asset->id,      $select_asset->id);
        $this->assertEquals($insert_asset->title,   $select_asset->title);
        $this->assertEquals($insert_asset->author,  $select_asset->author);
        $this->assertEquals($insert_asset->url,     $select_asset->url);
        $this->assertEquals($insert_asset->cover,   $select_asset->cover);
    }

    public function testSearch()
    {
        $num = 1;
        $result = $this->book->search([
            'keyword'   => '義経',
            'count'     => (string)$num
        ]);
        $this->assertInternalType('array', $result);
        $this->assertContainsOnly(\Hrgruri\Rarcs\Asset\Book::class, $result);
        $this->assertTrue($num >= count($result));
    }
}
