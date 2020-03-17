<?php

/*
 * GraphSpeech
 */

namespace App\Entity\GraphSpeech;

/**
 * Description of Document
 */
class Document implements \ArrayAccess, \IteratorAggregate, \Countable {

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

        if ($oldKey !== $value->getKey()) {
            unset($this->vertex[$oldKey]);
        }

        $this->vertex[$value->getKey()] = $value;
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
        $report = [];
        foreach ($this->vertex as $key => $sentence) {
            $link = [];
            if (preg_match_all('/\[\[([^\]]+)\]\]/', $sentence->getContent(), $link)) {
                foreach ($link[1] as $key) {
                    $report[] = $key;
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

}
