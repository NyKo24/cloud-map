<?php

namespace App\DataFixtures;

use App\Entity\Customer;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;

class CustomerFixtures extends Fixture
{
    public function load(ObjectManager $manager): void
    {
        $customer = new Customer();
        $customer->setName('ADVISEME');
        $customer->setAwsRootRoleArn('arn:aws:iam::687120744729:role/Ext-ResourceMapCloud-Root');
        $customer->setAwsRootOrganisationId('r-ss4y');
        $customer->setAwsAccountRoleName('Ext-ResourceMapCloud-Crawler');

        $manager->persist($customer);
        $manager->flush();
    }
}
