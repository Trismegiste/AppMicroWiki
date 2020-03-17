<?php

namespace App\Repository;

use App\Entity\GraphSpeech\Document;
use Symfony\Component\Filesystem\Filesystem;

/**
 * Repository of Document
 */
class DocumentFactory {

    protected $filesystem;

    public function __construct(Filesystem $fs) {
        $this->filesystem = $fs;
    }

    public function create() {
        return new Document();
    }

}
