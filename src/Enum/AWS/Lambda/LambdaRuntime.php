<?php

namespace App\Enum\AWS\Lambda;

enum LambdaRuntime: string
{
    case NODEJS18_X = 'nodejs18.x';
    case NODEJS20_X = 'nodejs20.x';
    case PYTHON3_9 = 'python3.9';
    case PYTHON3_10 = 'python3.10';
    case PYTHON3_11 = 'python3.11';
    case PYTHON3_12 = 'python3.12';
    case JAVA8 = 'java8';
    case JAVA8_AL2 = 'java8.al2';
    case JAVA11 = 'java11';
    case JAVA17 = 'java17';
    case JAVA21 = 'java21';
    case DOTNET6 = 'dotnet6';
    case DOTNET8 = 'dotnet8';
    case GO1_X = 'go1.x';
    case RUBY3_2 = 'ruby3.2';
    case RUBY3_3 = 'ruby3.3';
    case PROVIDED = 'provided';
    case PROVIDED_AL2 = 'provided.al2';
    case PROVIDED_AL2023 = 'provided.al2023';
}