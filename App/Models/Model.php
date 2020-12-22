<?php

namespace App\Models;

use PDO;
use App\Libraries\Database;
use App\Libraries\Str;

/**
 * User model to manage users.
 *
 * @category Model
 * @package  App
 */
class Model extends Database implements \Serializable
{
    protected $table;
    protected $attributes = [];

    /**
     * Magic get ($this->title) - get index from attributes if there is none properties with that name.
     *
     * @param $name attribute name to get
     *
     * @return mixed
     */
    public function __get($name)
    {
        return $this->attributes[$name] ?? null;
    }

    /**
     * Magic set ($this->title = 'lorem') - set value to the index in attributes.
     * If there is none property with that name
     *
     * @param $name  attribute name to set
     * @param $value value to set
     *
     * @return void
     */
    public function __set($name, $value)
    {
        $this->attributes[$name] = $value;
    }

    /**
     * Magic call ($this->getTitle()) - call (fake) virtual function to get properties.
     *
     * @param $method    name of fake method
     * @param $arguments artuments passet to function
     *
     * @return mixed
     */
    public function __call($method, $arguments)
    {
        if (strpos($method, 'get') === 0) {
            $snake = Str::snake(substr($method, 3));

            return $this->$snake;
        }
    }

    /**
     * Assign properties from array['id'] = 1 to Model object.($this->id = 1)
     * Convert generic Object (stdClass) to an array
     *
     * @param $data data to fill
     *
     * @return array $data
     */
    public function fill(array $data)
    {
        foreach ($data as $key => $value) {
            // Adding key as value to attributes
            $this->attributes[$key] = $value;
        }
    }

    /**
     * Function to create new record in database
     *
     * @return bool
     */
    public function create()
    {
        // ['id', 'title', 'description']
        $columns = array_keys($this->attributes);

        // ['`id`', '`title`', '`description`']
        $columnsWithAccent = array_map(function ($value) {
            return "`$value`";
        }, $columns);

        // `id`,`title`,`description`
        $columnsAsString = join(',', $columnsWithAccent);

        // [':id', ':title', ':description']
        $columnsWithDoubleCollon = array_map(function ($value) {
            return ":$value";
        }, $columns);

        // :id,:title,:description
        $valuesAsString = join(',', $columnsWithDoubleCollon);

        // Prepare SQL query
        // INSERT INTO `articles` (`id`,`title`,`description`) VALUES(:id,:title,:description)
        $sql = "INSERT INTO `$this->table`($columnsAsString) VALUES($valuesAsString)";

        // Prepare parameters to bind
        $params = $this->attributes;

        // Execute query and the status
        return $this->execute($sql, $params);
    }

    /**
     * Function to update record in database
     *
     * @return bool
     */
    public function update()
    {
        // Prepare SQL query
        $sql = "UPDATE `$this->table` SET ";

        foreach ($this->attributes as $key => $value) {
            // `title` = :title
            $sql .= "`$key` = :$key";

            if ($key !== array_key_last($this->attributes)) {
                // `title` = :title,
                $sql .= ',';
            }
        }

        $sql .= " WHERE `id` = :id";

        // Prepare parameters to bind
        $params = $this->attributes;

        // Execute query and the status
        return $this->execute($sql, $params);
    }

    /**
     * Static function to get model without creating a new object
     *
     * @param int $id model-id
     *
     * @return Model|null    ?-means null
     */
    public static function findById($id): ?Model
    {
        return self::findfirst(['id' => $id]);
    }

    /**
     * Function to find first record for specific filter (condition)
     *
     * @param array $filters condition which are added after WHERE sql
     *
     * @return Model|null
     */
    public static function findFirst(array $filters = []): ?Model
    {
        $model = current(self::find($filters, 1));

        // If true(find) return model if false(not results) return null
        return $model ? $model : null;
    }

