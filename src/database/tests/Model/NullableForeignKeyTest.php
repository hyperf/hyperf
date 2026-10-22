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

namespace HyperfTest\Database\Model;

use Hyperf\Database\Model\Builder;
use Hyperf\Database\Model\Collection;
use Hyperf\Database\Model\Relations\BelongsTo;
use Hyperf\Database\Model\Relations\MorphTo;
use HyperfTest\Database\Stubs\ModelStub;
use Mockery;
use PHPUnit\Framework\Attributes\CoversNothing;
use PHPUnit\Framework\TestCase;

/**
 * The foreign key of a `BelongsTo` (and the type / foreign key of a `MorphTo`) is
 * nullable, which means an unassociated model holds a null one there. Using null as
 * an array offset is deprecated since PHP 8.1, so those models have to be skipped
 * instead of being looked up in the dictionary.
 *
 * @internal
 * @coversNothing
 */
#[CoversNothing]
class NullableForeignKeyTest extends TestCase
{
    protected function tearDown(): void
    {
        Mockery::close();
    }

    public function testBelongsToMatchAssociatesModelsByForeignKey(): void
    {
        $associated = new ModelStub(['user_id' => 1]);
        $result = new ModelStub(['id' => 1, 'name' => 'hyperf']);

        $deprecations = $this->captureDeprecations(function () use ($associated, $result): void {
            $this->belongsTo()->match(
                [$associated],
                new Collection([$result]),
                'user'
            );
        });

        $this->assertSame([], $deprecations);
        $this->assertTrue($associated->relationLoaded('user'));
        $this->assertSame($result, $associated->getRelation('user'));
    }

    public function testBelongsToMatchSkipsNullForeignKey(): void
    {
        $unassociated = new ModelStub(['user_id' => null]);
        $associated = new ModelStub(['user_id' => 1]);

        $deprecations = $this->captureDeprecations(function () use ($unassociated, $associated): void {
            $this->belongsTo()->match(
                [$unassociated, $associated],
                new Collection([new ModelStub(['id' => 1, 'name' => 'hyperf'])]),
                'user'
            );
        });

        $this->assertSame([], $deprecations);
        $this->assertFalse($unassociated->relationLoaded('user'));
        $this->assertTrue($associated->relationLoaded('user'));
        $this->assertSame('hyperf', $associated->getRelation('user')->name);
    }

    public function testBelongsToMatchDoesNotMatchNullForeignKeyAgainstNullOwnerKey(): void
    {
        // Both the foreign key and the owner key are null. They used to end up
        // under the same "" array key, which made the unassociated model match
        // a result that shouldn't be associated with it at all.
        $unassociated = new ModelStub(['user_id' => null]);

        $deprecations = $this->captureDeprecations(function () use ($unassociated): void {
            $this->belongsTo()->match(
                [$unassociated],
                new Collection([new ModelStub(['name' => 'hyperf'])]),
                'user'
            );
        });

        $this->assertSame([], $deprecations);
        $this->assertFalse($unassociated->relationLoaded('user'));
    }

    public function testBelongsToMatchSkipsNullOwnerKey(): void
    {
        // The owner key is missing here, just like the rows fetched by `with('user:name')`
        $associated = new ModelStub(['user_id' => 1]);

        $deprecations = $this->captureDeprecations(function () use ($associated): void {
            $this->belongsTo()->match(
                [$associated],
                new Collection([new ModelStub(['name' => 'hyperf'])]),
                'user'
            );
        });

        $this->assertSame([], $deprecations);
        $this->assertFalse($associated->relationLoaded('user'));
    }

    public function testBelongsToMatchLeavesRelationUnloadedWhenOwnerKeyIsUnknown(): void
    {
        $associated = new ModelStub(['user_id' => 2]);

        $deprecations = $this->captureDeprecations(function () use ($associated): void {
            $this->belongsTo()->match(
                [$associated],
                new Collection([new ModelStub(['id' => 1, 'name' => 'hyperf'])]),
                'user'
            );
        });

        $this->assertSame([], $deprecations);
        $this->assertFalse($associated->relationLoaded('user'));
    }

    public function testMorphToBuildDictionarySkipsUnassociatedModels(): void
    {
        $morphTo = $this->morphTo();
        $associated = new ModelStub(['imageable_type' => ModelStub::class, 'imageable_id' => 1]);

        $deprecations = $this->captureDeprecations(function () use ($morphTo, $associated): void {
            $morphTo->addEagerConstraints([
                $associated,
                new ModelStub(['imageable_type' => ModelStub::class, 'imageable_id' => null]),
                new ModelStub(['imageable_type' => null, 'imageable_id' => 2]),
            ]);
        });

        $this->assertSame([], $deprecations);
        $this->assertSame([ModelStub::class => [1 => [$associated]]], $morphTo->getDictionary());
    }

    public function testMorphToBuildDictionarySkipsEmptyMorphType(): void
    {
        $morphTo = $this->morphTo();

        $deprecations = $this->captureDeprecations(function () use ($morphTo): void {
            $morphTo->addEagerConstraints([
                new ModelStub(['imageable_type' => '', 'imageable_id' => 1]),
            ]);
        });

        $this->assertSame([], $deprecations);
        $this->assertSame([], $morphTo->getDictionary());
    }

    public function testMorphToBuildDictionaryGroupsModelsByTypeAndKey(): void
    {
        $morphTo = $this->morphTo();
        $first = new ModelStub(['imageable_type' => ModelStub::class, 'imageable_id' => 1]);
        $second = new ModelStub(['imageable_type' => ModelStub::class, 'imageable_id' => 1]);

        $deprecations = $this->captureDeprecations(function () use ($morphTo, $first, $second): void {
            $morphTo->addEagerConstraints([$first, $second]);
        });

        $this->assertSame([], $deprecations);
        $this->assertSame([ModelStub::class => [1 => [$first, $second]]], $morphTo->getDictionary());
    }

    private function belongsTo(): BelongsTo
    {
        return new BelongsTo($this->mockBuilder(), new ModelStub(), 'user_id', 'id', 'user');
    }

    private function morphTo(): MorphTo
    {
        return new MorphTo($this->mockBuilder(), new ModelStub(), 'imageable_id', 'id', 'imageable_type', 'imageable');
    }

    private function mockBuilder(): Builder
    {
        $builder = Mockery::mock(Builder::class);
        $builder->shouldReceive('getModel')->andReturn(new ModelStub());
        $builder->shouldReceive('where')->andReturnSelf();

        return $builder;
    }

    /**
     * Run the given callback and collect the deprecations it triggers.
     *
     * @return string[]
     */
    private function captureDeprecations(callable $callback): array
    {
        $deprecations = [];

        set_error_handler(static function (int $errno, string $errstr) use (&$deprecations): bool {
            if (str_contains($errstr, 'array offset')) {
                $deprecations[] = $errstr;
            }

            // Swallow the deprecations to keep the suite output clean, but let
            // warnings and notices bubble up to the standard error handling.
            return ($errno & (E_DEPRECATED | E_USER_DEPRECATED)) !== 0;
        }, E_ALL);

        try {
            $callback();
        } finally {
            restore_error_handler();
        }

        return $deprecations;
    }
}
