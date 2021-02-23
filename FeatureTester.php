<?php

class FeatureTester
{
    public $model;

    private $currentValue;
    private $isMethod = false;
    private $args = [];

    public function __construct(array $config = [])
    {
        $this->init();

        foreach ($config as $property => $value) {
            $this->model->{$property} = $value;
        }
    }

    public function __get($name)
    {
        return $this->model->{$name};
    }

    public function __set($name, $value)
    {
        $this->model->{$name} = $value;
    }

    /**
     * Itialize the model being tested here.
     */
    public function init() { }

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
    /** @var Person */
    public $model;

    public function init()
    {
        $this->model = new Person([
            'name' => 'John',
            'age' => 20
        ]);
    }

    public function getName()
    {
        return $this->expect($this->model->getName())->toBe('John')->andReturn();
    }

    public function isAdult()
    {
        return $this->expect($this->model->isAdult())->toBe(true);
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
