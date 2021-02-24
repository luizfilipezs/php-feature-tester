<?php

ini_set('memory_limit', '-1');

class FeatureTester
{
    protected $model;

    private $currentValue;
    private $isMethod = false;
    private $args = [];

    public static function run()
    {
        $cls = get_class();
        $instance = new $cls;
        $instance->test();
    }

    public function test()
    {
        $methods = get_class_methods($this);

        foreach ($methods as $method) {
            $this->{$method}();
        }
    }

    public function expect($value)
    {
        $this->currentValue = $value;

        return $this;
    }

    public function expectMethod(string $method, array $args = [])
    {
        $this->currentValue = $method;
        $this->args = $args;
        $this->isMethod = true;

        return $this;
    }

    public function with(...$args)
    {
        $this->args = $args;
        $this->isMethod = true;

        return $this;
    }

    public function getValue($finalize = false)
    {
        $value = $this->isMethod ? $this->model->{$this->currentValue}(...$this->args) : $this->currentValue;

        if ($finalize) {
            $this->currentValue = null;
            $this->args = [];
            $this->isMethod = false;
        }

        return $value;
    }

    public function toBe($type)
    {
        $value = $this->getValue();

        if (gettype($value) === $type || $value === $type) {
            return $this;
        }

        throw new \Exception('O valor sendo testado não é compatível com o tipo/valor do argumento passado.');
    }

    public function toHave(array $props = [])
    {
        foreach ($props as $property => $value) {
            if ($this->model->$property != $value) {
                throw new \Exception("O valor de {$property} não é igual a {$value} ou essa propriedade não existe.");
            }
        }
    }

    public function toFail()
    {
        try {
            $value = $this->getValue();
        } catch (\Throwable $e) {
            return;
        }

        throw new \Exception("Nenhuma excessão foi lançada. Valor retornado: {strval($value)}");
    }

    public function andReturn()
    {
        return $this->getValue(true);
    }
}

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
}

class PersonTester extends FeatureTester
{
    public function setModel()
    {
        $this->model = new Person([
            'name' => 'John',
            'age' => 20
        ]);
    }

    public function getName()
    {
        return $this->expectMethod('getName')->toBe('John')->andReturn();
    }

    public function isAdult()
    {
        return $this->expectMethod('isAdult')->toBe(true)->andReturn();
    }

    public function setName()
    {
        $this->expectMethod('setName')->with('Joseph')->toHave([
            'name' => 'Joseph'
        ]);
    }

    public function doSomething()
    {
        $this->expectMethod('doSomething')->toFail();
    }
}

PersonTester::run();