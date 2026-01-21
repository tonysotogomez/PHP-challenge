<?php

declare(strict_types=1);

namespace GildedRose;

use GildedRose\Updaters\Factories\UpdaterFactory;

final class GildedRose
{
    /**
     * @param Item[] $items
     */
    public function __construct(
        private array $items
    ) {
    }

    public function updateQuality(): void
    {
        foreach ($this->items as $item) {
            $updater = UpdaterFactory::create($item->name);
            $updater->update($item);
        }
    }
}
