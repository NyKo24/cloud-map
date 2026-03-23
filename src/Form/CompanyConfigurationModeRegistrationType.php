<?php

namespace App\Form;

use App\Entity\Customer;
use App\Enum\Customer\CustomerIntegrationModeEnum;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\EnumType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class CompanyConfigurationModeRegistrationType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('integrationMode', EnumType::class, [
                'class' => CustomerIntegrationModeEnum::class,
                'label' => 'Integration Mode',
                'placeholder' => 'Select an integration mode',
                'required' => true,
                'expanded' => true,
                'multiple' => false,
            ])
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Customer::class,
        ]);
    }
}
