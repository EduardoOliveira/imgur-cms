<?php


namespace ImgurCms\Core\Models;


class Post {

    public $id;
    public $user;
    public $title;
    public $description;
    /**
     * @var array
     */
    public $media;
    /**
     * @var string
     */
    public $cover;
}