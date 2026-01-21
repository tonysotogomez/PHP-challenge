<?php

declare(strict_types=1);

namespace GildedRose\Updaters;

use GildedRose\Item;
use GildedRose\Updaters\Contracts\QualityUpdater;

class BackstagePassUpdater implements QualityUpdater
{
    public function update(Item $item): void
    {
        $item->sellIn--;

        if ($item->sellIn < 0) {
            $item->quality = 0;
            return;
        }

        $item->quality++;

        if ($item->sellIn < 10) {
            $item->quality++;
        }

        if ($item->sellIn < 5) {
            $item->quality++;
        }

        if ($item->quality > 50) {
            $item->quality = 50;
        }
    }
}
