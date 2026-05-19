<?php

declare(strict_types=1);

namespace Core\Database;

use PDO;

abstract class Model
{
    // Child models set this to the table they represent, for example "students".
    protected string $table;

    // Most tables use "id" as the primary key, but a child model can override it.
    protected string $primaryKey = 'id';

    // Stores one row of database data inside the model object.
    protected array $attributes = [];

    public function __construct(array $attributes = [])
    {
        // When a row is fetched from the database, it is saved here as model data.
        $this->attributes = $attributes;
    }

    public function __get(string $key): mixed
    {
        // Allows $student->first_name to read from the attributes array.
        return $this->attributes[$key] ?? null;
    }

    public function __set(string $key, mixed $value): void
    {
        // Allows $student->first_name = 'Juan' to update the attributes array.
        $this->attributes[$key] = $value;
    }

    public function toArray(): array
    {
        // Controllers and views mostly use arrays, so models can be converted back.
        return $this->attributes;
    }

    protected static function findRecord(int $id): ?static
    {
        // "new static()" creates the child model that called this method, such as Student.
        $model = new static();

        // Prepared statements keep values separate from SQL and help prevent SQL injection.
        $statement = self::connection()->prepare(
            'SELECT * FROM ' . self::identifier($model->table) . ' WHERE ' . self::identifier($model->primaryKey) . ' = :id LIMIT 1'
        );
        $statement->execute(['id' => $id]);
        $row = $statement->fetch();

        // Hydrate the row into a model object, or return null when no row exists.
        return $row ? new static($row) : null;
    }

    protected static function firstWhere(string $field, mixed $value): ?static
    {
        $model = new static();

        // Used for lookups like finding one user by email.
        $statement = self::connection()->prepare(
            'SELECT * FROM ' . self::identifier($model->table) . ' WHERE ' . self::identifier($field) . ' = :value LIMIT 1'
        );
        $statement->execute(['value' => $value]);
        $row = $statement->fetch();

        return $row ? new static($row) : null;
    }

    protected static function latestRecords(int $limit = 5, string $orderBy = 'created_at'): array
    {
        $model = new static();

        // LIMIT is bound as an integer because some database drivers reject it as a string.
        $statement = self::connection()->prepare(
            'SELECT * FROM ' . self::identifier($model->table) . ' ORDER BY ' . self::identifier($orderBy) . ' DESC LIMIT :limit'
        );
        $statement->bindValue(':limit', $limit, PDO::PARAM_INT);
        $statement->execute();

        return self::modelsToArrays(self::hydrateMany($statement->fetchAll()));
    }

    protected static function allOrdered(string $orderBy = 'id', string $direction = 'ASC'): array
    {
        $model = new static();

        // Only allow ASC or DESC so raw user text cannot become part of the SQL direction.
        $direction = strtoupper($direction) === 'DESC' ? 'DESC' : 'ASC';
        $statement = self::connection()->query(
            'SELECT * FROM ' . self::identifier($model->table) . ' ORDER BY ' . self::identifier($orderBy) . ' ' . $direction
        );

        return self::modelsToArrays(self::hydrateMany($statement->fetchAll()));
    }

    protected static function countRecords(array $conditions = []): int
    {
        $model = new static();

        // Build an optional WHERE clause, for example status = "Active".
        [$where, $params] = self::whereEquals($conditions);
        $statement = self::connection()->prepare('SELECT COUNT(*) FROM ' . self::identifier($model->table) . $where);
        $statement->execute($params);

        return (int) $statement->fetchColumn();
    }

