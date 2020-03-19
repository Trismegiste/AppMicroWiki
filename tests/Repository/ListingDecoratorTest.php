<?php

use App\Repository\ListingDecorator;
use PHPUnit\Framework\TestCase;

class ListingDecoratorTest extends TestCase {

    protected $sut;

    protected function setUp(): void {
        $file = $this->createMock(\Symfony\Component\Finder\SplFileInfo::class);
        $file->expects($this->atLeastOnce())
                ->method('getContents')
                ->willReturn('{"title":"2001","description":"Best sci-fi movie"}');

        $this->sut = new ListingDecorator(new ArrayIterator([$file]));
    }

    protected function tearDown(): void {
        unset($this->sut);
    }

    public function testIterable() {
        foreach ($this->sut as $key => $file) {
            $this->assertEquals('2001', $file->title);
        }
    }

}
