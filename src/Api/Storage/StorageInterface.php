<?php
declare(strict_types=1);


namespace TicTacToe\Api\Storage;

interface StorageInterface
{
    /**
     * Return a value previously stored.
     * If the value does not exists, return $default.
     *
     * @param string $id
     * @param null $default
     * @return mixed
     */
    public function get(string $id, $default = null);

    /**
     * Stores a value.
     *
     * @param string $id
     * @param $value
     * @return StorageInterface
     */
    public function set(string $id, $value): StorageInterface;

    /**
     * Check whether a values exists or not.
     *
     * @param string $id
     * @return bool
     */
    public function has(string $id): bool;

    /**
     * Delete a value.
     * If it does not exists, no error will be throw.
     *
     * @param string $id
     * @return StorageInterface
     */
    public function delete(string $id): StorageInterface;
}