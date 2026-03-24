<?php

namespace App\Form\Search;

use App\Search\CloudWatchAlarmListSearch;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class CloudWatchAlarmListSearchForm extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder->add('alarmName', TextType::class, [
            'required' => false,
            'label' => 'Alarm Name',
            'attr' => ['placeholder' => 'Enter alarm name'],
        ])
        ->add('stateValue', ChoiceType::class, [
            'required' => false,
            'label' => 'State',
            'placeholder' => 'All',
            'choices' => [
                'OK' => 'OK',
                'ALARM' => 'ALARM',
                'INSUFFICIENT_DATA' => 'INSUFFICIENT_DATA',
            ],
        ])
        ->add('metricName', TextType::class, [
            'required' => false,
            'label' => 'Metric Name',
            'attr' => ['placeholder' => 'Enter metric name'],
        ])
        ->add('namespace', TextType::class, [
            'required' => false,
            'label' => 'Namespace',
            'attr' => ['placeholder' => 'e.g. AWS/EC2'],
        ])
        ->add('actionsEnabled', ChoiceType::class, [
            'required' => false,
            'label' => 'Actions Enabled',
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
            'data_class' => CloudWatchAlarmListSearch::class,
            'method' => 'GET',
            'csrf_protection' => false,
        ]);
    }

    public function getBlockPrefix()
    {
        return '';
    }
}
