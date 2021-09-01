<?php
use Buchin\DdgImages\Scraper;

describe("Scraper", function () {
    describe("scrapeImages", function () {
        it("scrape images from duckduckgo", function () {
            $scraper = new Scraper();
            $images = $scraper->scrapeImages("makan nasi padang");

            expect(count($images) > 0)->toBe(true);
        });

        it("able to specify several filters", function () {
            $scraper = new Scraper([
                "filters" => [
                    "size" => "Wallpaper",
                    "color" => "color",
                    "type" => "transparent",
                ],
            ]);

            $images = $scraper->scrapeImages("tokyo revengers");
            shuffle($images);

            expect($images[0]["width"])->toBeGreaterThan(1000);
        });
    });
});
