<?php

namespace App\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\HiddenType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\FormBuilderInterface;

/**
 * Description of SentenceDeleteType
 */
class SentenceDeleteType extends AbstractType {

    public function buildForm(FormBuilderInterface $builder, array $options) {
        $builder
                ->setMethod('DELETE')
                ->add('key', HiddenType::class)
                ->add('delete', SubmitType::class, ['attr' => ['class' => 'warning']]);
    }

}
