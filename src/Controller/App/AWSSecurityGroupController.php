<?php

namespace App\Controller\App;

use App\Entity\User;
use App\Form\Search\SecurityGroupListSearchForm;
use App\Repository\AWS\EC2\SecurityGroupRepository;
use App\Search\SecurityGroupListSearch;
use Knp\Component\Pager\PaginatorInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Serializer\SerializerInterface;

final class AWSSecurityGroupController extends AbstractController
{
    #[Route('/app/aws/security-groups', name: 'app_aws_security_groups_list')]
    public function index(Request $request, SecurityGroupRepository $securityGroupRepository, PaginatorInterface $paginator): Response
    {
        return $this->render('app/aws_security_groups/index.html.twig');
    }

    #[Route('/app/aws/security-groups/_frame', name: 'app_aws_security_groups_list_frame')]
    public function indexFrame(Request $request, SecurityGroupRepository $securityGroupRepository, PaginatorInterface $paginator): Response
    {
        /** @var User $user */
        $user = $this->getUser();

        $search = new SecurityGroupListSearch();
        $search->userId = $user->getId();

        $searchForm = $this->createForm(SecurityGroupListSearchForm::class, $search);
        $searchForm->handleRequest($request);
        if ($searchForm->isSubmitted() && $searchForm->isValid()) {
            $search = $searchForm->getData();
        }

        $qb = $securityGroupRepository->listSecurityGroupsWithRuleCountsForUser($search);

        $pagination = $paginator->paginate(
            $qb,
            $request->query->getInt('page', 1),
            20
        );

        return $this->render('app/aws_security_groups/index_frame.html.twig', [
            'pagination' => $pagination,
            'searchForm' => $searchForm->createView(),
        ]);
    }

    #[Route('/app/aws/security-groups/export', name: 'app_aws_security_groups_list_export')]
    public function indexExport(Request $request, SecurityGroupRepository $securityGroupRepository, SerializerInterface $serializer): Response
    {
        /** @var User $user */
        $user = $this->getUser();

        $search = new SecurityGroupListSearch();
        $search->userId = $user->getId();

        $searchForm = $this->createForm(SecurityGroupListSearchForm::class, $search);
        $searchForm->handleRequest($request);
        if ($searchForm->isSubmitted() && $searchForm->isValid()) {
            $search = $searchForm->getData();
        }

        $qb = $securityGroupRepository->listSecurityGroupsForUser($search);

        $result = $serializer->serialize($qb->getQuery()->getResult(), 'csv', [
            'groups' => ['security_group_list_export'],
            'csv_delimiter' => ';',
            'csv_header' => true,
        ]);

        $response = new Response($result);
        $response->headers->set('Content-Type', 'text/csv');
        $response->headers->set('Content-Disposition', 'attachment; filename="security_groups.csv"');

        return $response;
    }

    #[Route('/app/aws/security-groups/{id}', name: 'app_aws_security_groups_show')]
    public function show(int $id, SecurityGroupRepository $securityGroupRepository): Response
    {
        /** @var User $user */
        $user = $this->getUser();

        $securityGroup = $securityGroupRepository->findOneForUser($id, $user->getId());

        if (!$securityGroup) {
            throw $this->createNotFoundException('Security Group not found');
        }

        return $this->render('app/aws_security_groups/show.html.twig', [
            'securityGroup' => $securityGroup,
        ]);
    }
}
