<?php

namespace App\Form;

use App\Entity\Customer;
use App\Enum\Customer\CustomerIntegrationModeEnum;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\EnumType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class CompanyConfigurationFullOrganizationRegistrationType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('awsRootOrganisationId', TextType::class, [
                'label' => 'AWS Organization ID',
                'required' => false,
            ])
            ->add('awsRootRoleArn', TextType::class, [
                'label' => 'AWS Root Role ARN',
                'help' => 'ARN du rôle que vous avez déployer sur votre compte AWS racine',
                'required' => false,
            ])
            ->add('awsAccountRoleName', TextType::class, [
                'label' => 'AWS Account Role ARN',
                'help' => 'ARN du rôle que vous avez déployer sur chacun de vos comptes AWS. Le rôle doit avoir le même nom sur chaque compte.',
                'required' => false,
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
