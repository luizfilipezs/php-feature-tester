<?php

require_once('Person.php');

class PersonTester extends FeatureTester
{
    /**
     * {@inheritdoc}
     */
    public function createSample()
    {
        $this->model = new Person;
        $this->model->name = 'John';
        $this->model->age = 18;

        $this->model->bestFriend = new Person;
        $this->model->bestFriend->name = 'Joseph';
        $this->model->bestFriend->age = 20;
    }

    /**
     * {@inheritdoc}
     */
    public function run()
    {
        $this->expect('age')->toBeEqualTo(18);

        $this->expect('bestFriend')
            ->toBeInstanceOf(Person::class)
            ->toHave([
                'name' => 'Joseph',
                'age' => 20
            ]);

        $this->expectMethod('setName')
            ->with('Mary')
            ->toKeepOrSet([
                'name' => 'Mary'
            ]);

        $this->expectMethod('getName')->toBeEqualTo('Mary');

        $this->expectMethod('isAdult')
            ->toBe('boolean')
            ->toBeEqualTo(true);

        $this->expectMethod('sum')
            ->with(1, 2)
            ->andWith(2)
            ->andWith(8, 2)
            ->toBeEqualTo(15)
            ->toBe('integer');

        $this->expectMethod('throwException')->toFail();

        $this->expectValue(['Hello', 'World'])
            ->toBe('array')
            ->toBeValidated(fn($value) => implode(' ', $value) === 'Hello World');

        $this->expectValue(['message' => 'Hello World!', 'friendly' => true])
            ->toHave([
                'friendly' => true
            ], true);
    }
}
