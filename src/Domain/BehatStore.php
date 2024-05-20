<?php

namespace YoRus\BehatContext\Domain;

/**
 * Class BehatStore
 */
class BehatStore
{
    private \stdClass $store;

    /**
     * BehatStore constructor.
     */
    public function __construct()
    {
        $this->store = new \stdClass();
    }

    /**
     * @param string $key
     *
     * @return mixed
     */
    public function getStoreValue(string $key): mixed
    {
        if (!isset($this->store->$key)) {
            return null;
        }

        return $this->store->$key;
    }

    /**
     * @param string $key
     * @param        $value
     *
     * @return void
     */
    public function setStoreValue(string $key, $value)
    {
        $this->store->$key = $value;
    }

    /**
     * @param string $key
     *
     * @return null
     */
    public function __get(string $key): mixed
    {
        return $this->getStoreValue($key);
    }

    /**
     * @param string $key
     * @param        $value
     *
     * @return void
     */
    public function __set(string $key, $value)
    {
        $this->setStoreValue($key, $value);
    }
}
