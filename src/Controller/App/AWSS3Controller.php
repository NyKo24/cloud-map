<?php

namespace App\Controller\App;

use App\Entity\User;
use App\Form\Search\S3BucketListSearchForm;
use App\Repository\AWS\S3\S3BucketRepository;
use App\Search\S3BucketListSearch;
use Knp\Component\Pager\PaginatorInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Serializer\SerializerInterface;

final class AWSS3Controller extends AbstractController
{
    #[Route('/aws/s3', name: 'app_aws_s3_list')]
    public function index(Request $request, S3BucketRepository $s3BucketRepository, PaginatorInterface $paginator): Response
    {
        return $this->render('app/aws_s3/index.html.twig');
    }

    #[Route('/aws/s3/_frame', name: 'app_aws_s3_list_frame')]
    public function indexFrame(Request $request, S3BucketRepository $s3BucketRepository, PaginatorInterface $paginator): Response
    {
        /** @var User $user */
        $user = $this->getUser();

        $search = new S3BucketListSearch();
        $search->userId = $user->getId();

        $searchForm = $this->createForm(S3BucketListSearchForm::class, $search);
        $searchForm->handleRequest($request);
        if ($searchForm->isSubmitted() && $searchForm->isValid()) {
            $search = $searchForm->getData();
        }

        $qb = $s3BucketRepository->listS3BucketsForUser($search);

        $pagination = $paginator->paginate(
            $qb,
            $request->query->getInt('page', 1),
            20
        );

        return $this->render('app/aws_s3/index_frame.html.twig', [
            'pagination' => $pagination,
            'searchForm' => $searchForm->createView(),
        ]);
    }

    #[Route('/aws/s3/export', name: 'app_aws_s3_list_export')]
    public function indexExport(Request $request, S3BucketRepository $s3BucketRepository, SerializerInterface $serializer): Response
    {
        /** @var User $user */
        $user = $this->getUser();

        $search = new S3BucketListSearch();
        $search->userId = $user->getId();

        $searchForm = $this->createForm(S3BucketListSearchForm::class, $search);
        $searchForm->handleRequest($request);
        if ($searchForm->isSubmitted() && $searchForm->isValid()) {
            $search = $searchForm->getData();
        }

        $qb = $s3BucketRepository->listS3BucketsForUser($search);

        $result = $serializer->serialize($qb->getQuery()->getResult(), 'csv', [
            'groups' => ['s3_bucket_list_export'],
            'csv_delimiter' => ';',
            'csv_header' => true,
        ]);

        $response = new Response($result);
        $response->headers->set('Content-Type', 'text/csv');
        $response->headers->set('Content-Disposition', 'attachment; filename="s3_buckets.csv"');

        return $response;
    }
}
