<?php

declare(strict_types=1);

namespace Core\Database;

use PDO;

abstract class Model
{
    // Child models mo-set ani sa table nga ilang gi-represent, example "students".
    protected string $table;

    // Most tables gamit ug "id" as primary key, pero pwede i-override sa child model.
    protected string $primaryKey = 'id';

    // Mo-store ug usa ka row sa database data sulod sa model object.
    protected array $attributes = [];

    public function __construct(array $attributes = [])
    {
        // Kung na-fetch ang row from database, diri siya i-save as model data.
        $this->attributes = $attributes;
    }

    public function __get(string $key): mixed
    {
        // Mo-allow sa $student->first_name nga mobasa from attributes array.
        return $this->attributes[$key] ?? null;
    }

    public function __set(string $key, mixed $value): void
    {
        // Mo-allow sa $student->first_name = 'Juan' nga mo-update sa attributes array.
        $this->attributes[$key] = $value;
    }

    public function toArray(): array
    {
        // Controllers ug views mostly gamit ug arrays, so models pwede i-convert balik.
        return $this->attributes;
    }

    protected static function findRecord(int $id): ?static
    {
        // "new static()" mo-create sa child model nga ni-call ani, like Student.
        $model = new static();

        // Prepared statements mo-separate sa values from SQL ug help prevent SQL injection.
        $statement = self::connection()->prepare(
            'SELECT * FROM ' . self::identifier($model->table) . ' WHERE ' . self::identifier($model->primaryKey) . ' = :id LIMIT 1'
        );
        $statement->execute(['id' => $id]);
        $row = $statement->fetch();

        // I-hydrate ang row into model object, or null kung walay row.
        return $row ? new static($row) : null;
    }

    protected static function firstWhere(string $field, mixed $value): ?static
    {
        $model = new static();

        // Gamiton for lookups like pagpangita ug user by email.
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

        // LIMIT gi-bind as integer kay some database drivers dili modawat kung string.
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

        // ASC or DESC ra ang allowed para raw user text dili maapil sa SQL direction.
        $direction = strtoupper($direction) === 'DESC' ? 'DESC' : 'ASC';
        $statement = self::connection()->query(
            'SELECT * FROM ' . self::identifier($model->table) . ' ORDER BY ' . self::identifier($orderBy) . ' ' . $direction
        );

        return self::modelsToArrays(self::hydrateMany($statement->fetchAll()));
    }

    protected static function countRecords(array $conditions = []): int
    {
        $model = new static();

        // Mag-build ug optional WHERE clause, example status = "Active".
        [$where, $params] = self::whereEquals($conditions);
        $statement = self::connection()->prepare('SELECT COUNT(*) FROM ' . self::identifier($model->table) . $where);
        $statement->execute($params);

        return (int) $statement->fetchColumn();
    }

    protected static function searchPage(array $fields, string $search = '', int $page = 1, int $perPage = 8, string $orderBy = 'created_at'): array
    {
        $model = new static();

        // I-keep valid ang page number, then calculate pila ka rows i-skip.
        $page = max(1, $page);
        $offset = ($page - 1) * $perPage;

        // Mag-build ug LIKE search sa multiple fields, like name, course, ug email.
        [$where, $params] = self::whereLike($fields, $search);
        $table = self::identifier($model->table);

        // First query mo-count sa matching rows para ma-show sa view ang total pages.
        $count = self::connection()->prepare("SELECT COUNT(*) FROM {$table}{$where}");
        $count->execute($params);
        $total = (int) $count->fetchColumn();

        // Second query mo-fetch ra sa rows para sa current page.
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

        // I-convert ang array keys into column names ug named placeholders.
        $columnSql = implode(', ', array_map(self::identifier(...), $columns));
        $placeholderSql = implode(', ', array_map(fn (string $column): string => ':' . $column, $columns));
        $statement = self::connection()->prepare(
            'INSERT INTO ' . self::identifier($model->table) . " ({$columnSql}) VALUES ({$placeholderSql})"
        );
        $statement->execute($attributes);

        // I-return ang new row id para maka-redirect ang controller sa created record.
        return (int) self::connection()->lastInsertId();
    }

    protected static function updateRecord(int $id, array $attributes): void
    {
        $model = new static();
        $setSql = implode(', ', array_map(
            fn (string $column): string => self::identifier($column) . ' = :' . $column,
            array_keys($attributes)
        ));

        // I-add ang id sa same parameter array nga gamit sa prepared statement.
        $attributes['id'] = $id;
        $statement = self::connection()->prepare(
            'UPDATE ' . self::identifier($model->table) . " SET {$setSql} WHERE " . self::identifier($model->primaryKey) . ' = :id'
        );
        $statement->execute($attributes);
    }

    protected static function deleteRecord(int $id): void
    {
        $model = new static();

        // I-delete ang usa ka row by primary key.
        $statement = self::connection()->prepare(
            'DELETE FROM ' . self::identifier($model->table) . ' WHERE ' . self::identifier($model->primaryKey) . ' = :id'
        );
        $statement->execute(['id' => $id]);
    }

    protected static function connection(): PDO
    {
        // Tanan ORM queries moagi sa shared PDO connection.
        return Connection::connection();
    }

    private static function hydrateMany(array $rows): array
    {
        // Himuon ang many database rows into many model objects.
        return array_map(fn (array $row): static => new static($row), $rows);
    }

    private static function modelsToArrays(array $models): array
    {
        // Himuon ang model objects into plain arrays para sa controllers/views.
        return array_map(fn (self $model): array => $model->toArray(), $models);
    }

    private static function whereEquals(array $conditions): array
    {
        // Kung walay conditions, dili need ang WHERE clause sa SQL.
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

        // I-return ang SQL fragment ug values nga i-bind.
        return [' WHERE ' . implode(' AND ', $parts), $params];
    }

    private static function whereLike(array $fields, string $search): array
    {
        // Empty search means ipakita tanan rows.
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
        // Table ug column names dili ma-bind like values, so i-validate sila strictly.
        if (!preg_match('/^[A-Za-z_][A-Za-z0-9_]*$/', $identifier)) {
            throw new \InvalidArgumentException('Invalid database identifier.');
        }

        return $identifier;
    }
}
