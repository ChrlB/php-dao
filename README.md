# PHP DATA ACCESS OBJECT

Simple PDO wrapper for organizing and reusing named prepared statements in MySQL.

## Status

Early-stage / student project — not production-ready yet. MySQL only for now.
Feedback and PRs welcome.

## Requirements

- PHP 8.0+
- `ext-pdo` and `ext-pdo_mysql` enabled

## Installation
run on terminal
```terminal
composer require chrlb/php-dao
```
## Quick Start

```php
require 'vendor/autoload.php';

use Chrlb\PhpDao\PHPDAO;
use PDO;

$conn = new PDO("mysql:host=localhost;dbname=your_db", "your_user", "your_password");
$dao = new PHPDAO($conn);

// Register a named statement
$dao->prepareStatement("getUsers", "SELECT * FROM tbl_users");

// Run it (SELECT)
$users = $dao->executeQuery("getUsers");

// Register and run an UPDATE
$dao->prepareStatement("updateUserName", "UPDATE tbl_users SET name = ? WHERE id = ?");
$affectedRows = $dao->executeUpdate("updateUserName", ["NewName", 1]);
```

## API Reference

| Method | Description |
|---|---|
| `prepareStatement(string $title, string $sql, string $description = "NOT SET"): void` | Prepares and caches a statement under a unique title. |
| `replacePstmt(string $oldTitle, string $title, string $sql, string $description = "NOT SET"): void` | Removes an existing statement and registers a new one in its place. |
| `executeQuery(string $title, array $params = []): array` | Executes a cached SELECT statement, returns matched rows. |
| `executeUpdate(string $title, array $params = []): int` | Executes a cached INSERT/UPDATE/DELETE statement, returns affected row count. |
| `isPstmtExists(string $title): bool` | Checks whether a statement with the given title is registered. |
| `getPstmtDescription(string $title): string` | Returns the description attached to a registered statement. |
| `getAllPstmt(): array` | Returns all currently registered statements. |
| `pstmtParamsCount(string $title): int` | Returns the number of parameters a registered statement expects. |

## Error Handling

Instead of returning `false`/`null` on failure, `PHPDAO` throws exceptions so callers can
handle errors explicitly:

- **`OutOfBoundsException`** — a statement title doesn't exist (or already exists, for duplicates).
- **`InvalidArgumentException`** — invalid SQL at prepare-time, or a parameter count mismatch.
- **`RuntimeException`** — a statement failed during execution (e.g. a database-level error).

```php
try {
    $dao->executeQuery("getUsers");
} catch (OutOfBoundsException $e) {
    echo "Statement not found: " . $e->getMessage();
}
```

## Extensibility

subclasses can override or build on top of `PHPDAO`'s behavior:

```php
class LoggingPHPDAO extends PHPDAO {
    public function executeQuery(string $title, array $params = []): array {
        error_log("Running query: $title");
        return parent::executeQuery($title, $params);
    }
}
```

## License

MIT