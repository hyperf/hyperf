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
namespace Hyperf\Database\Model\Concerns;

use Carbon\Carbon;

trait HasTimestamps
{
    /**
     * Indicates if the model should be timestamped.
     *
     * @var bool
     */
    public $timestamps = true;

    /**
     * Update the model's update timestamp.
     */
    public function touch(): bool
    {
        if (! $this->usesTimestamps()) {
            return false;
        }

        $this->updateTimestamps();

        return $this->save();
    }

    /**
     * Set the value of the "created at" attribute.
     *
     * @param mixed $value
     * @return $this
     */
    public function setCreatedAt($value)
    {
        $this->{static::CREATED_AT} = $value;

        return $this;
    }

    /**
     * Set the value of the "updated at" attribute.
     *
     * @param mixed $value
     * @return $this
     */
    public function setUpdatedAt($value)
    {
        $this->{static::UPDATED_AT} = $value;

        return $this;
    }

    /**
     * Get a fresh timestamp for the model.
     *
     * @return Carbon
     */
    public function freshTimestamp()
    {
        return Carbon::now();
    }

    /**
     * Get a fresh timestamp for the model.
     */
    public function freshTimestampString(): ?string
    {
        return $this->fromDateTime($this->freshTimestamp());
    }

    /**
     * Determine if the model uses timestamps.
     */
    public function usesTimestamps(): bool
    {
        return $this->timestamps;
    }

    /**
     * Get the name of the "created at" column.
     */
    public function getCreatedAtColumn(): ?string
    {
        return static::CREATED_AT;
    }

    /**
     * Get the name of the "updated at" column.
     */
    public function getUpdatedAtColumn(): ?string
    {
        return static::UPDATED_AT;
    }

    /**
     * Update the creation and update timestamps.
     */
    protected function updateTimestamps()
    {
        $time = $this->freshTimestamp();

        if (! is_null(static::UPDATED_AT) && ! $this->isDirty(static::UPDATED_AT)) {
            $this->setUpdatedAt($time);
        }

        if (! $this->exists && ! is_null(static::CREATED_AT)
            && ! $this->isDirty(static::CREATED_AT)) {
            $this->setCreatedAt($time);
        }
    }
}
