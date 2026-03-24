<?php

namespace App\Form\Search;

use App\Search\S3BucketListSearch;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class S3BucketListSearchForm extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder->add('name', TextType::class, [
            'required' => false,
            'label' => 'Bucket Name',
            'attr' => ['placeholder' => 'Enter bucket name'],
        ])
        ->add('region', TextType::class, [
            'required' => false,
            'label' => 'Region',
            'attr' => ['placeholder' => 'e.g. eu-west-1'],
        ])
        ->add('versioningStatus', ChoiceType::class, [
            'required' => false,
            'label' => 'Versioning',
            'placeholder' => 'All',
            'choices' => [
                'Enabled' => 'Enabled',
                'Suspended' => 'Suspended',
            ],
        ])
        ->add('encryptionEnabled', ChoiceType::class, [
            'required' => false,
            'label' => 'Encryption',
            'placeholder' => 'All',
            'choices' => [
                'Yes' => true,
                'No' => false,
            ],
        ]);
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => S3BucketListSearch::class,
            'method' => 'GET',
            'csrf_protection' => false,
        ]);
    }

    public function getBlockPrefix()
    {
        return '';
    }
}
