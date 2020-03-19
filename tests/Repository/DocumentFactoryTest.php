<?php

use App\Repository\DocumentFactory;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;

class DocumentFactoryTest extends TestCase {

    protected $sut;

    protected function setUp(): void {
        $url = $this->createStub(UrlGeneratorInterface::class);
        $this->sut = new DocumentFactory($fs);
    }

    protected function tearDown(): void {
        unset($this->sut);
    }

}
