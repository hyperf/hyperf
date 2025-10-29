<?php

declare(strict_types=1);
/**
 * This file is part of Hyperf.
 *
 * @link     https://www.hyperf.io
 * @document https://hyperf.wiki
 * @contact  group@hyperf.io
 * @license  https://github.com/hyperf/hyperf/blob/master/LICENSE
 */

namespace HyperfTest\Database\PgSQL\Cases;

use Hyperf\Database\Connection;
use Hyperf\Database\PgSQL\Schema\Grammars\PostgresGrammar;
use Hyperf\Database\Schema\Blueprint;
use Mockery as m;
use PHPUnit\Framework\Attributes\CoversNothing;
use PHPUnit\Framework\TestCase;

/**
 * @internal
 * @coversNothing
 */
#[CoversNothing]
class DatabasePostgresSchemaGrammarTest extends TestCase
{
    protected function tearDown(): void
    {
        m::close();
    }

    public function testBasicCreateTable()
    {
        $blueprint = new Blueprint('users');
        $blueprint->create();
        $blueprint->increments('id');
        $blueprint->string('email');
        $blueprint->string('name')->collation('nb_NO.utf8');
        $statements = $blueprint->toSql($this->getConnection(), $this->getGrammar());

        $this->assertCount(1, $statements);
        $this->assertSame('create table "users" ("id" serial primary key not null, "email" varchar(255) not null, "name" varchar(255) collate "nb_NO.utf8" not null)', $statements[0]);

        $blueprint = new Blueprint('users');
        $blueprint->increments('id');
        $blueprint->string('email');
        $statements = $blueprint->toSql($this->getConnection(), $this->getGrammar());

        $this->assertCount(1, $statements);
        $this->assertSame('alter table "users" add column "id" serial primary key not null, add column "email" varchar(255) not null', $statements[0]);
    }

    public function testCreateTableWithAutoIncrementStartingValue()
    {
        $blueprint = new Blueprint('users');
        $blueprint->create();
        $blueprint->increments('id')->startingValue(1000);
        $blueprint->string('email');
        $blueprint->string('name')->collation('nb_NO.utf8');
        $statements = $blueprint->toSql($this->getConnection(), $this->getGrammar());

        $this->assertCount(2, $statements);
        $this->assertSame('create table "users" ("id" serial primary key not null, "email" varchar(255) not null, "name" varchar(255) collate "nb_NO.utf8" not null)', $statements[0]);
        $this->assertSame('alter sequence users_id_seq restart with 1000', $statements[1]);
    }

    public function testCreateTableAndCommentColumn()
    {
        $blueprint = new Blueprint('users');
        $blueprint->create();
        $blueprint->increments('id');
        $blueprint->string('email')->comment('my first comment');
        $statements = $blueprint->toSql($this->getConnection(), $this->getGrammar());

        $this->assertCount(2, $statements);
        $this->assertSame('create table "users" ("id" serial primary key not null, "email" varchar(255) not null)', $statements[0]);
        $this->assertSame('comment on column "users"."email" is \'my first comment\'', $statements[1]);
    }

    public function testCreateTableWithTableComment()
    {
        $blueprint = new Blueprint('users');
        $blueprint->create();
        $blueprint->comment('This is a user table');
        $blueprint->increments('id');
        $blueprint->string('email');
        $statements = $blueprint->toSql($this->getConnection(), $this->getGrammar());

        $this->assertCount(2, $statements);
        $this->assertSame('create table "users" ("id" serial primary key not null, "email" varchar(255) not null)', $statements[0]);
        $this->assertSame('comment on table "users" is \'This is a user table\'', $statements[1]);
    }

    public function testCreateTemporaryTable()
    {
        $blueprint = new Blueprint('users');
        $blueprint->create();
        $blueprint->temporary();
        $blueprint->increments('id');
        $blueprint->string('email');
        $statements = $blueprint->toSql($this->getConnection(), $this->getGrammar());

        $this->assertCount(1, $statements);
        $this->assertSame('create temporary table "users" ("id" serial primary key not null, "email" varchar(255) not null)', $statements[0]);
    }

    public function testDropTable()
    {
        $blueprint = new Blueprint('users');
        $blueprint->drop();
        $statements = $blueprint->toSql($this->getConnection(), $this->getGrammar());

        $this->assertCount(1, $statements);
        $this->assertSame('drop table "users"', $statements[0]);
    }

    public function testDropTableIfExists()
    {
        $blueprint = new Blueprint('users');
        $blueprint->dropIfExists();
        $statements = $blueprint->toSql($this->getConnection(), $this->getGrammar());

        $this->assertCount(1, $statements);
        $this->assertSame('drop table if exists "users"', $statements[0]);
    }

    public function testDropColumn()
    {
        $blueprint = new Blueprint('users');
        $blueprint->dropColumn('foo');
        $statements = $blueprint->toSql($this->getConnection(), $this->getGrammar());

        $this->assertCount(1, $statements);
        $this->assertSame('alter table "users" drop column "foo"', $statements[0]);

        $blueprint = new Blueprint('users');
        $blueprint->dropColumn(['foo', 'bar']);
        $statements = $blueprint->toSql($this->getConnection(), $this->getGrammar());

        $this->assertCount(1, $statements);
        $this->assertSame('alter table "users" drop column "foo", drop column "bar"', $statements[0]);

        $blueprint = new Blueprint('users');
        $blueprint->dropColumn('foo', 'bar');
        $statements = $blueprint->toSql($this->getConnection(), $this->getGrammar());

        $this->assertCount(1, $statements);
        $this->assertSame('alter table "users" drop column "foo", drop column "bar"', $statements[0]);
    }

