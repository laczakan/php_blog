<?php

namespace App\Libraries;

use Exception;
use App\Models\User;

/**
 *  Validation helper
 *
 * @category Library
 * @package  App
 */
class Validation
{
    protected $data = [];

    protected $rules = [];

    //field 'name' in error message.
    protected $labels = [];

    protected $errors = [];

    /**
     * Set data to validate
     *
     * @param $data to validate
     *
     * @return void
     */
    public function setData($data)
    {
        $this->data = $data;
    }

    /**
     * Set rules for validation
     *
     * @param $rules to validate
     *
     * @return void
     */
    public function setRules($rules)
    {
        $this->rules = $rules;
    }

    /**
     * Set label to View
     *
     * @param array $labels for the view
     *
     * @return void
     */
    public function setLabels(array $labels)
    {
        $this->labels = $labels;
    }

    /**
     * Get all errors
     *
     * @return array
     */
    public function getErrors()
    {
        return $this->errors;
    }

    /**
     * Get specific error (first) for field name
     *
     * @param string $name of field
     *
     * @return string|null
     */
    public function getError($name)
    {
        // function current- expects an array (current — Return the current element in an array)
        return current($this->errors[$name] ?? []);
    }

    /**
     * Check if a field has any errors
     *
     * @param string $name of field
     *
     * @return bool
     */
    public function hasError($name): bool
    {
        return isset($this->errors[$name]);
    }

    /**
     * Add custom error e.g to display wrong password error
     *
     * @param string $name  of field
     * @param string $error error message
     *
     * @return void
     */
    public function addError($name, $error)
    {
        $this->errors[$name][] = $error;
    }

    /**
     * Check all of the Rules and add errors if not passed
     *
     * @return void
     */
    public function validate()
    {
        foreach ($this->rules as $name => $fieldRules) {
            foreach ($fieldRules as $rule) {
                // Split a string by a caracter ':' if has options
                $exploded = explode(':', $rule);
                // use 1st exploded name as a $ruleName
                $ruleName = $exploded[0];

                switch ($ruleName) {
                    case "required":
                        $this->required($name);
                        break;
                    case 'email':
                        $this->email($name);
                        break;
                    case 'minimum':
                        $this->minimum($name, $exploded[1]);
                        break;
                    case 'maximum':
                        $this->maximum($name, $exploded[1]);
                        break;
                    case 'between':
                        $this->between($name, $exploded[1], $exploded[2]);
                        break;
                    case 'exist':
                        $this->exist($name);
                        break;
                    case 'unique':
                        $this->unique($name, $exploded[1]);
                        break;
                    case 'same':
                        $this->same($name, $exploded[1]);
                        break;
                    case 'file':
                        $this->file($name, $exploded[1]);
                        break;
                    case 'size':
                        $this->size($name, $exploded[1]);
                        break;
                    case 'in':
                        $this->in($name, $exploded[1]);
                        break;
                    default:
                        throw new Exception("Rule $ruleName not found");
                    break;
                }
            }
        }
    }

    /**
     * Field required for Validation
     *
     * @param $name of field
     *
     * @return void
     */
    private function required($name)
    {
        if (empty($this->data[$name])) {
            $label = $this->labels[$name] ?? ucfirst($name);
            $this->errors[$name][] = "Field <em>$label</em> is required";
        }
    }

    /**
     * Check if field is email
     *
     * @param $name of field
     *
     * @return void
     */
    private function email($name)
    {
        if (isset($this->data[$name]) && !filter_var($this->data[$name], FILTER_VALIDATE_EMAIL)) {
            $label = $this->labels[$name] ?? ucfirst($name);
            $this->errors[$name][] = "Field <em>$label</em> must be email";
        }
    }

