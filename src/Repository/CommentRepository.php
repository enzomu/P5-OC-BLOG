<?php

namespace Enzo\P5OcBlog\Repository;

use Enzo\P5OcBlog\Entity\Comment;
use PDO;

class CommentRepository
{
    private PDO $db;

    public function __construct(PDO $db)
    {
        $this->db = $db;
    }


    public function save(Comment $comment): bool
    {
        if ($comment->getId() === null) {
            $sql = 'INSERT INTO comments (content, created_at, user_id, post_id, validated) 
                    VALUES (:content, NOW(), :user_id, :post_id, :validated)';
            $stmt = $this->db->prepare($sql);
            $stmt->bindValue(':content', $comment->getContent());
            $stmt->bindValue(':user_id', $comment->getUserId());
            $stmt->bindValue(':post_id', $comment->getPostId());
            $stmt->bindValue(':validated', $comment->getValidated());
        } else {
            $sql = 'UPDATE comments SET content = :content 
                    WHERE id = :id';
            $stmt = $this->db->prepare($sql);
            $stmt->bindValue(':content', $comment->getContent());
            $stmt->bindValue(':id', $comment->getId());
        }
        return $stmt->execute();
    }


    public function findById(int $id): ?Comment
    {
        $stmt = $this->db->prepare('SELECT * FROM comments WHERE id = :id');
        $stmt->bindValue(':id', $id);
        $stmt->execute();
        $row = $stmt->fetch();

        if ($row) {
            return new Comment(
                $row['id'],
                $row['content'],
                new \DateTime($row['created_at']),
                $row['user_id'],
                $row['post_id'],
                $row['validated']
            );
        }

        return null;
    }

    public function delete(int $id): bool
    {
        $stmt = $this->db->prepare('DELETE FROM comments WHERE id = :id');
        $stmt->bindValue(':id', $id);
        return $stmt->execute();
    }

    public function validate(int $commentId): bool
    {
        $stmt = $this->db->prepare("UPDATE comments SET validated = 1 WHERE id = :commentId");
        $stmt->bindValue(':commentId', $commentId);
        return $stmt->execute();
    }

    public function findByPostId(int $postId,  bool $onlyValidated = false): array
    {
        $sql = 'SELECT * FROM comments WHERE post_id = :post_id';
        if ($onlyValidated) {
            $sql .= ' AND validated = 1';
        }
        $sql .= ' ORDER BY created_at DESC';

        $stmt = $this->db->prepare($sql);
        $stmt->bindValue(':post_id', $postId);
        $stmt->execute();
        $comments = [];
        while ($row = $stmt->fetch()) {
            $comments[] = new Comment(
                $row['id'],
                $row['content'],
                new \DateTime($row['created_at']),
                $row['user_id'],
                $row['post_id'],
                $row['validated']
            );
        }
        return $comments;
    }

    public function findCommentsWithUsernamesByPostId(int $postId, bool $onlyValidated = false): array
    {
        $sql = "SELECT c.*, u.username FROM comments c JOIN users u ON c.user_id = u.id WHERE c.post_id = :post_id";
        if ($onlyValidated) {
            $sql .= " AND c.validated = 1";
        }
        $sql .= " ORDER BY c.created_at DESC";

        $stmt = $this->db->prepare($sql);
        $stmt->bindValue(':post_id', $postId);
        $stmt->execute();

        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function hasUnvalidatedComments(int $postId): bool
    {
        $sql = "SELECT COUNT(*) FROM comments WHERE post_id = :postId AND validated = 0";
        $stmt = $this->db->prepare($sql);
        $stmt->execute(['postId' => $postId]);

        return (bool) $stmt->fetchColumn();
    }
}
