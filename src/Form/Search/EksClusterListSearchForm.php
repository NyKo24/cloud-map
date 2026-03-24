<?php

namespace App\Form\Search;

use App\Search\EksClusterListSearch;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class EksClusterListSearchForm extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder->add('name', TextType::class, [
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
                'CREATING' => 'CREATING',
                'DELETING' => 'DELETING',
                'FAILED' => 'FAILED',
                'UPDATING' => 'UPDATING',
                'PENDING' => 'PENDING',
            ],
        ])
        ->add('version', TextType::class, [
            'required' => false,
            'label' => 'Version',
            'attr' => ['placeholder' => 'e.g. 1.28'],
        ]);
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => EksClusterListSearch::class,
            'method' => 'GET',
            'csrf_protection' => false,
        ]);
    }

    public function getBlockPrefix()
    {
        return '';
    }
}
