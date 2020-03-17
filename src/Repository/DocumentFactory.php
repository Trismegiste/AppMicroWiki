<?php

namespace App\Repository;

use App\Entity\GraphSpeech\Document;
use IteratorAggregate;
use Symfony\Component\Filesystem\Filesystem;
use Symfony\Component\Finder\Finder;

/**
 * Repository of Document
 */
class DocumentFactory {

    protected $filesystem;

    public function __construct(Filesystem $fs) {
        $this->filesystem = $fs;
    }

    public function create(): Document {
        return new Document();
    }

    public function list(): IteratorAggregate {
        $iter = new Finder();
        $iter->in(__DIR__ . '/../../var/document')->name("*.doc")->files();

        return $iter;
    }

    public function save(Document $doc) {
        $this->filesystem->dumpFile($doc->getTitle(), serialize($doc));
    }

}