    /**
     * Function to delete record in database
     *
     * @param bool $permanent set to false (only changes status to deleted/ if true- delete from database)
     *
     * @return bool
     */
    public function delete(bool $permanent = false)
    {
        if ($permanent) {
            // Prepare SQL query
            $sql = "DELETE FROM `$this->table` WHERE `id` = :id ";

            // Prepare parameters to bind
            $params = [
                'id' => $this->id,
            ];
        } else {
            $sql = "UPDATE `$this->table`
                SET `status` = :status, `deleted_at` = :deleted_at
                WHERE `id` = :id ";

            // Prepare parameters to bind
            $params = [
                'id' => $this->id,
                'status' => static::DELETED,
                'deleted_at' => date('Y-m-d H:i:s'),
            ];
        }
        // Execute query and the status
        return $this->execute($sql, $params);
    }

    /**
     * Function to find
     *
     * @param array $filters - WHERE conditions in the SQL (in NO filters - return all results)
     * @param int   $limit   set limit to show
     * @param int   $offset  set offset
     * @param array $orderBy how to show results (asc/desc)
     *
     * @return array model instances
     */
    public static function find(array $filters = [], $limit = 0, $offset = 0, $orderBy = []): array
    {
        $models = [];
        // create new model object (depending on Model name)
        $model = new static();

        // Get table name from Model Instance
        $sql = "SELECT * FROM `$model->table`";

        // NO parameter by default
        $params = [];

        // If any filter sent
        if ($filters) {
            // Treat filters as parameters
            $params = $filters;

            // Add...  SELECT * FROM `articles` WHERE  to sql
            $sql .= " WHERE ";

            // Prepare array for more than 1 filter
            $arr = [];

            // Prepare parameters to bind
            foreach ($filters as $key => $value) {
                if (is_array($value)) {
                    // @TODO: fix in()
                    $arr[] = "`$key` IN(:$key)";
                } else {
                    // Add binded filters to array
                    // `user_id` = :user_id
                    $arr[] = "`$key` = :$key";
                }
            }
            // SELECT * FROM `articles` WHERE `user_id` = :user_id AND .......
            $sql .= join(" AND ", $arr);
        }

        if ($orderBy) {
            $sql .= " ORDER BY ";

            foreach ($orderBy as $key => $value) {
                $sql .= $key . ' ' . $value;
            }
        }

        if ($limit) {
            $sql .= " LIMIT $limit";
        }

        if ($offset) {
            $sql .= " OFFSET $offset";
        }

        // Find all generic results (from database)
        // The parameter PDO::FETCH_ASSOC tells PDO to return the result as an associative array.
        $results = $model->findAll($sql, $params, PDO::FETCH_ASSOC);

        // Return empty array if NO results
        if (!$results) {
            return $models;
        }

        // Go through results and add model object to the models
        foreach ($results as $result) {
            // Create empty model object
            $model = new static();

            // Fill the object from the result
            $model->fill($result);

            // Add model object to models array.
            $models[] = $model;
        }

        return $models;
    }

    /**
     * Count all elements from Database
     *
     * @param array $filters add filter to sql
     *
     * @return array
     */
    public static function count(array $filters = [])
    {
        $model = [];

        $model = new static();

        // Prepare SQL query
        // Count — Count all elements in an array, or something in an object (set 'total' as alias)
        $sql = "SELECT count(*) AS `total` FROM `$model->table`";

        $params = [];

        if ($filters) {
            // Treat filters as parameters
            $params = $filters;

            // Add...  SELECT * FROM `articles` WHERE  to sql
            $sql .= " WHERE ";

            // Prepare array for more than 1 filter
            $arr = [];

            // Prepare parameters to bind
            foreach ($filters as $key => $value) {
                // Add binded filters to array
                $arr[] = "`$key` = :$key";
            }

            // SELECT * FROM `articles` WHERE `user_id` = :user_id AND .......
            $sql .= join(" AND ", $arr);
        }

        // Execute query and the status
        $result = $model->findOne($sql, $params);

        // Return result ->total (set in sql AS custom collumn 'total')
        return (int) $result->total;
    }

    /**
     * Serialize the model's data. Before storing to the session.
     *
     * @return string
     */
    public function serialize(): string
    {
        return base64_encode(serialize($this->attributes));
    }

    /**
     * Unserialize and set the data. After getting from the session.
     *
     * @param $serialized function
     *
     * @return object Model
     */
    public function unserialize($serialized): self
    {
        // Call the parent constructor to make PDO object
        $this->__construct();
        $this->attributes = unserialize(base64_decode($serialized));

        return $this;
    }
}