    public function testDropPrimary()
    {
        $blueprint = new Blueprint('users');
        $blueprint->dropPrimary();
        $statements = $blueprint->toSql($this->getConnection(), $this->getGrammar());

        $this->assertCount(1, $statements);
        $this->assertSame('alter table "users" drop constraint "users_pkey"', $statements[0]);
    }

    public function testDropUnique()
    {
        $blueprint = new Blueprint('users');
        $blueprint->dropUnique('foo');
        $statements = $blueprint->toSql($this->getConnection(), $this->getGrammar());

        $this->assertCount(1, $statements);
        $this->assertSame('alter table "users" drop constraint "foo"', $statements[0]);
    }

    public function testDropIndex()
    {
        $blueprint = new Blueprint('users');
        $blueprint->dropIndex('foo');
        $statements = $blueprint->toSql($this->getConnection(), $this->getGrammar());

        $this->assertCount(1, $statements);
        $this->assertSame('drop index "foo"', $statements[0]);
    }

    public function testDropSpatialIndex()
    {
        $blueprint = new Blueprint('geo');
        $blueprint->dropSpatialIndex(['coordinates']);
        $statements = $blueprint->toSql($this->getConnection(), $this->getGrammar());

        $this->assertCount(1, $statements);
        $this->assertSame('drop index "geo_coordinates_spatialindex"', $statements[0]);
    }

    public function testDropForeign()
    {
        $blueprint = new Blueprint('users');
        $blueprint->dropForeign('foo');
        $statements = $blueprint->toSql($this->getConnection(), $this->getGrammar());

        $this->assertCount(1, $statements);
        $this->assertSame('alter table "users" drop constraint "foo"', $statements[0]);
    }

    public function testDropTimestamps()
    {
        $blueprint = new Blueprint('users');
        $blueprint->dropTimestamps();
        $statements = $blueprint->toSql($this->getConnection(), $this->getGrammar());

        $this->assertCount(1, $statements);
        $this->assertSame('alter table "users" drop column "created_at", drop column "updated_at"', $statements[0]);
    }

    public function testDropTimestampsTz()
    {
        $blueprint = new Blueprint('users');
        $blueprint->dropTimestampsTz();
        $statements = $blueprint->toSql($this->getConnection(), $this->getGrammar());

        $this->assertCount(1, $statements);
        $this->assertSame('alter table "users" drop column "created_at", drop column "updated_at"', $statements[0]);
    }

    public function testDropMorphs()
    {
        $blueprint = new Blueprint('photos');
        $blueprint->dropMorphs('imageable');
        $statements = $blueprint->toSql($this->getConnection(), $this->getGrammar());

        $this->assertCount(2, $statements);
        $this->assertSame('drop index "photos_imageable_type_imageable_id_index"', $statements[0]);
        $this->assertSame('alter table "photos" drop column "imageable_type", drop column "imageable_id"', $statements[1]);
    }

    public function testRenameTable()
    {
        $blueprint = new Blueprint('users');
        $blueprint->rename('foo');
        $statements = $blueprint->toSql($this->getConnection(), $this->getGrammar());

        $this->assertCount(1, $statements);
        $this->assertSame('alter table "users" rename to "foo"', $statements[0]);
    }

    public function testRenameIndex()
    {
        $blueprint = new Blueprint('users');
        $blueprint->renameIndex('foo', 'bar');
        $statements = $blueprint->toSql($this->getConnection(), $this->getGrammar());

        $this->assertCount(1, $statements);
        $this->assertSame('alter index "foo" rename to "bar"', $statements[0]);
    }

    public function testAddingPrimaryKey()
    {
        $blueprint = new Blueprint('users');
        $blueprint->primary('foo');
        $statements = $blueprint->toSql($this->getConnection(), $this->getGrammar());

        $this->assertCount(1, $statements);
        $this->assertSame('alter table "users" add primary key ("foo")', $statements[0]);
    }

    public function testAddingUniqueKey()
    {
        $blueprint = new Blueprint('users');
        $blueprint->unique('foo', 'bar');
        $statements = $blueprint->toSql($this->getConnection(), $this->getGrammar());

        $this->assertCount(1, $statements);
        $this->assertSame('alter table "users" add constraint "bar" unique ("foo")', $statements[0]);
    }

    public function testAddingIndex()
    {
        $blueprint = new Blueprint('users');
        $blueprint->index(['foo', 'bar'], 'baz');
        $statements = $blueprint->toSql($this->getConnection(), $this->getGrammar());

        $this->assertCount(1, $statements);
        $this->assertSame('create index "baz" on "users" ("foo", "bar")', $statements[0]);
    }

    public function testAddingIndexWithAlgorithm()
    {
        $blueprint = new Blueprint('users');
        $blueprint->index(['foo', 'bar'], 'baz', 'hash');
        $statements = $blueprint->toSql($this->getConnection(), $this->getGrammar());

        $this->assertCount(1, $statements);
        $this->assertSame('create index "baz" on "users" using hash ("foo", "bar")', $statements[0]);
    }

