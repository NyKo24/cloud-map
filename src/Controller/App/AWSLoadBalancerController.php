<?php

namespace App\Controller\App;

use App\Entity\User;
use App\Form\Search\LoadBalancerListSearchForm;
use App\Repository\AWS\ELB\LoadBalancerRepository;
use App\Search\LoadBalancerListSearch;
use Knp\Component\Pager\PaginatorInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Serializer\SerializerInterface;

final class AWSLoadBalancerController extends AbstractController
{
    #[Route('/app/aws/load-balancers', name: 'app_aws_load_balancers_list')]
    public function index(Request $request, LoadBalancerRepository $loadBalancerRepository, PaginatorInterface $paginator): Response
    {
        return $this->render('app/aws_elb/index.html.twig');
    }

    #[Route('/app/aws/load-balancers/_frame', name: 'app_aws_load_balancers_list_frame')]
    public function indexFrame(Request $request, LoadBalancerRepository $loadBalancerRepository, PaginatorInterface $paginator): Response
    {
        /** @var User $user */
        $user = $this->getUser();

        $search = new LoadBalancerListSearch();
        $search->userId = $user->getId();

        $searchForm = $this->createForm(LoadBalancerListSearchForm::class, $search);
        $searchForm->handleRequest($request);
        if ($searchForm->isSubmitted() && $searchForm->isValid()) {
            $search = $searchForm->getData();
        }

        $qb = $loadBalancerRepository->listLoadBalancersForUser($search);

        $pagination = $paginator->paginate(
            $qb,
            $request->query->getInt('page', 1),
            20
        );

        return $this->render('app/aws_elb/index_frame.html.twig', [
            'pagination' => $pagination,
            'searchForm' => $searchForm->createView(),
        ]);
    }

    #[Route('/app/aws/load-balancers/export', name: 'app_aws_load_balancers_list_export')]
    public function indexExport(Request $request, LoadBalancerRepository $loadBalancerRepository, SerializerInterface $serializer): Response
    {
        /** @var User $user */
        $user = $this->getUser();

        $search = new LoadBalancerListSearch();
        $search->userId = $user->getId();

        $searchForm = $this->createForm(LoadBalancerListSearchForm::class, $search);
        $searchForm->handleRequest($request);
        if ($searchForm->isSubmitted() && $searchForm->isValid()) {
            $search = $searchForm->getData();
        }

        $qb = $loadBalancerRepository->listLoadBalancersForUser($search);

        $result = $serializer->serialize($qb->getQuery()->getResult(), 'csv', [
            'groups' => ['load_balancer_list_export'],
            'csv_delimiter' => ';',
            'csv_header' => true,
        ]);

        $response = new Response($result);
        $response->headers->set('Content-Type', 'text/csv');
        $response->headers->set('Content-Disposition', 'attachment; filename="load_balancers.csv"');

        return $response;
    }
}
