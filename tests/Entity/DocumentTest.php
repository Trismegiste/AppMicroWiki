<?php

use PHPUnit\Framework\TestCase;
use Trismegiste\MicroWiki\Document;
use Trismegiste\MicroWiki\Sentence;

class DocumentTest extends TestCase {

    protected $sut;

    protected function setUp(): void {
        $this->sut = new Document();
    }

    protected function tearDown(): void {
        unset($this->sut);
    }

    public function testSetter() {
        $this->assertequals('', $this->sut->getTitle());
        $this->assertequals('', $this->sut->getDescription());
        $this->sut->setTitle('a');
        $this->sut->setDescription('b');
        $this->assertequals('a', $this->sut->getTitle());
        $this->assertequals('b', $this->sut->getDescription());
    }

    public function testAutoInsert() {
        $this->assertCount(0, $this->sut);
        new Sentence($this->sut, 'yolo');
        $this->assertCount(1, $this->sut);
        unset($this->sut['yolo']);
        $this->assertCount(0, $this->sut);
    }

    public function testGet() {
        $obj = new Sentence($this->sut, 'yolo');
        $this->assertEquals($obj, $this->sut['yolo']);
    }

    public function testIsset() {
        new Sentence($this->sut, 'yolo');
        $this->assertArrayHasKey('yolo', $this->sut);
    }

    public function testInvalidKey() {
        $this->expectException(OutOfBoundsException::class);
        $this->sut[123];
    }

    public function testIterable() {
        $sentence = new Sentence($this->sut, 'yolo');
        foreach ($this->sut as $key => $item) {
            $this->assertEquals('yolo', $key);
            $this->assertEquals($sentence, $item);
        }
    }

    public function testSetTrash() {
        $this->expectException(InvalidArgumentException::class);
        $this->sut[] = new stdClass();
    }

    public function testLink() {
        $origin = new Sentence($this->sut, 'origin');
        $origin->setContent('Link to [[target]] and [[missing]]');
        $target = new Sentence($this->sut, 'target');
        $target->setContent('[[origin]] nothing [fake]');
        $this->assertEquals(['target', 'missing', 'origin'], $this->sut->getAllLinks());
        $this->assertEquals(['missing'], $this->sut->findBrokenLink());
        $this->assertCount(0, $this->sut->findOrphan());
    }

}
