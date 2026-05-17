<?php

declare(strict_types=1);

namespace Core\Database;

use PDO;

abstract class Model
{
    protected string $table;
    protected string $primaryKey = 'id';
    protected array $attributes = [];

    public function __construct(array $attributes = [])
    {
        $this->attributes = $attributes;
    }

    public function __get(string $key): mixed
    {
        return $this->attributes[$key] ?? null;
    }

    public function __set(string $key, mixed $value): void
    {
        $this->attributes[$key] = $value;
    }

    public function toArray(): array
    {
        return $this->attributes;
    }

    protected static function findRecord(int $id): ?static
    {
        $model = new static();
        $statement = self::connection()->prepare(
            'SELECT * FROM ' . self::identifier($model->table) . ' WHERE ' . self::identifier($model->primaryKey) . ' = :id LIMIT 1'
        );
        $statement->execute(['id' => $id]);
        $row = $statement->fetch();

        return $row ? new static($row) : null;
    }

    protected static function firstWhere(string $field, mixed $value): ?static
    {
        $model = new static();
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
        $direction = strtoupper($direction) === 'DESC' ? 'DESC' : 'ASC';
        $statement = self::connection()->query(
            'SELECT * FROM ' . self::identifier($model->table) . ' ORDER BY ' . self::identifier($orderBy) . ' ' . $direction
        );

        return self::modelsToArrays(self::hydrateMany($statement->fetchAll()));
    }

    protected static function countRecords(array $conditions = []): int
    {
        $model = new static();
        [$where, $params] = self::whereEquals($conditions);
        $statement = self::connection()->prepare('SELECT COUNT(*) FROM ' . self::identifier($model->table) . $where);
        $statement->execute($params);

        return (int) $statement->fetchColumn();
    }

    protected static function searchPage(array $fields, string $search = '', int $page = 1, int $perPage = 8, string $orderBy = 'created_at'): array
    {
        $model = new static();
        $page = max(1, $page);
        $offset = ($page - 1) * $perPage;
        [$where, $params] = self::whereLike($fields, $search);
        $table = self::identifier($model->table);

        $count = self::connection()->prepare("SELECT COUNT(*) FROM {$table}{$where}");
        $count->execute($params);
        $total = (int) $count->fetchColumn();

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
        $columnSql = implode(', ', array_map(self::identifier(...), $columns));
        $placeholderSql = implode(', ', array_map(fn (string $column): string => ':' . $column, $columns));
        $statement = self::connection()->prepare(
            'INSERT INTO ' . self::identifier($model->table) . " ({$columnSql}) VALUES ({$placeholderSql})"
        );
        $statement->execute($attributes);

        return (int) self::connection()->lastInsertId();
    }

    protected static function updateRecord(int $id, array $attributes): void
    {
        $model = new static();
        $setSql = implode(', ', array_map(
            fn (string $column): string => self::identifier($column) . ' = :' . $column,
            array_keys($attributes)
        ));
        $attributes['id'] = $id;
        $statement = self::connection()->prepare(
            'UPDATE ' . self::identifier($model->table) . " SET {$setSql} WHERE " . self::identifier($model->primaryKey) . ' = :id'
        );
        $statement->execute($attributes);
    }

    protected static function deleteRecord(int $id): void
    {
        $model = new static();
        $statement = self::connection()->prepare(
            'DELETE FROM ' . self::identifier($model->table) . ' WHERE ' . self::identifier($model->primaryKey) . ' = :id'
        );
        $statement->execute(['id' => $id]);
    }

    protected static function connection(): PDO
    {
        return Connection::connection();
    }

    private static function hydrateMany(array $rows): array
    {
        return array_map(fn (array $row): static => new static($row), $rows);
    }

    private static function modelsToArrays(array $models): array
    {
        return array_map(fn (self $model): array => $model->toArray(), $models);
    }

    private static function whereEquals(array $conditions): array
    {
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

        return [' WHERE ' . implode(' AND ', $parts), $params];
    }

    private static function whereLike(array $fields, string $search): array
    {
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
        if (!preg_match('/^[A-Za-z_][A-Za-z0-9_]*$/', $identifier)) {
            throw new \InvalidArgumentException('Invalid database identifier.');
        }

        return $identifier;
    }
}
