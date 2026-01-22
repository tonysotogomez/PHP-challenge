<?php

declare(strict_types=1);

namespace GildedRose\Updaters;

use GildedRose\Item;
use GildedRose\Updaters\Contracts\QualityUpdater;

class ConjuredUpdater implements QualityUpdater
{
    public function update(Item $item): void
    {
        $item->sellIn--;

        #nueva funcionalidad: item conjured debe degradarse el doble de rapido que el normal
        $decrement = ($item->sellIn < 0) ? 4 : 2;
        $item->quality = max(0, $item->quality - $decrement);
    }
}
