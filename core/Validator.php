<?php

declare(strict_types=1);

/**
 * Validador de datos de entrada reutilizable por los controladores.
 */
final class Validator
{
    /** @var array<string,string> */
    private array $errors = [];

    private array $data;

    public function __construct(array $data)
    {
        $this->data = $data;
    }

    /**
     * Campo obligatorio no vacio.
     */
    public function required(string $field, string $label = ''): self
    {
        $label = $label !== '' ? $label : $field;

        if (!isset($this->data[$field]) || (is_string($this->data[$field]) && trim($this->data[$field]) === '')) {
            $this->add($field, "El campo {$label} es obligatorio.");
        }

        return $this;
    }

    /**
     * Campo numerico entero positivo (o cero).
     */
    public function integer(string $field, int $min = 0, string $label = ''): self
    {
        $label = $label !== '' ? $label : $field;

        if (!isset($this->data[$field])) {
            return $this;
        }

        if (filter_var($this->data[$field], FILTER_VALIDATE_INT) === false || (int) $this->data[$field] < $min) {
            $this->add($field, "El campo {$label} debe ser un entero mayor o igual a {$min}.");
        }

        return $this;
    }

    /**
     * Campo numerico decimal positivo.
     */
    public function decimal(string $field, float $min = 0, float $max = 100, string $label = ''): self
    {
        $label = $label !== '' ? $label : $field;

        if (!isset($this->data[$field])) {
            return $this;
        }

        if (!is_numeric($this->data[$field]) || (float) $this->data[$field] < $min || (float) $this->data[$field] > $max) {
            $this->add($field, "El campo {$label} debe ser un numero entre {$min} y {$max}.");
        }

        return $this;
    }

    /**
     * Campo dentro de un conjunto de valores permitidos.
     */
    public function in(string $field, array $allowed, string $label = ''): self
    {
        $label = $label !== '' ? $label : $field;

        if (isset($this->data[$field]) && !in_array($this->data[$field], $allowed, true)) {
            $this->add($field, "El campo {$label} debe ser uno de: " . implode(', ', $allowed) . '.');
        }

        return $this;
    }

    /**
     * Campo con formato de correo valido.
     */
    public function email(string $field, string $label = ''): self
    {
        $label = $label !== '' ? $label : $field;

        if (isset($this->data[$field]) && filter_var($this->data[$field], FILTER_VALIDATE_EMAIL) === false) {
            $this->add($field, "El campo {$label} debe ser un correo valido.");
        }

        return $this;
    }

    /**
     * Arreglo no vacio (por ejemplo, lista de tallas).
     */
    public function arrayOfIds(string $field, string $label = ''): self
    {
        $label = $label !== '' ? $label : $field;

        if (!isset($this->data[$field])) {
            return $this;
        }

        if (!is_array($this->data[$field]) || $this->data[$field] === []) {
            $this->add($field, "El campo {$label} debe ser un arreglo con al menos un elemento.");
            return $this;
        }

        foreach ($this->data[$field] as $value) {
            if (filter_var($value, FILTER_VALIDATE_INT) === false || (int) $value <= 0) {
                $this->add($field, "El campo {$label} contiene valores invalidos.");
                break;
            }
        }

        return $this;
    }

    /**
     * Registra un error manual.
     */
    public function add(string $field, string $message): void
    {
        $this->errors[$field] = $message;
    }

    /**
     * Indica si la validacion fallo.
     */
    public function fails(): bool
    {
        return $this->errors !== [];
    }

    /**
     * Errores acumulados.
     *
     * @return array<string,string>
     */
    public function errors(): array
    {
        return $this->errors;
    }
}
