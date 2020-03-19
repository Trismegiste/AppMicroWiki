<?php

/*
 * MicroWiki
 */

namespace Trismegiste\MicroWiki;

/**
 * A graph that maintain a list of key-indexed Vertex
 */
class Graph implements \ArrayAccess, \IteratorAggregate, \Countable {

    protected $vertex = [];

    // ArrayAccess     
    public function offsetExists($offset): bool {
        return array_key_exists($offset, $this->vertex);
    }

    public function offsetGet($offset) {
        if (!array_key_exists($offset, $this->vertex)) {
            throw new \OutOfBoundsException("Unknown key '$offset'");
        }
        return $this->vertex[$offset];
    }

    public function offsetSet($key, $vertex): void {
        if (!($vertex instanceof Vertex)) {
            throw new \InvalidArgumentException("Item must be a Vertex");
        }

        if (!is_null($key)) {
            throw new \LogicException("Sorry Dave, I'm afraid I can't do that");
        }

        if (array_key_exists($vertex->getKey(), $this->vertex)) {
            throw new DuplicateKeyException("Key '" . $vertex->getKey() . "' already exists");
        }

        $this->vertex[$vertex->getKey()] = $vertex;
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

}
