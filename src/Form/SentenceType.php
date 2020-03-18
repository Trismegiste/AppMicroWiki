<?php

namespace App\Form;

use App\Entity\GraphSpeech\Document;
use App\Entity\GraphSpeech\Sentence;
use App\Repository\DocumentFactory;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\HiddenType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

/**
 * Description of SentenceType
 */
class SentenceType extends AbstractType {

    protected $repository;

    public function __construct(DocumentFactory $repo) {
        $this->repository = $repo;
    }

    public function buildForm(FormBuilderInterface $builder, array $options) {
        $builder
                ->add('key', TextType::class, ['label' => 'Name', 'data' => $options['new_key']])
                ->add('category', TextType::class)
                ->add('content', TextareaType::class)
                ->add('document', HiddenType::class, ['mapped' => false, 'data' => $options['document']->getTitle()])
                ->add('save', SubmitType::class);
    }

    public function configureOptions(OptionsResolver $resolver) {
        $resolver->setRequired(['document']);
        $resolver->setDefaults([
            'document' => null,
            'new_key' => '',
            'data_class' => Sentence::class,
            'empty_data' => function(FormInterface $form) {
                $doc = $this->repository->load($form->get('document')->getData());
                $obj = new Sentence($doc, $form->get('key')->getData());
                return $obj;
            }
        ]);
        $resolver->setAllowedTypes('document', Document::class);
        $resolver->setAllowedTypes('new_key', 'string');
    }

}