<?php

namespace App\Twig;

use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Symfony\Component\Security\Csrf\CsrfTokenManagerInterface;
use Trismegiste\MicroWiki\Document;
use Trismegiste\MicroWiki\Sentence;
use Twig\Extension\AbstractExtension;
use Twig\TwigFilter;
use Twig\TwigFunction;

/**
 * Description of DocumentExtension
 */
class DocumentExtension extends AbstractExtension
{

    const csrf = 'pin-vertex';
    const csrf_doc_delete = 'doc-delete';

    private $router;
    private $csrfTokenManager;

    public function __construct(UrlGeneratorInterface $router, CsrfTokenManagerInterface $csrfTokenManager)
    {
        $this->router = $router;
        $this->csrfTokenManager = $csrfTokenManager;
    }

    public function getFilters()
    {
        return [
            new TwigFilter('wiki', [$this, 'decorateWiki'], ['is_safe' => ['html'], 'pre_escape' => 'html']),
            new TwigFilter('innerlink', [$this, 'innerLinkPdf'], ['is_safe' => ['html'], 'pre_escape' => 'html']),
        ];
    }

    public function getFunctions()
    {
        return [
            new TwigFunction('pinned', [$this, 'getPinnedLink']),
            new TwigFunction('path_delete_doc', [$this, 'getDeleteDocLink'])
        ];
    }

    public function innerLinkPdf(string $content, Document $doc): string
    {
        $processed = preg_replace_callback(Sentence::linkRegex, function($match) use ($doc) {
            $pkStc = html_entity_decode($match[1], ENT_HTML5 | ENT_QUOTES);

            return ($doc->offsetExists($pkStc)) ? "<a href=\"#$pkStc\">$pkStc</a>" : $pkStc;
        }, $content);

        $processed = str_replace("\n", '<br/>', $processed);

        return $processed;
    }

    public function decorateWiki(string $content, Document $doc): string
    {
        $processed = preg_replace_callback(Sentence::linkRegex, function($match) use ($doc) {
            $pkDoc = $doc->getPk();
            $pkStc = html_entity_decode($match[1], ENT_HTML5 | ENT_QUOTES);

            if ($doc->offsetExists($pkStc)) {
                $url = $this->getLink($pkDoc, $pkStc);
                $css = 'wiki-link';
            } else {
                $url = $this->router->generate('app_sentencecrud_append', ['pk' => $pkDoc, 'key' => $pkStc]);
                $css = 'wiki-missing';
            }

            return "<a href=\"$url\" class=\"$css\">$pkStc</a>";
        }, $content);

        $processed = str_replace("\n", '<br/>', $processed);

        return $processed;
    }

    protected function getLink(string $pkDoc, string $pkStc): string
    {
        return $this->router->generate('app_sentencecrud_pinvertex', [
                'pk' => $pkDoc,
                'key' => $pkStc,
                'token' => $this->csrfTokenManager->getToken(self::csrf)->getValue()
        ]);
    }

    public function getPinnedLink(Document $doc, Sentence $stc): string
    {
        return $this->getLink($doc->getPk(), $stc->getKey());
    }

    public function getDeleteDocLink(string $pkDoc)
    {
        return $this->router->generate('app_documentcrud_deleteconfirm', [
                'pk' => $pkDoc,
                'token' => $this->csrfTokenManager->getToken(self::csrf_doc_delete)->getValue()
        ]);
    }

}
