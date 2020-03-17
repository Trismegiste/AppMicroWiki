<?php

/*
 * GraphSpeech
 */

namespace App\GraphSpeech;

/**
 * Description of Sentence
 */
class Sentence {

    protected $document;
    protected $uniqueKey;
    protected $category = 'all';
    protected $content = '';

    public function __construct(\ArrayAccess $doc, string $key) {
        if ($doc->offsetExists($key)) {
            throw new DuplicateKeyException("Key '$key' already exists");
        }
        $this->document = $doc;
        $this->uniqueKey = $key;
        $doc[$key] = $this;
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

    public function setKey(string $newKey): void {
        $oldKey = $this->uniqueKey;
        $this->uniqueKey = $newKey;
        $this->document[$oldKey] = $this;
    }

    public function getContent(): string {
        return $this->content;
    }

    public function setContent(string $content): void {
        $this->content = $content;
    }

}
