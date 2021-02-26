<?php

require_once('TestException.php');

class FeatureTester
{
    const VALUE = 'value';
    const PROPERTY = 'property';
    const METHOD = 'method';

    /**
     * Objeto sendo testado.
     * 
     * @var object
     */
    protected $model;

    /**
     * Valor sendo testado.
     * 
     * @var mixed
     */
    private $currentValue;

    /**
     * Indica o tipo de valor sendo testado, podendo ser `value`,
     * `property` ou `method`.
     * 
     * @var string
     */
    private $testType;

    /**
     * Argumentos passados para o método sendo testado quando a
     * propriedade `$isMethod` é igual a `false`.
     * 
     * @var array<mixed>
     */
    private $args;

    /**
     * Array com uma chave `current`, que recebe o valor obtido através do teste.
     * Assim, o valor não precisa ser consultado mais de uma vez.
     * 
     * @var array
     */
    private $cache;
    
    /**
     * Inicializa a instância e o modelo de testes.
     */
    public function __construct()
    {
        $this->createSample();
    }

    /**
     * Cria uma nova instância da classe, executa o método `run()` e retorna
     * a instância.
     * 
     * @return object A instância testadora criada,
     */
    public static function init()
    {
        $cls = get_called_class();

        $instance = new $cls;
        $instance->run();

        return $instance;
    }

    /**
     * Define o modelo que será utilizado nos testes.
     */
    public function createSample()
    {
        $this->model = $this;
    }

    /**
     * Executa as rotinas de teste.
     */
    public function run()
    {
        $this->expect('model')->toBeDifferentThan($this);
    }

    private function setValue($value, string $type)
    {
        $this->currentValue = $value;
        $this->testType = $type;
        $this->args = [];
        $this->cache = null;
    }

    /**
     * Recebe o nome de uma propriedade cujo valor será testado.
     * 
     * @param string $value Nome da propriedade cujo valor será testado.
     * 
     * @return static A instância de testes.
     */
    public function expect(string $propertyName)
    {
        $this->setValue($propertyName, self::PROPERTY);

        return $this;
    }

    public function expectValue($value)
    {
        $this->setValue($value, self::VALUE);

        return $this;
    }

    /**
     * Recebe o nome de um método cujo valor retornado será testado
     * e cujos argumentos serão os que forem definidos para o segundo
     * parâmetro deste método.
     * 
     * @param string $methodName Nome do método cujo valor retornado será
     * testado.
     * @param array<mixed> $args Argumentos que devem ser utilizados na
     * chamada do método.
     * 
     * @return static A instância de testes.
     */
    public function expectMethod(string $methodName, array $args = [])
    {
        $this->setValue($methodName, self::METHOD);

        return $this->with(...$args);
    }

    /**
     * Define argumentos para o método sendo testado.
     * 
     * @param array<mixed> $args Argumentos que devem ser utilizados na chamada
     * do método sendo testado.
     * 
     * @return static A instância de testes.
     */
    public function with(...$args)
    {
        $this->args = $args;

        return $this;
    }

    /**
     * Insere argumentos para o método sendo testado.
     * 
     * @param array<mixed> $args Argumentos que devem ser acrescentados na chamada
     * do método sendo testado.
     * 
     * @return static A instância de testes.
     */
    public function andWith(...$args)
    {
        return $this->with(...$this->args, ...$args);
    }

    /**
     * Retorna o valor da propriedade ou do método sendo testado.
     * 
     * @return mixed Valor sendo testado.
     * 
     * @throws TestException Exceção lançada caso o valor da propriedade `$testType`
     * não for nenhum dos esperados.
     */
    public function getValue()
    {
        if (isset($this->cache['current'])) return $this->cache['current'];

        switch ($this->testType) {
            case self::VALUE:
                $value = $this->currentValue;
                break;
            case self::PROPERTY:
                $value = $this->model->{$this->currentValue};
                break;
            case self::METHOD:
                $value = $this->model->{$this->currentValue}(...$this->args);
                break;
            default:
                throw new TestException('Não foi possível determinar que tipo de teste está sendo realizado.');
        }

        return $this->cache['current'] = $value;
    }

    /**
     * Checa se o valor sendo testado é do tipo especificado.
     * 
     * @param array<string> $types Tipo esperado para o valor sendo testado (ex.: `"string"`, `"integer"`).
     * Vários valores podem ser passados como argumentos.
     * 
     * @return static A instância de testes.
     * 
     * @throws TestException Exceção lançado caso nem o valor do tipo do valor sendo testado
     * sejam compatíveis com o argumento passado.
     */
    public function toBe(...$types)
    {
        $value = $this->getValue();

        foreach ($types as $type) {
            if (gettype($value) === $type) {
                return $this;
            }
        }

        $types = implode(' ou ', $types);

        throw new TestException("O tipo do valor sendo testado não é {$types}.");
    }

    /**
     * Checa se o valor sendo testado é compatível com o argumento passado.
     * 
     * @param array<mixed> $values Valor ao qual o valor sendo testado deve ser equivalente.
     * Vários valores podem ser passados como argumentos.
     * 
     * @return static A instância de testes.
     * 
     * @throws TestException Exceção lançada caso o valor passado como argumento seja
     * diferente daquele que está sendo testado.
     */
    public function toBeEqualTo(...$values)
    {
        $actualValue = $this->getValue();

        foreach ($values as $value) {
            if ($value === $actualValue) {
                return $this;
            }
        }

        throw new TestException("O valor sendo testado é diferente do(s) que foi(ram) passado(s).");
    }

