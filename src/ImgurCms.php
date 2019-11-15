<?php


namespace ImgurCms;


use Imgur\Client;
use Imgur\Pager\BasicPager;
use ImgurCms\Core\Storage\StorageDriver;

class ImgurCms {

    /**
     * @var Client
     */
    private static $client;

    private static $config;

    /**
     * @var StorageDriver
     */
    private static $storage;

    /**
     * @param $secrets object
     * @param $config object
     * @param $storage StorageDriver
     */
    public static function init($secrets, $config, $storage) {
        self::$config = $config;
        self::$storage = $storage;
        self::$client = new Client();
        self::$client->setOption('client_id', $secrets->clientId);
    }

    public static function fetchPosts($tag = "") {

        $albums = self::fetchAlbums($tag);
        self::$storage->setCollection($albums);
        return $albums;
    }

    public static function fetchAlbums($tag) {
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
        foreach ($albums as $i => $a) {
            if (!in_array($a['account_url'], self::$config->userWhitelist)) {
                unset($albums[$i]);
            }
        }
        return $albums;
    }

    public static function getPost($post) {
        return self::$storage->getPost($post);
    }

    public static function getPage($page) {
        return self::$storage->getPage($page);
    }
}