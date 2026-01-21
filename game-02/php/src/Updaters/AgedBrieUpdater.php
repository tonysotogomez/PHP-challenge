<?php

declare(strict_types=1);

namespace GildedRose\Updaters;
use GildedRose\Updaters\Contracts\QualityUpdater;

use GildedRose\Item;

class AgedBrieUpdater implements QualityUpdater
{
    public function update(Item $item): void
    {
        $item->sellIn--;

        $item->quality++;
        if ($item->sellIn < 0) {
            $item->quality++;
        }

        if ($item->quality > 50) {
            $item->quality = 50;
        }
    }
}
