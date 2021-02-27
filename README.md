# php-feature-tester

Este repositório traz um código que visa tornar simples a ação de testar classes PHP.

## `FeatureTester`

Para utilizar o testador, basta estendê-lo numa classe, sobrescrevendo os métodos `createSample()` e `run()`:

```php
class PersonTester extends FeatureTester
{
    /**
     * {@inheritdoc}
     */
    public function createSample()
    {
        // Defina uma instância como modelo de testes aqui
    }

    /**
     * {@inheritdoc}
     */
    public function run()
    {
        // Coloque seus testes aqui
    }
}
```

### `createSample()`

Esse método é utilizado para definir um modelo para testes, como no exemplo a seguir:

```php
public function createSample()
{
    $this->model = new Person();
    $this->model->name = 'John';
    $this->model->age = 18;
}
```

### `run()`

Coloque os testes dentro desse método. Também possível criar outros métodos na classe e chamá-los ali.

```php
public function run()
{
    $this->expect('name')->toBe('string');
    $this->expectMethod('isAdult')->toBe('boolean')->toBeEqualTo(true);
    ...
```

## Recebendo valores para testar

### `expect()`

Recebe o nome de uma propriedade e a prepara para ser testada.

```php
$this->expect('name');
```

### `expectMethod()`

Desempenha o mesmo papel do método `expect()`, mas com um método.

```php
$this->expectMethod('getName');
```

Também é possível passar argumentos para o método:

```php
$this->expectMethod('sum', 1, 2);
```

### `with()`

Os argumentos passados em `with()` serão utilizados no método sendo testado. Por exemplo:

```php
$this->expectMethod('sum')->with(1, 2);
```

>Os argumentos passados em `with()` sobrescreverão quaisquer outros que tenham sido definidos anteriormente.

### `andWith()`

Adiciona mais argumentos para serem aplicados no método sendo testado. A diferença entre esse método e o método `with()` é que ele não sobrescreve argumentos passados anteriormente.

```php
$this->expectMethod('sum', 1)->andWith(2); // [1, 2]

$this->expectMethod('sum')->with(1)->andWith(2); // [1, 2]

$this->expectMethod('sum', 5)->with(1)->andWith(2); // [1, 2]

$this->expectMethod('sum')->with(1, 2)
    ->andWith(3, 4)
    ->andWith(5, 6); // [1, 2, 3, 4, 5, 6]
```

### `expectValue()`

Prepara qualquer valor para ser testado.

```php
$this->expect('John');
```

## Executando os testes

Uma vez criados os testes, é possível executá-los com o comando `init()`:

```php
PersonTester::init();
```

Se algum dos testes falhar, será lançada uma exceção do tipo `TesterException`, com uma mensagem explicando o que aconteceu.