<?php

namespace Phariscope\EventStoreDoctrine\Tests;

use Doctrine\ORM\EntityManager;
use Phariscope\EventStore\Exceptions\EventNotFoundException;
use PHPUnit\Framework\TestCase;
use Safe\DateTimeImmutable;

class EventStoreDoctrineTest extends TestCase
{
    /**
     * @var EntityManager
     */
    protected $entityManager = null;

    protected EventStoreDoctrineTestPurpose $store;

    protected function setUp(): void
    {
        putenv('DATABASE_URL=sqlite:///:memory:?cache=shared');
        $this->store = new EventStoreDoctrineTestPurpose();
    }

    public function testLastEvent(): void
    {
        $this->makeEventSent('premierEvent', new DateTimeImmutable("-10 seconds"));
        $this->makeEventSent('unDernierEvenement', new DateTimeImmutable("-5 seconds"));
        $storedEvent = $this->store->lastEvent();

        $this->assertStringContainsString('unDernierEvenement', $storedEvent->getEventBody());
    }

    public function testAllStoredEventsSinceByEventId(): void
    {
        $event = $this->makeEventSent('unEvenementAPublier');
        $last = $this->store->lastEvent();
        $storedEvents = $this->store->allStoredEventsSince($last->eventId());
        $this->assertStringContainsString('unEvenementAPublier', $storedEvents[0]->getEventBody());
    }

    public function testAllStoredEventsSinceByOccurredOn(): void
    {
        $debut = new DateTimeImmutable("-100 days");
        for ($i = 1; $i <= 15; $i++) {
            $this->makeEventSent("unEvenementAPublier" . $i, new DateTimeImmutable("- " . (20 - $i) . " days"));
        }
        $events = $this->store->allStoredEventsSince($debut);

        $this->assertEquals(15, count($events));
        $this->assertStringContainsString('unEvenementAPublier1', $events[0]->getEventBody());
        $this->assertStringContainsString('unEvenementAPublier2', $events[1]->getEventBody());
        $this->assertStringContainsString('unEvenementAPublier15', $events[14]->getEventBody());
    }

    private function makeEventSent(
        string $chooseYourId,
        DateTimeImmutable $occurredOn = new DateTimeImmutable(),
        ?int $eventStoreId = null
    ): EventSent {
        $event = new EventSent($chooseYourId, $occurredOn);
        if ($eventStoreId == null) {
            $this->store->append($event);
        } else {
            $this->store->persistForcingEventIdForTestPurpose($event, $eventStoreId);
        }
        $this->store->flush();
        return $event;
    }

    public function testAllStoredEventsSinceWithoutOldEvent(): void
    {
        $this->makeEventSent('unVielEvent_1');
        $this->makeEventSent('unVielEvent_2');
        $this->makeEventSent('unVielEvent_3');
        $this->makeEventSent('unNouveau_1');
        $unNouveau_1_ID = $this->store->lastEvent()->eventId();
        $this->makeEventSent('unNouveau_2');

        $storedEvents = $this->store->allStoredEventsSince($unNouveau_1_ID);

        $this->assertSame(2, count($storedEvents));
        $this->assertStringContainsString('unNouveau_1', $storedEvents[0]->getEventBody());
        $this->assertStringContainsString('unNouveau_2', $storedEvents[1]->getEventBody());
    }

    public function testLastEventWithoutStoredEvent(): void
    {
        $this->expectException(EventNotFoundException::class);
        $this->expectExceptionMessage("0 stored event found");
        $last = $this->store->lastEvent();
    }

    public function testOrderBy(): void
    {
        $this->makeEventSent('unIdToto', $old = new DateTimeImmutable("-3 days"), 13);
        $this->makeEventSent('unIdTata', new DateTimeImmutable("-2 days"), 11);
        $this->makeEventSent('unIdTiti', new DateTimeImmutable("-1 days"), 12);

        $storedEvents = $this->store->allStoredEventsSince($old);

        $this->assertSame(3, count($storedEvents));
        $this->assertEquals(11, $storedEvents[0]->eventId());
        $this->assertEquals(12, $storedEvents[1]->eventId());
        $this->assertEquals(13, $storedEvents[2]->eventId());
    }

    public function testEnvSafe(): void
    {
        $this->expectExceptionMessage("DATABASE_URL must be initialised as a string env variable");
        $this->store = new EventStoreDoctrineTestPurpose();
        $this->store->getenvSafe("Bad");
    }
}
