<?php

namespace App\Twig;

use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Trismegiste\MicroWiki\Document;
use Trismegiste\MicroWiki\Sentence;
use Twig\Extension\AbstractExtension;
use Twig\TwigFilter;

/**
 * Description of DocumentExtension
 */
class DocumentExtension extends AbstractExtension {

    private $router;

    public function __construct(UrlGeneratorInterface $router) {
        $this->router = $router;
    }

    public function getFilters() {
        return [
            new TwigFilter('wiki', [$this, 'decorateWiki'], ['is_safe' => ['html'], 'pre_escape' => 'html']),
        ];
    }

    public function decorateWiki(string $content, Document $doc): string {
        $processed = preg_replace_callback(Sentence::linkRegex, function($match) use ($doc) {
            $pkDoc = $doc->getTitle();
            $pkStc = $match[1];

            if ($doc->offsetExists($pkStc)) {
                $url = $this->router->generate('app_documentcrud_show', ['title' => $pkDoc, 'key' => $pkStc]);
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
