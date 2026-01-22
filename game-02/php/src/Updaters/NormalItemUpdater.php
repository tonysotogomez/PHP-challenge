<?php

declare(strict_types=1);

namespace GildedRose\Updaters;

use GildedRose\Item;
use GildedRose\Updaters\Contracts\QualityUpdater;

class NormalItemUpdater implements QualityUpdater
{
    public function update(Item $item): void
    {
        $item->sellIn--;

        #siempre se reduce calidad, pero se reduce nuevamente si ya venció ($item->sellIn < 0)
        $decrement = ($item->sellIn < 0) ? 2 : 1;
        $item->quality = max(0, $item->quality - $decrement);
    }
}
