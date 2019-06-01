<?php

declare(strict_types=1);

namespace DataEcho;

use PDO;

class DataEcho
{
    public const DB_HOST = 'mysql';
    public const DB_NAME = 'mysql_db';
    public const DB_USER = 'user';
    public const DB_PASSWORD = 'user_password';

    public const PARAM_IDENT = 'ident';
    public const PARAM_VALUE = 'value';
    public const PARAM_VERSION = 'version';

    public const RESULT_PARAM_DELETE = 'delete';
    public const RESULT_PARAM_UPDATE = 'update';
    public const RESULT_PARAM_NEW = 'new';

    /**
     * @var null|PDO
     */
    private $db;

    /**
     * @var array
     */
    private $result = [self::RESULT_PARAM_DELETE => [], self::RESULT_PARAM_UPDATE => [], self::RESULT_PARAM_NEW => []];

    /**
     * @var string
     */
    private $echoType = 'serialize';

    public function __construct(PDO $db = null)
    {
        $this->db = $db;
    }

    public function prepare(array $data) : self
    {
        try {
            $requestData = $this->prepareRequestData($data);
            $dbData = $this->getDbData();
            foreach ($dbData as $rowIndent => $row) {
                if (isset($requestData[$rowIndent])) {
                    //update
                    if ($row[self::PARAM_VERSION] > $requestData[$rowIndent][self::PARAM_VERSION]) {
                        $this->result[self::RESULT_PARAM_UPDATE][$row[self::PARAM_IDENT]] = [
                            self::PARAM_VALUE => $row[self::PARAM_VALUE],
                            self::PARAM_VERSION => $row[self::PARAM_VERSION],
                        ];
                    }
                    unset($requestData[$rowIndent]);
                } else {
                    //new
                    $this->result[self::RESULT_PARAM_NEW][$row[self::PARAM_IDENT]] = [
                        self::PARAM_VALUE => $row[self::PARAM_VALUE],
                        self::PARAM_VERSION => $row[self::PARAM_VERSION],
                    ];
                }
            }
            //delete
            $this->result[self::RESULT_PARAM_DELETE] = empty($requestData) ? [] : array_keys($requestData);
        } catch (\Throwable $exception) {
            $this->result = sprintf(
                'Error: [%s] %s',
                $exception->getCode(),
                $exception->getMessage()
            );
            $this->echoType = '';
        }
        return $this;
    }

    public function echo(string $type = null) : void
    {
        $type = empty($type) ? $this->echoType : $type;
        switch ($type) {
            case 'json':
                $result = json_encode($this->result);
                break;
            case 'pre':
                $result = '<pre>' . print_r($this->result, true) . '</pre>';
                break;
            case 'serialize':
                $result = serialize($this->result);
                break;
            default:
                $result = (string) $this->result;
                break;
        }
        echo $result;
    }

    /**
     * @return mixed
     */
    public function getResult()
    {
        return $this->result;
    }

    private function prepareRequestData(array $data) : array
    {
        $result = [];
        $idents = $data[self::PARAM_IDENT] ?? [];
        foreach ($idents as $index => $value) {
            $ident = $data[self::PARAM_IDENT][$index] ?? null;
            $value = $data[self::PARAM_VALUE][$index] ?? '';
            $version = $data[self::PARAM_VERSION][$index] ?? 0;
            if ($ident !== null) {
                $result[$ident] = [
                    self::PARAM_IDENT => $ident,
                    self::PARAM_VALUE => $value,
                    self::PARAM_VERSION => (int) $version,
                ];
            }
        }
        return $result;
    }

    private function getDbData() : array
    {
        $result = [];
        foreach($this->getDb()->query('SELECT * FROM data', PDO::FETCH_ASSOC) as $row) {
            $result[$row[self::PARAM_IDENT]] = $row;
        }
        return $result;
    }

    private function getDb() : PDO
    {
        if ($this->db === null) {
            $this->db = new PDO(
                sprintf('mysql:host=%s;dbname=%s', self::DB_HOST, self::DB_NAME),
                self::DB_USER,
                self::DB_PASSWORD
            );
        }
        return $this->db;
    }
}
