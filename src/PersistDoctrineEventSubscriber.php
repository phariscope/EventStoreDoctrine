<?php

namespace Phariscope\EventStoreDoctrine;

use Phariscope\Event\EventAbstract;
use Phariscope\Event\EventSubscriber;
use Phariscope\EventStore\StoreInterface;

class PersistDoctrineEventSubscriber implements EventSubscriber
{
    public function __construct(private StoreInterface $eventStore = new EventStoreDoctrine())
    {
    }

    public function handle(EventAbstract $aDomainEvent): bool
    {
        $this->eventStore->append($aDomainEvent);
        return true;
    }

    public function isSubscribedTo(EventAbstract $aDomainEvent): bool
    {
        return true;
    }
}
