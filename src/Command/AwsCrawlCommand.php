<?php

namespace App\Command;

use App\Crawler\AWSCrawlerManager;
use App\Repository\CustomerRepository;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

#[AsCommand(
    name: 'aws:crawl-all',
    description: 'Start a crawl',
)]
class AwsCrawlCommand extends Command
{
    public function __construct(
        private AWSCrawlerManager $AWSCrawlerManager,
        private CustomerRepository $customerRepository
    )
    {
        parent::__construct();
    }

    protected function configure(): void
    {
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);


        $customers = $this->customerRepository->findAll();
        foreach ($customers as $customer) {
            $io->note(sprintf('Crawling AWS account for client %s', $customer->getName()));
            $this->AWSCrawlerManager->crawl($customer);
        }

        $io->success('You have a new command! Now make it your own! Pass --help to see your options.');

        return Command::SUCCESS;
    }
}
