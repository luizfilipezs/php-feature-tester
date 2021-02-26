# php-feature-tester

Este repositório traz um código que visa tornar simples a ação de testar classes PHP.

# `FeatureTester`

Para utilizar o testador, para estendê-lo numa classe, sobrescrevendo os métodos `createSample()` e `run()`:

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

## Métodos

### `createSample()`

