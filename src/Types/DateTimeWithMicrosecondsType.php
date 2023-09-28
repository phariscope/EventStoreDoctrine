<?php

namespace Phariscope\EventStoreDoctrine\Types;

use Doctrine\DBAL\Platforms\AbstractPlatform;
use Doctrine\DBAL\Platforms\PostgreSQLPlatform;
use Doctrine\DBAL\Types\ConversionException;
use Doctrine\DBAL\Types\Type;

class DateTimeWithMicrosecondsType extends Type
{
    private const TYPENAME = 'datetime_immutable_us';

    public function getSQLDeclaration(array $column, AbstractPlatform $platform): string
    {
        if (isset($column['version']) && $column['version'] === true) {
            return 'TIMESTAMP';
        }

        if ($platform instanceof PostgreSqlPlatform) {
            return 'TIMESTAMP(6) WITHOUT TIME ZONE';
        }

        return 'DATETIME(6)';
    }

    public function convertToPHPValue($value, AbstractPlatform $platform): mixed
    {
        if ($value === null) {
            return $value;
        }

        /** @var string $value */
        if (str_contains($value, '.')) {
            return \DateTimeImmutable::createFromFormat('Y-m-d H:i:s.u', $value);
        }

        return \DateTimeImmutable::createFromFormat('Y-m-d H:i:s', $value);
    }

    public function convertToDatabaseValue($value, AbstractPlatform $platform): mixed
    {
        if (null === $value) {
            return null;
        }

        if ($value instanceof \DateTimeInterface) {
            return $value->format('Y-m-d H:i:s.u');
        }

        throw ConversionException::conversionFailedInvalidType(
            $value,
            $this->getName(),
            ['null', 'DateTimeImmutable']
        );
    }

    public function getName(): string
    {
        return self::TYPENAME;
    }

    public function requiresSQLCommentHint(AbstractPlatform $platform): bool
    {
        return true;
    }
}
