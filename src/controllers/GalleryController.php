<?php

require_once 'src/models/Image.php';

class GalleryController {
    private $imageModel;
    
    public function __construct() {
        $this->imageModel = new Image();
    }
    
    public function index() {
        $page = isset($_GET['p']) ? (int)$_GET['p'] : 1;
        $page = max(1, $page);
        
        $images = $this->imageModel->getAll($page);
        $totalImages = $this->imageModel->getTotalCount();
        $totalPages = ceil($totalImages / ITEMS_PER_PAGE);
        
        require_once 'src/views/gallery.php';
    }
}
