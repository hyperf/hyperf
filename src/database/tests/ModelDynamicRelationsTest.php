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

namespace HyperfTest\Database;

use Hyperf\Database\ConnectionInterface;
use Hyperf\Database\ConnectionResolverInterface;
use Hyperf\Database\Model\Model;
use Hyperf\Database\Model\Register;
use Hyperf\Database\Model\Relations\HasMany;
use Hyperf\Database\Model\Relations\HasOne;
use Mockery;
use PHPUnit\Framework\Attributes\CoversNothing;
use PHPUnit\Framework\TestCase;

/**
 * @internal
 * @coversNothing
 */
#[CoversNothing]
class ModelDynamicRelationsTest extends TestCase
{
    public function setUp(): void
    {
        $grammarClass = 'Hyperf\Database\Query\Grammars\Grammar';
        $processorClass = 'Hyperf\Database\Query\Processors\Processor';
        $grammar = new $grammarClass();
        $processor = new $processorClass();
        $connection = Mockery::mock(ConnectionInterface::class, ['getQueryGrammar' => $grammar, 'getPostProcessor' => $processor]);
        $resolver = Mockery::mock(ConnectionResolverInterface::class, ['connection' => $connection]);
        Register::setConnectionResolver($resolver);
    }

    public function testBasicDynamicRelations()
    {
        DynamicRelationModel::resolveRelationUsing('dynamicRel_2', fn () => new FakeHasManyRel());
        $model = new DynamicRelationModel();
        $this->assertEquals(['many' => 'related'], $model->dynamicRel_2);
        $this->assertEquals(['many' => 'related'], $model->getRelationValue('dynamicRel_2'));
    }

    public function testBasicDynamicRelationsOverride()
    {
        // Dynamic Relations can override each other.
        DynamicRelationModel::resolveRelationUsing('dynamicRelConflict', fn ($m) => $m->hasOne(DynamicRelationModel2::class));
        DynamicRelationModel::resolveRelationUsing('dynamicRelConflict', fn (DynamicRelationModel $m) => new FakeHasManyRel());

        $model = new DynamicRelationModel();
        $this->assertInstanceOf(HasMany::class, $model->dynamicRelConflict());
        $this->assertEquals(['many' => 'related'], $model->dynamicRelConflict);
        $this->assertEquals(['many' => 'related'], $model->getRelationValue('dynamicRelConflict'));
        $this->assertTrue($model->isRelation('dynamicRelConflict'));
    }

    public function testInharitedDynamicRelations()
    {
        DynamicRelationModel::resolveRelationUsing('inheritedDynamicRel', fn () => new FakeHasManyRel());
        $model = new DynamicRelationModel();
        $model4 = new DynamicRelationModel4();
        $this->assertEquals($model->inheritedDynamicRel(), $model4->inheritedDynamicRel());
        $this->assertEquals($model->inheritedDynamicRel, $model4->inheritedDynamicRel);
    }

    public function testInheritedDynamicRelationsOverride()
    {
        // Inherited Dynamic Relations can be overriden
        DynamicRelationModel::resolveRelationUsing('dynamicRelConflict', fn ($m) => $m->hasOne(DynamicRelationModel2::class));
        $model = new DynamicRelationModel();
        $model4 = new DynamicRelationModel4();
        $this->assertInstanceOf(HasOne::class, $model->dynamicRelConflict());
        $this->assertInstanceOf(HasOne::class, $model4->dynamicRelConflict());
        DynamicRelationModel4::resolveRelationUsing('dynamicRelConflict', fn ($m) => $m->hasMany(DynamicRelationModel2::class));
        $this->assertInstanceOf(HasOne::class, $model->dynamicRelConflict());
        $this->assertInstanceOf(HasMany::class, $model4->dynamicRelConflict());
    }

    public function testDynamicRelationsCanNotHaveTheSameNameAsNormalRelations()
    {
        $model = new DynamicRelationModel();

        // Dynamic relations can not override hard-coded methods.
        DynamicRelationModel::resolveRelationUsing('hardCodedRelation', fn ($m) => $m->hasOne(DynamicRelationModel2::class));
        $this->assertInstanceOf(HasMany::class, $model->hardCodedRelation());
        $this->assertEquals(['many' => 'related'], $model->hardCodedRelation);
        $this->assertEquals(['many' => 'related'], $model->getRelationValue('hardCodedRelation'));
    }

    public function testRelationResolvers()
    {
        $model1 = new DynamicRelationModel();
        $model3 = new DynamicRelationModel3();

        // Same dynamic methods with the same name on two models do not conflict or override.
        DynamicRelationModel::resolveRelationUsing('dynamicRel', fn ($m) => $m->hasOne(DynamicRelationModel2::class));
        DynamicRelationModel3::resolveRelationUsing('dynamicRel', fn (DynamicRelationModel3 $m) => $m->hasMany(DynamicRelationModel2::class));
        $this->assertInstanceOf(HasOne::class, $model1->dynamicRel());
        $this->assertInstanceOf(HasMany::class, $model3->dynamicRel());
    }
}

class DynamicRelationModel extends Model
{
    public function hardCodedRelation()
    {
        return new FakeHasManyRel();
    }
}

class DynamicRelationModel2 extends Model
{
}

class DynamicRelationModel3 extends Model
{
}

class DynamicRelationModel4 extends DynamicRelationModel
{
}

class FakeHasManyRel extends HasMany
{
    public function __construct()
    {
    }

    public function getResults()
    {
        return ['many' => 'related'];
    }
}
