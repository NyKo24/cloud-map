<?php
namespace App\Serializer;

use Doctrine\Common\Collections\ArrayCollection;
use Symfony\Component\DependencyInjection\Attribute\Autowire;
use Symfony\Component\Serializer\Normalizer\DenormalizerInterface;
use Symfony\Component\Serializer\Normalizer\NormalizerInterface;
use Symfony\Component\Serializer\Normalizer\ObjectNormalizer;
use Symfony\Component\PropertyInfo\Extractor\ReflectionExtractor;
use Symfony\Component\PropertyInfo\PropertyTypeExtractorInterface;
use Symfony\Component\Serializer\Serializer;
use Symfony\Component\Serializer\SerializerInterface;

class DoctrineCollectionDenormalizer implements DenormalizerInterface
{
    private PropertyTypeExtractorInterface $propertyTypeExtractor;

    public function __construct(
        #[Autowire(service: 'serializer.normalizer.object')]
        private readonly NormalizerInterface $normalizer,
    )
    {
        $this->propertyTypeExtractor = new ReflectionExtractor();
    }

    public function supportsDenormalization(mixed $data, string $type, ?string $format = null, array $context = []): bool
    {
        return is_array($data) &&
            $type == 'Doctrine\Common\Collections\Collection';
    }

    public function denormalize(mixed $data, string $type, ?string $format = null, array $context = []): mixed
    {
        $objectTargetFQDN = $context['object_context'];
        if (!$objectTargetFQDN) {
            throw new \InvalidArgumentException('The "object_context" key must be set in the context array.');
        }
        if (!class_exists($objectTargetFQDN)) {
            throw new \InvalidArgumentException(sprintf('The class "%s" does not exist.', $objectTargetFQDN));
        }
        $reflectionClass = new \ReflectionClass($objectTargetFQDN);

        $property = $reflectionClass->getProperty(lcfirst($context['deserialization_path']));
        foreach ($property->getAttributes() as $attribute) {
            if (str_starts_with($attribute->getName(), 'Doctrine\ORM\Mapping\\')) {
                $targetClass = $attribute->getArguments()['targetEntity'];
                $collection = new ArrayCollection();
                foreach ($data as $item) {
                    $collection->add($this->normalizer->denormalize($item, $targetClass, $format, $context));
                }
                return $collection;
            }
        }
        return null;
        dd($data, $type, $format, $context, $property ?? null);
    }

    public function getSupportedTypes(?string $format): array
    {
        return [
            'Doctrine\Common\Collections\Collection' => true,
        ];
    }
}