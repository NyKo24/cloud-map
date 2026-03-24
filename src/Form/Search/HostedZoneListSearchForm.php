<?php

namespace App\Form\Search;

use App\Search\HostedZoneListSearch;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class HostedZoneListSearchForm extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder->add('name', TextType::class, [
            'required' => false,
            'label' => 'Zone Name',
            'attr' => ['placeholder' => 'Enter zone name'],
        ])
        ->add('hostedZoneId', TextType::class, [
            'required' => false,
            'label' => 'Hosted Zone ID',
            'attr' => ['placeholder' => 'Enter hosted zone ID'],
        ])
        ->add('privateZone', ChoiceType::class, [
            'required' => false,
            'label' => 'Type',
            'placeholder' => 'All',
            'choices' => [
                'Private' => true,
                'Public' => false,
            ],
        ]);
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => HostedZoneListSearch::class,
            'method' => 'GET',
            'csrf_protection' => false,
        ]);
    }

    public function getBlockPrefix()
    {
        return '';
    }
}
