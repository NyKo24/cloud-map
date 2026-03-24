<?php

namespace App\Controller\App;

use App\Entity\User;
use App\Form\Search\HostedZoneListSearchForm;
use App\Repository\AWS\Route53\HostedZoneRepository;
use App\Search\HostedZoneListSearch;
use Knp\Component\Pager\PaginatorInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Serializer\SerializerInterface;

final class AWSRoute53Controller extends AbstractController
{
    #[Route('/app/aws/route53', name: 'app_aws_route53_list')]
    public function index(Request $request, HostedZoneRepository $hostedZoneRepository, PaginatorInterface $paginator): Response
    {
        return $this->render('app/aws_route53/index.html.twig');
    }

    #[Route('/app/aws/route53/_frame', name: 'app_aws_route53_list_frame')]
    public function indexFrame(Request $request, HostedZoneRepository $hostedZoneRepository, PaginatorInterface $paginator): Response
    {
        /** @var User $user */
        $user = $this->getUser();

        $search = new HostedZoneListSearch();
        $search->userId = $user->getId();

        $searchForm = $this->createForm(HostedZoneListSearchForm::class, $search);
        $searchForm->handleRequest($request);
        if ($searchForm->isSubmitted() && $searchForm->isValid()) {
            $search = $searchForm->getData();
        }

        $qb = $hostedZoneRepository->listHostedZonesForUser($search);

        $pagination = $paginator->paginate(
            $qb,
            $request->query->getInt('page', 1),
            20
        );

        return $this->render('app/aws_route53/index_frame.html.twig', [
            'pagination' => $pagination,
            'searchForm' => $searchForm->createView(),
        ]);
    }

    #[Route('/app/aws/route53/export', name: 'app_aws_route53_list_export')]
    public function indexExport(Request $request, HostedZoneRepository $hostedZoneRepository, SerializerInterface $serializer): Response
    {
        /** @var User $user */
        $user = $this->getUser();

        $search = new HostedZoneListSearch();
        $search->userId = $user->getId();

        $searchForm = $this->createForm(HostedZoneListSearchForm::class, $search);
        $searchForm->handleRequest($request);
        if ($searchForm->isSubmitted() && $searchForm->isValid()) {
            $search = $searchForm->getData();
        }

        $qb = $hostedZoneRepository->listHostedZonesForUser($search);

        $result = $serializer->serialize($qb->getQuery()->getResult(), 'csv', [
            'groups' => ['hosted_zone_list_export'],
            'csv_delimiter' => ';',
            'csv_header' => true,
        ]);

        $response = new Response($result);
        $response->headers->set('Content-Type', 'text/csv');
        $response->headers->set('Content-Disposition', 'attachment; filename="route53_hosted_zones.csv"');

        return $response;
    }
}
