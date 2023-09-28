<?php

namespace Phariscope\EventStoreDoctrine\Tests;

use Phariscope\Event\EventAbstract;
use Phariscope\EventStore\StoredEvent;
use Phariscope\EventStoreDoctrine\EventStoreDoctrine;

class EventStoreDoctrineTestPurpose extends EventStoreDoctrine
{
    public function persistForcingEventIdForTestPurpose(EventAbstract $event, int $id): void
    {
        $storedEvent = new StoredEvent(
            $event,
            $id
        );
        $this->getEntityManager()->persist($storedEvent);
        $this->getEntityManager()->flush();
        $autoId = $storedEvent->eventId();

        $stm = $this->getEntityManager()->getConnection()->prepare(
            "update events set event_id = $id where event_id = $autoId"
        );
        $stm ->executeQuery();
        $this->getEntityManager()->flush();
        $this->getEntityManager()->detach($storedEvent);
    }

    public function flush(): void
    {
        $this->getEntityManager()->flush();
    }

    public function getenvSafe(string $envname): string
    {
        return parent::getenvSafe($envname);
    }
}
