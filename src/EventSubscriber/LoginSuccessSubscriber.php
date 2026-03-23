<?php

namespace App\EventSubscriber;

use App\Entity\User;
use App\Enum\Registration\RegistrationStepEnum;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\Routing\RouterInterface;
use Symfony\Component\Security\Http\Event\LoginSuccessEvent;

readonly class LoginSuccessSubscriber implements EventSubscriberInterface
{
    public function __construct(
        private RouterInterface $router,
    ) {
    }

    public function onLoginSuccessEvent(LoginSuccessEvent $event): void
    {
        /** @var User $user */
        $user = $event->getUser();

        if ($user->getRegistrationStep() === RegistrationStepEnum::FINISH) {
            return;
        }

        if ($user->getRegistrationStep() === RegistrationStepEnum::REGISTER_COMPANY_INFORMATION) {
            $event->stopPropagation();
            $event->setResponse(new RedirectResponse(
                $this->router->generate(
                    'app_register_company_information',
                )
            ));
        }

        if ($user->getRegistrationStep() === RegistrationStepEnum::REGISTER_COMPANY_CONFIGURATION) {
            $event->stopPropagation();
            $event->setResponse(new RedirectResponse(
                $this->router->generate(
                    'app_register_company_configuration',
                    ['customerId' => $user->getCustomers()->first()->getId()]
                )
            ));
        }
    }

    public static function getSubscribedEvents(): array
    {
        return [
            LoginSuccessEvent::class => 'onLoginSuccessEvent',
        ];
    }
}
