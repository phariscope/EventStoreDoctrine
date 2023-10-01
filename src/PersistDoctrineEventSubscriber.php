<?php

namespace Phariscope\EventStoreDoctrine;

use Phariscope\EventStore\PersistEventSubscriberAbstract;
use Phariscope\EventStore\StoreInterface;

class PersistDoctrineEventSubscriber extends PersistEventSubscriberAbstract
{
    public function __construct(private StoreInterface $store = new EventStoreDoctrine())
    {
        parent::__construct($store);
    }
}
