<?php

namespace App\Controller\App;

use App\Entity\User;
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
    #[Route('/aws/accounts', name: 'app_aws_accounts_list')]
    public function index(Request $request, AwsAccountRepository $awsAccountRepository, PaginatorInterface $paginator): Response
    {
        return $this->render('app/aws_accounts/index.html.twig');
    }

    #[Route('/aws/accounts/_frame', name: 'app_aws_accounts_list_frame')]
    public function indexFrame(Request $request, AwsAccountRepository $awsAccountRepository, PaginatorInterface $paginator): Response
    {
        /** @var User $user */
        $user = $this->getUser();

        $search = new AwsAccountListSearch();
        $search->userId = $user->getId();

        $searchForm = $this->createForm(AWSAccountListSearchForm::class, $search);
        $searchForm->handleRequest($request);
        if ($searchForm->isSubmitted() && $searchForm->isValid()) {
            $search = $searchForm->getData();
        }

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
    public function indexExport(Request $request, AwsAccountRepository $awsAccountRepository, SerializerInterface $serialize): Response
    {
        /** @var User $user */
        $user = $this->getUser();

        $search = new AwsAccountListSearch();
        $search->userId = $user->getId();

        $searchForm = $this->createForm(AWSAccountListSearchForm::class, $search);
        $searchForm->handleRequest($request);
        if ($searchForm->isSubmitted() && $searchForm->isValid()) {
            $search = $searchForm->getData();
        }

        $qb = $awsAccountRepository->listAccountForAllUserCustomers($search);

        $result = $serialize->serialize($qb->getQuery()->getResult(), 'csv', [
            'groups' => ['aws_account_list_export'],
            'csv_delimiter' => ';',
            'csv_header' => true,
        ]);


        return $this->file();
    }


}
