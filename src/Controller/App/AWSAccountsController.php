<?php

namespace App\Controller\App;

use App\Controller\App\Trait\AwsResourceSearchTrait;
use App\Form\Search\AWSAccountListSearchForm;
use App\Repository\AWS\AwsAccountRepository;
use App\Search\AWSAccountListSearch;
use Knp\Component\Pager\PaginatorInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Serializer\SerializerInterface;

final class AWSAccountsController extends AbstractController
{
    use AwsResourceSearchTrait;

    #[Route('/aws/accounts', name: 'app_aws_accounts_list')]
    public function index(): Response
    {
        return $this->render('app/aws_accounts/index.html.twig');
    }

    #[Route('/aws/accounts/_frame', name: 'app_aws_accounts_list_frame')]
    public function indexFrame(Request $request, AwsAccountRepository $awsAccountRepository, PaginatorInterface $paginator): Response
    {
        [$search, $searchForm] = $this->handleSearchForm($request, AWSAccountListSearch::class, AWSAccountListSearchForm::class);

        $qb = $awsAccountRepository->listAccountForAllUserCustomers($search);

        $pagination = $paginator->paginate(
            $qb,
            $request->query->getInt('page', 1)
        );

        return $this->render('app/aws_accounts/index_frame.html.twig', [
            'pagination' => $pagination,
            'searchForm' => $searchForm->createView(),
        ]);
    }

    #[Route('/aws/accounts/export', name: 'app_aws_accounts_list_export')]
    public function indexExport(Request $request, AwsAccountRepository $awsAccountRepository, SerializerInterface $serializer): Response
    {
        [$search] = $this->handleSearchForm($request, AWSAccountListSearch::class, AWSAccountListSearchForm::class);

        $qb = $awsAccountRepository->listAccountForAllUserCustomers($search);

        $csvContent = $serializer->serialize($qb->getQuery()->getResult(), 'csv', [
            'groups' => ['aws_account_list_export'],
            'csv_delimiter' => ';',
            'csv_header' => true,
        ]);

        return $this->createCsvResponse($csvContent, 'aws_accounts.csv');
    }
}
