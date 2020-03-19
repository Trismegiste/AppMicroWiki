<?php

use App\Form\SentenceType;
use Symfony\Component\Form\Test\TypeTestCase;
use Trismegiste\MicroWiki\Sentence;

class SentenceTypeTest extends TypeTestCase {

    public function testEmptyData() {
        $form = $this->factory->create(SentenceType::class);
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
    }

    public function testEdit() {
        $stc = new Sentence("HAL9000");
        $form = $this->factory->create(SentenceType::class, $stc);

        $formData = [
            'category' => 'chess master',
            'content' => "Murderer of [[Frank Poole]]"
        ];
        $form->submit($formData);
        $this->assertTrue($form->isSynchronized());

        $this->assertEquals('chess master', $stc->getCategory());
    }

}
