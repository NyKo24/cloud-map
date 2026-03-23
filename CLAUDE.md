# CLAUDE.md

This file provides guidance to Claude Code (claude.ai/code) when working with code in this repository.

## Project Overview

Symfony 7.3 web application for AWS resource mapping. Crawls and catalogs AWS infrastructure (VPCs, Lambda functions, accounts) across customer AWS organizations. Multi-tenant: each Customer has Users and AwsAccounts. Deployable locally via Docker or to AWS Lambda via Bref.

## Key Commands

All PHP commands must run inside the Docker container with `docker compose exec app` prefix.

```bash
# Local environment
docker compose up -d

# Dependencies
composer install
npm install

# Assets
npm run dev          # Build once
npm run watch        # Watch mode
npm run build        # Production build

# Database
docker compose exec app php bin/console doctrine:migrations:migrate
docker compose exec app php bin/console make:migration
docker compose exec app php bin/console doctrine:fixtures:load

# Testing
docker compose exec app php bin/phpunit
docker compose exec app php bin/phpunit tests/path/to/TestFile.php

# AWS crawling
docker compose exec app php bin/console aws:crawl-all

# Serverless deployment
serverless deploy
serverless invoke --function console --data '{"cli":"doctrine:migrations:status"}'
```

## Architecture

### Crawler System

The core domain logic lives in `src/Crawler/`. Crawlers discover AWS resources via the AWS SDK and persist them as Doctrine entities using the Symfony Serializer for denormalization.

- `AWSCrawlerInterface` — tagged with `#[AutoconfigureTag('crawler.aws')]`. All crawlers auto-register via this tag.
- `AWSBaseCrawler` — abstract base providing `ManagerRegistry`, `EntityManager`, `Serializer`, and `Denormalizer` via constructor injection.
- `AWSCrawlerManager` — orchestrator. Collects all tagged crawlers via `#[AutowireIterator('crawler.aws')]`, iterates customer accounts, assumes IAM roles via STS, then runs each crawler per region.
- Crawl flow: `AwsCrawlCommand` -> `AWSCrawlerManager::crawl(Customer)` -> STS assumeRole per account -> each `AWSCrawlerInterface::crawl()` per region.

**To add a new AWS resource crawler**: create a class extending `AWSBaseCrawler`, implement `AWSCrawlerInterface::crawl()`. It will be auto-discovered via the tag. Create matching Entity and Repository.

### Custom Serialization

AWS API responses are denormalized directly into Doctrine entities using Symfony Serializer with custom denormalizers (registered at priority 100 in `services.yaml`):

- `AwsApiDateTimeResultDenormalizer` — converts AWS SDK `DateTimeResult` objects to PHP `DateTime`.
- `DoctrineCollectionDenormalizer` — handles denormalization into `Doctrine\Common\Collections\Collection` by inspecting ORM mapping attributes to resolve target entity classes. Requires `object_context` in the serializer context.

Entity properties use `#[SerializedName('AwsFieldName')]` to map AWS API field names (PascalCase) to PHP properties.

### Multi-Step Registration Flow

Registration is a state machine driven by `RegistrationStepEnum` on the User entity. `LoginSuccessSubscriber` intercepts login and redirects users to their current registration step if incomplete. Steps: email verification -> company info -> integration mode selection -> mode-specific configuration (full org or selected accounts).

### Controller Patterns

- `/app/*` routes require authentication (`access_control: { path: ^/app, roles: IS_AUTHENTICATED }`).
- List views use a two-route pattern: a parent route renders the page shell, a `_frame` route renders the data table (for Turbo Frames). Search forms + KnpPaginator handle filtering/pagination.
- Export routes serialize query results to CSV using Symfony Serializer with serialization `#[Groups]`.

### Entity Relationships

- `Customer` <-> `User` (many-to-many)
- `Customer` -> `AwsAccount` (one-to-many)
- `Customer` -> `CrawlVersion` (one-to-many, tracks crawl sessions)
- `CrawlVersion` -> `Vpc`, `LambdaFunction`, `AwsAccount` (one-to-many, groups resources per crawl)
- AWS entities use `TimestampableEntity` trait (Gedmo) for automatic `createdAt`/`updatedAt`

### Frontend

- Webpack Encore with single `app` entry point
- TailwindCSS 4.x via PostCSS
- Flowbite component library (`tales-from-a-dev/flowbite-bundle`)
- Stimulus controllers in `assets/controllers/`
- Twig templates: `base.html.twig` (app), `base_landing_page.html.twig` (public), `base_login.html.twig` (auth)
- Partials in `templates/_partial/`, macros in `templates/_macro/`

## Doctrine Entity Conventions

- **No `type: 'integer'`** on `#[ORM\Id]` columns — always integer by default.
- **No `#[ORM\Table()]`** attribute — Doctrine infers table names from entity class names.
- **Always create a Repository** for each entity:
  - Mirror the entity namespace: `App\Entity\AWS\Lambda\LambdaFunction` -> `App\Repository\AWS\Lambda\LambdaFunctionRepository`
  - Extend `ServiceEntityRepository` with `@extends ServiceEntityRepository<EntityName>` PHPDoc
  - Reference in entity: `#[ORM\Entity(repositoryClass: XRepository::class)]`

## Docker Services

| Service | Port | Purpose |
|---------|------|---------|
| app | 8000 | Symfony application |
| mysql | 3006 | MySQL 8.0 (user: app, pass: app, db: app) |
| pma | 8080 | phpMyAdmin |
| dynamodb | 8001 | Local DynamoDB |
| dynamodb-admin | 8002 | DynamoDB admin UI |
| swagger | 8100 | API docs (from `doc/swagger.yaml`) |
| mailcatcher | 1080 (web), 1025 (SMTP) | Email testing |
