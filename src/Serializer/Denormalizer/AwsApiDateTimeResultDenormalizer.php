<?php

namespace App\Serializer\Denormalizer;

use Aws\Api\DateTimeResult;
use Symfony\Component\Serializer\Normalizer\DenormalizerInterface;

class AwsApiDateTimeResultDenormalizer implements DenormalizerInterface
{

    /**
     * @param DateTimeResult $data
     * @param string $type
     * @param string|null $format
     * @param array $context
     * @return mixed
     * @throws \Exception
     */
    public function denormalize(mixed $data, string $type, ?string $format = null, array $context = []): mixed
    {
        // The AWS SDK for PHP returns a DateTimeResult object
        // Convert the DateTimeResult to the PHP DateTime object
        return \DateTime::createFromFormat('U', $data->getTimestamp());
    }

    public function supportsDenormalization(mixed $data, string $type, ?string $format = null, array $context = []): bool
    {
        return $data instanceof DateTimeResult;
    }

    public function getSupportedTypes(?string $format): array
    {
        return [
            \DateTime::class => false,
        ];
    }
}
