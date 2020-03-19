<?php

namespace App\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Trismegiste\MicroWiki\Sentence;

/**
 * Description of SentenceType
 */
class SentenceType extends AbstractType {

    public function buildForm(FormBuilderInterface $builder, array $options) {
        $builder
                ->add('key', TextType::class, ['label' => 'Name'])
                ->add('category', TextType::class)
                ->add('content', TextareaType::class)
                ->add('save', SubmitType::class);
    }

    public function configureOptions(OptionsResolver $resolver) {
        $resolver->setDefaults([
            'data_class' => Sentence::class,
            'empty_data' => function(FormInterface $form) {
                $obj = new Sentence($form->get('key')->getData());
                return $obj;
            }
        ]);
    }

}
