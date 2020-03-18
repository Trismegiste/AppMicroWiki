<?php

namespace App\Twig;

use App\Entity\GraphSpeech\Document;
use Twig\Extension\AbstractExtension;
use Twig\TwigFilter;

/**
 * Description of DocumentExtension
 */
class DocumentExtension extends AbstractExtension {

    public function getFilters() {
        return [
            new TwigFilter('wiki', [$this, 'decorateWiki'], ['is_safe' => ['html'], 'pre_escape' => 'html']),
        ];
    }

    public function decorateWiki(string $content, string $pkDoc): string {
        return preg_replace_callback(Document::linkRegex, function($match) use ($pkDoc) {
            return "<a href=\"/docu/append/$pkDoc/{$match[1]}\">{$match[1]}</a>";
        }, $content);
    }

}
