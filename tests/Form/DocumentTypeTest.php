<?php

use App\Form\DocumentType;
use Symfony\Component\Form\Test\TypeTestCase;
use Trismegiste\MicroWiki\Document;

class DocumentTypeTest extends TypeTestCase {

    protected $sut;

    protected function setUp(): void {
        parent::setUp();
        $this->sut = new DocumentType();
    }

    protected function tearDown(): void {
        unset($this->sut);
        parent::tearDown();
    }

    public function testEmptyData() {
        $form = $this->factory->create(DocumentType::class);
        $formData = [
            'title' => '2001',
            'description' => "Best scifi flick evar"
        ];
        $form->submit($formData);
        $this->assertTrue($form->isSynchronized());
        $doc = $form->getData();
        $this->assertInstanceOf(Document::class, $doc);
        $this->assertEquals('2001', $doc->getTitle());
    }

    public function testEdit() {
        $doc = new Document("2001", "Best scifi flick evar");
        $form = $this->factory->create(DocumentType::class, $doc);

        $formData = [
            'title' => '2010',
            'description' => "Not so good"
        ];
        $form->submit($formData);
        $this->assertTrue($form->isSynchronized());

        $newDoc = $form->getData();
        $this->assertInstanceOf(Document::class, $newDoc);
        $this->assertEquals('2010', $doc->getTitle());
    }

}