    /**
     * Set minimum length
     *
     * @param $name of field
     * @param $min  minimum number
     *
     * @return void
     */
    private function minimum($name, $min)
    {
        if (isset($this->data[$name]) && strlen($this->data[$name]) < $min) {
            $label = $this->labels[$name] ?? ucfirst($name);
            $this->errors[$name][] = "Field <em>$label</em> must be more than <em>$min</em>";
        }
    }

    /**
     * Set maximum length
     *
     * @param $name of field
     * @param $max  maximum number
     *
     * @return void
     */
    private function maximum($name, $max)
    {
        if (isset($this->data[$name]) && strlen($this->data[$name]) > $max) {
            $label = $this->labels[$name] ?? ucfirst($name);
            $this->errors[$name][] = "Field <em>$label</em> must be less than <em>$max</em>";
        }
    }

    /**
     * Check if field lenghth is between number
     *
     * @param $name of field
     * @param $min  minimum number
     * @param $max  maximum number
     *
     * @return void
     */
    private function between($name, $min, $max)
    {
        if (
            isset($this->data[$name]) &&
            (strlen($this->data[$name]) < $min || strlen($this->data[$name]) > $max)
        ) {
            $label = $this->labels[$name] ?? ucfirst($name);
            $this->errors[$name][] = "Field <em>$label</em> must be between <em>$min</em> and <em>$max</em>";
        }
    }

    /**
     * Check if email exist in database
     *
     * @param $name of field
     *
     * @return void
     */
    private function exist($name)
    {
        // Check whether email is unique
        if (!User::existByEmail($this->data[$name])) {
            $label = $this->labels[$name] ?? ucfirst($name);
            $this->errors[$name][] = "Field <em>$label</em> not found";
        }
    }

    /**
     * Check if field is unique
     *
     * @param $name  of field
     * @param $table name of table
     *
     * @return void
     */
    private function unique($name, $table)
    {
        // Prepare SQL query
        $sql = "SELECT * FROM `$table` WHERE `$name` = :column";

        // Prepare parameters to bind
        $params = [
            'column' => $this->data[$name],
        ];
        $database = new Database();

        $result = $database->findOne($sql, $params);

        // Check whether record is unique
        if ($result) {
            $label = $this->labels[$name] ?? ucfirst($name);
            $this->errors[$name][] = "Field <em>$label</em> already exist";
        }
    }

    /**
     * Check if field is same
     *
     * @param $name of field
     * @param $same field to compare
     *
     * @return void
     */
    private function same($name, $same)
    {
        if (isset($this->data[$name]) && $this->data[$name] != $same) {
            $label = $this->labels[$name] ?? ucfirst($name);
            $this->errors[$name][] = "Field <em>$label</em> is incorrect";
        }
    }

    /**
     * Check type of file
     *
     * @param $name  of field
     * @param $types type of file to compare to field name
     *
     * @return void
     */
    private function file($name, $types)
    {
        $allowed = explode(',', $types);

        if (!empty($this->data[$name]['type']) && !in_array($this->data[$name]['type'], $allowed)) {
            $label = $this->labels[$name] ?? ucfirst($name);
            $this->errors[$name][] = "Field <em>$label</em> must be <em>$types</em>";
        }
    }

    /**
     * Check size of uploaded file
     *
     * @param $name of field
     * @param $max  maximum size of file
     *
     * @return void
     */
    private function size($name, $max)
    {
        if (isset($this->data[$name]['size']) && $this->data[$name]['size'] > $max) {
            $label = $this->labels[$name] ?? ucfirst($name);
            $this->errors[$name][] = "<em>$label</em> must be less than <em>$max</em>";
        }
    }

    /**
     * Check if field value is in the allowed values.
     *
     * @param $name   of field
     * @param $values field value to compare
     *
     * @return void
     */
    private function in($name, $values)
    {
        $allowed = explode(',', $values);

        if (isset($this->data[$name]) && !in_array($this->data[$name], $allowed)) {
            $label = $this->labels[$name] ?? ucfirst($name);
            $this->errors[$name][] = "<em>$label</em> is invalid";
        }
    }
}
