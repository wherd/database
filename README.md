# Database

Yet another database wrapper.

## Installation

Install using composer:

```bash
composer require wherd/database
```

# Usage

```php
use Wherd\Database\Connection;
use Wherd\Database\Fetch;

$db = new Connection('sqlite::memory:');
$db->prepare('CREATE TABLE users (username TEXT, email TEXT, password TEXT)')->execute();

$stmt = $db->prepare('INSERT INTO users (username, email, password) VALUES (?, ?, ?)');
$stmt->execute('wherd', 'ola@wherd.name', '*****');
// Can allso invoke execute method directly
// $stmt('wherd', 'ola@wherd.name', '*****');

$users = $db
    ->prepare('SELECT username, email FROM users')
    ->as(Fetch::KeyValuePair)
    ->fetchAll() // Optional - Statement is traversables
;

foreach ($users as $username => $email) {
    echo $username, ' - ', $email;
}
```