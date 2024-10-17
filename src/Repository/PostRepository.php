<?php

namespace Enzo\P5OcBlog\Repository;

use Enzo\P5OcBlog\Services\DbManager;
use PDO;

class PostRepository
{
    private $pdo;

    public function __construct(DbManager $dbManager)
    {
        $this->pdo = $dbManager->getPdo();
    }
    public function createPost($title, $content, $category, $imageUrl, $imageCaption, $authorId)
    {
        $sql = 'INSERT INTO posts (title, content, category, image_url, image_caption, author_id, created_at) 
                VALUES (:title, :content, :category, :image_url, :image_caption, :author_id, NOW())';
        $query = $this->pdo->prepare($sql);
        $query->bindParam(':title', $title);
        $query->bindParam(':content', $content);
        $query->bindParam(':category', $category);
        $query->bindParam(':image_url', $imageUrl);
        $query->bindParam(':image_caption', $imageCaption);
        $query->bindParam(':author_id', $authorId);
        $query->execute();
    }
}