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

namespace Hyperf\Paginator;

use Hyperf\Contract\LengthAwarePaginatorInterface;

class UrlWindow
{
    /**
     * @param AbstractPaginator&LengthAwarePaginatorInterface $paginator
     */
    public function __construct(protected LengthAwarePaginatorInterface $paginator)
    {
    }

    /**
     * Create a new URL window instance.
     */
    public static function make(LengthAwarePaginatorInterface $paginator): array
    {
        return (new static($paginator))->get();
    }

    /**
     * Get the window of URLs to be shown.
     */
    public function get(): array
    {
        $onEachSide = $this->paginator->onEachSide;

        if ($this->paginator->lastPage() < ($onEachSide * 2) + 6) {
            return $this->getSmallSlider();
        }

        return $this->getUrlSlider($onEachSide);
    }

    /**
     * Get the page range for the current page window.
     */
    public function getAdjacentUrlRange(int $onEachSide): array
    {
        return $this->paginator->getUrlRange(
            $this->currentPage() - $onEachSide,
            $this->currentPage() + $onEachSide
        );
    }

    /**
     * Get the starting URLs of a pagination slider.
     */
    public function getStart(): array
    {
        return $this->paginator->getUrlRange(1, 2);
    }

    /**
     * Get the ending URLs of a pagination slider.
     */
    public function getFinish(): array
    {
        return $this->paginator->getUrlRange(
            $this->lastPage() - 1,
            $this->lastPage()
        );
    }

    /**
     * Determine if the underlying paginator being presented has pages to show.
     */
    public function hasPages(): bool
    {
        return $this->paginator->lastPage() > 1;
    }

    /**
     * Get the slider of URLs there are not enough pages to slide.
     */
    protected function getSmallSlider(): array
    {
        return [
            'first' => $this->paginator->getUrlRange(1, $this->lastPage()),
            'slider' => null,
            'last' => null,
        ];
    }

    /**
     * Create a URL slider links.
     */
    protected function getUrlSlider(int $onEachSide): array
    {
        $window = $onEachSide * 2;

        if (! $this->hasPages()) {
            return ['first' => null, 'slider' => null, 'last' => null];
        }

        // If the current page is very close to the beginning of the page range, we will
        // just render the beginning of the page range, followed by the last 2 of the
        // links in this list, since we will not have room to create a full slider.
        if ($this->currentPage() <= $window) {
            return $this->getSliderTooCloseToBeginning($window);
        }

        // If the current page is close to the ending of the page range we will just get
        // this first couple pages, followed by a larger window of these ending pages
        // since we're too close to the end of the list to create a full on slider.
        if ($this->currentPage() > ($this->lastPage() - $window)) {
            return $this->getSliderTooCloseToEnding($window);
        }

        // If we have enough room on both sides of the current page to build a slider we
        // will surround it with both the beginning and ending caps, with this window
        // of pages in the middle providing a Google style sliding paginator setup.
        return $this->getFullSlider($onEachSide);
    }

    /**
     * Get the slider of URLs when too close to beginning of window.
     */
    protected function getSliderTooCloseToBeginning(int $window): array
    {
        return [
            'first' => $this->paginator->getUrlRange(1, $window + 2),
            'slider' => null,
            'last' => $this->getFinish(),
        ];
    }

    /**
     * Get the slider of URLs when too close to ending of window.
     */
    protected function getSliderTooCloseToEnding(int $window): array
    {
        $last = $this->paginator->getUrlRange(
            $this->lastPage() - ($window + 2),
            $this->lastPage()
        );

        return [
            'first' => $this->getStart(),
            'slider' => null,
            'last' => $last,
        ];
    }

    /**
     * Get the slider of URLs when a full slider can be made.
     */
    protected function getFullSlider(int $onEachSide): array
    {
        return [
            'first' => $this->getStart(),
            'slider' => $this->getAdjacentUrlRange($onEachSide),
            'last' => $this->getFinish(),
        ];
    }

    /**
     * Get the current page from the paginator.
     */
    protected function currentPage(): int
    {
        return $this->paginator->currentPage();
    }

    /**
     * Get the last page from the paginator.
     */
    protected function lastPage(): int
    {
        return $this->paginator->lastPage();
    }
}
