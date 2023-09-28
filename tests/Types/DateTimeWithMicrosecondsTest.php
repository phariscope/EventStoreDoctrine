<?php

namespace Phariscope\EventStoreDoctrine\Tests\Types;

use Doctrine\DBAL\Platforms\AbstractPlatform;
use Doctrine\DBAL\Platforms\PostgreSQLPlatform;
use Doctrine\DBAL\Types\ConversionException;
use Phariscope\EventStoreDoctrine\Types\DateTimeWithMicrosecondsType;
use PHPUnit\Framework\TestCase;

class DateTimeWithMicrosecondsTest extends TestCase
{
    private DateTimeWithMicrosecondsType $type;

    protected function setUp(): void
    {
        parent::setUp();
        $this->type = new DateTimeWithMicrosecondsType();
    }

    public function testSqlDeclarationIsTimestampForVersionTrue(): void
    {
        $column = ['version' => true];
        $platform = $this->createMock(AbstractPlatform::class);
        $this->assertSame('TIMESTAMP', $this->type->getSQLDeclaration($column, $platform));
    }

    public function testSqlDeclarationForPostgre(): void
    {
        $column = [];
        $platform = $this->createMock(PostgreSqlPlatform::class);
        $this->assertSame('TIMESTAMP(6) WITHOUT TIME ZONE', $this->type->getSQLDeclaration($column, $platform));
    }

    public function testSqlDeclarationDefaultIsTimestamp6(): void
    {
        $column = [];
        $platform = $this->createMock(AbstractPlatform::class);
        $this->assertSame('DATETIME(6)', $this->type->getSQLDeclaration($column, $platform));
    }

    /**
     * @dataProvider provideTimestamps
     */
    public function testConvertToPhpValue(?string $value, ?\DateTimeImmutable $expected): void
    {
        $platform = $this->createMock(AbstractPlatform::class);
        $this->assertEquals($expected, $this->type->convertToPHPValue($value, $platform));
    }

    /**
     *
     *'null' => "null[]",
     *'with zero microseconds' => "array",
     *'without microseconds' => "array",
     *'with 200 microseconds' => "array"
     * @return array<string,mixed>
     */
    public static function provideTimestamps(): array
    {
        return [
            'null' => [null, null],
            'with zero microseconds' => ['2001-01-03 12:46:18.000', new \DateTimeImmutable('2001-01-03 12:46:18.000')],
            'without microseconds' => ['2001-01-03 12:46:18', new \DateTimeImmutable('2001-01-03 12:46:18.000')],
            'with 200 microseconds' => ['2001-01-03 12:46:18.200', new \DateTimeImmutable('2001-01-03 12:46:18.200')],
        ];
    }

    /**
     * @dataProvider provideDateTimeObject
     */
    public function testConvertToDatabaseValue(?\DateTimeImmutable $value, ?string $expected): void
    {
        $platform = $this->createMock(AbstractPlatform::class);
        $this->assertSame($expected, $this->type->convertToDatabaseValue($value, $platform));
    }

    /**
     *
     *'null' => "null[]",
     *'with zero microseconds' => "array",
     *'without microseconds' => "array",
     *'with 200 microseconds' => "array"
     * @return array<string,mixed>
     */
    public static function provideDateTimeObject(): array
    {
        return [
            'null' => [null, null],
            'with zero microseconds' => [
                new \DateTimeImmutable('2001-01-03 12:46:18.000'),
                '2001-01-03 12:46:18.000000'
            ],
            'without microseconds' => [new \DateTimeImmutable('2001-01-03 12:46:18.000'), '2001-01-03 12:46:18.000000'],
            'with 200 microseconds' => [
                new \DateTimeImmutable('2001-01-03 12:46:18.200'),
                '2001-01-03 12:46:18.200000'
            ],
        ];
    }

    public function testThrowsExceptionIfNotDateTimeOrNullOnConvertToDatabase(): void
    {
        $this->expectException(ConversionException::class);
        $this->expectExceptionMessage(
            "Could not convert PHP value 'not an object' to type datetime_immutable_us. " .
            "Expected one of the following types: null, DateTimeImmutable"
        );
        $platform = $this->createMock(AbstractPlatform::class);
        $this->type->convertToDatabaseValue('not an object', $platform);
    }

    public function testGetName(): void
    {
        $this->assertSame('datetime_immutable_us', $this->type->getName());
    }

    public function testRequiresSqlCommentHint(): void
    {
        $platform = $this->createMock(AbstractPlatform::class);
        $this->assertTrue($this->type->requiresSQLCommentHint($platform));
    }
}
