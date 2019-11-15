<?php


namespace ImgurCms\Storages;


use ImgurCms\Core\Models\Context;
use ImgurCms\Core\Models\Post;
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

    public function getPage($idx, $contextName) {
        $data = json_decode(file_get_contents($this->path . '/' . $contextName . '.json'));
        return array_slice($data->items, $idx * $this->pageSize, $this->pageSize);
    }

    public function getPost($id) {
        return json_decode(file_get_contents($this->path . '/posts/' . $id . '.json'));

    }

    public function getUser($id) {
        // TODO: Implement getUser() method.
    }

    /**
     * @param $context Context
     */
    public function setContext($context) {
        file_put_contents($this->path . '/' . $context->name . '.json', json_encode($context));
    }

    /**
     * @param $post Post
     */
    public function setPost($post) {
        file_put_contents($this->path . '/posts/' . $post->id . '.json', json_encode($post));
    }
}