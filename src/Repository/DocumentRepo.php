<?php

/*
 * AppMicrowiki
 */

namespace App\Repository;

use Iterator;
use Trismegiste\MicroWiki\Document;
use Trismegiste\Toolbox\Iterator\ClosureDecorator;
use Trismegiste\Toolbox\MongoDb\Repository;

/**
 * Adapter of Document
 */
class DocumentRepo {

    protected $repository;

    public function __construct(Repository $documentRepo) {
        $this->repository = $documentRepo;
    }

    public function list(): Iterator {
        $iter = $this->repository->search();

        return new ClosureDecorator($iter, function(Document $docu) {
            return (object) [
                        'pk' => $docu->getPk(),
                        'title' => $docu->getTitle(),
                        'description' => $docu->getDescription(),
                        'vertex' => count($docu)
            ];
        });
    }

    public function save(Document $doc): void {
        $this->repository->save($doc);
    }

    public function load(string $pk): Document {
        return $this->repository->load($pk);
    }

}
