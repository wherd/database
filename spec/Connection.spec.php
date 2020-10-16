<?php

namespace Wherd\Database\Spec;

use \Wherd\Database\Connection;

describe('Connection', function() {
    given('instance', function() {
        $db =  new Connection('sqlite::memory:');

        $pdo = $db->getPdo();
        $date = date('Y-m-d H:i:s');

        $pdo->query('CREATE TABLE `posts` (`id` INTEGER PRIMARY KEY AUTOINCREMENT NOT NULL, `title` TEXT NOT NULL, `content` TEXT NOT NULL, `created` DATE);');
        $pdo->query('INSERT INTO `posts` (`title`, `content`, `created`) VALUES ("first post", "first post content", date("now"));');
        $pdo->query('INSERT INTO `posts` (`title`, `content`, `created`) VALUES ("second post", "second post content", date("now"));');
        $pdo->query('INSERT INTO `posts` (`title`, `content`, `created`) VALUES ("third post", "third post content", date("now"));');

        return $db;
    });

    it('should return be an instance of pdo', function() {
        expect($this->instance->getPdo())->toBeAnInstanceOf('PDO');
    });

    it('should insert single row of data', function() {
        /** @var Connection */
        $db = $this->instance;

        $query = $db->query(
            'INSERT INTO `posts` (`title`, `content`, `created`) VALUES (?, ?, ?)',
            'Lorem ipsum dolor sit amet',
            'In id interdum dolor. Nulla egestas elit et elit molestie, vitae placerat nulla sagittis.',
            date('Y-m-d H:i:s')
        )->execute();

        expect($db->getInsertId())->toBe('4');
        expect($query->getRowCount())->toBe(1);
    });

    it('should insert multiple rows of data using the same statement', function() {
        /** @var Connection */
        $db = $this->instance;
        $db->beginTransaction();

        $query = $db->query('INSERT INTO `posts` (`title`, `content`, `created`) VALUES (?, ?, ?)');
        expect($query)->toBeAnInstanceOf(\Wherd\Database\ResultSet::class);
        
        $date = date('Y-m-d H:i:s');
        $data = [
            ['Suspendisse et orci quis nisi finibus', 'Nunc ut est metus. Sed interdum erat vel lacus rutrum tincidunt. Nullam ex nulla, molestie a luctus id, tincidunt vitae urna.', $date],
            ['Cras luctus nisl ligula', 'Aenean id molestie dolor. Quisque pulvinar sollicitudin malesuada. Nam at erat sed leo tempus pharetra eu et velit.', $date],
            ['Morbi et ornare libero', 'In mollis suscipit turpis sit amet aliquam. Pellentesque placerat odio quis magna ullamcorper sagittis. Mauris in arcu vel risus.', $date],
        ];

        $lastInsertId = 0;

        foreach ($data as $row) {
            $query->execute(...$row);
            $id = $db->getInsertId();

            expect($id)->toBeGreaterThan($lastInsertId);
            $lastInsertId = $id;
        }

        $db->commit();
    });

    it('should retrive data from table', function() {
        /** @var Connection */
        $db = $this->instance;

        $query = $db->query('SELECT * FROM `posts`');
        expect($query)->toBeAnInstanceOf(\Wherd\Database\ResultSet::class);

        while (($row = $query->fetch())) {
            expect($row)->toBeA('array');
            expect(count($row))->toBe($query->getColumnCount());
            expect(array_keys($row))->toBe(['id', 'title', 'content', 'created']);
        }
    });

    it('should retrive all data from table', function() {
        /** @var Connection */
        $db = $this->instance;

        $query = $db->query('SELECT * FROM `posts`');
        expect($query)->toBeAnInstanceOf(\Wherd\Database\ResultSet::class);

        $rows = $query->fetchAll();
        expect($rows)->toBeA('array');
        expect(count($rows))->toBe(3);

        foreach ($rows as $row) {
            expect($row)->toBeA('array');
            expect(count($row))->toBe($query->getColumnCount());
            expect(array_keys($row))->toBe(['id', 'title', 'content', 'created']);
        }
    });

    it('should retrive all data indexed by id', function() {
        /** @var Connection */
        $db = $this->instance;

        $query = $db->query('SELECT * FROM `posts`');
        expect($query)->toBeAnInstanceOf(\Wherd\Database\ResultSet::class);

        $rows = $query->fetchPairs('id');
        expect($rows)->toBeA('array');
        expect(count($rows))->toBe(3);

        foreach ($rows as $id => $row) {
            expect($row)->toBeA('array');
            expect(count($row))->toBe($query->getColumnCount());
            expect(array_keys($row))->toBe(['id', 'title', 'content', 'created']);
            expect((int) $id)->toBe((int) $row['id']);
        }
    });

    it('should retrive all only titles', function() {
        /** @var Connection */
        $db = $this->instance;

        $query = $db->query('SELECT * FROM `posts`');
        expect($query)->toBeAnInstanceOf(\Wherd\Database\ResultSet::class);

        $rows = $query->fetchPairs(null, 'title');
        expect($rows)->toBeA('array');
        expect(count($rows))->toBe(3);

        foreach ($rows as $row) {
            expect($row)->toBeA('string');
        }
    });
});
