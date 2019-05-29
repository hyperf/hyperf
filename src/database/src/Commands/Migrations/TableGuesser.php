<?php

namespace Hyperf\Database\Commands\Migrations;

class TableGuesser
{
    const CREATE_PATTERNS = [
        '/^create_(\w+)_table$/',
        '/^create_(\w+)$/',
    ];

    const CHANGE_PATTERNS = [
        '/_(to|from|in)_(\w+)_table$/',
        '/_(to|from|in)_(\w+)$/',
    ];

    /**
     * Attempt to guess the table name and "creation" status of the given migration.
     */
    public static function guess(string $migration): array
    {
        foreach (self::CREATE_PATTERNS as $pattern) {
            if (preg_match($pattern, $migration, $matches)) {
                return [$matches[1], $create = true];
            }
        }

        foreach (self::CHANGE_PATTERNS as $pattern) {
            if (preg_match($pattern, $migration, $matches)) {
                return [$matches[2], $create = false];
            }
        }
        return ['', false];
    }
}
