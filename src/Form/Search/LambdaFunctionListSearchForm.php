<?php

namespace App\Form\Search;

use App\Enum\AWS\Lambda\LambdaRuntime;
use App\Enum\AWS\Lambda\LambdaState;
use App\Enum\AWS\Lambda\PackageType;
use App\Search\LambdaFunctionListSearch;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\EnumType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class LambdaFunctionListSearchForm extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder->add('functionName', TextType::class, [
            'required' => false,
            'label' => 'Function Name',
            'attr' => ['placeholder' => 'Enter function name'],
        ])
        ->add('functionArn', TextType::class, [
            'required' => false,
            'label' => 'Function ARN',
            'attr' => ['placeholder' => 'Enter function ARN'],
        ])
        ->add('runtime', EnumType::class, [
            'class' => LambdaRuntime::class,
            'required' => false,
            'label' => 'Runtime',
            'placeholder' => 'All runtimes',
        ])
        ->add('state', EnumType::class, [
            'class' => LambdaState::class,
            'required' => false,
            'label' => 'State',
            'placeholder' => 'All states',
        ])
        ->add('packageType', EnumType::class, [
            'class' => PackageType::class,
            'required' => false,
            'label' => 'Package Type',
            'placeholder' => 'All package types',
        ]);
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => LambdaFunctionListSearch::class,
            'method' => 'GET',
            'csrf_protection' => false,
        ]);
    }

    public function getBlockPrefix()
    {
        return '';
    }
}