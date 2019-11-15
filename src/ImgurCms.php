<?php


namespace ImgurCms;


use Imgur\Client;
use Imgur\Pager\BasicPager;
use ImgurCms\Core\Models\Context;
use ImgurCms\Core\Models\ContextItem;
use ImgurCms\Core\Models\Post;
use ImgurCms\Core\Storage\StorageDriver;
use stdClass;

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
        if ($tag == "") {
            $tag = self::$config->mainTag;
        }
        $albums = self::fetchAlbums($tag);
        self::organizeAndStore($albums, $tag);
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

    private static function organizeAndStore($collection, $contextName) {

        $contextObj = new Context();
        $contextObj->name = $contextName;

        foreach ($collection as $i => $e) {

            $item = new ContextItem();
            $item->id = $e['id'];
            $item->title = $e['title'];
            $item->user = $e['account_url'];
            $item->description = $e['description'];

            $post = new Post();
            $post->id = $e['id'];
            $post->user = $e['account_url'];
            $post->title = $e['title'];
            $post->description = $e['description'];
            $post->media = [];

            if (empty($e['images'])) {
                break;
            }

            foreach ($e['images'] as $media) {
                if (empty($item->cover) && strpos($media['type'], 'image') === 0) {
                    $item->cover = $media['link'];

                }
                if (empty($post->cover) && strpos($media['type'], 'image') === 0) {
                    $post->cover = $media['link'];
                }
                $m = new stdClass();
                $m->link = $media['link'];
                $m->date = $media['datetime'];
                $m->description = $media['description'];
                $m->animated = $media['animated'];
                $post->media[] = $m;
            }

            $item->tags = [];
            foreach ($e['tags'] as $tag) {
                $t = new stdClass();
                $t->name = $tag['name'];
                $t->displayName = $tag['display_name'];
                $item->tags[] = $t;
            }

            $contextObj->items[] = $item;

            self::$storage->setPost($post);
        }

        self::$storage->setContext($contextObj);
    }

    public static function getPost($post) {
        return self::$storage->getPost($post);
    }

    public static function getPage($page, $contextName = "") {
        if (empty($contextName)) {
            $contextName = self::$config->mainTag;
        }
        return self::$storage->getPage($page, $contextName);
    }
}