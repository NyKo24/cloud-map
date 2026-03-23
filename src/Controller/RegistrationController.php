<?php

namespace App\Controller;

use App\Entity\Customer;
use App\Entity\User;
use App\Enum\Customer\CustomerIntegrationModeEnum;
use App\Enum\Registration\RegistrationStepEnum;
use App\Form\CompanyConfigurationFullOrganizationRegistrationType;
use App\Form\CompanyConfigurationModeRegistrationType;
use App\Form\CompanyInformationRegistrationType;
use App\Form\RegistrationFormType;
use App\Security\EmailVerifier;
use App\Security\UserAuthenticator;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bridge\Doctrine\Attribute\MapEntity;
use Symfony\Bridge\Twig\Mime\TemplatedEmail;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Bundle\SecurityBundle\Security;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Mime\Address;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;
use Symfony\Contracts\Translation\TranslatorInterface;
use SymfonyCasts\Bundle\VerifyEmail\Exception\VerifyEmailExceptionInterface;

class RegistrationController extends AbstractController
{
    public function __construct(private EmailVerifier $emailVerifier)
    {
    }

    #[Route('/register', name: 'app_register')]
    public function register(Request $request, UserPasswordHasherInterface $userPasswordHasher, Security $security, EntityManagerInterface $entityManager): Response
    {
        $user = new User();
        $form = $this->createForm(RegistrationFormType::class, $user);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            /** @var string $plainPassword */
            $plainPassword = $form->get('plainPassword')->getData();

            // encode the plain password
            $user->setPassword($userPasswordHasher->hashPassword($user, $plainPassword));

            $entityManager->persist($user);
            $entityManager->flush();

            // generate a signed url and email it to the user
            $this->emailVerifier->sendEmailConfirmation('app_verify_email', $user,
                (new TemplatedEmail())
                    ->from(new Address('resourcemap.cloudno-reply@resourcemap.cloud', 'ResourceMap - Inscription'))
                    ->to((string) $user->getEmail())
                    ->subject('Please Confirm your Email')
                    ->htmlTemplate('registration/confirmation_email.html.twig')
            );

            // do anything else you need here, like send an email

            return $security->login($user, UserAuthenticator::class, 'main');
        }