    public function testAddingFullTextIndex()
    {
        $blueprint = new Blueprint('users');
        $blueprint->fullText('body');
        $statements = $blueprint->toSql($this->getConnection(), $this->getGrammar());

        $this->assertCount(1, $statements);
        $this->assertSame('create index "users_body_fulltext" on "users" using gin ((to_tsvector(\'english\', "body")))', $statements[0]);
    }

    public function testAddingFullTextIndexWithLanguage()
    {
        $blueprint = new Blueprint('users');
        $blueprint->fullText('body')->language('spanish');
        $statements = $blueprint->toSql($this->getConnection(), $this->getGrammar());

        $this->assertCount(1, $statements);
        $this->assertSame('create index "users_body_fulltext" on "users" using gin ((to_tsvector(\'spanish\', "body")))', $statements[0]);
    }

    public function testAddingSpatialIndex()
    {
        $blueprint = new Blueprint('geo');
        $blueprint->spatialIndex('coordinates');
        $statements = $blueprint->toSql($this->getConnection(), $this->getGrammar());

        $this->assertCount(1, $statements);
        $this->assertSame('create index "geo_coordinates_spatialindex" on "geo" using gist ("coordinates")', $statements[0]);
    }

    public function testAddingFluentSpatialIndex()
    {
        $blueprint = new Blueprint('geo');
        $blueprint->point('coordinates')->spatialIndex();
        $statements = $blueprint->toSql($this->getConnection(), $this->getGrammar());

        $this->assertCount(2, $statements);
        $this->assertSame('create index "geo_coordinates_spatialindex" on "geo" using gist ("coordinates")', $statements[1]);
    }

    public function testAddingRawIndex()
    {
        $blueprint = new Blueprint('users');
        $blueprint->rawIndex('(function(column))', 'raw_index');
        $statements = $blueprint->toSql($this->getConnection(), $this->getGrammar());

        $this->assertCount(1, $statements);
        $this->assertSame('create index "raw_index" on "users" ((function(column)))', $statements[0]);
    }

    public function testAddingIncrementingID()
    {
        $blueprint = new Blueprint('users');
        $blueprint->increments('id');
        $statements = $blueprint->toSql($this->getConnection(), $this->getGrammar());

        $this->assertCount(1, $statements);
        $this->assertSame('alter table "users" add column "id" serial primary key not null', $statements[0]);
    }

    public function testAddingSmallIncrementingID()
    {
        $blueprint = new Blueprint('users');
        $blueprint->smallIncrements('id');
        $statements = $blueprint->toSql($this->getConnection(), $this->getGrammar());

        $this->assertCount(1, $statements);
        $this->assertSame('alter table "users" add column "id" smallserial primary key not null', $statements[0]);
    }

    public function testAddingMediumIncrementingID()
    {
        $blueprint = new Blueprint('users');
        $blueprint->mediumIncrements('id');
        $statements = $blueprint->toSql($this->getConnection(), $this->getGrammar());

        $this->assertCount(1, $statements);
        $this->assertSame('alter table "users" add column "id" serial primary key not null', $statements[0]);
    }

    public function testAddingID()
    {
        $blueprint = new Blueprint('users');
        $blueprint->id();
        $statements = $blueprint->toSql($this->getConnection(), $this->getGrammar());

        $this->assertCount(1, $statements);
        $this->assertSame('alter table "users" add column "id" bigserial primary key not null', $statements[0]);

        $blueprint = new Blueprint('users');
        $blueprint->id('foo');
        $statements = $blueprint->toSql($this->getConnection(), $this->getGrammar());

        $this->assertCount(1, $statements);
        $this->assertSame('alter table "users" add column "foo" bigserial primary key not null', $statements[0]);
    }

    public function testAddingBigIncrementingID()
    {
        $blueprint = new Blueprint('users');
        $blueprint->bigIncrements('id');
        $statements = $blueprint->toSql($this->getConnection(), $this->getGrammar());

        $this->assertCount(1, $statements);
        $this->assertSame('alter table "users" add column "id" bigserial primary key not null', $statements[0]);
    }

    public function testAddingString()
    {
        $blueprint = new Blueprint('users');
        $blueprint->string('foo');
        $statements = $blueprint->toSql($this->getConnection(), $this->getGrammar());

        $this->assertCount(1, $statements);
        $this->assertSame('alter table "users" add column "foo" varchar(255) not null', $statements[0]);

        $blueprint = new Blueprint('users');
        $blueprint->string('foo', 100);
        $statements = $blueprint->toSql($this->getConnection(), $this->getGrammar());

        $this->assertCount(1, $statements);
        $this->assertSame('alter table "users" add column "foo" varchar(100) not null', $statements[0]);

        $blueprint = new Blueprint('users');
        $blueprint->string('foo', 100)->nullable()->default('bar');
        $statements = $blueprint->toSql($this->getConnection(), $this->getGrammar());

        $this->assertCount(1, $statements);
        $this->assertSame('alter table "users" add column "foo" varchar(100) null default \'bar\'', $statements[0]);
    }

