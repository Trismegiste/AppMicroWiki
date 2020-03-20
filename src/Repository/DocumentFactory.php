<?php

namespace App\Repository;

use Iterator;
use Symfony\Component\Filesystem\Filesystem;
use Symfony\Component\Finder\Finder;
use Trismegiste\MicroWiki\Document;
use Trismegiste\MicroWiki\Sentence;

/**
 * Repository of Document
 */
class DocumentFactory {

    protected $filesystem;
    protected $basedir;

    public function __construct(Filesystem $fs, string $projectDir, string $repositoryDir) {
        $this->filesystem = $fs;
        $this->basedir = $projectDir . '/' . $repositoryDir . '/';
    }

    public function create(): Document {
        return new Document();
    }

    public function list(): Iterator {
        $iter = new Finder();
        $iter->in($this->basedir)
                ->name("*.json")
                ->files();

        return new ListingDecorator($iter->getIterator());
    }

    public function save(Document $doc): void {
        $flatten = [
            'title' => $doc->getTitle(),
            'description' => $doc->getDescription()
        ];

        $flatten['vertex'] = [];
        foreach ($doc as $vertex) {
            $flatten['vertex'][] = [
                'key' => $vertex->getKey(),
                'category' => $vertex->getCategory(),
                'content' => $vertex->getContent()
            ];
        }

        $this->filesystem->dumpFile($this->basedir . $doc->getTitle() . '.json', json_encode($flatten));
    }

    public function load(string $filename): Document {
        $flatten = json_decode(file_get_contents($this->basedir . $filename . '.json'));

        $doc = $this->create();
        $doc->setTitle($flatten->title);
        $doc->setDescription($flatten->description);
        if (isset($flatten->vertex)) {
            foreach ($flatten->vertex as $vertex) {
                $stc = new Sentence($vertex->key);
                $stc->setCategory($vertex->category);
                $stc->setContent($vertex->content);
                $doc[] = $stc;
            }
        }

        return $doc;
    }

}
