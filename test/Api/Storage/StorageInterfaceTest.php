<?php
declare(strict_types=1);

namespace TicTacToe\Test\Api\Storage;

use PHPUnit\Framework\TestCase;
use TicTacToe\Api\Storage\ArrayStorage;
use TicTacToe\Api\Storage\PhpSessionStorage;
use TicTacToe\Api\Storage\StorageInterface;

class StorageInterfaceTest extends TestCase
{
    /**
     * @var StorageInterface[]
     */
    protected $allStorageImplementation;

    protected function setUp(): void
    {
        $this->allStorageImplementation = [
            new ArrayStorage(),
            new PhpSessionStorage(),
        ];

        $_SESSION = [];
    }

    public function testSetMethod()
    {
        $id = 'test-id';
        $value = 'value';
        foreach ($this->allStorageImplementation as $storage) {
            $this->assertFalse($storage->has($id), sprintf('The storage "%s" must not have this entry yet.', get_class($storage)));

            $storage->set($id, $value);

            $this->assertTrue($storage->has($id), sprintf('The storage "%s" does not have the value I just set.', get_class($storage)));
            $this->assertEquals($value, $storage->get($id), sprintf('The storage "%s" returned a value different from what I just set.', get_class($storage)));
        }
    }

    public function testGetMethod()
    {
        $value = 'value';
        foreach ($this->allStorageImplementation as $storage) {
            $storage->set('test1', $value);
            $this->assertEquals($value, $storage->get('test1'), sprintf('The storage "%s" returned a value different from what I just set.', get_class($storage)));


            $defaultValue = '_DEFAULT_';
            $this->assertFalse($storage->has('test2'), sprintf('The storage "%s" must not have this entry yet.', get_class($storage)));
            $this->assertEquals($defaultValue, $storage->get('test2', $defaultValue), sprintf('The storage "%s" did not return the default value I asked for when the value does not exists yet.', get_class($storage)));
        }
    }

    public function testHasMethod()
    {
        foreach ($this->allStorageImplementation as $storage) {
            $this->assertFalse($storage->has('test1'), sprintf('The storage "%s" must not have this entry yet.', get_class($storage)));

            $storage->set('test1', 'value');

            $this->assertTrue($storage->has('test1'), sprintf('The storage "%s" return false when I asked if it has the value I just set.', get_class($storage)));
        }
    }

    public function testDeleteMethod()
    {
        foreach ($this->allStorageImplementation as $storage) {
            $this->assertFalse($storage->has('test1'), sprintf('The storage "%s" must not have this entry yet.', get_class($storage)));

            // Make sure it does not fail when deleting something that does not exists yet
            $storage->delete('test1');

            $storage->set('test1', 'value1');
            $storage->set('test2', 'value2');
            $this->assertTrue($storage->has('test1'), sprintf('The storage "%s" return false when I asked if it has the value I just set.', get_class($storage)));
            $this->assertTrue($storage->has('test2'), sprintf('The storage "%s" return false when I asked if it has the value I just set.', get_class($storage)));

            $storage->delete('test1');
            $this->assertFalse($storage->has('test1'), sprintf('The storage "%s" did not remove the value I ask to be removed.', get_class($storage)));

            $this->assertTrue($storage->has('test2'), sprintf('The storage "%s" deleted a value that I did not ask to be removed.', get_class($storage)));
        }
    }

}
