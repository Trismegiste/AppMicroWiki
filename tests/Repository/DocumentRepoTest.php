<?php

use App\Repository\DocumentRepo;
use MongoDB\BSON\ObjectIdInterface;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;
use Trismegiste\MicroWiki\Document;

class DocumentRepoTest extends KernelTestCase {

    protected $sut;

    static public function setUpBeforeClass(): void {
        static::bootKernel();
    }

    protected function setUp(): void {
        $this->sut = self::$container->get(DocumentRepo::class);
    }

    protected function tearDown(): void {
        unset($this->sut);
    }

    public function testSave() {
        $doc = new Document('title', 'descr');
        $this->sut->save($doc);
        $this->assertInstanceOf(ObjectIdInterface::class, $doc->getPk());
        $this->assertRegExp('/^[0-9a-f]{24}$/', $doc->getPk());

        return (string) $doc->getPk();
    }

    /** @depends testSave */
    public function testLoad(string $pk) {
        $doc = $this->sut->load($pk);
        $this->assertEquals('title', $doc->getTitle());
        $this->assertEquals('descr', $doc->getDescription());
    }

}
