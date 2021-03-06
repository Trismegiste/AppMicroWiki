<?php

use PHPUnit\Framework\TestCase;
use Trismegiste\MicroWiki\Document;
use Trismegiste\MicroWiki\Sentence;

class SentenceTest extends TestCase
{

    protected $sut;

    protected function setUp(): void
    {
        $this->sut = new Sentence('name');
        $this->sut->setCategory('category');
        $this->sut->setContent('A sentence with [[link]] and...');
        $this->sut->setLink('http://zog.zog');
    }

    protected function tearDown(): void
    {
        unset($this->sut);
    }

    public function testGetters()
    {
        $this->assertEquals('name', $this->sut->getKey());
        $this->assertEquals('category', $this->sut->getCategory());
        $this->assertStringStartsWith('A sentence', $this->sut->getContent());
        $this->assertEquals('http://zog.zog', $this->sut->getLink());
    }

    public function testSetters()
    {
        $this->sut->setContent("New");
        $this->assertEquals('New', $this->sut->getContent());
    }

    public function testChangeKey()
    {
        $doc = new Document();
        $doc[] = $this->sut;
        $this->sut->renameKey($doc, 'New Name');
        $this->assertArrayHasKey('New Name', $doc);
        $this->assertArrayNotHasKey('name', $doc);
    }

    public function testNonEmptyKey()
    {
        $this->expectException(InvalidArgumentException::class);
        new Sentence('');
    }

    public function testOutboundLink()
    {
        $this->assertEquals(['link'], $this->sut->getOutboundKey());
    }

    public function testNonDuplicateOutbound()
    {
        $this->sut = new Sentence('name');
        $this->sut->setContent('[[link]] [[zelda]] and [[link]]');
        $this->assertEquals(['link', 'zelda'], $this->sut->getOutboundKey());
    }

}
