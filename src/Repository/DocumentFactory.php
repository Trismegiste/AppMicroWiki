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
    protected $basedir;

    public function __construct(Filesystem $fs) {
        $this->filesystem = $fs;
        $this->basedir = __DIR__ . '/../../var/document/';
    }

    public function create(): Document {
        return new Document();
    }

    public function list(): IteratorAggregate {
        $iter = new Finder();
        $iter->in($this->basedir)->name("*.doc")->files();

        return $iter;
    }

    public function save(Document $doc): void {
        $this->filesystem->dumpFile($this->basedir . $doc->getTitle() . '.doc', serialize($doc));
    }

    public function load(string $title): Document {
        return unserialize(file_get_contents($this->basedir . $title . '.doc'));
    }

}
