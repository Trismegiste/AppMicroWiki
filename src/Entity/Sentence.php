<?php

/*
 * MicroWiki
 */

namespace Trismegiste\MicroWiki;

/**
 * Description of Sentence
 */
class Sentence implements Vertex {

    protected $uniqueKey;
    protected $category;
    protected $content;

    public function __construct(string $key) {
        if (empty($key)) {
            throw new \InvalidArgumentException("Key '$key' cannot be empty");
        }
        $this->uniqueKey = $key;
    }

    public function setCategory(string $cat): void {
        $this->category = $cat;
    }

    public function getCategory(): string {
        return $this->category;
    }

    public function getKey(): string {
        return $this->uniqueKey;
    }

    public function renameKey(Document $doc, string $newKey): void {
        $doc->renameVertexKey($this, $newKey);
        $this->uniqueKey = $newKey;
    }

    public function getContent(): string {
        return $this->content;
    }

    public function setContent(string $content): void {
        $this->content = $content;
    }

}
