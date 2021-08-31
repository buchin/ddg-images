<?php
use Buchin\DdgImages\Scraper;

describe("Scraper", function () {
    describe("scrapeImages", function () {
        it("scrape images from duckduckgo", function () {
            $scraper = new Scraper();
            $images = $scraper->scrapeImages("makan nasi");

            expect(count($images) > 0)->toBe(true);
        });
    });
});
