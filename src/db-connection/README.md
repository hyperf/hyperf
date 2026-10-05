# Database connection lifecycle

The default connection remains eager and is held until the coroutine ends.
These options are siblings of `pool` in `databases.php`:

```php
'default' => [
    // Existing driver settings ...
    'lazy' => true,
    'release_after_use' => true,
    'pool' => [/* pool settings */],
],
```

`lazy` postpones pool acquisition until execution. Built-in MySQL, PostgreSQL,
SQLite and SQL Server drivers expose query metadata without constructing an
executable connection. Building `Model::query()` or calling `toSql()` does not
open a PDO connection.

`release_after_use` implies lazy and returns the connection after a complete
operation. Plain `insert()` can release immediately. `insertGetId()` retains
one lease for both insertion and ID retrieval, including PostgreSQL RETURNING
and SQL Server ODBC. Transactions, streaming cursors and nested execution hooks
retain the lease until their outer operation finishes. A saved generator that
has started but has not finished retains its lease until it is destroyed.

Sticky connections conservatively retain the same physical connection after
an actual write, until the coroutine ends. Read-only transactions and failed
or zero-row updates do not set a synthetic write marker. SQL submitted as a
write statement follows the driver's existing write-marker behavior.

Query logs are stored by the logical session. Enabling logs does not reserve
a pool slot; `getQueryLog()` remains available after connections are returned.
`pretend()` keeps the entire callback on one lease and restores both logging
and dry-run state on normal return, nested calls and exceptions.

## Commands that need the same connection

Session variables, temporary tables, locks and raw PDO operations need an
explicit scope. This scope does not start a transaction:

```php
Db::withConnection(function ($db) {
    $db->statement('SET @x = 1');
    return $db->select('SELECT @x');
});
```

The callback receives the logical connection, so Db, Model and callback calls
share its lease. Scopes can nest and release their pin when a callback throws.
Do not retain a physical PDO, driver connection or schema builder obtained
inside the callback after it returns.

The scope guarantees connection affinity, not automatic reset of arbitrary SQL
session state. When ordinary statements create session variables, temporary
tables or locks, clean them up before returning the connection. Raw-handle
access follows the invalidation behavior described below.

For compatibility, accessing raw handles or unknown driver APIs outside such
a scope pins the lease until explicit `release()` or coroutine cleanup.
This includes `getPdo()`, `getReadPdo()`, `getConnection()` and
`getSchemaBuilder()`. Opaque APIs can mutate state that the framework cannot
reset, so the driver connection is invalidated before the next borrower;
the pool wrapper is retained and reconnects on its next acquisition. Raw APIs
inside an explicit scope also invalidate at scope exit. Ordinary query-builder
operations reuse pooled PDO connections.

`getResolvedConnection()` is a diagnostic view only. Do not execute commands
or return the displayed pool wrapper yourself. `release()` is rejected while
an operation, transaction or explicit scope still uses the connection.

## Temporary and worker policies

Use a bounded override for the current coroutine:

```php
Db::withReleaseAfterUse(false, function ($db) {
    // Temporarily retain a connection across statements.
});
```

The previous policy is restored even if the callback throws. Nested overrides
restore their parent's value. An override can create a logical connection when
lazy is disabled, provided it is set before obtaining an eager connection.
Calling it after an eager handle has been obtained throws `LogicException`;
the framework cannot safely replace an object already held by business code.

The existing `enableReleaseAfterUse()`, `disableReleaseAfterUse()` and
`resetReleaseAfterUse()` methods set a default in the **current worker**.
They do not broadcast to other workers. Existing logical connections observe
the default on their next release decision; existing eager handles keep their
original lifecycle. Prefer worker defaults at startup and scoped overrides
inside requests. A logical connection's `setReleaseAfterUse(?bool)` remains
available, with priority over the worker default and configuration.

## Coroutine cleanup

A logical connection belongs to its creating coroutine and cannot be shared
with another coroutine. Cleanup closes the session, returns its lease once,
and removes it from Context. Using a captured session after cleanup throws
`LogicException`, rather than silently borrowing a connection without cleanup.
A late `defer()` callback may obtain a fresh session through `Db::connection()`.
Do not retain sessions or builders in static properties or across coroutines.
An abandoned active cursor invalidates its physical connection before reuse.

Outside coroutine execution, explicitly close sessions when their work ends;
there is no automatic coroutine cleanup.

## Custom drivers

A driver's third optional resolver argument declares IO-free metadata:

```php
Connection::resolverFor(
    'custom',
    fn ($pdo, $database, $prefix, $config) => new CustomConnection($pdo, $database, $prefix, $config),
    fn (array $config) => new ConnectionMetadata(
        $config,
        new CustomQueryGrammar(),
        new CustomProcessor(),
        $config['database'],
        $config['prefix'],
        CustomQueryBuilder::class // Optional; defaults to Query\Builder.
    )
);
```

The metadata callback must not perform IO and should create session-local
mutable grammar/processor instances. Replacing a driver resolver without a
metadata callback clears the previous metadata registration. A custom
ConnectionFactory may override `makeMetadata()`.

Drivers without this contract use the settings of a pinned real connection;
that compatibility fallback can perform IO during construction. It preserves
the driver's dialect instead of assuming that a constructor is side-effect
free. A driver connection should extend `Hyperf\Database\Connection` to expose
state used for safe automatic return. Nonstandard connections without that
state contract retain scope lifetime instead of automatically returning.
Custom ConnectionResolver implementations must implement their own lifecycle
policy; worker policy methods explicitly require this component's resolver.

`ConnectionOperationInterface` is an optional internal capability for compound
operations. Its callback must not retain the physical connection or handles.
The existing database ConnectionInterface does not gain required methods.

## MySQL integration tests

`tests/MySqlConnectionLifecycleTest.php` uses real PDO MySQL. Set
`HYPERF_MYSQL_DATABASE` to opt in; otherwise these tests are skipped. Connection
settings come from `HYPERF_MYSQL_HOST`, `HYPERF_MYSQL_PORT`, `HYPERF_MYSQL_USER`
and `HYPERF_MYSQL_PASSWORD`. Defaults are localhost:3306, root and an empty
password. Use a database where the test account can create and drop tables.
Tests create randomly named `hyperf_pr7819_*` tables and clean them up afterward.

From the repository root, with those variables set, run:

```sh
php bin/co-phpunit src/db-connection/tests/MySqlConnectionLifecycleTest.php --do-not-cache-result
```

Coverage includes ID retrieval across two connections, savepoints, transaction
exceptions and coroutine cleanup, dry runs, sticky routing, scoped session
variables, temporary-table invalidation and unbuffered cursors. Read and write
PDOs point to the same server; connection IDs verify routing, not replication.
