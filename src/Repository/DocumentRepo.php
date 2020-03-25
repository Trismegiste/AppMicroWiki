<?php

/*
 * AppMicrowiki
 */

namespace App\Repository;

use MongoDB\Driver\Manager;
use MongoDB\Driver\Query;
use Traversable;

/**
 * Repository of Document
 */
class DocumentRepo {

    protected $mongo;
    protected $dbName;

    public function __construct(Manager $man, $database = 'dev') {
        $this->mongo = $man;
        $this->dbName = $database;
    }

    public function list(): Traversable {
        $cursor = $this->mongo->executeQuery($this->dbName . '.wiki', new Query([]));
        
    }

}
