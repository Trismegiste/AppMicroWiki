<?php

use App\Twig\DocumentExtension;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Symfony\Component\Security\Csrf\CsrfToken;
use Symfony\Component\Security\Csrf\CsrfTokenManagerInterface;
use Trismegiste\MicroWiki\Document;
use Trismegiste\MicroWiki\Sentence;

class DocumentExtensionTest extends TestCase {

    protected $sut;

    protected function setUp(): void {
        $url = $this->createMock(UrlGeneratorInterface::class);
        $url->expects($this->any())
                ->method('generate')
                ->willReturn('#');
        $token = $this->createStub(CsrfToken::class);
        $csrf = $this->createMock(CsrfTokenManagerInterface::class);
        $csrf->expects($this->any())
                ->method('getToken')
                ->willReturn($token);

        $this->sut = new DocumentExtension($url, $csrf);
    }

    protected function tearDown(): void {
        unset($this->sut);
    }

    public function fixtures() {
        $doc = new Document();
        $doc->setTitle('graph');
        $doc[] = new Sentence('existing');

        return [[$doc]];
    }

    /** @dataProvider fixtures */
    public function testDecoration(Document $doc) {
        $result = $this->sut->decorateWiki('This string a link to [[existing]] and [[missing]]', $doc);
        // notice that href is empy because the router is a stub :
        $this->assertEquals('This string a link to <a href="#" class="wiki-link">existing</a> and <a href="#" class="wiki-missing">missing</a>', $result);
    }

    public function testFilter() {
        $this->assertCount(1, $this->sut->getFilters());
    }

    public function testFunction() {
        $this->assertCount(1, $this->sut->getFunctions());
    }

}
