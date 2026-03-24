<?php

namespace App\Form\Search;

use App\Search\SecurityGroupListSearch;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class SecurityGroupListSearchForm extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder->add('groupId', TextType::class, [
            'required' => false,
            'label' => 'Group ID',
            'attr' => ['placeholder' => 'Enter security group ID'],
        ])
        ->add('groupName', TextType::class, [
            'required' => false,
            'label' => 'Group Name',
            'attr' => ['placeholder' => 'Enter security group name'],
        ])
        ->add('vpcId', TextType::class, [
            'required' => false,
            'label' => 'VPC ID',
            'attr' => ['placeholder' => 'Enter VPC ID'],
        ]);
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => SecurityGroupListSearch::class,
            'method' => 'GET',
            'csrf_protection' => false,
        ]);
    }

    public function getBlockPrefix()
    {
        return '';
    }
}
