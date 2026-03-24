<?php

namespace App\Controller\App;

use App\Entity\User;
use App\Form\Search\RdsInstanceListSearchForm;
use App\Repository\AWS\RDS\RdsInstanceRepository;
use App\Search\RdsInstanceListSearch;
use Knp\Component\Pager\PaginatorInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Serializer\SerializerInterface;

final class AWSRdsController extends AbstractController
{
    #[Route('/aws/rds', name: 'app_aws_rds_list')]
    public function index(Request $request, RdsInstanceRepository $rdsInstanceRepository, PaginatorInterface $paginator): Response
    {
        return $this->render('app/aws_rds/index.html.twig');
    }

    #[Route('/aws/rds/_frame', name: 'app_aws_rds_list_frame')]
    public function indexFrame(Request $request, RdsInstanceRepository $rdsInstanceRepository, PaginatorInterface $paginator): Response
    {
        /** @var User $user */
        $user = $this->getUser();

        $search = new RdsInstanceListSearch();
        $search->userId = $user->getId();

        $searchForm = $this->createForm(RdsInstanceListSearchForm::class, $search);
        $searchForm->handleRequest($request);
        if ($searchForm->isSubmitted() && $searchForm->isValid()) {
            $search = $searchForm->getData();
        }

        $qb = $rdsInstanceRepository->listRdsInstancesForUser($search);

        $pagination = $paginator->paginate(
            $qb,
            $request->query->getInt('page', 1),
            20
        );

        return $this->render('app/aws_rds/index_frame.html.twig', [
            'pagination' => $pagination,
            'searchForm' => $searchForm->createView(),
        ]);
    }

    #[Route('/aws/rds/export', name: 'app_aws_rds_list_export')]
    public function indexExport(Request $request, RdsInstanceRepository $rdsInstanceRepository, SerializerInterface $serializer): Response
    {
        /** @var User $user */
        $user = $this->getUser();

        $search = new RdsInstanceListSearch();
        $search->userId = $user->getId();

        $searchForm = $this->createForm(RdsInstanceListSearchForm::class, $search);
        $searchForm->handleRequest($request);
        if ($searchForm->isSubmitted() && $searchForm->isValid()) {
            $search = $searchForm->getData();
        }

        $qb = $rdsInstanceRepository->listRdsInstancesForUser($search);

        $result = $serializer->serialize($qb->getQuery()->getResult(), 'csv', [
            'groups' => ['rds_instance_list_export'],
            'csv_delimiter' => ';',
            'csv_header' => true,
        ]);

        $response = new Response($result);
        $response->headers->set('Content-Type', 'text/csv');
        $response->headers->set('Content-Disposition', 'attachment; filename="rds_instances.csv"');

        return $response;
    }
}