    public function testAddingText()
    {
        $blueprint = new Blueprint('users');
        $blueprint->text('foo');
        $statements = $blueprint->toSql($this->getConnection(), $this->getGrammar());

        $this->assertCount(1, $statements);
        $this->assertSame('alter table "users" add column "foo" text not null', $statements[0]);
    }

    public function testAddingBigInteger()
    {
        $blueprint = new Blueprint('users');
        $blueprint->bigInteger('foo');
        $statements = $blueprint->toSql($this->getConnection(), $this->getGrammar());

        $this->assertCount(1, $statements);
        $this->assertSame('alter table "users" add column "foo" bigint not null', $statements[0]);

        $blueprint = new Blueprint('users');
        $blueprint->bigInteger('foo', true);
        $statements = $blueprint->toSql($this->getConnection(), $this->getGrammar());

        $this->assertCount(1, $statements);
        $this->assertSame('alter table "users" add column "foo" bigserial primary key not null', $statements[0]);
    }

    public function testAddingInteger()
    {
        $blueprint = new Blueprint('users');
        $blueprint->integer('foo');
        $statements = $blueprint->toSql($this->getConnection(), $this->getGrammar());

        $this->assertCount(1, $statements);
        $this->assertSame('alter table "users" add column "foo" integer not null', $statements[0]);

        $blueprint = new Blueprint('users');
        $blueprint->integer('foo', true);
        $statements = $blueprint->toSql($this->getConnection(), $this->getGrammar());

        $this->assertCount(1, $statements);
        $this->assertSame('alter table "users" add column "foo" serial primary key not null', $statements[0]);
    }

    public function testAddingMediumInteger()
    {
        $blueprint = new Blueprint('users');
        $blueprint->mediumInteger('foo');
        $statements = $blueprint->toSql($this->getConnection(), $this->getGrammar());

        $this->assertCount(1, $statements);
        $this->assertSame('alter table "users" add column "foo" integer not null', $statements[0]);

        $blueprint = new Blueprint('users');
        $blueprint->mediumInteger('foo', true);
        $statements = $blueprint->toSql($this->getConnection(), $this->getGrammar());

        $this->assertCount(1, $statements);
        $this->assertSame('alter table "users" add column "foo" serial primary key not null', $statements[0]);
    }

    public function testAddingTinyInteger()
    {
        $blueprint = new Blueprint('users');
        $blueprint->tinyInteger('foo');
        $statements = $blueprint->toSql($this->getConnection(), $this->getGrammar());

        $this->assertCount(1, $statements);
        $this->assertSame('alter table "users" add column "foo" smallint not null', $statements[0]);

        $blueprint = new Blueprint('users');
        $blueprint->tinyInteger('foo', true);
        $statements = $blueprint->toSql($this->getConnection(), $this->getGrammar());

        $this->assertCount(1, $statements);
        $this->assertSame('alter table "users" add column "foo" smallserial primary key not null', $statements[0]);
    }

    public function testAddingSmallInteger()
    {
        $blueprint = new Blueprint('users');
        $blueprint->smallInteger('foo');
        $statements = $blueprint->toSql($this->getConnection(), $this->getGrammar());

        $this->assertCount(1, $statements);
        $this->assertSame('alter table "users" add column "foo" smallint not null', $statements[0]);

        $blueprint = new Blueprint('users');
        $blueprint->smallInteger('foo', true);
        $statements = $blueprint->toSql($this->getConnection(), $this->getGrammar());

        $this->assertCount(1, $statements);
        $this->assertSame('alter table "users" add column "foo" smallserial primary key not null', $statements[0]);
    }

    public function testAddingFloat()
    {
        $blueprint = new Blueprint('users');
        $blueprint->float('foo', 5, 2);
        $statements = $blueprint->toSql($this->getConnection(), $this->getGrammar());

        $this->assertCount(1, $statements);
        $this->assertSame('alter table "users" add column "foo" double precision not null', $statements[0]);
    }

    public function testAddingDouble()
    {
        $blueprint = new Blueprint('users');
        $blueprint->double('foo', 15, 8);
        $statements = $blueprint->toSql($this->getConnection(), $this->getGrammar());

        $this->assertCount(1, $statements);
        $this->assertSame('alter table "users" add column "foo" double precision not null', $statements[0]);
    }

    public function testAddingDecimal()
    {
        $blueprint = new Blueprint('users');
        $blueprint->decimal('foo', 5, 2);
        $statements = $blueprint->toSql($this->getConnection(), $this->getGrammar());

        $this->assertCount(1, $statements);
        $this->assertSame('alter table "users" add column "foo" decimal(5, 2) not null', $statements[0]);
    }

    public function testAddingBoolean()
    {
        $blueprint = new Blueprint('users');
        $blueprint->boolean('foo');
        $statements = $blueprint->toSql($this->getConnection(), $this->getGrammar());

        $this->assertCount(1, $statements);
        $this->assertSame('alter table "users" add column "foo" boolean not null', $statements[0]);
    }

    public function testAddingEnum()
    {
        $blueprint = new Blueprint('users');
        $blueprint->enum('role', ['member', 'admin']);
        $statements = $blueprint->toSql($this->getConnection(), $this->getGrammar());

        $this->assertCount(1, $statements);
        $this->assertSame('alter table "users" add column "role" varchar(255) check ("role" in (\'member\', \'admin\')) not null', $statements[0]);
    }

