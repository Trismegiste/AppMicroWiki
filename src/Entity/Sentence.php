<?php

/*
 * MicroWiki
 */

namespace Trismegiste\MicroWiki;

use InvalidArgumentException;
use MongoDB\BSON\Persistable;
use Trismegiste\Toolbox\MongoDb\PersistableImpl;

/**
 * Description of Sentence
 */
class Sentence implements Vertex, Persistable {

    use PersistableImpl;

    const linkRegex = '/\[\[([^\]]+)\]\]/';

    protected $uniqueKey;
    protected $category = '';
    protected $content = '';

    public function __construct(string $key) {
        if (empty($key)) {
            throw new InvalidArgumentException("Key '$key' cannot be empty");
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
        $doc->moveVertexToNewKey($this, $newKey);
        $this->uniqueKey = $newKey;
    }

    public function getContent(): string {
        return $this->content;
    }

    public function setContent(string $content): void {
        $this->content = $content;
    }

    /**
     * Gets the links to other Sentence. We only deals with __fuzzy__ STRING keys because this
     * object does not know its siblings. Only Document could know other OBJECTS
     * @return array an array of STRING for each link.
     */
    public function getOutboundKey(): array {
        $report = [];
        $link = [];
        if (preg_match_all(self::linkRegex, $this->content, $link)) {
            foreach ($link[1] as $key) {
                $report[$key] = true;
            }
        }

        return array_keys($report);
    }

}
