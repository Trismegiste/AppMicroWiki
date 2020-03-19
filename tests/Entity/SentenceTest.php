<?php

use PHPUnit\Framework\TestCase;
use Trismegiste\MicroWiki\Document;
use Trismegiste\MicroWiki\DuplicateKeyException;
use Trismegiste\MicroWiki\Sentence;

class SentenceTest extends TestCase {

    protected $sut;
    protected $doc;

    protected function setUp(): void {
        $this->doc = new Document();
        $this->sut = new Sentence($this->doc, 'name');
        $this->sut->setCategory('category');
        $this->sut->setContent('A sentence with [[link]] and...');
    }

    protected function tearDown(): void {
        unset($this->sut);
        unset($this->doc);
    }

    public function testRegistered() {
        $this->assertCount(1, $this->doc);
        $this->assertArrayHasKey('name', $this->doc);
    }

    public function testGetters() {
        $this->assertEquals('name', $this->sut->getKey());
        $this->assertEquals('category', $this->sut->getCategory());
        $this->assertStringStartsWith('A sentence', $this->sut->getContent());
    }

    public function testSetters() {
        $this->sut->setContent("New");
        $this->assertEquals('New', $this->sut->getContent());
    }

    public function testChangeKey() {
        $this->sut->setKey('New Name');
        $this->assertArrayHasKey('New Name', $this->doc);
        $this->assertArrayNotHasKey('name', $this->doc);
    }

    public function testUniqueKey() {
        $this->expectException(DuplicateKeyException::class);
        new Sentence($this->doc, 'name');
    }

}
