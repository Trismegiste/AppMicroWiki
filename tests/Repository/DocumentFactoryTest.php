<?php

use App\Repository\DocumentFactory;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Filesystem\Filesystem;
use Trismegiste\MicroWiki\Document;

class DocumentFactoryTest extends TestCase {

    protected $sut;

    protected function setUp(): void {
        $fs = $this->createStub(Filesystem::class);
        $this->sut = new DocumentFactory($fs, __DIR__);
    }

    protected function tearDown(): void {
        unset($this->sut);
    }

    public function testList() {
        $result = iterator_to_array($this->sut->list());
        $this->assertCount(1, $result);
        foreach ($result as $obj) {
            $this->assertEquals('Holon', $obj->title);
            $this->assertStringEndsWith('fixtures.json', $obj->filename);
        }
    }

    public function testLoad() {
        $doc = $this->sut->load('fixtures.json');
        $this->assertInstanceOf(Document::class, $doc);
        $this->assertEquals('Holon', $doc->getTitle());
        $this->assertCount(5, $doc);
    }

}
