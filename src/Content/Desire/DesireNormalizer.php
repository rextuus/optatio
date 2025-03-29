<?php

declare(strict_types=1);

namespace App\Content\Desire;

use App\Entity\Desire;
use Symfony\Component\Serializer\Normalizer\DenormalizerInterface;
use Symfony\Component\Serializer\Normalizer\NormalizerInterface;

/**
 * @author Wolfgang Hinzmann <wolfgang.hinzmann@doccheck.com>
 * @license 2025 DocCheck Community GmbH
 */
class DesireNormalizer implements NormalizerInterface, DenormalizerInterface
{
    public function supportsNormalization($data, string $format = null): bool
    {
        return $data instanceof Desire;
    }

    public function normalize($object, string $format = null, array $context = []): array
    {
        /** @var Desire $object */
        return [
            'id' => $object->getId(),
            'name' => $object->getName(),
        ];
    }

    public function supportsDenormalization($data, string $type, string $format = null): bool
    {
        return $type === Desire::class;
    }

    public function denormalize($data, string $type, string $format = null, array $context = [])
    {
        $desire = new Desire();
        $desire->setId($data['id']);
        $desire->setName($data['name']);

        return $desire;
    }
}
