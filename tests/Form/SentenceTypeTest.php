<?php

use App\Form\SentenceType;
use Symfony\Component\Form\Test\TypeTestCase;
use Trismegiste\MicroWiki\Document;
use Trismegiste\MicroWiki\Sentence;

class SentenceTypeTest extends TypeTestCase {

    public function testEmptyData() {
        $doc = new Document();
        $form = $this->factory->create(SentenceType::class, null, ['document' => $doc]);
        $formData = [
            'key' => 'HAL9000',
            'category' => "evil computer",
            'content' => "Computer from the [[Discovery One]]"
        ];
        $form->submit($formData);
        $this->assertTrue($form->isSynchronized());
        $stc = $form->getData();
        $this->assertInstanceOf(Sentence::class, $stc);
        $this->assertEquals('HAL9000', $stc->getKey());
        $this->assertEquals('evil computer', $stc->getCategory());
        $this->assertArrayHasKey('HAL9000', $doc);
    }

    public function testEdit() {
        $doc = new Document();
        $stc = new Sentence("HAL9000");
        $form = $this->factory->create(SentenceType::class, $stc, ['document' => $doc]);

        $formData = [
            'key' => 'IBM8000',
            'category' => 'chess master',
            'content' => "Murderer of [[Frank Poole]]"
        ];
        $form->submit($formData);
        $this->assertTrue($form->isSynchronized());

        $this->assertEquals('IBM8000', $stc->getKey());
        $this->assertEquals('chess master', $stc->getCategory());
        $this->assertArrayHasKey('IBM8000', $doc);
    }

}
