<?php

namespace App\Twig;

use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Symfony\Component\Security\Csrf\CsrfTokenManagerInterface;
use Trismegiste\MicroWiki\Document;
use Trismegiste\MicroWiki\Sentence;
use Twig\Extension\AbstractExtension;
use Twig\TwigFilter;

/**
 * Description of DocumentExtension
 */
class DocumentExtension extends AbstractExtension {

    private $router;
    private $csrfTokenManager;

    public function __construct(UrlGeneratorInterface $router, CsrfTokenManagerInterface $csrfTokenManager) {
        $this->router = $router;
        $this->csrfTokenManager = $csrfTokenManager;
    }

    public function getFilters() {
        return [
            new TwigFilter('wiki', [$this, 'decorateWiki'], ['is_safe' => ['html'], 'pre_escape' => 'html']),
        ];
    }

    public function decorateWiki(string $content, Document $doc): string {
        $processed = preg_replace_callback(Sentence::linkRegex, function($match) use ($doc) {
            $pkDoc = $doc->getTitle();
            $pkStc = html_entity_decode($match[1], ENT_HTML5 | ENT_QUOTES);

            if ($doc->offsetExists($pkStc)) {
                $url = $this->router->generate('app_sentencecrud_pinvertex', [
                    'title' => $pkDoc,
                    'key' => $pkStc,
                    'token' => $this->csrfTokenManager->getToken('pin-vertex')->getValue()
                ]);
                $css = 'wiki-link';
            } else {
                $url = $this->router->generate('app_sentencecrud_append', ['title' => $pkDoc, 'key' => $pkStc]);
                $css = 'wiki-missing';
            }

            return "<a href=\"$url\" class=\"$css\">$pkStc</a>";
        }, $content);

        $processed = str_replace("\n", '<br/>', $processed);

        return $processed;
    }

}
