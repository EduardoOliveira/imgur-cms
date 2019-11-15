<?php


namespace ImgurCms\Core\Storage;


interface StorageDriver {

    public function setCollection($collection);

    public function getPage($idx);

    public function getPost($id);

    public function getUser($id);
}