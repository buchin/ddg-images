<?php
namespace Buchin\DdgImages;

use Illuminate\Support\Facades\Http;

class Scraper
{
    public $base_url = "http://duckduckgo.com/";
    public $options = [
        "max_retry" => 3,
        "proxy" => false,
        "start" => 0,
        "filters" => [],
    ];

    public function __construct($options = [])
    {
        $this->options = array_merge($this->options, $options);
    }

    public function http()
    {
        return Http::withHeaders([
            "User-Agent" =>
                "Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:68.0) Gecko/20100101 Firefox/68.0",
        ]);
    }

    public function getToken($query)
    {
        $url =
            $this->base_url .
            "?" .
            http_build_query([
                "q" => $query,
                "t" => "h_",
                "iax" => "images",
                "ia" => "images",
            ]);

        $html = file_get_contents($url);

        // vqd='3-142216309694420347760037715005911496568-220342727435306949816443430106535095457';
        $vqd_token = "";
        if (
            !preg_match("/vqd\s*\=\s*\'(?<vqd_token>[^\']*)/", $html, $matches)
        ) {
            throw new \Exception("Error: Banned IP. We will rest for a bit");
        }

        $vqd_token = $matches["vqd_token"];

        return $vqd_token;
    }

    public function getImages($query)
    {
        $options = $this->options;

        $filters = $this->buildFilters();

        $vqd_token = $this->getToken($query);

        $url =
            $this->base_url .
            "i.js?l=wt-wt&o=json&q=" .
            urlencode($query) .
            "&vqd=" .
            $vqd_token .
            "&f=" .
            $filters .
            "&p=1&v7exp=a&sltexp=b&s=" .
            $options["start"];

        $html = file_get_contents($url);
        // "results":[{"title":"Nintendo 64 controller

        if (
            !preg_match('/"results":(?<images_json>.+?\}\])/m', $html, $matches)
        ) {
            throw new \Exception("Error: unable to extract images json...");
        }

        $images_json = $matches["images_json"];
        $images = json_decode($images_json, true);

        foreach ($images as $key => $image) {
            $images[$key]["url"] = $image["image"];
        }

        return $images;
    }

    public function buildFilters()
    {
        // time:Week,size:Wallpaper,color:color,type:transparent,layout:Tall,license:Share

        $filters = "{time},{size},{color},{type},{layout},{license}";

        foreach (
            ["time", "size", "color", "type", "layout", "license"]
            as $filter
        ) {
            if (!isset($this->options["filters"][$filter])) {
                $filters = str_replace("{" . $filter . "}", "", $filters);
            } else {
                $filters = str_replace(
                    "{" . $filter . "}",
                    $filter . ":" . $this->options["filters"][$filter],
                    $filters
                );
            }
        }

        return $filters;
    }

    public function scrapeImages($keyword, $tries = 0)
    {
        try {
            $images = [];

            $images = $this->getImages($keyword);

            return $images;
        } catch (\Exception $e) {
            if ($tries > $this->options["max_retry"]) {
                return [];
            }

            $tries++;
            if (!$this->options["proxy"]) {
                sleep(15 * $tries);
            }
            return $this->scrapeImages($keyword, $tries);
        }
    }
}
