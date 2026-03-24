<?php

namespace App\Form\Search;

use App\Search\EcsClusterListSearch;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class EcsClusterListSearchForm extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder->add('clusterName', TextType::class, [
            'required' => false,
            'label' => 'Cluster Name',
            'attr' => ['placeholder' => 'Enter cluster name'],
        ])
        ->add('status', ChoiceType::class, [
            'required' => false,
            'label' => 'Status',
            'placeholder' => 'All',
            'choices' => [
                'ACTIVE' => 'ACTIVE',
                'PROVISIONING' => 'PROVISIONING',
                'DEPROVISIONING' => 'DEPROVISIONING',
                'FAILED' => 'FAILED',
                'INACTIVE' => 'INACTIVE',
            ],
        ]);
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => EcsClusterListSearch::class,
            'method' => 'GET',
            'csrf_protection' => false,
        ]);
    }

    public function getBlockPrefix()
    {
        return '';
    }
}
