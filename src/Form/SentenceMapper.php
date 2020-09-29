<?php

/*
 * AppMicroWiki
 */

namespace App\Form;

use Symfony\Component\Form\Extension\Core\DataMapper\PropertyPathMapper;
use Symfony\Component\Validator\Exception\UnexpectedTypeException;
use Trismegiste\MicroWiki\Document;
use Trismegiste\MicroWiki\Sentence;

/**
 * Mapper for Sentence
 */
class SentenceMapper extends PropertyPathMapper
{

    protected $parentDocument;

    public function __construct(Document $doc)
    {
        parent::__construct();
        $this->parentDocument = $doc;
    }

    public function mapDataToForms($viewData, $forms)
    {
        // there is no data yet, so nothing to prepopulate
        if (null === $viewData) {
            return;
        }

        // invalid data type
        if (!$viewData instanceof Sentence) {
            throw new UnexpectedTypeException($viewData, Sentence::class);
        }

        parent::mapDataToForms($viewData, $forms);
    }

    public function mapFormsToData($forms, &$viewData)
    {
        // invalid data type
        if (!$viewData instanceof Sentence) {
            throw new UnexpectedTypeException($viewData, Sentence::class);
        }

        // Mamange the key with the parent Document
        if (!$this->parentDocument->offsetExists($viewData->getKey())) {
            $this->parentDocument[] = $viewData;
        }

        $tmp = iterator_to_array($forms);
        $keyField = $tmp['key'];
        if ($viewData->getKey() !== $keyField->getData()) {
            $viewData->renameKey($this->parentDocument, $keyField->getData());
        }

        // unset the key and map other fields as standard procedure :
        unset($tmp['key']);
        parent::mapFormsToData($tmp, $viewData);
    }

}
