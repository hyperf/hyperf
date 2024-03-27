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

namespace Hyperf\ViewEngine\Component;

class AnonymousComponent extends Component
{
    /**
     * Create a new anonymous component instance.
     *
     * @param string $view the component view
     * @param array $data the component data
     */
    public function __construct(protected string $view, protected array $data)
    {
    }

    /**
     * Get the view / view contents that represent the component.
     */
    public function render(): mixed
    {
        return $this->view;
    }

    /**
     * Get the data that should be supplied to the view.
     */
    public function data(): array
    {
        $this->attributes = $this->attributes ?: new ComponentAttributeBag();

        return $this->data + ['attributes' => $this->attributes];
    }
}
