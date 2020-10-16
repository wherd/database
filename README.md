# Wherd Database

Simple database layer

## Installation

Install using composer:

```
composer require wherd/database
```

## Usage

To create a new database connection just create a new instance of `Wherd\Database\Connection` class:

```php
$database = new Wherd\Database\Connection($dsn, $user, $password); // the same arguments as uses PDO
```

Connection allows you to easily query your database by calling `query` method:

```php
$database->query('INSERT INTO posts (title, content) VALUES (?, ?)')
    ->execute('first post', 'first post content')
    ->execute('second post', 'second post content')
    ->execute('third post', 'third post content');

$database->query('UPDATE posts SET title=? WHERE id=?')->execute($title, $id);
$database->query('SELECT * FROM posts WHERE id=?', 123)->fetchPairs('id');
```
