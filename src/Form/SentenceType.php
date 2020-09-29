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
class SentenceType extends AbstractType
{

    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('key', TextType::class, [
                'label' => 'Name',
                'constraints' => [
                    new Length(['min' => 3]),
                    new Regex(['pattern' => '/[\]\[]/', 'match' => false, 'message' => 'Square brackets are forbidden'])
                ]
            ])
            ->add('category', TextareaType::class, ['attr' => ['rows' => 1, 'style' => 'resize: none; height:2.27em;']])
            ->add('content', TextareaType::class, ['attr' => ['rows' => 10, 'style' => 'resize: vertical']])
            ->add('link', TextType::class, ['required' => false, 'empty_data' => ''])
            ->add('save', SubmitType::class)
            ->setDataMapper(new SentenceMapper($options['document']));
    }

    public function configureOptions(OptionsResolver $resolver)
    {
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

}
