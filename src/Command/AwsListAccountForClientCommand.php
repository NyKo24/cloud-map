<?php

namespace App\Command;

use App\Entity\AWS\AwsAccount;
use App\Entity\CrawlVersion;
use App\Repository\AWS\AwsAccountRepository;
use App\Repository\CrawlVersionRepository;
use App\Repository\CustomerRepository;
use Aws\Iam\IamClient;
use Aws\Organizations\OrganizationsClient;
use Aws\Sts\StsClient;
use Doctrine\Persistence\ManagerRegistry;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;
use Symfony\Component\Serializer\Normalizer\DenormalizerInterface;

#[AsCommand(
    name: 'aws:list-account',
    description: 'Scrap AWS account for client',
)]
class AwsListAccountForClientCommand extends Command
{
    public function __construct(
        private CustomerRepository $customerRepository,
        private CrawlVersionRepository $crawlVersionRepository,
        private AwsAccountRepository $awsAccountRepository,
        private DenormalizerInterface $denormalizer,
        private ManagerRegistry $registry
    )
    {
        parent::__construct();
    }

    protected function configure(): void
    {
        $this
            ->addOption('customer', null, InputOption::VALUE_REQUIRED, 'Client name')
            ->addOption('crawl', null, InputOption::VALUE_REQUIRED, 'Client name')
        ;
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);

        $customerName = $input->getOption('customer');
        $crawl = $input->getOption('customer');

        $customers = [];

        if ($customerName) {
            $io->note(sprintf('Looking for AWS account for client %s', $customerName));

            $customer = $this->customerRepository->findOneBy(['name' => strtoupper($customerName)]);

            if ($customer) {
                $customers[] = $customer;
            } else {
                $io->error(sprintf('Client %s not found', $customerName));

                return Command::FAILURE;
            }
        } else {
            $io->note('Looking for all AWS accounts for clients');

            $customers = $this->customerRepository->findAll();
        }

        $stsClient = new StsClient([
            'version' => 'latest',
            'region' => 'us-east-1',
        ]);

        foreach ($customers as $customer) {
            $io->info(sprintf('Start crawl for client "%s (%s)"', $customer->getName(), $customer->getId()));
            $crawlVersion = new CrawlVersion();
            $crawlVersion->setVersion((string)time());
            $crawlVersion->setCustomer($customer);

            if ($crawl) {
                $crawlVersionDb = $this->crawlVersionRepository->findOneBy(['version' => $crawl, 'customer' => $customer]);
                if (!$crawlVersionDb) {
                    $this->registry->getManager()->persist($crawlVersion);
                } else {
                    $crawlVersion = $crawlVersionDb;
                }
            } else {
                $this->registry->getManager()->persist($crawlVersion);
            }

            $stsRole = $stsClient->assumeRole([
                'RoleArn' => $customer->getAwsRootRoleArn(),
                'RoleSessionName' => 'Ext-ResourceMapCloud-Root',
            ]);

            $awsOrganisationClient = new OrganizationsClient([
                'version' => 'latest',
                'region' => 'us-east-1',
                'credentials' => [
                    'key' => $stsRole['Credentials']['AccessKeyId'],
                    'secret' => $stsRole['Credentials']['SecretAccessKey'],
                    'token' => $stsRole['Credentials']['SessionToken'],
                ],
            ]);

            $accounts = $awsOrganisationClient->listAccounts([
                //'ParentId' => $customer->getAwsRootOrganisationId(),
            ]);


            foreach ($accounts['Accounts'] as $account) {
                $io->info(sprintf('Found account %s', $account['Id']));

                $accountEntity = $this->denormalizer->denormalize($account, AwsAccount::class);
                $accountEntity->setCrawl($crawlVersion);
                $accountEntity->setCustomer($customer);

                $this->registry->getManager()->persist($accountEntity);
            }

            $this->registry->getManager()->flush();
        }


        $io->success('You have a new command! Now make it your own! Pass --help to see your options.');

        return Command::SUCCESS;
    }
}
