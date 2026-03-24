<?php

namespace App\Form\Search;

use App\Search\RdsInstanceListSearch;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class RdsInstanceListSearchForm extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder->add('dbInstanceIdentifier', TextType::class, [
            'required' => false,
            'label' => 'DB Instance ID',
            'attr' => ['placeholder' => 'Enter DB instance identifier'],
        ])
        ->add('engine', ChoiceType::class, [
            'required' => false,
            'label' => 'Engine',
            'placeholder' => 'All engines',
            'choices' => [
                'MySQL' => 'mysql',
                'PostgreSQL' => 'postgres',
                'MariaDB' => 'mariadb',
                'Oracle' => 'oracle-ee',
                'SQL Server' => 'sqlserver-ee',
                'Aurora MySQL' => 'aurora-mysql',
                'Aurora PostgreSQL' => 'aurora-postgresql',
            ],
        ])
        ->add('dbInstanceStatus', ChoiceType::class, [
            'required' => false,
            'label' => 'Status',
            'placeholder' => 'All statuses',
            'choices' => [
                'Available' => 'available',
                'Creating' => 'creating',
                'Deleting' => 'deleting',
                'Modifying' => 'modifying',
                'Stopped' => 'stopped',
                'Starting' => 'starting',
                'Stopping' => 'stopping',
                'Rebooting' => 'rebooting',
                'Failed' => 'failed',
            ],
        ])
        ->add('dbInstanceClass', TextType::class, [
            'required' => false,
            'label' => 'Instance Class',
            'attr' => ['placeholder' => 'e.g. db.t3.micro'],
        ]);
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => RdsInstanceListSearch::class,
            'method' => 'GET',
            'csrf_protection' => false,
        ]);
    }

    public function getBlockPrefix()
    {
        return '';
    }
}
