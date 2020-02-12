<?php
abstract class Model
{
    protected $PK = "id";
     /**
      * @var string
      */
    protected $table;
     /**
      * @var PDO $connection
      */
    protected $connection;
    public function __construct(PDO $connection)
    {
        $this->setTable();
        $this->setConnection($connection);
    }


     /**
     * @return void
     */
    public function setTable(): void
    {
       if (empty($this->table))
           $this->table = strtolower(get_class($this)) . "s";
    }
    /**
     * @param mixed $connection
     */
    public function setConnection(PDO $connection): void
    {
        $connection->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        $this->connection = $connection;
    }

    /**
     * @param array $columns
     * @return mixed
     */
     public function all(array $columns = ['*'])
     {
         try {
             $cols = count($columns) === 1 ? $columns[0] : implode(",",$columns);
             $stmt = $this->connection->prepare("SELECT {$cols} FROM {$this->getTable()}");
             $stmt->execute();
             $stmt->setFetchMode(PDO::FETCH_ASSOC);
             return $stmt->fetchAll();
         }
         catch (Throwable $exception){
             die($exception->getMessage());
         }
     }

     /**
      * @return PDO
      */
     public function getConnection() : PDO
     {
         return $this->connection;
     }
     /**
      * @return string
      */
     public function getTable() : string
     {
         return $this->table;
     }

    /**
     * @param array $data
     * @return mixed
     * @throws Exception
     */
    public function create(array $data)
    {
        try {
            $columns = implode(",",array_keys($data));
            $stmt = $this->getConnection()->prepare(
                $query = "INSERT INTO {$this->getTable()} ({$columns}) VALUES (" . set_values_as_questions_mark(count(array_values($data))) .")"
            );
            $stmt->execute(array_values($data));
            return $this->getLatestRecords($this->getConnection()->lastInsertId($this->table . ".{$this->PK}"));
        } catch (Throwable $exception){
            exit(json_encode(['error' => $exception->getMessage()]));
        }
    }

    public function update(array $data , array $wheres = null)
    {
        if ($wheres != null){
            $wheresString = "WHERE ";
            foreach ($wheres as $where) {
                $wheresString .=
                    $where['column'] .
                    $where['operator'] ?? "=" .
                    is_string($where['value']) ? filter_var($where['value'],FILTER_SANITIZE_STRING) : $where['value'] .
                    $where['link_operator'] ?? "";
            }
        }
        $upgradeableData = [];
        foreach ($data as $column => $value) {
            $upgradeableData[] = "{$column}=" . set_values_as_questions_mark(count($data));
        }
        $query = "UPDATE {$this->getTable()} SET " . implode(",",$upgradeableData) . $wheresString ?? "";
        try{
            $stmt = $this->getConnection()->prepare($query);
            $stmt->execute(array_values($data));
            $result = $this->getConnection()->prepare("SELECT * FROM {$this->getTable()} " . $wheres ?? "ORDER DESC LIMIT " . $stmt->rowCount() - 1);
            $result->execute();
            return $result->rowCount() > 1 ? $result->fetchAll(PDO::FETCH_ASSOC) : $result->fetch(PDO::FETCH_ASSOC);
        }catch (Exception $exception){
            die($exception->getMessage());
        }
    }

    public function rawQuery(string $query)
    {
        return $this->getConnection()->prepare($query)->fetch(PDO::FETCH_ASSOC);
    }

    public function delete(array $wheres = null)
    {
        $query = "DELETE FROM {$this->getTable()} ";
        if ($wheres != null){
            $query .= "WHERE ";
            foreach ($wheres as $where) {
                $query .= sprintf("%s%s%s%s",
                    $where['column'],
                    isset($where['operator']) || ( isset($where['operator']) && $where['operator'] === "=") ? " " .  $where['operator'] : "=",
                    $this->getConnection()->quote($where['value']),
                    isset($where['link_operator']) ? " ". strtoupper($where['link_operator']) . " "  : ""
                    );
            }
        }
        try{
            $stmt = $this->getConnection()->prepare($query);
            return $stmt->execute();
        } catch (Exception $exception){
            die($exception->getMessage());
        }
    }

    protected function getLatestRecords(int $lastId)
    {
        $stmt = $this->getConnection()->prepare("SELECT * FROM {$this->getTable()} WHERE `id`=:lastId");
        $stmt->execute([':lastId' => $lastId]);
        return $stmt->rowCount() > 1 ? $stmt->fetchAll(PDO::FETCH_ASSOC) : $stmt->fetch(PDO::FETCH_ASSOC);
    }
 }
 function set_values_as_questions_mark(int $arrayCount)
 {
     $marks = "";
     for ($i = 0 ; $i < $arrayCount ; $i++){
         $marks .= "?";
         if ($arrayCount - 1 !== $i)
             $marks .= ",";
     }
     return $marks;
 }
