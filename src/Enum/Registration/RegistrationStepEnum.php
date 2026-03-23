<?php

namespace App\Enum\Registration;

enum RegistrationStepEnum: string
{
    case START_NEED_VERIFY_EMAIL = 'start_need_verify_email';
    case REGISTER_COMPANY_INFORMATION = 'register_company_information';
    case REGISTER_COMPANY_CONFIGURATION = 'register_company_configuration';
    case REGISTER_COMPANY_CONFIGURATION_FULL_ORGANIZATION = 'register_company_configuration_full_organization';
    case REGISTER_COMPANY_CONFIGURATION_SELECTED_ACCOUNT = 'register_company_configuration_selected_account';

    case FINISH = 'finish';

    public function getNextStep(): ?self
    {
        return match ($this) {
            self::START_NEED_VERIFY_EMAIL => self::REGISTER_COMPANY_INFORMATION,
            self::REGISTER_COMPANY_INFORMATION => self::REGISTER_COMPANY_CONFIGURATION,
            self::REGISTER_COMPANY_CONFIGURATION => self::FINISH,
            self::FINISH => null,
        };
    }
}
