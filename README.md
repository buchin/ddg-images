# ddg-images
Scrape images from duckduckgo

## Installation
composer require buchin/ddg-images

## Usage

$scraper = new \Buchin\DdgImages\Scraper;

$images = $scraper->scrapeImages('search query');
