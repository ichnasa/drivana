<?php

namespace App\Libraries;

/**
 * Demo data store — NOT a database. All data comes from App\Libraries\DummyData (the single source).
 * Changes made while using the app (bookings, payments, verification decisions…) are kept in one JSON file
 * (writable/demo-state.json) so every role sees the same state. Reset any time at /demo/reset.
 */
class Store
{
    private static ?array $state = null;
    private static bool $dirty = false;

    private static function file(): string
    {
        return WRITEPATH . 'demo-state.json';
    }

    private static function boot(): void
    {
        if (self::$state !== null) {
            return;
        }
        $state = null;
        if (is_file(self::file())) {
            $json = json_decode((string) file_get_contents(self::file()), true);
            if (is_array($json) && ($json['_v'] ?? null) === DummyData::VERSION) {
                $state = $json;
            }
        }
        if ($state === null) {
            $state = DummyData::seed();
            self::$dirty = true;
        }
        self::$state = $state;
        register_shutdown_function([self::class, 'flush']);
    }

    public static function flush(): void
    {
        if (self::$dirty && self::$state !== null) {
            file_put_contents(self::file(), json_encode(self::$state, JSON_UNESCAPED_UNICODE), LOCK_EX);
            self::$dirty = false;
        }
    }

    public static function reset(): void
    {
        self::$state = DummyData::seed();
        self::$dirty = true;
        self::flush();
    }

    /** All rows of a table (list). */
    public static function all(string $table): array
    {
        self::boot();

        return array_values(self::$state['t'][$table] ?? []);
    }

    public static function get(string $table, int $id): ?array
    {
        self::boot();

        return self::$state['t'][$table][$id] ?? null;
    }

    /** @param callable(array):bool $fn */
    public static function where(string $table, callable $fn): array
    {
        return array_values(array_filter(self::all($table), $fn));
    }

    /** First row matching $fn, or null. */
    public static function first(string $table, callable $fn): ?array
    {
        foreach (self::all($table) as $r) {
            if ($fn($r)) {
                return $r;
            }
        }

        return null;
    }

    public static function insert(string $table, array $row): int
    {
        self::boot();
        $id = (self::$state['seq'][$table] ?? 0) + 1;
        self::$state['seq'][$table] = $id;
        $row['id'] = $id;
        self::$state['t'][$table][$id] = $row;
        self::$dirty = true;

        return $id;
    }

    public static function update(string $table, int $id, array $changes): void
    {
        self::boot();
        if (isset(self::$state['t'][$table][$id])) {
            self::$state['t'][$table][$id] = $changes + self::$state['t'][$table][$id];
            self::$dirty = true;
        }
    }

    public static function remove(string $table, int $id): void
    {
        self::boot();
        unset(self::$state['t'][$table][$id]);
        self::$dirty = true;
    }

    public static function count(string $table, ?callable $fn = null): int
    {
        return count($fn ? self::where($table, $fn) : self::all($table));
    }
}
