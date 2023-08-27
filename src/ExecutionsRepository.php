<?php
namespace Imefisto\PhpWithSqliteOnS3;

class ExecutionsRepository
{
    public function __construct(private ConnectionManager $conn)
    {
    }

    public function all()
    {
        $rows = $this->conn->get()->query('SELECT * FROM executions');
        while ($row = $rows->fetch(\PDO::FETCH_ASSOC)) {
            yield $row;
        }
    }

    public function registerExecution()
    {
        $sql = 'INSERT INTO executions (created_at) VALUES (:created_at)';
        $stmt = $this->conn->get()->prepare($sql);
        $stmt->bindValue(':created_at', date('Y-m-d H:i:s'));
        $stmt->execute();
    }
}
