<?php

declare(strict_types=1);

namespace GildedRose\Updaters\Factories;
use GildedRose\Updaters\Contracts\QualityUpdater;
use GildedRose\Updaters\AgedBrieUpdater;
use GildedRose\Updaters\SulfurasUpdater;
use GildedRose\Updaters\BackstagePassUpdater;
use GildedRose\Updaters\ConjuredUpdater;
use GildedRose\Updaters\NormalItemUpdater;

class UpdaterFactory
{
    public static function create(string $itemName): QualityUpdater
    {
        return match ($itemName) {
            'Aged Brie' => new AgedBrieUpdater(),
            'Sulfuras, Hand of Ragnaros' => new SulfurasUpdater(),
            'Backstage passes to a TAFKAL80ETC concert' => new BackstagePassUpdater(),
            'Conjured Mana Cake' => new ConjuredUpdater(),
            default => new NormalItemUpdater(),
        };
    }
}
