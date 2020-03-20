<?php

use App\Form\SentenceType;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;
use Symfony\Component\Form\Form;
use Trismegiste\MicroWiki\Document;
use Trismegiste\MicroWiki\Sentence;

/** Functionnal tests */
class SentenceTypeTest extends KernelTestCase {

    protected $document;

    static public function setUpBeforeClass(): void {
        static::bootKernel();
    }

    protected function setUp(): void {
        $this->document = new Document();
    }

    protected function createForm($obj = null): Form {
        return self::$kernel->getContainer()->get('form.factory')->create(SentenceType::class, $obj, [
                    'document' => $this->document,
                    'csrf_protection' => false
        ]);
    }

    protected function tearDown(): void {
        unset($this->document);
    }

    public function testEmptyData() {
        $doc = $this->document;
        $form = $this->createForm();
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
        $doc = $this->document;
        $stc = new Sentence("HAL9000");
        $doc[] = $stc;
        $form = $this->createForm($stc);

        $formData = [
            'key' => 'IBM8000',
            'category' => 'chess master',
            'content' => "Murderer of [[Frank Poole]]"
        ];
        $form->submit($formData);
        $this->assertTrue($form->isSynchronized());
        $this->assertTrue($form->isValid());

        $this->assertEquals('IBM8000', $stc->getKey());
        $this->assertEquals('chess master', $stc->getCategory());
        $this->assertArrayHasKey('IBM8000', $doc);
        $this->assertEquals($stc, $doc['IBM8000']);
    }

    public function getBadNames(): array {
        return [
            ['HA[9000', 'bracket'],
            ['HA]9000', 'bracket'],
            ['[HA9000]', 'bracket'],
            ['HA', 'too short'],
        ];
    }

    /** @dataProvider getBadNames */
    public function testBadData($badname, $msg) {
        $sut = $this->createForm();
        $formData = [
            'key' => $badname,
            'category' => "evil computer",
            'content' => "Computer from the [[Discovery One]]"
        ];
        $sut->submit($formData);
        $this->assertFalse($sut->isValid());
        $errors = $sut->get('key')->getErrors();
        $this->assertCount(1, $errors);
        $this->assertStringContainsStringIgnoringCase($msg, $errors[0]->getMessage());
    }

}
