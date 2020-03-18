<?php

namespace App\Repository;

use App\Entity\GraphSpeech\Document;
use App\Entity\GraphSpeech\Sentence;
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
        $iter->in($this->basedir)->name("*.json")->files();

        return $iter;
    }

    public function save(Document $doc): void {
        $flatten = [
            'title' => $doc->getTitle(),
            'description' => $doc->getDescription()
        ];
        foreach ($doc as $vertex) {
            $flatten['vertex'][] = [
                'key' => $vertex->getKey(),
                'category' => $vertex->getCategory(),
                'content' => $vertex->getContent()
            ];
        }

        $this->filesystem->dumpFile($this->basedir . $doc->getTitle() . '.json', json_encode($flatten));
    }

    public function load(string $title): Document {
        $flatten = json_decode(file_get_contents($this->basedir . $title . '.json'));

        $doc = $this->create();
        $doc->setTitle($flatten->title);
        $doc->setDescription($flatten->description);
        foreach ($flatten->vertex as $vertex) {
            $stc = new Sentence($doc, $vertex->key);
            $stc->setCategory($vertex->category);
            $stc->setContent($vertex->content);
        }

        return $doc;
    }

}