    public function testAddingDate()
    {
        $blueprint = new Blueprint('users');
        $blueprint->date('foo');
        $statements = $blueprint->toSql($this->getConnection(), $this->getGrammar());

        $this->assertCount(1, $statements);
        $this->assertSame('alter table "users" add column "foo" date not null', $statements[0]);
    }

    public function testAddingYear()
    {
        $blueprint = new Blueprint('users');
        $blueprint->year('birth_year');
        $statements = $blueprint->toSql($this->getConnection(), $this->getGrammar());
        $this->assertCount(1, $statements);
        $this->assertSame('alter table "users" add column "birth_year" integer not null', $statements[0]);
    }

    public function testAddingJson()
    {
        $blueprint = new Blueprint('users');
        $blueprint->json('foo');
        $statements = $blueprint->toSql($this->getConnection(), $this->getGrammar());

        $this->assertCount(1, $statements);
        $this->assertSame('alter table "users" add column "foo" json not null', $statements[0]);
    }

    public function testAddingJsonb()
    {
        $blueprint = new Blueprint('users');
        $blueprint->jsonb('foo');
        $statements = $blueprint->toSql($this->getConnection(), $this->getGrammar());

        $this->assertCount(1, $statements);
        $this->assertSame('alter table "users" add column "foo" jsonb not null', $statements[0]);
    }

    public function testAddingDateTime()
    {
        $blueprint = new Blueprint('users');
        $blueprint->dateTime('created_at');
        $statements = $blueprint->toSql($this->getConnection(), $this->getGrammar());
        $this->assertCount(1, $statements);
        $this->assertSame('alter table "users" add column "created_at" timestamp(0) without time zone not null', $statements[0]);
    }

    public function testAddingDateTimeWithPrecision()
    {
        $blueprint = new Blueprint('users');
        $blueprint->dateTime('created_at', 1);
        $statements = $blueprint->toSql($this->getConnection(), $this->getGrammar());
        $this->assertCount(1, $statements);
        $this->assertSame('alter table "users" add column "created_at" timestamp(1) without time zone not null', $statements[0]);
    }

    public function testAddingDateTimeWithNullPrecision()
    {
        $blueprint = new Blueprint('users');
        $blueprint->dateTime('created_at', null);
        $statements = $blueprint->toSql($this->getConnection(), $this->getGrammar());
        $this->assertCount(1, $statements);
        $this->assertSame('alter table "users" add column "created_at" timestamp without time zone not null', $statements[0]);
    }

    public function testAddingDateTimeTz()
    {
        $blueprint = new Blueprint('users');
        $blueprint->dateTimeTz('created_at');
        $statements = $blueprint->toSql($this->getConnection(), $this->getGrammar());
        $this->assertCount(1, $statements);
        $this->assertSame('alter table "users" add column "created_at" timestamp(0) with time zone not null', $statements[0]);
    }

    public function testAddingDateTimeTzWithPrecision()
    {
        $blueprint = new Blueprint('users');
        $blueprint->dateTimeTz('created_at', 1);
        $statements = $blueprint->toSql($this->getConnection(), $this->getGrammar());
        $this->assertCount(1, $statements);
        $this->assertSame('alter table "users" add column "created_at" timestamp(1) with time zone not null', $statements[0]);
    }

    public function testAddingDateTimeTzWithNullPrecision()
    {
        $blueprint = new Blueprint('users');
        $blueprint->dateTimeTz('created_at', null);
        $statements = $blueprint->toSql($this->getConnection(), $this->getGrammar());
        $this->assertCount(1, $statements);
        $this->assertSame('alter table "users" add column "created_at" timestamp with time zone not null', $statements[0]);
    }

    public function testAddingTime()
    {
        $blueprint = new Blueprint('users');
        $blueprint->time('created_at');
        $statements = $blueprint->toSql($this->getConnection(), $this->getGrammar());
        $this->assertCount(1, $statements);
        $this->assertSame('alter table "users" add column "created_at" time(0) without time zone not null', $statements[0]);
    }

    public function testAddingTimeWithPrecision()
    {
        $blueprint = new Blueprint('users');
        $blueprint->time('created_at', 1);
        $statements = $blueprint->toSql($this->getConnection(), $this->getGrammar());
        $this->assertCount(1, $statements);
        $this->assertSame('alter table "users" add column "created_at" time(1) without time zone not null', $statements[0]);
    }

    public function testAddingTimeWithNullPrecision()
    {
        $blueprint = new Blueprint('users');
        $blueprint->time('created_at', null);
        $statements = $blueprint->toSql($this->getConnection(), $this->getGrammar());
        $this->assertCount(1, $statements);
        $this->assertSame('alter table "users" add column "created_at" time without time zone not null', $statements[0]);
    }

    public function testAddingTimeTz()
    {
        $blueprint = new Blueprint('users');
        $blueprint->timeTz('created_at');
        $statements = $blueprint->toSql($this->getConnection(), $this->getGrammar());
        $this->assertCount(1, $statements);
        $this->assertSame('alter table "users" add column "created_at" time(0) with time zone not null', $statements[0]);
    }