    protected static function searchPage(array $fields, string $search = '', int $page = 1, int $perPage = 8, string $orderBy = 'created_at'): array
    {
        $model = new static();

        // Keep the page number valid, then calculate how many rows to skip.
        $page = max(1, $page);
        $offset = ($page - 1) * $perPage;

        // Build a LIKE search across multiple fields, such as name, course, and email.
        [$where, $params] = self::whereLike($fields, $search);
        $table = self::identifier($model->table);

        // First query counts all matching rows so the view can show total pages.
        $count = self::connection()->prepare("SELECT COUNT(*) FROM {$table}{$where}");
        $count->execute($params);
        $total = (int) $count->fetchColumn();

        // Second query fetches only the rows for the current page.
        $statement = self::connection()->prepare(
            "SELECT * FROM {$table}{$where} ORDER BY " . self::identifier($orderBy) . ' DESC LIMIT :limit OFFSET :offset'
        );

        foreach ($params as $key => $value) {
            $statement->bindValue(':' . $key, $value);
        }
        $statement->bindValue(':limit', $perPage, PDO::PARAM_INT);
        $statement->bindValue(':offset', $offset, PDO::PARAM_INT);
        $statement->execute();

        return [
            'items' => self::modelsToArrays(self::hydrateMany($statement->fetchAll())),
            'total' => $total,
            'page' => $page,
            'pages' => max(1, (int) ceil($total / $perPage)),
        ];
    }

    protected static function insertRecord(array $attributes): int
    {
        $model = new static();
        $columns = array_keys($attributes);

        // Convert array keys into column names and named placeholders.
        $columnSql = implode(', ', array_map(self::identifier(...), $columns));
        $placeholderSql = implode(', ', array_map(fn (string $column): string => ':' . $column, $columns));
        $statement = self::connection()->prepare(
            'INSERT INTO ' . self::identifier($model->table) . " ({$columnSql}) VALUES ({$placeholderSql})"
        );
        $statement->execute($attributes);

        // Return the new row id so the controller can redirect to the created record.
        return (int) self::connection()->lastInsertId();
    }

    protected static function updateRecord(int $id, array $attributes): void
    {
        $model = new static();
        $setSql = implode(', ', array_map(
            fn (string $column): string => self::identifier($column) . ' = :' . $column,
            array_keys($attributes)
        ));

        // Add the id to the same parameter array used by the prepared statement.
        $attributes['id'] = $id;
        $statement = self::connection()->prepare(
            'UPDATE ' . self::identifier($model->table) . " SET {$setSql} WHERE " . self::identifier($model->primaryKey) . ' = :id'
        );
        $statement->execute($attributes);
    }

    protected static function deleteRecord(int $id): void
    {
        $model = new static();

        // Delete one row by primary key.
        $statement = self::connection()->prepare(
            'DELETE FROM ' . self::identifier($model->table) . ' WHERE ' . self::identifier($model->primaryKey) . ' = :id'
        );
        $statement->execute(['id' => $id]);
    }

    protected static function connection(): PDO
    {
        // All ORM queries go through the shared PDO connection.
        return Connection::connection();
    }

    private static function hydrateMany(array $rows): array
    {
        // Turn many database rows into many model objects.
        return array_map(fn (array $row): static => new static($row), $rows);
    }

    private static function modelsToArrays(array $models): array
    {
        // Turn model objects into plain arrays for controllers/views.
        return array_map(fn (self $model): array => $model->toArray(), $models);
    }

    private static function whereEquals(array $conditions): array
    {
        // No conditions means the SQL does not need a WHERE clause.
        if (!$conditions) {
            return ['', []];
        }

        $parts = [];
        $params = [];

        foreach ($conditions as $field => $value) {
            $key = 'where_' . $field;
            $parts[] = self::identifier((string) $field) . ' = :' . $key;
            $params[$key] = $value;
        }

        // Return both the SQL fragment and the values that should be bound to it.
        return [' WHERE ' . implode(' AND ', $parts), $params];
    }

    private static function whereLike(array $fields, string $search): array
    {
        // Empty search means show all rows.
        if ($search === '') {
            return ['', []];
        }

        $parts = [];

        foreach ($fields as $index => $field) {
            $parts[] = self::identifier((string) $field) . ' LIKE :search' . $index;
        }

        return [
            ' WHERE ' . implode(' OR ', $parts),
            array_fill_keys(array_map(fn (int $index): string => 'search' . $index, array_keys($fields)), '%' . $search . '%'),
        ];
    }

    private static function identifier(string $identifier): string
    {
        // Table and column names cannot be bound like values, so validate them strictly.
        if (!preg_match('/^[A-Za-z_][A-Za-z0-9_]*$/', $identifier)) {
            throw new \InvalidArgumentException('Invalid database identifier.');
        }

        return $identifier;
    }
}
