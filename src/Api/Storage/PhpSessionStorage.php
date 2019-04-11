<?php
declare(strict_types=1);


namespace TicTacToe\Api\Storage;

/**
 * This storage use the PHP's global $_SESSION variable to store values.
 */
class PhpSessionStorage implements StorageInterface
{
    public function get(string $id, $default = null)
    {
        if ($this->has($id)) {
            return $_SESSION[$id];
        }

        return $default;
    }

    public function set(string $id, $value): StorageInterface
    {
        $_SESSION[$id] = $value;

        return $this;
    }

    public function has(string $id): bool
    {
        return array_key_exists($id, $_SESSION);
    }

    public function delete(string $id): StorageInterface
    {
        unset($_SESSION[$id]);

        return $this;
    }
}