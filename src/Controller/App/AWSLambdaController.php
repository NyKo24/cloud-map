<?php

namespace App\Controller\App;

use App\Entity\User;
use App\Form\Search\LambdaFunctionListSearchForm;
use App\Repository\AWS\Lambda\LambdaFunctionRepository;
use App\Search\LambdaFunctionListSearch;
use Knp\Component\Pager\PaginatorInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Serializer\SerializerInterface;

final class AWSLambdaController extends AbstractController
{
    #[Route('/app/aws/lambda', name: 'app_aws_lambda_list')]
    public function index(Request $request, LambdaFunctionRepository $lambdaFunctionRepository, PaginatorInterface $paginator): Response
    {
        return $this->render('app/aws_lambda/index.html.twig');
    }

    #[Route('/app/aws/lambda/_frame', name: 'app_aws_lambda_list_frame')]
    public function indexFrame(Request $request, LambdaFunctionRepository $lambdaFunctionRepository, PaginatorInterface $paginator): Response
    {
        /** @var User $user */
        $user = $this->getUser();

        $search = new LambdaFunctionListSearch();
        $search->userId = $user->getId();

        $searchForm = $this->createForm(LambdaFunctionListSearchForm::class, $search);
        $searchForm->handleRequest($request);
        if ($searchForm->isSubmitted() && $searchForm->isValid()) {
            $search = $searchForm->getData();
        }

        $qb = $lambdaFunctionRepository->listLambdaFunctionsForUser($search);

        $pagination = $paginator->paginate(
            $qb,
            $request->query->getInt('page', 1),
            20
        );

        return $this->render('app/aws_lambda/index_frame.html.twig', [
            'pagination' => $pagination,
            'searchForm' => $searchForm->createView(),
        ]);
    }

    #[Route('/app/aws/lambda/export', name: 'app_aws_lambda_list_export')]
    public function indexExport(Request $request, LambdaFunctionRepository $lambdaFunctionRepository, SerializerInterface $serializer): Response
    {
        /** @var User $user */
        $user = $this->getUser();

        $search = new LambdaFunctionListSearch();
        $search->userId = $user->getId();

        $searchForm = $this->createForm(LambdaFunctionListSearchForm::class, $search);
        $searchForm->handleRequest($request);
        if ($searchForm->isSubmitted() && $searchForm->isValid()) {
            $search = $searchForm->getData();
        }

        $qb = $lambdaFunctionRepository->listLambdaFunctionsForUser($search);

        $result = $serializer->serialize($qb->getQuery()->getResult(), 'csv', [
            'groups' => ['lambda_function_list_export'],
            'csv_delimiter' => ';',
            'csv_header' => true,
        ]);

        $response = new Response($result);
        $response->headers->set('Content-Type', 'text/csv');
        $response->headers->set('Content-Disposition', 'attachment; filename="lambda_functions.csv"');

        return $response;
    }
}