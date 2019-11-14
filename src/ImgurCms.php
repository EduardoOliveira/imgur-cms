<?php


namespace ImgurCms;


use Imgur\Client;
use Imgur\Pager\BasicPager;

class ImgurCms {

    /**
     * @var Client
     */
    private static $client;

    private static $config;

    public static function init($secrets, $config) {
        self::$config = $config;
        self::$client = new Client();
        self::$client->setOption('client_id', $secrets->clientId);
    }

    public static function fetchPosts($tag = "") {
        if ($tag == "") {
            $tag = self::$config->mainTag;
        }

        $albums = [];

        $page = 1;
        $pager = new BasicPager();

        $res = self::$client->api("gallery")->galleryTag($tag);

        if (!empty($res->items)) {
            $albums = array_merge($albums, $res['items']);
        }

        while (!empty($res)) {

            $pager->setPage($page++);

            $res = self::$client->api("gallery")->galleryTag($tag);
            $albums = array_merge($albums, $res['items']);
            if ($page > 10) {
                break;
            }
        }
        return $albums;
    }
}