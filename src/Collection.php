<?php

namespace Yao;

class Collection implements \ArrayAccess, \JsonSerializable
{

    private ?int $lastInsertId = null;
    private ?int $count = null;
    private ?string $dump = null;

    private $query = '';
    private $bindParam = [];

    private $data = [];

    public function __set($arg, $value)
    {
        $this->$arg = $value;
    }

    public function __get($arg)
    {
        return $this->data[$arg] ?? null;
    }

    public function __construct()
    {
    }


    public function toJson()
    {
        return json_encode($this->data);
    }

    public function toArray()
    {
        return $this->data;
    }

    public function offsetExists($offset)
    {
    }


    public function offsetGet($offset)
    {
        return $this->data[$offset] ?? null;
    }


    public function offsetSet($offset, $value)
    {
    }


    public function offsetUnset($offset)
    {
    }

    public function jsonSerialize()
    {
        return $this->data;
    }

    //    public function __destruct()
    //    {
    //        $this->data = [];
    //        $this->dump = '';
    //    }

}
