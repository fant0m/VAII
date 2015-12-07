<?php namespace models;

use PDO;
use lib\Connection;

class Model
{
    protected $table; // table name
    protected $attributes; // values from database
    protected $fillable; // mass assignable attributes
    protected $visible; // attributes being taken from table
    protected $hidden; // attributes being excluded from json form

    protected $result;
    protected $db; // database connection -> singleton -> didn't feel like doing DI

    public function __construct($attributes = array()) {
        $this->db = Connection::getInstance();
        if (!$this->table) $this->table = $this->detectTableName(get_called_class());
        $this->visible = implode(', ', $this->visible);
        if (!$this->fillable) {
            $this->fillable = $this->attributes;
        }

        if (count($attributes) > 0) {
            $this->create($attributes);
        }
    }

    private function detectModel($class) {
        return strtolower(explode('\\', $class)[1]);
    }

    private function detectTableName($class) {
        return $this->detectModel($class). 's';
    }

    public function all() {
        $query = $this->db->query('select '.$this->visible.' from '.$this->table.' order by id desc');

        return $query->fetchAll(PDO::FETCH_CLASS, get_called_class());
    }

    public function find($id) {
        $statement = $this->db->prepare('select '.$this->visible.' from '.$this->table.' where id = ?');
        $statement->execute([$id]);

        $this->result = $statement->fetchAll(PDO::FETCH_CLASS, get_called_class());

        return $this->first();
    }

    public function where($properties = []) {
        $sql = 'select '.$this->visible.' from '.$this->table.' where ';
        foreach ($properties as $key => $property) {
            $sql .= $key.' = ? and ';
        }
        $sql = substr($sql, 0, -4);
        $sql .= ' order by id desc';
        $properties = $this->specialChars($properties);
        $statement = $this->db->prepare($sql);
        $statement->execute($properties);

        $this->result = $statement->fetchAll(PDO::FETCH_CLASS, get_called_class());

        return $this;
    }

    private function specialChars($array) {
        $new = [];
        foreach ($array as $key => $value) {
            $new[] = htmlspecialchars($value);
        }

        return $new;
    }

    public function create($attributes) {
        $attr = array_values($this->attributes);
        $this->attributes = [];
        $this->attributes = array_fill_keys($attr, '');
        $this->fill($attributes);

        $filter = array_filter($this->attributes);
        $fields = implode(',', array_keys($filter));
        $values = array_values($filter);
        $q = str_repeat('?, ', count($values) - 1);
        $sql = 'insert into '.$this->table.'('.$fields.') values ('.$q.'?)';

        $statement = $this->db->prepare($sql);
        $statement->execute($values);
        $this->attributes['id'] = $this->db->lastInsertId();

        return true;
    }

    public function update($attributes = []) {
        if ($attributes) $this->fill($attributes);
        if (array_key_exists('id', $this->attributes)) {
            $sql = 'update '.$this->table.' set ';
            $values = [];
            $values['id'] = $this->attributes['id'];
            foreach ($this->attributes as $key => $value) {
                if ($key != 'id' && !is_int($key) && !is_array($value)) {
                    $sql .= $key . ' = :' . $key . ', ';
                    $values[$key] = $value;
                }
            }
            $sql = substr($sql, 0, -2).' where id = :id';
            $statement = $this->db->prepare($sql);

            return $statement->execute($values);
        } else {
            throw new \Exception('Can\'\t save item!');
        }
    }

    public function save() {
        $this->removeAttr();
        $filter = array_filter($this->attributes);
        $fields = implode(',', array_keys($filter));
        $values = array_values($filter);
        $q = str_repeat('?, ', count($values) - 1);
        $sql = 'insert into '.$this->table.'('.$fields.') values ('.$q.'?)';

        $statement = $this->db->prepare($sql);
        $statement->execute($values);
        $this->attributes['id'] = $this->db->lastInsertId();

        return true;
    }

    public function removeAttr() {
        $keys = [];
        for ($i = 0; $i < count($this->attributes); $i++) {
            if (array_key_exists($i, $this->attributes)) {
                $keys[] = $i;
            }
        }
        foreach ($keys as $key) {
            unset($this->attributes[$key]);
        }
    }

    public function get() {
        return $this->result;
    }

    public function first() {
        foreach ($this->result as $row) {
            return $row;
        }
    }

    private function fill($attributes) {
        foreach ($attributes as $key => $value) {
            if ($this->isFillable($key)) {
                $this->attributes[$key] = $value;
            }
        }
    }

    private function isFillable($property) {
        if (!in_array($property, $this->fillable)) {
            throw new \Exception('Property '.$property.' is not fillable!');
        } else {
            return true;
        }
    }

    public function __get($property) {
        return $this->attributes[$property];
    }

    public function __set($property, $value) {
        $this->attributes[$property] = $value;
    }

    public function belongsToMany($model) {
        $first_table = $this->detectModel($model);
        $second_table = $this->detectModel(get_called_class());
        $test = strcasecmp($first_table, $second_table);
        if ($test > 0) {
            $table_name = $second_table.'_'.$first_table;
        } elseif ($test < 0) {
            $table_name = $first_table.'_'.$second_table;
        } else {
            $table_name = $first_table.'_'.$second_table;
        }

        $where_key = $second_table.'_id';
        $primary_key = $first_table.'_id';

        $new_model = new $model;
        $fields = explode(', ', $new_model->visible);
        $select = [];
        foreach ($fields as $field) {
            $select[] = $first_table.'s.'.$field;
        }
        $select = implode(', ', $select);

        $sql = 'select '.$select.' from '.$table_name.', '.$first_table.'s where '.$table_name.'.'.$where_key.' = '.$this->attributes['id'].' and '.$first_table.'s.id = '.$table_name.'.'.$primary_key;
        $query = $this->db->query($sql);

        return $query->fetchAll(PDO::FETCH_CLASS, $model);
    }

    public function belongsTo($model) {
        $first_table = $this->detectTableName($model);
        $second_table = $this->detectTableName(get_called_class());

        $new_model = new $model;
        $fields = $new_model->visible;
        $where_key = $this->primary_key($first_table);

        $sql = 'select '.$fields.' from '.$first_table.' where id = '.$this->attributes[$where_key];
        $query = $this->db->query($sql);

        $result = $query->fetchAll(PDO::FETCH_CLASS, $model);
        return $result[0];
    }

    public function response($encode = false) {
        $this->removeAttr();
        $output = [];
        foreach ($this->attributes as $key => $value) {
            if ($this->hidden) {
                if (!in_array($key, $this->hidden))
                    $output[$key] = $value;
            } else {
                $output[$key] = $value;
            }
        }

        if ($encode) {
            return json_encode($output);
        } else {
            return $output;
        }
    }

    private function primary_key($table) {
        return substr($table, 0, -1).'_id';
    }

    public function hasMany($model) {
        $primary_key = $this->primary_key($this->table);
        $model = new $model;
        $result = $model->where([$primary_key => $this->attributes['id']])->get();

        return $result;
    }

    public function standardDate() {
        return date('d.m.Y H:i:s', strtotime($this->attributes['created_at']));
    }

    public function updatedDate() {
        return date('d.m.Y H:i:s', strtotime($this->attributes['updated_at']));
    }

    public function in($ids) {
        $sql = 'select '.$this->visible.' from posts where user_id in ('.$ids.') order by created_at desc';
        $query = $this->db->query($sql);

        return $query->fetchAll(PDO::FETCH_CLASS, get_called_class());
    }
}