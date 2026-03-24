<?php

namespace App\Controller\App;

use App\Entity\User;
use App\Form\Search\Ec2InstanceListSearchForm;
use App\Repository\AWS\EC2\Ec2InstanceRepository;
use App\Search\Ec2InstanceListSearch;
use Knp\Component\Pager\PaginatorInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Serializer\SerializerInterface;

final class AWSEC2Controller extends AbstractController
{
    #[Route('/app/aws/ec2', name: 'app_aws_ec2_list')]
    public function index(Request $request, Ec2InstanceRepository $ec2InstanceRepository, PaginatorInterface $paginator): Response
    {
        return $this->render('app/aws_ec2/index.html.twig');
    }

    #[Route('/app/aws/ec2/_frame', name: 'app_aws_ec2_list_frame')]
    public function indexFrame(Request $request, Ec2InstanceRepository $ec2InstanceRepository, PaginatorInterface $paginator): Response
    {
        /** @var User $user */
        $user = $this->getUser();

        $search = new Ec2InstanceListSearch();
        $search->userId = $user->getId();

        $searchForm = $this->createForm(Ec2InstanceListSearchForm::class, $search);
        $searchForm->handleRequest($request);
        if ($searchForm->isSubmitted() && $searchForm->isValid()) {
            $search = $searchForm->getData();
        }

        $qb = $ec2InstanceRepository->listEc2InstancesForUser($search);

        $pagination = $paginator->paginate(
            $qb,
            $request->query->getInt('page', 1),
            20
        );

        return $this->render('app/aws_ec2/index_frame.html.twig', [
            'pagination' => $pagination,
            'searchForm' => $searchForm->createView(),
        ]);
    }

    #[Route('/app/aws/ec2/export', name: 'app_aws_ec2_list_export')]
    public function indexExport(Request $request, Ec2InstanceRepository $ec2InstanceRepository, SerializerInterface $serializer): Response
    {
        /** @var User $user */
        $user = $this->getUser();

        $search = new Ec2InstanceListSearch();
        $search->userId = $user->getId();

        $searchForm = $this->createForm(Ec2InstanceListSearchForm::class, $search);
        $searchForm->handleRequest($request);
        if ($searchForm->isSubmitted() && $searchForm->isValid()) {
            $search = $searchForm->getData();
        }

        $qb = $ec2InstanceRepository->listEc2InstancesForUser($search);

        $result = $serializer->serialize($qb->getQuery()->getResult(), 'csv', [
            'groups' => ['ec2_instance_list_export'],
            'csv_delimiter' => ';',
            'csv_header' => true,
        ]);

        $response = new Response($result);
        $response->headers->set('Content-Type', 'text/csv');
        $response->headers->set('Content-Disposition', 'attachment; filename="ec2_instances.csv"');

        return $response;
    }
}
