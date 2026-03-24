<?php

namespace App\Form\Search;

use App\Search\IamUserListSearch;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class IamUserListSearchForm extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder->add('userName', TextType::class, [
            'required' => false,
            'label' => 'User Name',
            'attr' => ['placeholder' => 'Enter user name'],
        ])
        ->add('path', TextType::class, [
            'required' => false,
            'label' => 'Path',
            'attr' => ['placeholder' => 'e.g. /'],
        ])
        ->add('arn', TextType::class, [
            'required' => false,
            'label' => 'ARN',
            'attr' => ['placeholder' => 'Enter ARN'],
        ]);
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => IamUserListSearch::class,
            'method' => 'GET',
            'csrf_protection' => false,
        ]);
    }

    public function getBlockPrefix()
    {
        return '';
    }
}
