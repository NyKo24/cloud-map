<?php

namespace App\Controller\App\Trait;

use App\Entity\User;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

trait AwsResourceSearchTrait
{
    /**
     * @template T of object
     *
     * @param class-string<T> $searchClass
     * @param class-string     $formClass
     *
     * @return array{T, \Symfony\Component\Form\FormInterface}
     */
    private function handleSearchForm(Request $request, string $searchClass, string $formClass): array
    {
        /** @var User $user */
        $user = $this->getUser();

        $search = new $searchClass();
        $search->userId = $user->getId();

        $searchForm = $this->createForm($formClass, $search);
        $searchForm->handleRequest($request);
        if ($searchForm->isSubmitted() && $searchForm->isValid()) {
            $search = $searchForm->getData();
        }

        return [$search, $searchForm];
    }

    private function createCsvResponse(string $csvContent, string $filename): Response
    {
        $response = new Response($csvContent);
        $response->headers->set('Content-Type', 'text/csv');
        $response->headers->set('Content-Disposition', sprintf('attachment; filename="%s"', $filename));

        return $response;
    }
}
