<?php

namespace App\Controller\App;

use App\Entity\User;
use App\Form\Search\CloudWatchAlarmListSearchForm;
use App\Repository\AWS\CloudWatch\CloudWatchAlarmRepository;
use App\Search\CloudWatchAlarmListSearch;
use Knp\Component\Pager\PaginatorInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Serializer\SerializerInterface;

final class AWSCloudWatchController extends AbstractController
{
    #[Route('/app/aws/cloudwatch/alarms', name: 'app_aws_cloudwatch_alarms_list')]
    public function index(): Response
    {
        return $this->render('app/aws_cloudwatch/index.html.twig');
    }

    #[Route('/app/aws/cloudwatch/alarms/_frame', name: 'app_aws_cloudwatch_alarms_list_frame')]
    public function indexFrame(Request $request, CloudWatchAlarmRepository $cloudWatchAlarmRepository, PaginatorInterface $paginator): Response
    {
        /** @var User $user */
        $user = $this->getUser();

        $search = new CloudWatchAlarmListSearch();
        $search->userId = $user->getId();

        $searchForm = $this->createForm(CloudWatchAlarmListSearchForm::class, $search);
        $searchForm->handleRequest($request);
        if ($searchForm->isSubmitted() && $searchForm->isValid()) {
            $search = $searchForm->getData();
        }

        $qb = $cloudWatchAlarmRepository->listCloudWatchAlarmsForUser($search);

        $pagination = $paginator->paginate(
            $qb,
            $request->query->getInt('page', 1),
            20
        );

        return $this->render('app/aws_cloudwatch/index_frame.html.twig', [
            'pagination' => $pagination,
            'searchForm' => $searchForm->createView(),
        ]);
    }

    #[Route('/app/aws/cloudwatch/alarms/export', name: 'app_aws_cloudwatch_alarms_list_export')]
    public function indexExport(Request $request, CloudWatchAlarmRepository $cloudWatchAlarmRepository, SerializerInterface $serializer): Response
    {
        /** @var User $user */
        $user = $this->getUser();

        $search = new CloudWatchAlarmListSearch();
        $search->userId = $user->getId();

        $searchForm = $this->createForm(CloudWatchAlarmListSearchForm::class, $search);
        $searchForm->handleRequest($request);
        if ($searchForm->isSubmitted() && $searchForm->isValid()) {
            $search = $searchForm->getData();
        }

        $qb = $cloudWatchAlarmRepository->listCloudWatchAlarmsForUser($search);

        $result = $serializer->serialize($qb->getQuery()->getResult(), 'csv', [
            'groups' => ['cloudwatch_alarm_list_export'],
            'csv_delimiter' => ';',
            'csv_header' => true,
        ]);

        $response = new Response($result);
        $response->headers->set('Content-Type', 'text/csv');
        $response->headers->set('Content-Disposition', 'attachment; filename="cloudwatch_alarms.csv"');

        return $response;
    }
}
