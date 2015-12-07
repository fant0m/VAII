<?php namespace lib;


class Validator
{
    private $data;
    private $rules;
    public $errors;

    public function __construct($data, $rules) {
        $this->data = $data;
        $this->rules = $rules;
        $this->errors = [];
    }

    private function testEmail($email) {
        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $this->errors[] = 'Neplatný formát e-mailovej adresy!';
        }
    }

    private function testRequired($value, $field) {
        if ($value === '') {
            $this->errors[] = 'Pole '.$field.' nesmie byť prázdne!';
        }
    }

    private function testMin($value, $minCharacters, $field) {
        if (strlen($value) < $minCharacters) {
            $this->errors[] = $field.' musí mať minimálne '.$minCharacters.' znakov!';
        }
    }

    private function testUnique($key, $data, $name, $field) {
        if ($data == '') return;
        $name = 'models\\'.$name;
        $model = new $name;
        $count = count($model->where([$key => $data])->get());
        if ($count == 1) {
            $this->errors[] = $field.' sa už nachádza v našej databáze!';
        }
    }

    private function testConfirmed($key, $field, $field2) {
        if (property_exists($this->data, $key.'_confirmation')) {
            if ($this->data->{$key.'_confirmation'} != $this->data->$key) {
                $this->errors[] = $field2.' sa musia zhodovať!';
            }
        } else {
            $this->errors[] = $field.' musí mať konfirmáciu!';
        }
    }

    private function testValues($key, $values, $field) {
        if (!in_array($this->data->{$key}, $values)) {
            $this->errors[] = 'Neplatná hodnota pre pole '.$field;
        }
    }

    public function validate() {
        foreach ($this->data as $key => $data) {
            if (array_key_exists($key, $this->rules)) {
                $rules = explode('|', $this->rules[$key]);
                foreach ($rules as $rule) {
                    if (strpos($rule, ':') !== false) {
                        $cut = explode(':', $rule);
                        switch ($cut[0]) {
                            case 'unique':
                                $this->testUnique($key, $data, $cut[1], $cut[2]);
                                break;
                            case 'min':
                                $this->testMin($data, $cut[1], $cut[2]);
                                break;
                            case 'required':
                                $this->testRequired($data, $cut[1]);
                                break;
                            case 'confirmed':
                                $this->testConfirmed($key, $cut[1], $cut[2]);
                                break;
                            case 'values':
                                $this->testValues($key, explode(',', $cut[1]), $cut[2]);
                                break;
                            default:
                        }
                    } else {
                        switch ($rule) {
                            case 'email':
                                $this->testEmail($data);
                                break;
                            default:
                        }
                    }
                }
            }
        }

        return $this;
    }

    public function errors() {
        return count($this->errors);
    }

    public function getErrors() {
        return $this->errors;
    }
}