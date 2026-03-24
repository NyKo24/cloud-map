<?php

namespace App\Controller\App;

use App\Entity\User;
use App\Form\Search\IamRoleListSearchForm;
use App\Form\Search\IamUserListSearchForm;
use App\Repository\AWS\IAM\IamRoleRepository;
use App\Repository\AWS\IAM\IamUserRepository;
use App\Search\IamRoleListSearch;
use App\Search\IamUserListSearch;
use Knp\Component\Pager\PaginatorInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Serializer\SerializerInterface;

final class AWSIamController extends AbstractController
{
    // --- IAM Roles ---

    #[Route('/app/aws/iam/roles', name: 'app_aws_iam_roles_list')]
    public function rolesIndex(): Response
    {
        return $this->render('app/aws_iam/roles_index.html.twig');
    }

    #[Route('/app/aws/iam/roles/_frame', name: 'app_aws_iam_roles_list_frame')]
    public function rolesIndexFrame(Request $request, IamRoleRepository $iamRoleRepository, PaginatorInterface $paginator): Response
    {
        /** @var User $user */
        $user = $this->getUser();

        $search = new IamRoleListSearch();
        $search->userId = $user->getId();

        $searchForm = $this->createForm(IamRoleListSearchForm::class, $search);
        $searchForm->handleRequest($request);
        if ($searchForm->isSubmitted() && $searchForm->isValid()) {
            $search = $searchForm->getData();
        }

        $qb = $iamRoleRepository->listIamRolesForUser($search);

        $pagination = $paginator->paginate(
            $qb,
            $request->query->getInt('page', 1),
            20
        );

        return $this->render('app/aws_iam/roles_index_frame.html.twig', [
            'pagination' => $pagination,
            'searchForm' => $searchForm->createView(),
        ]);
    }

    #[Route('/app/aws/iam/roles/export', name: 'app_aws_iam_roles_list_export')]
    public function rolesIndexExport(Request $request, IamRoleRepository $iamRoleRepository, SerializerInterface $serializer): Response
    {
        /** @var User $user */
        $user = $this->getUser();

        $search = new IamRoleListSearch();
        $search->userId = $user->getId();

        $searchForm = $this->createForm(IamRoleListSearchForm::class, $search);
        $searchForm->handleRequest($request);
        if ($searchForm->isSubmitted() && $searchForm->isValid()) {
            $search = $searchForm->getData();
        }

        $qb = $iamRoleRepository->listIamRolesForUser($search);

        $result = $serializer->serialize($qb->getQuery()->getResult(), 'csv', [
            'groups' => ['iam_role_list_export'],
            'csv_delimiter' => ';',
            'csv_header' => true,
        ]);

        $response = new Response($result);
        $response->headers->set('Content-Type', 'text/csv');
        $response->headers->set('Content-Disposition', 'attachment; filename="iam_roles.csv"');

        return $response;
    }

    // --- IAM Users ---

    #[Route('/app/aws/iam/users', name: 'app_aws_iam_users_list')]
    public function usersIndex(): Response
    {
        return $this->render('app/aws_iam/users_index.html.twig');
    }

    #[Route('/app/aws/iam/users/_frame', name: 'app_aws_iam_users_list_frame')]
    public function usersIndexFrame(Request $request, IamUserRepository $iamUserRepository, PaginatorInterface $paginator): Response
    {
        /** @var User $user */
        $user = $this->getUser();

        $search = new IamUserListSearch();
        $search->userId = $user->getId();

        $searchForm = $this->createForm(IamUserListSearchForm::class, $search);
        $searchForm->handleRequest($request);
        if ($searchForm->isSubmitted() && $searchForm->isValid()) {
            $search = $searchForm->getData();
        }

        $qb = $iamUserRepository->listIamUsersForUser($search);

        $pagination = $paginator->paginate(
            $qb,
            $request->query->getInt('page', 1),
            20
        );

        return $this->render('app/aws_iam/users_index_frame.html.twig', [
            'pagination' => $pagination,
            'searchForm' => $searchForm->createView(),
        ]);
    }

    #[Route('/app/aws/iam/users/export', name: 'app_aws_iam_users_list_export')]
    public function usersIndexExport(Request $request, IamUserRepository $iamUserRepository, SerializerInterface $serializer): Response
    {
        /** @var User $user */
        $user = $this->getUser();

        $search = new IamUserListSearch();
        $search->userId = $user->getId();

        $searchForm = $this->createForm(IamUserListSearchForm::class, $search);
        $searchForm->handleRequest($request);
        if ($searchForm->isSubmitted() && $searchForm->isValid()) {
            $search = $searchForm->getData();
        }

        $qb = $iamUserRepository->listIamUsersForUser($search);

        $result = $serializer->serialize($qb->getQuery()->getResult(), 'csv', [
            'groups' => ['iam_user_list_export'],
            'csv_delimiter' => ';',
            'csv_header' => true,
        ]);

        $response = new Response($result);
        $response->headers->set('Content-Type', 'text/csv');
        $response->headers->set('Content-Disposition', 'attachment; filename="iam_users.csv"');

        return $response;
    }
}
