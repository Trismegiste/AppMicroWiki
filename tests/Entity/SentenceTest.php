<?php

use PHPUnit\Framework\TestCase;
use Trismegiste\MicroWiki\Document;
use Trismegiste\MicroWiki\DuplicateKeyException;
use Trismegiste\MicroWiki\Sentence;

class SentenceTest extends TestCase {

    protected $sut;

    protected function setUp(): void {
        $this->sut = new Sentence('name');
        $this->sut->setCategory('category');
        $this->sut->setContent('A sentence with [[link]] and...');
    }

    protected function tearDown(): void {
        unset($this->sut);
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
        $doc = new Document();
        $doc[] = $this->sut;
        $this->sut->renameKey($doc, 'New Name');
        $this->assertArrayHasKey('New Name', $doc);
        $this->assertArrayNotHasKey('name', $doc);
    }

    public function testNonEmptyKey() {
        $this->expectException(\InvalidArgumentException::class);
        new Sentence('');
    }

}
