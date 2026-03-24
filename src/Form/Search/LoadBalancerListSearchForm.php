<?php

namespace App\Form\Search;

use App\Search\LoadBalancerListSearch;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class LoadBalancerListSearchForm extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder->add('loadBalancerName', TextType::class, [
            'required' => false,
            'label' => 'Name',
            'attr' => ['placeholder' => 'Enter load balancer name'],
        ])
        ->add('type', ChoiceType::class, [
            'required' => false,
            'label' => 'Type',
            'placeholder' => 'All types',
            'choices' => [
                'Application' => 'application',
                'Network' => 'network',
                'Gateway' => 'gateway',
            ],
        ])
        ->add('scheme', ChoiceType::class, [
            'required' => false,
            'label' => 'Scheme',
            'placeholder' => 'All schemes',
            'choices' => [
                'Internet-facing' => 'internet-facing',
                'Internal' => 'internal',
            ],
        ]);
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => LoadBalancerListSearch::class,
            'method' => 'GET',
            'csrf_protection' => false,
        ]);
    }

    public function getBlockPrefix()
    {
        return '';
    }
}
