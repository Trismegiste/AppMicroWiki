<?php

namespace App\Repository;

/**
 * ListingDecorateur for Finder
 */
class ListingDecorator implements \Iterator {

    protected $embed;

    public function __construct(\Iterator $it) {
        $this->embed = $it;
    }

    public function current() {
        $current = $this->embed->current();
        $docu = json_decode($current->getContents());

        return (object) [
                    'filename' => $current->getBasename('.json'),
                    'title' => $docu->title,
                    'info' => $docu->description
        ];
    }

    public function key() {
        return $this->embed->key();
    }

    public function next(): void {
        $this->embed->next();
    }

    public function rewind(): void {
        $this->embed->rewind();
    }

    public function valid(): bool {
        return $this->embed->valid();
    }

}
