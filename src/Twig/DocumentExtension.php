<?php

namespace App\Twig;

use App\Entity\GraphSpeech\Document;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
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

    public function decorateWiki(string $content, string $pkDoc): string {
        return preg_replace_callback(Document::linkRegex, function($match) use ($pkDoc) {
            $url = $this->router->generate('app_documentcrud_show', ['title' => $pkDoc, 'key' => $match[1], '_fragment' => $pkDoc . '-' . $match[1]]);
            return "<a href=\"$url\">{$match[1]}</a>";
        }, $content);
    }

}