    public function testAddingTimeTzWithPrecision()
    {
        $blueprint = new Blueprint('users');
        $blueprint->timeTz('created_at', 1);
        $statements = $blueprint->toSql($this->getConnection(), $this->getGrammar());
        $this->assertCount(1, $statements);
        $this->assertSame('alter table "users" add column "created_at" time(1) with time zone not null', $statements[0]);
    }

    public function testAddingTimeTzWithNullPrecision()
    {
        $blueprint = new Blueprint('users');
        $blueprint->timeTz('created_at', null);
        $statements = $blueprint->toSql($this->getConnection(), $this->getGrammar());
        $this->assertCount(1, $statements);
        $this->assertSame('alter table "users" add column "created_at" time with time zone not null', $statements[0]);
    }

    public function testAddingTimestamp()
    {
        $blueprint = new Blueprint('users');
        $blueprint->timestamp('created_at');
        $statements = $blueprint->toSql($this->getConnection(), $this->getGrammar());
        $this->assertCount(1, $statements);
        $this->assertSame('alter table "users" add column "created_at" timestamp(0) without time zone not null', $statements[0]);
    }

    public function testAddingTimestampWithPrecision()
    {
        $blueprint = new Blueprint('users');
        $blueprint->timestamp('created_at', 1);
        $statements = $blueprint->toSql($this->getConnection(), $this->getGrammar());
        $this->assertCount(1, $statements);
        $this->assertSame('alter table "users" add column "created_at" timestamp(1) without time zone not null', $statements[0]);
    }

    public function testAddingTimestampWithNullPrecision()
    {
        $blueprint = new Blueprint('users');
        $blueprint->timestamp('created_at', null);
        $statements = $blueprint->toSql($this->getConnection(), $this->getGrammar());
        $this->assertCount(1, $statements);
        $this->assertSame('alter table "users" add column "created_at" timestamp without time zone not null', $statements[0]);
    }

    public function testAddingTimestampTz()
    {
        $blueprint = new Blueprint('users');
        $blueprint->timestampTz('created_at');
        $statements = $blueprint->toSql($this->getConnection(), $this->getGrammar());
        $this->assertCount(1, $statements);
        $this->assertSame('alter table "users" add column "created_at" timestamp(0) with time zone not null', $statements[0]);
    }

    public function testAddingTimestampTzWithPrecision()
    {
        $blueprint = new Blueprint('users');
        $blueprint->timestampTz('created_at', 1);
        $statements = $blueprint->toSql($this->getConnection(), $this->getGrammar());
        $this->assertCount(1, $statements);
        $this->assertSame('alter table "users" add column "created_at" timestamp(1) with time zone not null', $statements[0]);
    }

    public function testAddingTimestampTzWithNullPrecision()
    {
        $blueprint = new Blueprint('users');
        $blueprint->timestampTz('created_at', null);
        $statements = $blueprint->toSql($this->getConnection(), $this->getGrammar());
        $this->assertCount(1, $statements);
        $this->assertSame('alter table "users" add column "created_at" timestamp with time zone not null', $statements[0]);
    }

    public function testAddingTimestamps()
    {
        $blueprint = new Blueprint('users');
        $blueprint->timestamps();
        $statements = $blueprint->toSql($this->getConnection(), $this->getGrammar());
        $this->assertCount(1, $statements);
        $this->assertSame('alter table "users" add column "created_at" timestamp(0) without time zone null, add column "updated_at" timestamp(0) without time zone null', $statements[0]);
    }

    public function testAddingTimestampsTz()
    {
        $blueprint = new Blueprint('users');
        $blueprint->timestampsTz();
        $statements = $blueprint->toSql($this->getConnection(), $this->getGrammar());
        $this->assertCount(1, $statements);
        $this->assertSame('alter table "users" add column "created_at" timestamp(0) with time zone null, add column "updated_at" timestamp(0) with time zone null', $statements[0]);
    }

    public function testAddingBinary()
    {
        $blueprint = new Blueprint('users');
        $blueprint->binary('foo');
        $statements = $blueprint->toSql($this->getConnection(), $this->getGrammar());

        $this->assertCount(1, $statements);
        $this->assertSame('alter table "users" add column "foo" bytea not null', $statements[0]);
    }

    public function testAddingUuid()
    {
        $blueprint = new Blueprint('users');
        $blueprint->uuid('foo');
        $statements = $blueprint->toSql($this->getConnection(), $this->getGrammar());

        $this->assertCount(1, $statements);
        $this->assertSame('alter table "users" add column "foo" uuid not null', $statements[0]);
    }

    public function testAddingGeneratedAs()
    {
        $blueprint = new Blueprint('users');
        $blueprint->increments('foo')->generatedAs();
        $statements = $blueprint->toSql($this->getConnection(), $this->getGrammar());
        $this->assertCount(1, $statements);
        $this->assertSame('alter table "users" add column "foo" integer generated by default as identity primary key not null', $statements[0]);
        // With always modifier
        $blueprint = new Blueprint('users');
        $blueprint->increments('foo')->generatedAs()->always();
        $statements = $blueprint->toSql($this->getConnection(), $this->getGrammar());
        $this->assertCount(1, $statements);
        $this->assertSame('alter table "users" add column "foo" integer generated always as identity primary key not null', $statements[0]);
        // With sequence options
        $blueprint = new Blueprint('users');
        $blueprint->increments('foo')->generatedAs('increment by 10 start with 100');
        $statements = $blueprint->toSql($this->getConnection(), $this->getGrammar());
        $this->assertCount(1, $statements);
        $this->assertSame('alter table "users" add column "foo" integer generated by default as identity (increment by 10 start with 100) primary key not null', $statements[0]);
        // Not a primary key
        $blueprint = new Blueprint('users');
        $blueprint->integer('foo')->generatedAs();
        $statements = $blueprint->toSql($this->getConnection(), $this->getGrammar());
        $this->assertCount(1, $statements);
        $this->assertSame('alter table "users" add column "foo" integer generated by default as identity not null', $statements[0]);
    }

