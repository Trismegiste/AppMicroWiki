<?php

/*
 * MicroWiki
 */

namespace Trismegiste\MicroWiki;

/**
 * A vertex from a graph
 */
interface Vertex {

    public function getKey(): string;

    public function getOutboundKey(): array;
}
