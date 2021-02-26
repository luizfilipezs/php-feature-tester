<?php

require_once('FeatureTester.php');

class Person
{
    public $name, $age;

    public function getName()
    {
        return $this->name;
    }

    public function setName(string $name)
    {
        $this->name = $name;
    }

    public function isAdult()
    {
        return $this->age >= 18;
    }

    public function doSomething()
    {
        return true;
    }

    public function sum(...$values)
    {
        return array_reduce($values, fn($total, $value) => $total += $value, 0);
    }
}
