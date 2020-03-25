<?php

/*
 * AppMicrowiki
 */

namespace App\Repository;

use MongoDB\BSON\ObjectId;
use MongoDB\Driver\BulkWrite;
use MongoDB\Driver\Manager;
use MongoDB\Driver\Query;
use Trismegiste\MicroWiki\Document;

/**
 * Repository of Document
 */
class DocumentRepo {

    const collectionName = 'wikizz';

    protected $mongo;
    protected $dbName;

    public function __construct(Manager $man, $database = 'dev') {
        $this->mongo = $man;
        $this->dbName = $database;
    }

    private function getNamespace() {
        return $this->dbName . '.' . self::collectionName;
    }

    public function list(): array {
        $cursor = $this->mongo->executeQuery($this->getNamespace(), new Query([]));

        $listing = [];
        foreach ($cursor as $docu) {
            $listing[] = (object) [
                        'pk' => $docu->getPk(),
                        'title' => $docu->getTitle(),
                        'description' => $docu->getDescription(),
                        'vertex' => count($docu)
            ];
        }

        return $listing;
    }

    public function save(Document $doc): void {
        $bulk = new BulkWrite();

        if ($doc->isNew()) {
            $id = $bulk->insert($doc);
            $doc->setPk($id);
        } else {
            $bulk->update(['_id' => $doc->getPk()], $doc);
        }

        $this->mongo->executeBulkWrite($this->getNamespace(), $bulk);
    }

    public function load(string $pk): Document {
        $cursor = $this->mongo->executeQuery($this->getNamespace(), new Query(['_id' => new ObjectId($pk)]));
        $cursor->setTypeMap(['zob' => 'array']);
        $rows = iterator_to_array($cursor);

        return $rows[0];
    }

}
