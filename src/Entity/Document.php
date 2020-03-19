<?php

/*
 * MicroWiki
 */

namespace Trismegiste\MicroWiki;

/**
 * Description of Document
 */
class Document implements \ArrayAccess, \IteratorAggregate, \Countable {

    const linkRegex = '/\[\[([^\]]+)\]\]/';

    protected $title;
    protected $description;
    protected $vertex = [];

    public function __construct(string $t = '', string $d = '') {
        $this->title = $t;
        $this->description = $d;
    }

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

    public function offsetExists($offset): bool {
        return array_key_exists($offset, $this->vertex);
    }

    public function offsetGet($offset) {
        if (!array_key_exists($offset, $this->vertex)) {
            throw new \OutOfBoundsException("Unknown key '$offset'");
        }
        return $this->vertex[$offset];
    }

    public function offsetSet($oldKey, $value): void {
        if (!($value instanceof Sentence)) {
            throw new \InvalidArgumentException("Item '$oldKey' must be a Sentence");
        }

        if (!is_null($oldKey) && ($oldKey !== $value->getKey())) {
            unset($this->vertex[$oldKey]);
            $this->replaceBrokenLinks($oldKey, $value->getKey());
        }

        $this->vertex[$value->getKey()] = $value;
    }

    public function renameVertexKey(Vertex $vertex, $newKey) {
        
    }

    protected function replaceBrokenLinks(string $oldKey, string $newKey): void {
        foreach ($this->vertex as $sentence) {
            $sentence->setContent(preg_replace('/\[\[(' . $oldKey . ')\]\]/', "[[$newKey]]", $sentence->getContent()));
        }
    }

    public function offsetUnset($offset): void {
        unset($this->vertex[$offset]);
    }

    public function count(): int {
        return count($this->vertex);
    }

    public function getIterator(): \Traversable {
        return new \ArrayIterator($this->vertex);
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
