<?php

namespace App\Entity\GraphSpeech;

/**
 * A vertex from a graph
 */
interface Vertex {

    public function getKey(): string;
}
