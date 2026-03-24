<?php

namespace App\Form\Search;

use App\Search\IamRoleListSearch;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class IamRoleListSearchForm extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder->add('roleName', TextType::class, [
            'required' => false,
            'label' => 'Role Name',
            'attr' => ['placeholder' => 'Enter role name'],
        ])
        ->add('path', TextType::class, [
            'required' => false,
            'label' => 'Path',
            'attr' => ['placeholder' => 'e.g. /service-role/'],
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
            'data_class' => IamRoleListSearch::class,
            'method' => 'GET',
            'csrf_protection' => false,
        ]);
    }

    public function getBlockPrefix()
    {
        return '';
    }
}
