<?php

use PHPUnit\Framework\TestCase;
use Trismegiste\MicroWiki\Document;
use Trismegiste\MicroWiki\Sentence;

class DocumentTest extends TestCase
{

    protected $sut;

    protected function setUp(): void
    {
        $this->sut = new Document();
    }

    protected function tearDown(): void
    {
        unset($this->sut);
    }

    public function simpleEdgeFactory()
    {
        $origin = new Sentence('origin');
        $origin->setContent('Link to [[target]] and [[missing]]');
        $target = new Sentence('target');
        $target->setContent('[[origin]] nothing');

        return [
            [$origin, $target]
        ];
    }

    public function testSetter()
    {
        $this->assertequals('', $this->sut->getTitle());
        $this->assertequals('', $this->sut->getDescription());
        $this->sut->setTitle('a');
        $this->sut->setDescription('b');
        $this->assertequals('a', $this->sut->getTitle());
        $this->assertequals('b', $this->sut->getDescription());
    }

    /** @dataProvider simpleEdgeFactory */
    public function testMoveVertexToNewKeyBadKey($origin, $target)
    {
        $this->sut[] = $origin;
        $this->expectException(OutOfBoundsException::class);
        $this->sut->moveVertexToNewKey($target, 'dummy');
    }

    /** @dataProvider simpleEdgeFactory */
    public function testMoveVertexToNewKeySameKey($origin, $target)
    {
        $this->sut[] = $origin;
        $identityThief = new Sentence('origin');
        $this->expectException(LogicException::class);
        $this->sut->moveVertexToNewKey($identityThief, 'dummy');
    }

    /** @dataProvider simpleEdgeFactory */
    public function testMoveVertexToNewKey($origin, $target)
    {
        $this->sut[] = $origin;
        $this->sut[] = $target;
        $this->sut->moveVertexToNewKey($target, 'new target');
        $this->assertArrayHasKey('new target', $this->sut);
        $this->assertStringStartsWith('Link to [[new target]]', $this->sut['origin']->getContent());
    }

    /** @dataProvider simpleEdgeFactory */
    public function testLink($origin, $target)
    {
        $orphan = new Sentence('orphan');
        $orphan->setContent('nothing');
        $this->sut[] = $origin;
        $this->sut[] = $target;
        $this->sut[] = $orphan;
        $this->assertEquals(['target', 'missing', 'origin'], $this->sut->getAllLinks());
        $this->assertEquals(['missing'], $this->sut->findBrokenLink());
        $this->assertEquals([$orphan], $this->sut->findOrphan());
        $this->assertEquals([$target], $this->sut->findVertexByLink('origin'));
    }

    public function testFindVertexByLink()
    {
        $source = new Sentence('NameOk');
        $source->setContent('link to [[R+C]]');
        $target = new Sentence('R+C');
        $target->setContent('nothing');

        $this->sut[] = $source;
        $this->sut[] = $target;

        $result = $this->sut->findVertexByLink('R+C');
        $this->assertCount(1, $result);
        $this->assertEquals('NameOk', $result[0]->getKey());
    }

    public function testSearchKeysStartingBy()
    {
        $uniqueKey = 'R+C';
        $target = new Sentence($uniqueKey);
        $this->sut[] = $target;

        $result = $this->sut->searchKeysStartingBy('r+');
        $this->assertCount(1, $result);
        $this->assertEquals($uniqueKey, $result[0]);
    }

    public function testSearchLinksStartingBy()
    {
        $uniqueKey = 'name';
        $target = new Sentence($uniqueKey);
        $target->setContent('link to [[R+C]]');
        $this->sut[] = $target;

        $result = $this->sut->searchLinksStartingBy('r+');
        $this->assertCount(1, $result);
        $this->assertEquals($uniqueKey, $result[0]);
    }

}
