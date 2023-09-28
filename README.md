# Installation

```console
composer require phariscope/event-store
```

# Usage

* first, be sure to have DATABASE_URL env var correctly initialized
* first add the PersistEventSubscriber to the listeners as soon as you want

Enjoy. And observe an 'events' table which contains all published events.

```php
    // Assume env var is intiailized.
    // Example DATABASE_URL=mysql://root:pwd1234@mariadb:3306/ap-prod?serverVersion=mariadb-10.9.3&amp;charset=utf8mb4

    $store = new EventStoreDoctrine();
    $subscriber = new PersistDoctrineEventSubscriber($store);
    EventPublisher::instance()->subscribe($subscriber);
```

# To Contribut to pharsicope/EventStoreDoctrine

## Requirements

* docker
* git

## Install

* git clone git@github.com:phariscope/EventStoreDoctrine.git

## Unit test

```console
bin/phpunit
```

with Test Developpment Driven (thanks Kent Beck and the others), good practices (thanks R.Martin and the others)

## Quality

* phpcs PSR12
* phpstan level 9
* coverage 100%
* infection MSI >99%

Quick check with:
```console
./codecheck
```

Check coverage with:
```console
bin/phpunit --coverage-html var
```
and view 'var/index.html' with your browser

Check infection with:
```console
bin/infection
```
and view 'var/infection.html' with your browser