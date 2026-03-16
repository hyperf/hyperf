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

namespace HyperfTest\Database\Stubs;

use Hyperf\Database\Model\Builder;
use Hyperf\Database\Model\Model;

class ModelStub extends Model
{
    public ?string $connection = null;

    public $scopesCalled = [];

    protected ?string $table = 'stub';

    protected array $guarded = [];

    protected $morph_to_stub_type = ModelSaveStub::class;

    public function getListItemsAttribute($value)
    {
        return json_decode($value, true);
    }

    public function setListItemsAttribute($value)
    {
        $this->attributes['list_items'] = json_encode($value);
    }

    public function getPasswordAttribute()
    {
        return '******';
    }

    public function setPasswordAttribute($value)
    {
        $this->attributes['password_hash'] = sha1($value);
    }

    public function publicIncrement($column, $amount = 1, $extra = [])
    {
        return $this->increment($column, $amount, $extra);
    }

    public function belongsToStub()
    {
        return $this->belongsTo(ModelSaveStub::class);
    }

    public function morphToStub()
    {
        return $this->morphTo();
    }

    public function morphToStubWithKeys()
    {
        return $this->morphTo(null, 'type', 'id');
    }

    public function morphToStubWithName()
    {
        return $this->morphTo('someName');
    }

    public function morphToStubWithNameAndKeys()
    {
        return $this->morphTo('someName', 'type', 'id');
    }

    public function belongsToExplicitKeyStub()
    {
        return $this->belongsTo(ModelSaveStub::class, 'foo');
    }

    public function incorrectRelationStub()
    {
        return 'foo';
    }

    public function getDates(): array
    {
        return [];
    }

    public function getAppendableAttribute()
    {
        return 'appended';
    }

    public function scopePublished(Builder $builder)
    {
        $this->scopesCalled[] = 'published';
    }

    public function scopeCategory(Builder $builder, $category)
    {
        $this->scopesCalled['category'] = $category;
    }

    public function scopeFramework(Builder $builder, $framework, $version)
    {
        $this->scopesCalled['framework'] = [$framework, $version];
    }
}
