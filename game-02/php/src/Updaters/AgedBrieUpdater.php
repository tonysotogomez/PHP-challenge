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

        #aumento normal de calidad
        $item->quality++;

        #si ya venció se aumenta calidad nuevamente
        if ($item->sellIn < 0) {
            $item->quality++;
        }

        #regla para mantener el limite
        if ($item->quality > 50) {
            $item->quality = 50;
        }
    }
}
