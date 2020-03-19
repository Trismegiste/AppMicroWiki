<?php

use PHPUnit\Framework\TestCase;
use Trismegiste\MicroWiki\DuplicateKeyException;
use Trismegiste\MicroWiki\Graph;
use Trismegiste\MicroWiki\Vertex;

class GraphTest extends TestCase {

    protected $sut;

    protected function setUp(): void {
        $this->sut = new Graph();
    }

    protected function createVertexMock(string $key): Vertex {
        $vertex = $this->createMock(Vertex::class);
        $vertex->expects($this->atLeastOnce())
                ->method('getKey')
                ->willReturn($key);

        return $vertex;
    }

    protected function tearDown(): void {
        unset($this->sut);
    }

    public function testEmpty() {
        $this->assertCount(0, $this->sut);
    }

    public function testNonExitingKey() {
        $this->expectException(OutOfBoundsException::class);
        $this->sut[123];
    }

    public function testPush() {
        $vertex = $this->createStub(Vertex::class);
        $this->sut[] = $vertex;
        $this->assertCount(1, $this->sut);
    }

    public function testPushAndIndexing() {
        $vertex = $this->createVertexMock('42');

        $this->sut[] = $vertex;

        $this->assertCount(1, $this->sut);
        $this->assertArrayHasKey('42', $this->sut);
        $this->assertEquals($vertex, $this->sut['42']);
    }

    public function testFailSetWithDumb() {
        $this->expectException(InvalidArgumentException::class);
        $this->sut[] = new stdClass();
    }

    public function testFailSetWithKey() {
        $this->expectException(LogicException::class);
        $vertex = $this->createStub(Vertex::class);
        $this->sut['42'] = $vertex;
    }

    public function testDuplicateKey() {
        $this->expectException(DuplicateKeyException::class);

        $vertex = $this->createVertexMock('42');

        $this->sut[] = $vertex;
        $this->sut[] = $vertex;
    }

    public function testUnset() {
        $vertex = $this->createVertexMock('42');

        $this->sut[] = $vertex;
        $this->assertCount(1, $this->sut);
        unset($this->sut['42']);
        $this->assertArrayNotHasKey('42', $this->sut);
        $this->assertCount(0, $this->sut);
    }

    public function testIterator() {
        $vertex = $this->createVertexMock('42');
        $this->sut[] = $vertex;

        foreach ($this->sut as $key => $item) {
            $this->assertEquals('42', $key);
            $this->assertEquals($vertex, $item);
        }
    }

}
