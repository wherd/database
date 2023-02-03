<?php

declare(strict_types=1);

namespace Wherd\Database;

use PDO;

enum Fetch: int
{
    case Column = PDO::FETCH_COLUMN;
    case Assoc = PDO::FETCH_ASSOC;
    case ToClass = PDO::FETCH_CLASS;
    case ToObject = PDO::FETCH_INTO;
    case KeyValuePair = PDO::FETCH_KEY_PAIR;
    case KeyRowPair = PDO::FETCH_UNIQUE;
    case GroupRow = PDO::FETCH_GROUP;
    case GroupColumn = PDO::FETCH_GROUP|PDO::FETCH_COLUMN;
}