    public function testAddingVirtualAs()
    {
        $blueprint = new Blueprint('users');
        $blueprint->integer('foo')->nullable();
        $blueprint->boolean('bar')->virtualAs('foo is not null');
        $statements = $blueprint->toSql($this->getConnection(), $this->getGrammar());
        $this->assertCount(1, $statements);
        $this->assertSame('alter table "users" add column "foo" integer null, add column "bar" boolean not null generated always as (foo is not null)', $statements[0]);
    }

    public function testAddingStoredAs()
    {
        $blueprint = new Blueprint('users');
        $blueprint->integer('foo')->nullable();
        $blueprint->boolean('bar')->storedAs('foo is not null');
        $statements = $blueprint->toSql($this->getConnection(), $this->getGrammar());
        $this->assertCount(1, $statements);
        $this->assertSame('alter table "users" add column "foo" integer null, add column "bar" boolean not null generated always as (foo is not null) stored', $statements[0]);
    }

    public function testAddingIpAddress()
    {
        $blueprint = new Blueprint('users');
        $blueprint->ipAddress('foo');
        $statements = $blueprint->toSql($this->getConnection(), $this->getGrammar());

        $this->assertCount(1, $statements);
        $this->assertSame('alter table "users" add column "foo" inet not null', $statements[0]);
    }

    public function testAddingMacAddress()
    {
        $blueprint = new Blueprint('users');
        $blueprint->macAddress('foo');
        $statements = $blueprint->toSql($this->getConnection(), $this->getGrammar());

        $this->assertCount(1, $statements);
        $this->assertSame('alter table "users" add column "foo" macaddr not null', $statements[0]);
    }

    public function testCompileForeign()
    {
        $blueprint = new Blueprint('users');
        $blueprint->foreign('parent_id')->references('id')->on('parents')->onDelete('cascade')->deferrable();
        $statements = $blueprint->toSql($this->getConnection(), $this->getGrammar());

        $this->assertCount(1, $statements);
        $this->assertSame('alter table "users" add constraint "users_parent_id_foreign" foreign key ("parent_id") references "parents" ("id") on delete cascade deferrable', $statements[0]);

        $blueprint = new Blueprint('users');
        $blueprint->foreign('parent_id')->references('id')->on('parents')->onDelete('cascade')->deferrable(false)->initiallyImmediate();
        $statements = $blueprint->toSql($this->getConnection(), $this->getGrammar());

        $this->assertCount(1, $statements);
        $this->assertSame('alter table "users" add constraint "users_parent_id_foreign" foreign key ("parent_id") references "parents" ("id") on delete cascade not deferrable', $statements[0]);

        $blueprint = new Blueprint('users');
        $blueprint->foreign('parent_id')->references('id')->on('parents')->onDelete('cascade')->deferrable()->initiallyImmediate(false);
        $statements = $blueprint->toSql($this->getConnection(), $this->getGrammar());

        $this->assertCount(1, $statements);
        $this->assertSame('alter table "users" add constraint "users_parent_id_foreign" foreign key ("parent_id") references "parents" ("id") on delete cascade deferrable initially deferred', $statements[0]);

        $blueprint = new Blueprint('users');
        $blueprint->foreign('parent_id')->references('id')->on('parents')->onDelete('cascade')->deferrable()->notValid();
        $statements = $blueprint->toSql($this->getConnection(), $this->getGrammar());

        $this->assertCount(1, $statements);
        $this->assertSame('alter table "users" add constraint "users_parent_id_foreign" foreign key ("parent_id") references "parents" ("id") on delete cascade deferrable not valid', $statements[0]);
    }

    public function testAddingGeometry()
    {
        $blueprint = new Blueprint('geo');
        $blueprint->geometry('coordinates');
        $statements = $blueprint->toSql($this->getConnection(), $this->getGrammar());

        $this->assertCount(1, $statements);
        $this->assertSame('alter table "geo" add column "coordinates" geography(geometry, 4326) not null', $statements[0]);
    }

    public function testAddingPoint()
    {
        $blueprint = new Blueprint('geo');
        $blueprint->point('coordinates');
        $statements = $blueprint->toSql($this->getConnection(), $this->getGrammar());

        $this->assertCount(1, $statements);
        $this->assertSame('alter table "geo" add column "coordinates" geography(point, 4326) not null', $statements[0]);
    }

    public function testAddingLineString()
    {
        $blueprint = new Blueprint('geo');
        $blueprint->linestring('coordinates');
        $statements = $blueprint->toSql($this->getConnection(), $this->getGrammar());

        $this->assertCount(1, $statements);
        $this->assertSame('alter table "geo" add column "coordinates" geography(linestring, 4326) not null', $statements[0]);
    }

