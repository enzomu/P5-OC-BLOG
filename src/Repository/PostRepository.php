<?php

namespace Enzo\P5OcBlog\Repository;

use Enzo\P5OcBlog\Entity\Post;
use PDO;

class PostRepository
{
    private PDO $db;

    public function __construct(PDO $db)
    {
        $this->db = $db;
    }

    public function save(Post $post): bool
    {
        if ($post->getId() === null) {
            $sql = 'INSERT INTO posts (title, content, image, caption, chapo, created_at, user_id) 
                    VALUES (:title, :content, :image, :caption, :chapo, NOW(), :user_id)';
            $stmt = $this->db->prepare($sql);
            $stmt->bindValue(':title', $post->getTitle());
            $stmt->bindValue(':content', $post->getContent());
            $stmt->bindValue(':image', $post->getImage());
            $stmt->bindValue(':caption', $post->getcaption());
            $stmt->bindValue(':chapo', $post->getChapo());
            $stmt->bindValue(':user_id', $post->getUserId());
        } else {
            $sql = 'UPDATE posts SET title = :title, content = :content, image = :image, caption = :caption, chapo = :chapo, updated_at = NOW() 
                    WHERE id = :id';
            $stmt = $this->db->prepare($sql);
            $stmt->bindValue(':title', $post->getTitle());
            $stmt->bindValue(':content', $post->getContent());
            $stmt->bindValue(':image', $post->getImage());
            $stmt->bindValue(':caption', $post->getcaption());
            $stmt->bindValue(':chapo', $post->getChapo());
            $stmt->bindValue(':id', $post->getId());
        }
        return $stmt->execute();
    }

    public function findById(int $id): ?Post
    {
        $stmt = $this->db->prepare('SELECT * FROM posts WHERE id = :id');
        $stmt->bindValue(':id', $id);
        $stmt->execute();
        $row = $stmt->fetch();

        if ($row) {
            return $this->hydratePost($row);
        }

        return null;
    }

    public function findAll(): array
    {
        $stmt = $this->db->query('SELECT * FROM posts ORDER BY created_at DESC');
        $posts = [];
        while ($row = $stmt->fetch()) {
            $posts[] = $this->hydratePost($row);
        }
        return $posts;
    }

    public function findByAuthorId(int $authorId): array
    {
        $stmt = $this->db->prepare('SELECT * FROM posts WHERE user_id = :user_id ORDER BY created_at DESC');
        $stmt->bindValue(':user_id', $authorId);
        $stmt->execute();
        $posts = [];
        while ($row = $stmt->fetch()) {
            $posts[] = $this->hydratePost($row);
        }
        return $posts;
    }

    private function hydratePost(array $row): Post
    {
        return new Post(
            $row['id'],
            $row['title'],
            $row['content'],
            $row['image'],
            $row['caption'],
            $row['chapo'],
            $row['user_id'],
            new \DateTime($row['created_at']),
            new \DateTime($row['updated_at'])
        );
    }


    public function delete(int $id): bool
    {
        $stmt = $this->db->prepare('DELETE FROM posts WHERE id = :id');
        $stmt->bindValue(':id', $id);
        return $stmt->execute();
    }
}
