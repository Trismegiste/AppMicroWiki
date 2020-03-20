<?php

namespace App\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\DataMapperInterface;
use Symfony\Component\Form\Exception\UnexpectedTypeException;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints\Length;
use Symfony\Component\Validator\Constraints\Regex;
use Trismegiste\MicroWiki\Document;
use Trismegiste\MicroWiki\Sentence;

/**
 * Description of SentenceType
 */
class SentenceType extends AbstractType implements DataMapperInterface {

    protected $parentDocument;

    public function buildForm(FormBuilderInterface $builder, array $options) {
        $this->parentDocument = $options['document'];

        $builder
                ->add('key', TextType::class, [
                    'label' => 'Name',
                    'constraints' => [
                        new Length(['min' => 3]),
                        new Regex(['pattern' => '/[\]\[]/', 'match' => false, 'message' => 'Square brackets are forbidden'])
                    ]
                ])
                ->add('category', TextType::class)
                ->add('content', TextareaType::class, ['attr' => ['rows' => 10]])
                ->add('save', SubmitType::class)
                ->setDataMapper($this);
    }

    public function configureOptions(OptionsResolver $resolver) {
        $resolver->setRequired(['document']);
        $resolver->setDefaults([
            'document' => null,
            'data_class' => Sentence::class,
            'empty_data' => function(FormInterface $form) {
                $obj = new Sentence($form->get('key')->getData());
                return $obj;
            }
        ]);
        $resolver->setAllowedTypes('document', Document::class);
    }

    public function mapDataToForms($viewData, $forms) {
        // there is no data yet, so nothing to prepopulate
        if (null === $viewData) {
            return;
        }

        // invalid data type
        if (!$viewData instanceof Sentence) {
            throw new UnexpectedTypeException($viewData, Sentence::class);
        }

        /** @var FormInterface[] $forms */
        $forms = iterator_to_array($forms);

        // initialize form field values
        $forms['key']->setData($viewData->getKey());
        $forms['category']->setData($viewData->getCategory());
        $forms['content']->setData($viewData->getContent());
    }

    public function mapFormsToData($forms, &$viewData) {
        // invalid data type
        if (!$viewData instanceof Sentence) {
            throw new UnexpectedTypeException($viewData, Sentence::class);
        }
        /** @var FormInterface[] $forms */
        $forms = iterator_to_array($forms);
        $viewData->setCategory($forms['category']->getData());
        $viewData->setContent($forms['content']->getData());

        if (!$this->parentDocument->offsetExists($viewData->getKey())) {
            $this->parentDocument[] = $viewData;
        }

        if ($viewData->getKey() !== $forms['key']->getData()) {
            $viewData->renameKey($this->parentDocument, $forms['key']->getData());
        }
    }

}
