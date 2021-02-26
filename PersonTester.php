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
    }

    /**
     * {@inheritdoc}
     */
    public function run()
    {
        $this->expectMethod('setName')
            ->with('Mary')
            ->toHave([
                'name' => 'Mary'
            ]);

        $this->expectMethod('getName')
            ->toBeEqualTo('Mary');

        $this->expectMethod('sum')
            ->with(1, 2)
            ->andWith(2)
            ->andWith(8, 2)
            ->toBeEqualTo(15)
            ->toBe('integer');
    }
}
