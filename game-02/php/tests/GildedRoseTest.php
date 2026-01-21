<?php

declare(strict_types=1);

namespace Tests;

use GildedRose\GildedRose;
use GildedRose\Item;
use PHPUnit\Framework\TestCase;

class GildedRoseTest extends TestCase
{
    // public function testFoo(): void
    // {
    //     $items = [new Item('foo', 0, 0)];
    //     $gildedRose = new GildedRose($items);
    //     $gildedRose->updateQuality();
    //     $this->assertSame('foo', $items[0]->name);
    // }
    public function testNormalItemDegradesCorrectly(): void
    {
        $items = [new Item('Normal Item', 10, 20)];
        $gildedRose = new GildedRose($items);
        $gildedRose->updateQuality();

        $this->assertSame(9, $items[0]->sellIn);
        $this->assertSame(19, $items[0]->quality);
    }

    public function testAgedBrieIncreasesInQuality(): void
    {
        $items = [new Item('Aged Brie', 10, 20)];
        $gildedRose = new GildedRose($items);
        $gildedRose->updateQuality();

        $this->assertSame(21, $items[0]->quality);
    }

    public function testConjuredItemDegradesDoubleFast(): void
    {
        $items = [new Item('Conjured Mana Cake', 10, 20)];
        $gildedRose = new GildedRose($items);
        $gildedRose->updateQuality();

        // Baja 2 en lugar de 1
        $this->assertSame(18, $items[0]->quality);
    }
}
