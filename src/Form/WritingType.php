<?php

namespace App\Form;

use App\Entity\Writing;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\BirthdayType;
use Symfony\Component\Form\Extension\Core\Type\DateTimeType;
use Symfony\Component\Form\Extension\Core\Type\DateType;
use Symfony\Component\Form\Extension\Core\Type\HiddenType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class WritingType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('id', HiddenType::class)
            ->add('title', TextType::class, [
                'label' => false,
            ])
            ->add('content', TextareaType::class, [
                'label' => false,
                'attr' => [
                    'class' => 'tinymce']
            ]);
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
        ]);
    }
}
