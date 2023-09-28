<?php

namespace Phariscope\EventStoreDoctrine;

use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\EntityManager;
use Doctrine\ORM\EntityRepository;
use Doctrine\ORM\Mapping\ClassMetadata;
use Phariscope\Event\EventAbstract;
use Phariscope\EventStore\Exceptions\EventNotFoundException;
use Phariscope\EventStore\StoreInterface;
use DateTimeImmutable;
use Doctrine\DBAL\DriverManager;
use Doctrine\DBAL\Types\Type;
use Doctrine\ORM\Mapping\Driver\SimplifiedXmlDriver;
use Doctrine\ORM\ORMSetup;
use Doctrine\ORM\Tools\SchemaTool;
use Phariscope\EventStore\StoredEvent;
use Phariscope\EventStoreDoctrine\Types\DateTimeWithMicrosecondsType;

/**
 * @extends EntityRepository<StoredEvent>
 */
class EventStoreDoctrine extends EntityRepository implements StoreInterface
{
    protected const DATABASE_URL_ENV_NAME = 'DATABASE_URL';

    public function __construct()
    {
        $this->createSpecificTypes();

        $em = $this->createEntityManager();

        /** @var ClassMetadata<StoredEvent> */
        $classMD = $em->getClassMetadata(StoredEvent::class);
        parent::__construct($em, $classMD);

        $this->createSchemaIfNeeded($classMD);
    }

    private function createSpecificTypes(): void
    {
        if (!Type::hasType('date_immutable_us')) {
            Type::addType(
                'date_immutable_us',
                DateTimeWithMicrosecondsType::class
            );
        }
    }

    private function createEntityManager(): EntityManager
    {
        $params = ['url' => $this->getenvSafe(self::DATABASE_URL_ENV_NAME)];

        $xmlEventFolder = __DIR__ . '/Mapping';
        $driver = new SimplifiedXmlDriver(
            [
                $xmlEventFolder => 'Phariscope\EventStore',
            ]
        );
        $config = ORMSetup::createConfiguration();
        $config->setMetadataDriverImpl($driver);

        $connection = DriverManager::getConnection($params);

        return new EntityManager($connection, $config);
    }

    protected function getenvSafe(string $envName): string
    {
        $value = getenv($envName);
        if (is_string($value)) {
            return $value;
        }
        throw new \Exception("DATABASE_URL must be initialised as a string env variable");
    }

    /**
     *
     * @param ClassMetadata<StoredEvent> $classMD
     */
    private function createSchemaIfNeeded(ClassMetadata $classMD): void
    {
        $em = $this->getEntityManager();
        $schemaManager = $em->getConnection()->createSchemaManager();
        $schema = $schemaManager->introspectSchema();
        if (!$schema->hasTable('events')) {
            $tool = new SchemaTool($em);
            $tool->createSchema([$classMD]);
        }
    }

    /**
     * No flush here. Flush outside please.
     * @param EventAbstract $event
     * @return void
     */
    public function append(EventAbstract $event): void
    {
        $storedEvent = new StoredEvent(
            $event
        );
        $this->getEntityManager()->persist($storedEvent);
    }

    public function lastEvent(): StoredEvent
    {
        $query = $this->createQueryBuilder('e');
        $query->setMaxResults(1);
        $query->orderBy('e.eventId', 'DESC');
        /** @var array<int,StoredEvent> $result */
        $result = $query->getQuery()->getResult();
        $resultNumber = count($result);
        if ($resultNumber != 1) {
            throw new EventNotFoundException(sprintf("%d stored event found", $resultNumber));
        }
        return $result[0];
    }

    /**
     * @return array<int,StoredEvent>
     */
    public function allStoredEventsSince(DateTimeImmutable|int $storedEventId): array
    {
        $query = $this->createQueryBuilder('e');

        if ($storedEventId instanceof DateTimeImmutable) {
            $query->where('e.occurredOn >= :depuis')
                ->setParameter('depuis', $storedEventId, Types::DATE_IMMUTABLE);
        } else {
            $query->where('e.eventId >= :depuis');
        }

        $query->setParameter('depuis', $storedEventId);

        /** @var array<int,StoredEvent> $result */
        $result = $query->getQuery()->getResult();

        return $result;
    }
}
