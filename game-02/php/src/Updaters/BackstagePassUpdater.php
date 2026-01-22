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

        // si el concierto terminó, entonces calidad es 0 ($item->quality - $item->quality)
        if ($item->sellIn < 0) {
            $item->quality = 0;
            return;
        }

        #siempre se aumenta en 1 la calidad
        $item->quality++;

        //como reducimos sellIn al inicio, ajustamos la condicional en 10 ($item->sellIn < 11)
        if ($item->sellIn < 10) {
            $item->quality++;
        }

        //como reducimos sellIn al inicio, ajustamos la condicional en 5 ($item->sellIn < 6)
        if ($item->sellIn < 5) {
            $item->quality++;
        }

        #regla para mantener el limite
        if ($item->quality > 50) {
            $item->quality = 50;
        }
    }
}
