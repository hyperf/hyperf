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

namespace Hyperf\ViewEngine\Concern;

use Closure;
use Hyperf\Collection\Arr;
use Hyperf\ViewEngine\Contract\Htmlable;
use Hyperf\ViewEngine\HtmlString;
use Hyperf\ViewEngine\View;
use InvalidArgumentException;

use function Hyperf\Collection\last;

trait ManagesComponents
{
    /**
     * The components being rendered.
     */
    protected array $componentStack = [];

    /**
     * The original data passed to the component.
     */
    protected array $componentData = [];

    /**
     * The slot contents for the component.
     */
    protected array $slots = [];

    /**
     * The names of the slots being rendered.
     */
    protected array $slotStack = [];

    /**
     * Start a component rendering process.
     *
     * @param Closure|Htmlable|string|View $view
     */
    public function startComponent(mixed $view, array $data = [])
    {
        if (ob_start()) {
            $this->componentStack[] = $view;

            $this->componentData[$this->currentComponent()] = $data;

            $this->slots[$this->currentComponent()] = [];
        }
    }

    /**
     * Get the first view that actually exists from the given list, and start a component.
     */
    public function startComponentFirst(array $names, array $data = [])
    {
        $name = Arr::first($names, fn ($item) => $this->exists($item));

        $this->startComponent($name, $data);
    }

    /**
     * Render the current component.
     *
     * @return string
     */
    public function renderComponent()
    {
        $view = array_pop($this->componentStack);

        $data = $this->componentData();

        if ($view instanceof Closure) {
            $view = $view($data);
        }

        if ($view instanceof View) {
            return $view->with($data)->render();
        }

        if ($view instanceof Htmlable) {
            return $view->toHtml();
        }

        return $this->make($view, $data)->render();
    }

    /**
     * Start the slot rendering process.
     *
     * @param string $name
     * @param null|string $content
     */
    public function slot($name, $content = null)
    {
        if (func_num_args() > 2) {
            throw new InvalidArgumentException('You passed too many arguments to the [' . $name . '] slot.');
        }
        if (func_num_args() === 2) {
            $this->slots[$this->currentComponent()][$name] = $content;
        } elseif (ob_start()) {
            $this->slots[$this->currentComponent()][$name] = '';

            $this->slotStack[$this->currentComponent()][] = $name;
        }
    }

    /**
     * Save the slot content for rendering.
     */
    public function endSlot()
    {
        last($this->componentStack);

        $currentSlot = array_pop(
            $this->slotStack[$this->currentComponent()]
        );

        $this->slots[$this->currentComponent()][$currentSlot] = new HtmlString(trim(ob_get_clean()));
    }

    /**
     * Get the data for the given component.
     *
     * @return array
     */
    protected function componentData()
    {
        $defaultSlot = new HtmlString(trim(ob_get_clean()));

        $slots = array_merge([
            '__default' => $defaultSlot,
        ], $this->slots[count($this->componentStack)]);

        return array_merge(
            $this->componentData[count($this->componentStack)],
            ['slot' => $defaultSlot],
            $this->slots[count($this->componentStack)],
            ['__laravel_slots' => $slots]
        );
    }

    /**
     * Get the index for the current component.
     *
     * @return int
     */
    protected function currentComponent()
    {
        return count($this->componentStack) - 1;
    }
}
