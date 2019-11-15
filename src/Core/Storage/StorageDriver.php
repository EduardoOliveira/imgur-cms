<?php


namespace ImgurCms\Core\Storage;


interface StorageDriver {

    public function setContext($context);

    public function setPost($post);

    public function getPage($idx, $contextName);

    public function getPost($id);

    public function getUser($id);
}