        return $this->render('registration/register.html.twig', [
            'registrationForm' => $form,
        ]);
    }

    #[Route('/verify/email', name: 'app_verify_email')]
    public function verifyUserEmail(Request $request, TranslatorInterface $translator): Response
    {
        $this->denyAccessUnlessGranted('IS_AUTHENTICATED_FULLY');

        // validate email confirmation link, sets User::isVerified=true and persists
        try {
            /** @var User $user */
            $user = $this->getUser();
            $this->emailVerifier->handleEmailConfirmation($request, $user);
        } catch (VerifyEmailExceptionInterface $exception) {
            $this->addFlash('verify_email_error', $translator->trans($exception->getReason(), [], 'VerifyEmailBundle'));

            return $this->redirectToRoute('app_register');
        }

        $user->setRegistrationStep(RegistrationStepEnum::REGISTER_COMPANY_INFORMATION);

        $this->addFlash('success', 'Your email address has been verified.');

        return $this->redirectToRoute('app_home');
    }

    #[Route('/register/company', name: 'app_register_company_information')]
    #[IsGranted('IS_AUTHENTICATED_FULLY')]
    public function registerCompanyInformation(Request $request, EntityManagerInterface $entityManager): Response
    {
        /** @var User $user */
        $user = $this->getUser();

        if ($user->getRegistrationStep() !== RegistrationStepEnum::REGISTER_COMPANY_INFORMATION) {
            return $this->redirectToRoute('app_home');
        }

        $customer = new Customer();
        $form = $this->createForm(CompanyInformationRegistrationType::class, $customer);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            // Set the user to the customer
            $user->addCustomer($customer);

            // Persist the customer entity
            $entityManager->persist($customer);

            // Update user's registration step
            $user->setRegistrationStep(RegistrationStepEnum::REGISTER_COMPANY_CONFIGURATION);
            $entityManager->persist($user);
            $entityManager->flush();

            // Redirect to a success page or home page
            return $this->redirectToRoute('app_register_company_configuration', [
                'customerId' => $customer->getId(),
            ]);
        }

        return $this->render('registration/company_information.html.twig', [
            'user' => $user,
            'companyInformationForm' => $form->createView(),
        ]);
    }

    #[Route('/register/company/{customerId}/configuration/mode', name: 'app_register_company_configuration')]
    #[IsGranted('IS_AUTHENTICATED_FULLY')]
    public function registerCompanyConfigurationSelectMode(
        Request $request,
        EntityManagerInterface $entityManager,
        #[MapEntity(mapping: ['customerId' => 'id'])]
        Customer $customer
    ): Response
    {
        $this->denyAccessUnlessGranted('IS_AUTHENTICATED_FULLY');

        /** @var User $user */
        $user = $this->getUser();

        if ($user->getRegistrationStep() !== RegistrationStepEnum::REGISTER_COMPANY_CONFIGURATION) {
            return $this->redirectToRoute('app_home');
        }

        $form = $this->createForm(CompanyConfigurationModeRegistrationType::class, $customer);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            // Set the user to the customer
            $user->addCustomer($customer);

            // Persist the customer entity
            $entityManager->persist($customer);

            // Update user's registration step
            if ($customer->getIntegrationMode() === CustomerIntegrationModeEnum::FULL_ORGANIZATION) {
                $user->setRegistrationStep(RegistrationStepEnum::REGISTER_COMPANY_CONFIGURATION_FULL_ORGANIZATION);
                $entityManager->flush();
                return $this->redirectToRoute('app_register_company_configuration_full_organization', [
                    'customerId' => $customer->getId(),
                ]);
            } else if ($customer->getIntegrationMode() === CustomerIntegrationModeEnum::SELECTED_ACCOUNTS) {
                $user->setRegistrationStep(RegistrationStepEnum::REGISTER_COMPANY_CONFIGURATION_SELECTED_ACCOUNT);
            } else {
                $this->addFlash('error', 'Invalid integration mode selected.');
                return $this->render('registration/company_configuration.html.twig', [
                    'user' => $user,
                    'companyConfigurationForm' => $form->createView(),
                ]);
            }
            $user->setRegistrationStep(RegistrationStepEnum::COMPANY_INFORMATION_COMPLETED);
            $entityManager->persist($user);
            $entityManager->flush();

            // Redirect to a success page or home page
            return $this->redirectToRoute('app_home');
        }

        return $this->render('registration/company_configuration.html.twig', [
            'user' => $user,
            'companyConfigurationForm' => $form->createView(),
        ]);
    }

    #[Route('/register/company/{customerId}/configuration/full-organization', name: 'app_register_company_configuration_full_organization')]
    #[IsGranted('IS_AUTHENTICATED_FULLY')]
    public function registerCompanyConfigurationFullOrganization(
        Request $request,
        EntityManagerInterface $entityManager,
        #[MapEntity(mapping: ['customerId' => 'id'])]
        Customer $customer
    ): Response
    {
        /** @var User $user */
        $user = $this->getUser();

        if ($user->getRegistrationStep() !== RegistrationStepEnum::REGISTER_COMPANY_CONFIGURATION_FULL_ORGANIZATION) {
            return $this->redirectToRoute('app_home');
        }

        $form = $this->createForm(CompanyConfigurationFullOrganizationRegistrationType::class, $customer);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            // Set the user to the customer
            $user->addCustomer($customer);

            // Persist the customer entity
            $entityManager->persist($customer);

            $entityManager->flush();

            // Redirect to a success page or home page
            return $this->redirectToRoute('app_home');
        }

        return $this->render('registration/company_configuration_full_organization.html.twig', [
            'user' => $user,
            'companyConfigurationFullOrganizationForm' => $form->createView(),
        ]);
    }

    #[Route('/register/company/{customerId}/configuration/selected-account', name: 'app_register_company_configuration_selected_account')]
    #[IsGranted('IS_AUTHENTICATED_FULLY')]
    public function registerCompanyConfigurationSelectedAccounts(
        Request $request,
        EntityManagerInterface $entityManager,
        #[MapEntity(mapping: ['customerId' => 'id'])]
        Customer $customer
    ): Response
    {
        /** @var User $user */
        $user = $this->getUser();

        if ($user->getRegistrationStep() !== RegistrationStepEnum::REGISTER_COMPANY_CONFIGURATION_FULL_ORGANIZATION) {
            return $this->redirectToRoute('app_home');
        }

        $form = $this->createForm(CompanyConfigurationModeRegistrationType::class, $customer);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            // Set the user to the customer
            $user->addCustomer($customer);

            // Persist the customer entity
            $entityManager->persist($customer);

            // Update user's registration step
            if ($customer->getIntegrationMode() === CustomerIntegrationModeEnum::FULL_ORGANIZATION) {
                $user->setRegistrationStep(RegistrationStepEnum::REGISTER_COMPANY_CONFIGURATION_FULL_ORGANIZATION);
            } else if ($customer->getIntegrationMode() === CustomerIntegrationModeEnum::SELECTED_ACCOUNTS) {
                $user->setRegistrationStep(RegistrationStepEnum::REGISTER_COMPANY_CONFIGURATION_SELECTED_ACCOUNT);
            } else {
                $this->addFlash('error', 'Invalid integration mode selected.');
                return $this->render('registration/company_configuration.html.twig', [
                    'user' => $user,
                    'companyConfigurationForm' => $form->createView(),
                ]);
            }
            $entityManager->persist($user);
            $entityManager->flush();

            // Redirect to a success page or home page
            return $this->redirectToRoute('app_home');
        }

        return $this->render('registration/company_configuration.html.twig', [
            'user' => $user,
            'companyConfigurationForm' => $form->createView(),
        ]);
    }
}
