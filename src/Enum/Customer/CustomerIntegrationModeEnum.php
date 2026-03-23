<?php

namespace App\Enum\Customer;

enum CustomerIntegrationModeEnum: string
{
    case FULL_ORGANIZATION = 'full_organization';
    case SELECTED_ACCOUNTS = 'selected_accounts';
}
