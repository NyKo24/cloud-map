<?php

namespace App\Form\Search;

use App\Search\Ec2InstanceListSearch;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class Ec2InstanceListSearchForm extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder->add('instanceId', TextType::class, [
            'required' => false,
            'label' => 'Instance ID',
            'attr' => ['placeholder' => 'Enter instance ID'],
        ])
        ->add('instanceType', TextType::class, [
            'required' => false,
            'label' => 'Instance Type',
            'attr' => ['placeholder' => 'e.g. t3.micro'],
        ])
        ->add('state', ChoiceType::class, [
            'required' => false,
            'label' => 'State',
            'placeholder' => 'All states',
            'choices' => [
                'Running' => 'running',
                'Stopped' => 'stopped',
                'Pending' => 'pending',
                'Shutting down' => 'shutting-down',
                'Terminated' => 'terminated',
                'Stopping' => 'stopping',
            ],
        ])
        ->add('vpcId', TextType::class, [
            'required' => false,
            'label' => 'VPC ID',
            'attr' => ['placeholder' => 'Enter VPC ID'],
        ])
        ->add('architecture', ChoiceType::class, [
            'required' => false,
            'label' => 'Architecture',
            'placeholder' => 'All architectures',
            'choices' => [
                'x86_64' => 'x86_64',
                'arm64' => 'arm64',
                'i386' => 'i386',
            ],
        ]);
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Ec2InstanceListSearch::class,
            'method' => 'GET',
            'csrf_protection' => false,
        ]);
    }

    public function getBlockPrefix()
    {
        return '';
    }
}
