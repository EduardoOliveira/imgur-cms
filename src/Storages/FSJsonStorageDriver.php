<?php


namespace ImgurCms\Storages;


use ImgurCms\Core\Storage\StorageDriver;
use stdClass;

class FSJsonStorageDriver implements StorageDriver {

    /**
     * @var string
     */
    private $path;
    /**
     * @var int
     */
    private $pageSize;

    /**
     * FSJsonStorageDriver constructor.
     * @param $path string
     * @param $pageSize
     */
    public function __construct($path, $pageSize) {
        $this->path = $path;
        $this->pageSize = $pageSize;
    }

    public function getPage($idx, $context = 'main') {
        $data = json_decode(file_get_contents($this->path . '/' . $context . '.json'));
        return array_slice($data->items, $idx * $this->pageSize, $this->pageSize);
    }

    public function getPost($id) {
        return json_decode(file_get_contents($this->path . '/posts/' . $id . '.json'));

    }

    public function getUser($id) {
        // TODO: Implement getUser() method.
    }

    public function setCollection($collection, $context = 'main') {
        $contextObj = new stdClass();

        foreach ($collection as $i => $e) {

            $item = new stdClass();
            $item->id = $e['id'];
            $item->user = $e['account_url'];
            $item->description = $e['description'];

            $post = new stdClass();
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
                $m = new stdClass();
                $m->link = $media['link'];
                $m->date = $media['datetime'];
                $m->description = $media['description'];
                $m->animated = $media['animated'];
                $post->media[] = $media;
            }

            $item->tags = [];
            foreach ($e['tags'] as $tag) {
                $t = new stdClass();
                $t->name = $tag['name'];
                $t->displayName = $tag['display_name'];
                $item->tags[] = $t;
            }

            $contextObj->items[] = $item;
            file_put_contents($this->path . '/posts/' . $post->id . '.json', json_encode($post));
        }

        file_put_contents($this->path . '/' . $context . '.json', json_encode($contextObj));
    }
}