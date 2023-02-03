<?php

namespace Wherd\Spec\Database;

use Wherd\Database\Connection;
use Wherd\Database\Fetch;

describe('Describes database connection', function () {
    beforeAll(fn () => $this->instance = new Connection('sqlite::memory:'));

    it('gets a valid pdo resource', function () {
        expect($this->instance->getPdo())->toBeAnInstanceOf('PDO');
    });

    it('should execute queries', function () {
        /** @var Connection */
        $instance = $this->instance;
        $instance->prepare('CREATE TABLE users (username TEXT, password TEXT)')->execute();
        $query = $instance->prepare('INSERT INTO users (username, password) VALUES (?, ?)');

        $query->execute('wherd', 'teste');
        expect($instance->getLastInsertId())->toBe(1);

        $query->execute('nadal', 'teste');
        expect($instance->getLastInsertId())->toBe(2);

        $query->close();
    });

    it('should call execute method as function', function () {
        /** @var Connection */
        $instance = $this->instance;
        $query = $instance->prepare('INSERT INTO users (username, password) VALUES (?, ?)');

        $query('wherd2', 'teste');
        expect($instance->getLastInsertId())->toBe(3);

        $query('nadal2', 'teste');
        expect($instance->getLastInsertId())->toBe(4);

        $query->close();
    });

    it('should fetch column', function () {
        /** @var Connection */
        $instance = $this->instance;
        $query = $instance->prepare('SELECT * FROM users');

        $query->as(Fetch::Column);

        expect($query->fetch())->toBe('wherd');
        expect($query->fetch())->toBe('nadal');

        $query->close();
    });

    it('should fetch KeyValuePair', function () {
        /** @var Connection */
        $instance = $this->instance;
        $query = $instance
            ->prepare('SELECT * FROM users')
            ->as(Fetch::KeyValuePair)
        ;

        expect($query->fetchAll())->toContainKeys(['wherd', 'nadal']);
        $query->close();
    });

    it('should loop columns', function () {
        /** @var Connection */
        $instance = $this->instance;
        $users = $instance
            ->prepare('SELECT * FROM users')
            ->as(Fetch::Column)
        ;

        foreach ($users as $user) {
            expect($user)->toBe('wherd');
            break;
        }

        $users->close();
    });
});
