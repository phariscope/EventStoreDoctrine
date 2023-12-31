# Installation

```console
composer require phariscope/event-store
```

# Usage

1. First, make sure to have the DATABASE_URL environment variable correctly initialized.
2. Second, add the PersistEventSubscriber to the listeners as soon as you want.

Enjoy, and observe an 'events' table that contains all published events.

```php
    // Assume env var is intiailized.
    // Example DATABASE_URL=mysql://root:pwd1234@mariadb:3306/ap-prod?serverVersion=mariadb-10.9.3&amp;charset=utf8mb4

    $store = new EventStoreDoctrine();
    $subscriber = new PersistDoctrineEventSubscriber($store);
    EventPublisher::instance()->subscribe($subscriber); // use your own EventPublisherFacade could be a good idea
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

Using Test-Driven Development (TDD) principles (thanks to Kent Beck and others), following good practices (thanks to Uncle Bob and others) and the great book 'DDD in PHP' by C. Buenosvinos, C. Soronellas, K. Akbary

## Quality

* phpcs PSR12
* phpstan level 9
* coverage 100%
* infection MSI 100%

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