    /**
     * Checa se o tipo especificado é diferente do verdadeiro tipo do valor
     * sendo testado.
     * 
     * @param array<string> $types Tipo que deve ser diferente do verdadeiro
     * tipo do valor sendo testado. Vários tipos podem ser passados como argumentos.
     * 
     * @return static A instância de testes.
     * 
     * @throws TestException Exceção lançada caso o tipo especificado seja diferente
     * do verdadeiro tipo do valor sendo testado.
     */
    public function notToBe(...$types)
    {
        try {
            $this->toBe(...$types);
        } catch (TestException $e) {
            return $this;
        }

        $types = implode(' ou ', $types);

        throw new TestException("O valor testado é do tipo {$types}.");
    }

    /**
     * Checa se o valor especificado é diferente do verdadeiro valor sendo testado.
     * 
     * @param array<mixed> $values Valor que deve ser igual ao que está sendo testado.
     * Vários valores podem ser passados como argumentos.
     * 
     * @return static A instância de testes.
     * 
     * @throws TestException Exceção caso o valor passado seja diferente do verdadeiro
     * valor que está sendo testado.
     */
    public function toBeDifferentThan(...$values)
    {
        try {
            $this->toBeEqualTo(...$values);
        } catch (TestException $e) {
            return $this;
        }

        throw new TestException("O valor sendo testado é igual a um dos que foram passados.");
    }

    /**
     * Checa se o valor sendo testado é uma instância da classe especificada
     * no argumento.
     * 
     * @param string $className Classe da qual o valor sendo testado deve ser
     * uma instância.
     * 
     * @return static A instância de testes.
     * 
     * @throws TestException Exceção caso o valor sendo testado não seja um objeto
     * ou se não for uma instância da classe especificada.
     */
    public function toBeInstanceOf(string $className)
    {
        $value = $this->getValue();

        if (!is_object($value)) {
            throw new \Exception('O valor sendo testado não é um objeto.');
        }

        if ($value instanceof $className) {
            return $this;
        }

        throw new TestException("O objeto sendo testado não é uma instância de {$className}.");
    }

    /**
     * Recebe uma função de callback e lança uma exceção se o retorno dela
     * for falso.
     * 
     * @param callable $func Função de callback que recebe o valor sendo testado
     * e deve retornar um booleano indicando o sucesso da validação.
     * 
     * @return static A instância de testes.
     * 
     * @throws TestException Exceção caso o retorno da função de validação seja
     * falso.
     */
    public function toBeValidated(callable $func)
    {
        if ($func($this->getValue())) {
            return $this;
        }

        throw new TestException('A validação falhou.');
    }

    /**
     * Recebe um array associativo representando propriedades e valores do objeto
     * sendo testado. Se os valores não forem compatíveis, lança uma exceção.
     * 
     * @return static A instância de testes.
     * 
     * @throws TestException Exceção lançada caso uma propriedade especificada não
     * exista ou se seu valor for diferente do esperado.
     */
    public function toKeepOrSet(array $props = [])
    {
        $this->getValue();

        foreach ($props as $property => $value) {
            if ($this->model->$property != $value) {
                throw new TestException("O valor de {$property} não é igual ao valor esperado para essa propriedade/chave ou então ela não existe.");
            }
        }

        return $this;
    }

    /**
     * Checa os valores de propriedades dentro do valor sendo testado.
     * 
     * @param array $props Array associativo representando propriedades e seus valores
     * dentro do objeto.
     * @param bool $asArray Booleano indicando se o valor deve ser analisado como um `array`.
     * 
     * @return static A instância de testes.
     * 
     * @throws TestException Exceção lançada caso uma das propriedades/chaves não exista
     * ou seu valor seja diferente do esperado.
     * @throws \Exception Exceção lançada quando o booleano `$asArray` é falso e o valor
     * sendo testado não é um objeto.
     */
    public function toHave(array $props = [], bool $asArray = false)
    {
        $object = $this->getValue();

        if (!$asArray && !is_object($object)) {
            throw new \Exception('O valor sendo testado não é um objeto.');
        }

        foreach ($props as $property => $value) {
            if ($asArray) {
                if ($object[$property] === $value) {
                    continue;
                }
            } else if ($object->$property === $value) {
                continue;
            }

            throw new TestException("O valor de {$property} não é igual ao valor esperado para essa propriedade/chave ou então ela não existe.");
        }

        return $this;
    }

    /**
     * Lança uma exceção se a rotina de obtenção do valor sendo testado
     * não lançar.
     * 
     * @param string $exceptionClass Nome da classe da instância de exceção que deve
     * ser lançada.
     * 
     * @throws TestException Exceção lançada quando nenhuma outra for ou quando
     * a exceção lançada não for da mesma instância que a classe especificada no
     * parâmetro `$exceptionClass`.
     */
    public function toFail(string $exceptionClass = \Exception::class)
    {
        try {
            $this->getValue();
        } catch (\Exception $e) {
            if ($e instanceof $exceptionClass) {
                return;
            }

            throw new TestException("A exceção lançada não é uma instância de {$exceptionClass}.");
        }

        throw new TestException('Nenhuma exceção foi lançada.');
    }

    /**
     * Retorna o valor que está sendo testado.
     * 
     * @return mixed Valor sendo testado.
     */
    public function andReturn()
    {
        return $this->getValue();
    }
}
