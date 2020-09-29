<?php

use App\Form\SentenceMapper;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Validator\Exception\UnexpectedTypeException;
use Trismegiste\MicroWiki\Document;

/*
 * AppMicroWiki
 */

/**
 * Description of SentenceMapperTest
 */
class SentenceMapperTest extends TestCase
{

    protected $sut;

    protected function setUp(): void
    {
        $this->sut = new SentenceMapper(new Document);
    }

    public function testEmptyDataToForm()
    {
        $arr = [];
        $this->sut->mapDataToForms(null, $arr);
        $this->assertCount(0, $arr);
    }

    public function testBadDataToForm()
    {
        $this->expectException(UnexpectedTypeException::class);
        $this->sut->mapDataToForms(new stdClass(), []);
    }

    public function testBadDataForForm()
    {
        $this->expectException(UnexpectedTypeException::class);
        $dummy = [];
        $this->sut->mapFormsToData(new stdClass(), $dummy);
    }

}
