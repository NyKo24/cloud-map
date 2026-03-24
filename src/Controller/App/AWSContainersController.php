<?php

namespace App\Controller\App;

use App\Entity\User;
use App\Form\Search\EcsClusterListSearchForm;
use App\Form\Search\EksClusterListSearchForm;
use App\Repository\AWS\ECS\EcsClusterRepository;
use App\Repository\AWS\EKS\EksClusterRepository;
use App\Search\EcsClusterListSearch;
use App\Search\EksClusterListSearch;
use Knp\Component\Pager\PaginatorInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Serializer\SerializerInterface;

final class AWSContainersController extends AbstractController
{
    // --- ECS ---

    #[Route('/app/aws/ecs/clusters', name: 'app_aws_ecs_clusters_list')]
    public function ecsIndex(): Response
    {
        return $this->render('app/aws_containers/ecs_index.html.twig');
    }

    #[Route('/app/aws/ecs/clusters/_frame', name: 'app_aws_ecs_clusters_list_frame')]
    public function ecsIndexFrame(Request $request, EcsClusterRepository $ecsClusterRepository, PaginatorInterface $paginator): Response
    {
        /** @var User $user */
        $user = $this->getUser();

        $search = new EcsClusterListSearch();
        $search->userId = $user->getId();

        $searchForm = $this->createForm(EcsClusterListSearchForm::class, $search);
        $searchForm->handleRequest($request);
        if ($searchForm->isSubmitted() && $searchForm->isValid()) {
            $search = $searchForm->getData();
        }

        $qb = $ecsClusterRepository->listEcsClustersForUser($search);

        $pagination = $paginator->paginate(
            $qb,
            $request->query->getInt('page', 1),
            20
        );

        return $this->render('app/aws_containers/ecs_index_frame.html.twig', [
            'pagination' => $pagination,
            'searchForm' => $searchForm->createView(),
        ]);
    }

    #[Route('/app/aws/ecs/clusters/export', name: 'app_aws_ecs_clusters_list_export')]
    public function ecsIndexExport(Request $request, EcsClusterRepository $ecsClusterRepository, SerializerInterface $serializer): Response
    {
        /** @var User $user */
        $user = $this->getUser();

        $search = new EcsClusterListSearch();
        $search->userId = $user->getId();

        $searchForm = $this->createForm(EcsClusterListSearchForm::class, $search);
        $searchForm->handleRequest($request);
        if ($searchForm->isSubmitted() && $searchForm->isValid()) {
            $search = $searchForm->getData();
        }

        $qb = $ecsClusterRepository->listEcsClustersForUser($search);

        $result = $serializer->serialize($qb->getQuery()->getResult(), 'csv', [
            'groups' => ['ecs_cluster_list_export'],
            'csv_delimiter' => ';',
            'csv_header' => true,
        ]);

        $response = new Response($result);
        $response->headers->set('Content-Type', 'text/csv');
        $response->headers->set('Content-Disposition', 'attachment; filename="ecs_clusters.csv"');

        return $response;
    }

    // --- EKS ---

    #[Route('/app/aws/eks/clusters', name: 'app_aws_eks_clusters_list')]
    public function eksIndex(): Response
    {
        return $this->render('app/aws_containers/eks_index.html.twig');
    }

    #[Route('/app/aws/eks/clusters/_frame', name: 'app_aws_eks_clusters_list_frame')]
    public function eksIndexFrame(Request $request, EksClusterRepository $eksClusterRepository, PaginatorInterface $paginator): Response
    {
        /** @var User $user */
        $user = $this->getUser();

        $search = new EksClusterListSearch();
        $search->userId = $user->getId();

        $searchForm = $this->createForm(EksClusterListSearchForm::class, $search);
        $searchForm->handleRequest($request);
        if ($searchForm->isSubmitted() && $searchForm->isValid()) {
            $search = $searchForm->getData();
        }

        $qb = $eksClusterRepository->listEksClustersForUser($search);

        $pagination = $paginator->paginate(
            $qb,
            $request->query->getInt('page', 1),
            20
        );

        return $this->render('app/aws_containers/eks_index_frame.html.twig', [
            'pagination' => $pagination,
            'searchForm' => $searchForm->createView(),
        ]);
    }

    #[Route('/app/aws/eks/clusters/export', name: 'app_aws_eks_clusters_list_export')]
    public function eksIndexExport(Request $request, EksClusterRepository $eksClusterRepository, SerializerInterface $serializer): Response
    {
        /** @var User $user */
        $user = $this->getUser();

        $search = new EksClusterListSearch();
        $search->userId = $user->getId();

        $searchForm = $this->createForm(EksClusterListSearchForm::class, $search);
        $searchForm->handleRequest($request);
        if ($searchForm->isSubmitted() && $searchForm->isValid()) {
            $search = $searchForm->getData();
        }

        $qb = $eksClusterRepository->listEksClustersForUser($search);

        $result = $serializer->serialize($qb->getQuery()->getResult(), 'csv', [
            'groups' => ['eks_cluster_list_export'],
            'csv_delimiter' => ';',
            'csv_header' => true,
        ]);

        $response = new Response($result);
        $response->headers->set('Content-Type', 'text/csv');
        $response->headers->set('Content-Disposition', 'attachment; filename="eks_clusters.csv"');

        return $response;
    }
}
