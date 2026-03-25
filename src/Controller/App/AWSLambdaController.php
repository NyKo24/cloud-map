<?php

namespace App\Controller\App;

use App\Controller\App\Trait\AwsResourceSearchTrait;
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
    use AwsResourceSearchTrait;

    #[Route('/app/aws/lambda', name: 'app_aws_lambda_list')]
    public function index(): Response
    {
        return $this->render('app/aws_lambda/index.html.twig');
    }

    #[Route('/app/aws/lambda/_frame', name: 'app_aws_lambda_list_frame')]
    public function indexFrame(Request $request, LambdaFunctionRepository $lambdaFunctionRepository, PaginatorInterface $paginator): Response
    {
        [$search, $searchForm] = $this->handleSearchForm($request, LambdaFunctionListSearch::class, LambdaFunctionListSearchForm::class);

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
        [$search] = $this->handleSearchForm($request, LambdaFunctionListSearch::class, LambdaFunctionListSearchForm::class);

        $qb = $lambdaFunctionRepository->listLambdaFunctionsForUser($search);

        $csvContent = $serializer->serialize($qb->getQuery()->getResult(), 'csv', [
            'groups' => ['lambda_function_list_export'],
            'csv_delimiter' => ';',
            'csv_header' => true,
        ]);

        return $this->createCsvResponse($csvContent, 'lambda_functions.csv');
    }
}
