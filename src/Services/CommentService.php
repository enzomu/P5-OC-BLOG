<?php

namespace Enzo\P5OcBlog\Services;

use Enzo\P5OcBlog\Entity\Comment;
use Enzo\P5OcBlog\Repository\CommentRepository;

class CommentService
{
    private CommentRepository $commentRepository;

    public function __construct(CommentRepository $commentRepository)
    {
        $this->commentRepository = $commentRepository;
    }

    public function createComment(string $content, int $userId, int $postId, bool $validated): array
    {
        $comment = new Comment(null, $content, new \DateTime(), $userId, $postId, $validated);

        if ($this->commentRepository->save($comment)) {
            return ['success' => true, 'message' => 'Commentaire ajouté avec succès'];
        } else {
            return ['success' => false, 'message' => "Erreur lors de l'ajout du commentaire"];
        }
    }

    public function updateComment(int $id, string $content): array
    {
        $comment = $this->commentRepository->findById($id);

        if ($comment) {
            $updatedComment = new Comment($id, $content, new \DateTime(), $comment->getUserId(), $comment->getPostId());
            if ($this->commentRepository->save($updatedComment)) {
                return ['success' => true, 'message' => 'Commentaire mis à jour avec succès'];
            } else {
                return ['success' => false, 'message' => "Erreur lors de la mise à jour du commentaire"];
            }
        }

        return ['success' => false, 'message' => 'Commentaire non trouvé'];
    }

    public function validateComment(int $commentId): bool
    {
        return $this->commentRepository->validate($commentId);
    }

    public function deleteComment(int $commentId): bool
    {
        return $this->commentRepository->delete($commentId);
    }

    public function getCommentsByPostId(int $postId): array
    {
        return $this->commentRepository->findByPostId($postId);
    }

    public function getCommentsWithUsernamesByPostId(int $postId, $onlyValidated): array
    {
        return $this->commentRepository->findCommentsWithUsernamesByPostId($postId, $onlyValidated);
    }

    public function getCommentById(int $id): ?Comment
    {
        return $this->commentRepository->findById($id);
    }

    public function hasUnvalidatedComments(int $postId): bool
    {
        return $this->commentRepository->hasUnvalidatedComments($postId);
    }
}
