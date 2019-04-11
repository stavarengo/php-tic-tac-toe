<?php
declare(strict_types=1);


namespace TicTacToe\Api\Storage;

/**
 * This storage store the values in a PHP array, thus it is in memory storage.
 * It is a good storage to be used in test units.
 */
class ArrayStorage implements StorageInterface
{
    /**
     * @var array
     */
    protected $storage;

    /**
     * ArrayStorage constructor.
     * @param array $storage
     */
    public function __construct(array $storage = [])
    {
        $this->storage = $storage;
    }


    public function get(string $id, $default = null)
    {
        if ($this->has($id)) {
            return $this->storage[$id];
        }

        return $default;
    }

    public function set(string $id, $value): StorageInterface
    {
        $this->storage[$id] = $value;

        return $this;
    }

    public function has(string $id): bool
    {
        return array_key_exists($id, $this->storage);
    }

    public function delete(string $id): StorageInterface
    {
        unset($this->storage[$id]);

        return $this;
    }
}