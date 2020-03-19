<?php

/*
 * MicroWiki
 */

namespace Trismegiste\MicroWiki;

/**
 * Description of Document
 */
class Document extends Graph {

    const linkRegex = '/\[\[([^\]]+)\]\]/';

    protected $title;
    protected $description;

    public function __construct(string $t = '', string $d = '') {
        $this->title = $t;
        $this->description = $d;
    }

    // getters & setters
    public function setTitle(string $str): void {
        $this->title = $str;
    }

    public function getTitle(): string {
        return $this->title;
    }

    public function setDescription(string $str): void {
        $this->description = $str;
    }

    public function getDescription(): string {
        return $this->description;
    }

    // Graph methods
    public function moveVertexToNewKey(Vertex $vertex, string $newKey): void {
        $oldKey = $vertex->getKey();

        if (!array_key_exists($oldKey, $this->vertex)) {
            throw new \OutOfBoundsException("Key '$oldKey' does not exists in this Document");
        }

        if ($vertex !== $this->vertex[$oldKey]) {
            throw new \LogicException('This Vertex does not belong to this Document');
        }

        unset($this->vertex[$oldKey]);
        $this->vertex[$newKey] = $vertex;
        // this is the responsibility of Vertex to change its own Key !
        $this->replaceBrokenLinks($oldKey, $newKey);
    }

    protected function replaceBrokenLinks(string $oldKey, string $newKey): void {
        foreach ($this->vertex as $sentence) {
            $sentence->setContent(preg_replace('/\[\[(' . $oldKey . ')\]\]/', "[[$newKey]]", $sentence->getContent()));
        }
    }

    public function findBrokenLink(): array {
        $report = [];
        foreach ($this->getAllLinks() as $key) {
            if (!array_key_exists($key, $this->vertex)) {
                $report[] = $key;
            }
        }

        return $report;
    }

    public function getAllLinks(): array {
        return array_keys($this->getLinksCount());
    }

    public function getLinksCount(): array {
        $report = [];
        foreach ($this->vertex as $sentence) {
            $link = [];
            if (preg_match_all(self::linkRegex, $sentence->getContent(), $link)) {
                foreach ($link[1] as $key) {
                    if (!array_key_exists($key, $report)) {
                        $report[$key] = 1;
                    }
                    $report[$key] ++;
                }
            }
        }

        return $report;
    }

    public function findOrphan(): array {
        $report = [];
        $target = $this->getAllLinks();
        foreach ($this->vertex as $key => $obj) {
            if (!in_array($key, $target)) {
                $report[] = $obj;
            }
        }

        return $report;
    }

    /**
     * Finds all Sentence that contain a link to [[key]]
     * @param string $searchKey
     * @return array of Sentence
     */
    public function findVertexByLink(string $searchKey): array {
        $report = [];
        foreach ($this->vertex as $key => $obj) {
            if (preg_match('/\[\[' . $searchKey . '\]\]/', $obj->getContent())) {
                $report[] = $obj;
            }
        }

        return $report;
    }

}
