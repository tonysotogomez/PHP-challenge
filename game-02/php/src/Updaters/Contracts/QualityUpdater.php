<?php

declare(strict_types=1);

namespace GildedRose\Updaters\Contracts;

use GildedRose\Item;

interface QualityUpdater
{
    public function update(Item $item): void;
}
