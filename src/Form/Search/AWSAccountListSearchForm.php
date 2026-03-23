<?php

namespace App\Form\Search;

use App\Search\AWSAccountListSearch;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class AWSAccountListSearchForm extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder->add('accountName', TextType::class, [
            'required' => false,
            'label' => 'Account Name (Start, contains or exact match)',
            'attr' => ['placeholder' => 'Enter account name'],
        ])
            ->add('email', TextType::class, [
                'required' => false,
                'label' => 'Email (Start, contains or exact match)',
                'attr' => ['placeholder' => 'Enter email address'],
            ])
            ->add('status', ChoiceType::class, [
                'choices' => [
                    'Tous' => null,
                    'Actif' => 'ACTIVE',
                    'Suspendu' => 'SUSPENDED',
                    'En attente de fermeture' => 'PENDING_CLOSURE',
                ],
            ])
            ->add('associationMethod', ChoiceType::class, [
                'choices' => [
                    'Tous' => null,
                    'Rattaché à l\'organisation' => 'INVITED',
                    'Créé dans l\'organisation' => 'CREATED',
                ],
            ])
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => AWSAccountListSearch::class,
            'method' => 'GET',
            'csrf_protection' => false,
        ]);
    }

    public function getBlockPrefix()
    {
        return '';
    }
}