    public function testAddingPolygon()
    {
        $blueprint = new Blueprint('geo');
        $blueprint->polygon('coordinates');
        $statements = $blueprint->toSql($this->getConnection(), $this->getGrammar());

        $this->assertCount(1, $statements);
        $this->assertSame('alter table "geo" add column "coordinates" geography(polygon, 4326) not null', $statements[0]);
    }

    public function testAddingGeometryCollection()
    {
        $blueprint = new Blueprint('geo');
        $blueprint->geometrycollection('coordinates');
        $statements = $blueprint->toSql($this->getConnection(), $this->getGrammar());

        $this->assertCount(1, $statements);
        $this->assertSame('alter table "geo" add column "coordinates" geography(geometrycollection, 4326) not null', $statements[0]);
    }

    public function testAddingMultiPoint()
    {
        $blueprint = new Blueprint('geo');
        $blueprint->multipoint('coordinates');
        $statements = $blueprint->toSql($this->getConnection(), $this->getGrammar());

        $this->assertCount(1, $statements);
        $this->assertSame('alter table "geo" add column "coordinates" geography(multipoint, 4326) not null', $statements[0]);
    }

    public function testAddingMultiLineString()
    {
        $blueprint = new Blueprint('geo');
        $blueprint->multilinestring('coordinates');
        $statements = $blueprint->toSql($this->getConnection(), $this->getGrammar());

        $this->assertCount(1, $statements);
        $this->assertSame('alter table "geo" add column "coordinates" geography(multilinestring, 4326) not null', $statements[0]);
    }

    public function testAddingMultiPolygon()
    {
        $blueprint = new Blueprint('geo');
        $blueprint->multipolygon('coordinates');
        $statements = $blueprint->toSql($this->getConnection(), $this->getGrammar());

        $this->assertCount(1, $statements);
        $this->assertSame('alter table "geo" add column "coordinates" geography(multipolygon, 4326) not null', $statements[0]);
    }

    public function testCreateDatabase()
    {
        $connection = $this->getConnection();
        $connection->shouldReceive('getConfig')->once()->once()->with('charset')->andReturn('utf8_foo');
        $statement = $this->getGrammar()->compileCreateDatabase('my_database_a', $connection);

        $this->assertSame(
            'create database "my_database_a" encoding "utf8_foo"',
            $statement
        );

        $connection = $this->getConnection();
        $connection->shouldReceive('getConfig')->once()->once()->with('charset')->andReturn('utf8_bar');
        $statement = $this->getGrammar()->compileCreateDatabase('my_database_b', $connection);

        $this->assertSame(
            'create database "my_database_b" encoding "utf8_bar"',
            $statement
        );
    }

    public function testDropDatabaseIfExists()
    {
        $statement = $this->getGrammar()->compileDropDatabaseIfExists('my_database_a');

        $this->assertSame(
            'drop database if exists "my_database_a"',
            $statement
        );

        $statement = $this->getGrammar()->compileDropDatabaseIfExists('my_database_b');

        $this->assertSame(
            'drop database if exists "my_database_b"',
            $statement
        );
    }

    public function testDropAllTablesEscapesTableNames()
    {
        $statement = $this->getGrammar()->compileDropAllTables(['alpha', 'beta', 'gamma']);

        $this->assertSame('drop table "alpha","beta","gamma" cascade', $statement);
    }

    public function testDropAllViewsEscapesTableNames()
    {
        $statement = $this->getGrammar()->compileDropAllViews(['alpha', 'beta', 'gamma']);

        $this->assertSame('drop view "alpha","beta","gamma" cascade', $statement);
    }

    public function testDropAllTypesEscapesTableNames()
    {
        $statement = $this->getGrammar()->compileDropAllTypes(['alpha', 'beta', 'gamma']);

        $this->assertSame('drop type "alpha","beta","gamma" cascade', $statement);
    }

    public function getGrammar()
    {
        return new PostgresGrammar();
    }

    public function testGrammarsAreMacroable()
    {
        // compileReplace macro.
        $this->getGrammar()::macro('compileReplace', function () {
            return true;
        });

        $c = $this->getGrammar()::compileReplace();

        $this->assertTrue($c);
    }

    public function testCompileTables(): void
    {
        $this->assertSame('select c.relname as name, n.nspname as schema, pg_total_relation_size(c.oid) as size, '
            . "obj_description(c.oid, 'pg_class') as comment from pg_class c, pg_namespace n "
            . "where c.relkind in ('r', 'p') and n.oid = c.relnamespace and n.nspname not in ('pg_catalog', 'information_schema') "
            . 'order by c.relname', $this->getGrammar()->compileTables());
    }

    public function testAddingFulltextIndexMultipleColumns()
    {
        $blueprint = new Blueprint('users');
        $blueprint->fulltext(['body', 'title']);
        $statements = $blueprint->toSql($this->getConnection(), $this->getGrammar());

        $this->assertCount(1, $statements);
        $this->assertSame('create index "users_body_title_fulltext" on "users" using gin ((to_tsvector(\'english\', "body") || to_tsvector(\'english\', "title")))', $statements[0]);
    }

    protected function getConnection()
    {
        return m::mock(Connection::class);
    }
}
