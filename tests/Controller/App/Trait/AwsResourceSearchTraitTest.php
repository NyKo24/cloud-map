<?php

namespace App\Tests\Controller\App\Trait;

use App\Controller\App\Trait\AwsResourceSearchTrait;
use App\Entity\User;
use App\Form\Search\AWSAccountListSearchForm;
use App\Search\AWSAccountListSearch;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Form\FormFactoryInterface;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

class AwsResourceSearchTraitTest extends TestCase
{
    private object $controller;

    protected function setUp(): void
    {
        $this->controller = new class () {
            use AwsResourceSearchTrait;

            private ?object $user = null;
            private ?FormInterface $form = null;

            public function setUser(object $user): void
            {
                $this->user = $user;
            }

            public function setForm(FormInterface $form): void
            {
                $this->form = $form;
            }

            public function getUser(): ?object
            {
                return $this->user;
            }

            public function createForm(string $type, mixed $data = null, array $options = []): FormInterface
            {
                $this->form->method('getData')->willReturn($data);
                return $this->form;
            }

            public function callHandleSearchForm(Request $request, string $searchClass, string $formClass): array
            {
                return $this->handleSearchForm($request, $searchClass, $formClass);
            }

            public function callCreateCsvResponse(string $csvContent, string $filename): Response
            {
                return $this->createCsvResponse($csvContent, $filename);
            }
        };
    }

    public function testHandleSearchFormSetsUserIdAndReturnsSearchAndForm(): void
    {
        $user = new User();
        $reflection = new \ReflectionClass($user);
        $idProperty = $reflection->getProperty('id');
        $idProperty->setValue($user, 42);

        $this->controller->setUser($user);

        $form = $this->createMock(FormInterface::class);
        $form->method('isSubmitted')->willReturn(false);
        $form->method('isValid')->willReturn(false);
        $this->controller->setForm($form);

        $request = new Request();

        [$search, $returnedForm] = $this->controller->callHandleSearchForm(
            $request,
            AWSAccountListSearch::class,
            AWSAccountListSearchForm::class
        );

        $this->assertInstanceOf(AWSAccountListSearch::class, $search);
        $this->assertSame(42, $search->userId);
        $this->assertSame($form, $returnedForm);
    }

    public function testHandleSearchFormUsesFormDataWhenSubmittedAndValid(): void
    {
        $user = new User();
        $reflection = new \ReflectionClass($user);
        $idProperty = $reflection->getProperty('id');
        $idProperty->setValue($user, 10);

        $this->controller->setUser($user);

        $formSearch = new AWSAccountListSearch();
        $formSearch->userId = 10;
        $formSearch->accountName = 'filtered';

        $form = $this->createMock(FormInterface::class);
        $form->method('isSubmitted')->willReturn(true);
        $form->method('isValid')->willReturn(true);
        $form->method('getData')->willReturn($formSearch);
        $this->controller->setForm($form);

        $request = new Request();

        [$search, $returnedForm] = $this->controller->callHandleSearchForm(
            $request,
            AWSAccountListSearch::class,
            AWSAccountListSearchForm::class
        );

        $this->assertSame($formSearch, $search);
        $this->assertSame('filtered', $search->accountName);
        $this->assertSame($form, $returnedForm);
    }

    public function testHandleSearchFormDoesNotUseFormDataWhenNotSubmitted(): void
    {
        $user = new User();
        $reflection = new \ReflectionClass($user);
        $idProperty = $reflection->getProperty('id');
        $idProperty->setValue($user, 5);

        $this->controller->setUser($user);

        $form = $this->createMock(FormInterface::class);
        $form->method('isSubmitted')->willReturn(false);
        $form->method('isValid')->willReturn(true);
        $this->controller->setForm($form);

        $request = new Request();

        [$search] = $this->controller->callHandleSearchForm(
            $request,
            AWSAccountListSearch::class,
            AWSAccountListSearchForm::class
        );

        $this->assertInstanceOf(AWSAccountListSearch::class, $search);
        $this->assertSame(5, $search->userId);
        $this->assertNull($search->accountName);
    }

    public function testHandleSearchFormDoesNotUseFormDataWhenSubmittedButInvalid(): void
    {
        $user = new User();
        $reflection = new \ReflectionClass($user);
        $idProperty = $reflection->getProperty('id');
        $idProperty->setValue($user, 7);

        $this->controller->setUser($user);

        $form = $this->createMock(FormInterface::class);
        $form->method('isSubmitted')->willReturn(true);
        $form->method('isValid')->willReturn(false);
        $this->controller->setForm($form);

        $request = new Request();

        [$search] = $this->controller->callHandleSearchForm(
            $request,
            AWSAccountListSearch::class,
            AWSAccountListSearchForm::class
        );

        $this->assertInstanceOf(AWSAccountListSearch::class, $search);
        $this->assertSame(7, $search->userId);
    }

    public function testCreateCsvResponseSetsContentAndHeaders(): void
    {
        $csvContent = "col1;col2\nval1;val2\n";
        $response = $this->controller->callCreateCsvResponse($csvContent, 'export.csv');

        $this->assertSame(200, $response->getStatusCode());
        $this->assertSame($csvContent, $response->getContent());
        $this->assertSame('text/csv', $response->headers->get('Content-Type'));
        $this->assertSame('attachment; filename="export.csv"', $response->headers->get('Content-Disposition'));
    }

    public function testCreateCsvResponseWithEmptyContent(): void
    {
        $response = $this->controller->callCreateCsvResponse('', 'empty.csv');

        $this->assertSame(200, $response->getStatusCode());
        $this->assertSame('', $response->getContent());
        $this->assertSame('text/csv', $response->headers->get('Content-Type'));
        $this->assertSame('attachment; filename="empty.csv"', $response->headers->get('Content-Disposition'));
    }

    public function testCreateCsvResponseWithSpecialFilename(): void
    {
        $response = $this->controller->callCreateCsvResponse('data', 'my report (2026).csv');

        $this->assertSame('attachment; filename="my report (2026).csv"', $response->headers->get('Content-Disposition'));
    }
}
