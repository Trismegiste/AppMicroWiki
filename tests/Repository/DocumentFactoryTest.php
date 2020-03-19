<?php

use App\Repository\DocumentFactory;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Filesystem\Filesystem;
use Trismegiste\MicroWiki\Document;
use Trismegiste\MicroWiki\Sentence;

class DocumentFactoryTest extends TestCase {

    protected $sut;
    protected $fs;

    protected function setUp(): void {
        $this->fs = $this->createMock(Filesystem::class);
        $this->sut = new DocumentFactory($this->fs, __DIR__, '.');
    }

    protected function tearDown(): void {
        unset($this->sut);
    }

    public function testList() {
        $result = iterator_to_array($this->sut->list());
        $this->assertCount(1, $result);
        foreach ($result as $obj) {
            $this->assertEquals('Holon', $obj->title);
            $this->assertEquals('fixtures', $obj->filename);
        }
    }

    public function testLoad() {
        $doc = $this->sut->load('fixtures');
        $this->assertInstanceOf(Document::class, $doc);
        $this->assertEquals('Holon', $doc->getTitle());
        $this->assertCount(5, $doc);
    }

    public function testSave() {
        $doc = $this->sut->create();
        $doc->setTitle('Movies');
        $doc[] = new Sentence('Solid State Society');

        $this->fs->expects($this->once())
                ->method('dumpFile');

        $this->sut->save($doc);
    }

}
