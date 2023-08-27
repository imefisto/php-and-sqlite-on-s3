<?php
namespace Imefisto\PhpWithSqliteOnS3;

use Aws\S3\Exception\S3Exception;
use Aws\S3\S3Client;

class ConnectionManager
{
    private $localPath;
    private $db;

    public function __construct(
        private S3Client $client,
        private string $bucket,
        private string $database
    ) {
        $this->localPath = tempnam(sys_get_temp_dir(), 'sqlite-');
    }

    public function get(): \PDO
    {
        if (empty($this->db)) {
            $this->downloadDatabaseFile();
            $this->initDatabase();
        }

        return $this->db;
    }

    private function downloadDatabaseFile()
    {
        try {
            $this->client->getObject([
                'Bucket' => $this->bucket,
                'Key' => $this->database,
                'SaveAs' => $this->localPath
            ]);
        } catch (S3Exception $e) {
            unlink($this->localPath);

            if (false === strpos($e->getMessage(), '404 Not Found')) {
                throw $e;
            }
        }
    }

    private function initDatabase()
    {
        $this->db = new \PDO('sqlite:' . $this->localPath);
        $this->createTableIfNotExists();
    }

    private function createTableIfNotExists()
    {
        $sql = <<<END
CREATE TABLE IF NOT EXISTS executions (
    id INTEGER PRIMARY KEY,
    created_at TEXT NOT NULL)
END ;

        $this->db->exec($sql);
    }

    public function __destruct()
    {
        $this->client->putObject([
            'Bucket' => $this->bucket,
            'Key' => $this->database,
            'SourceFile' => $this->localPath,
            'ContentType' => 'application/vnd.sqlite3',
        ]);

        $this->db = null;
        unlink($this->localPath);
    }
}
