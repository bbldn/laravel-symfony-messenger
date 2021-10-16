<?php

namespace BBLDN\Laravel\Messenger\Serializers;

use Symfony\Component\Serializer\Serializer;
use Symfony\Component\Serializer\Encoder\JsonEncoder;
use Symfony\Component\Serializer\Normalizer\ArrayDenormalizer;
use Symfony\Component\Serializer\Normalizer\DataUriNormalizer;
use Symfony\Component\Serializer\Normalizer\DateTimeNormalizer;
use Symfony\Component\Serializer\Normalizer\PropertyNormalizer;
use Symfony\Component\Messenger\Transport\Serialization\Serializer as Base;

final class TransportJsonSerializer extends Base
{
    public function __construct()
    {
        $normalizers = [
            new DataUriNormalizer(),
            new ArrayDenormalizer(),
            new DateTimeNormalizer(),
            new PropertyNormalizer(),
        ];

        $serializer = new Serializer($normalizers, [new JsonEncoder()]);

        parent::__construct($serializer);
    }
}