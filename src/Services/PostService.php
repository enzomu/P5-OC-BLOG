<?php

namespace Enzo\P5OcBlog\Services;

use Enzo\P5OcBlog\Entity\Post;
use Enzo\P5OcBlog\Repository\PostRepository;

class PostService
{
    private PostRepository $postRepository;

    public function __construct(PostRepository $postRepository)
    {
        $this->postRepository = $postRepository;
    }


    public function createPost($title, $content, $image, $caption, $chapo, $userId): array
    {

        $post = new Post(
            null,
            $title,
            $content,
            $image,
            $caption,
            $chapo,
            $userId,
            new \DateTime()
        );

        if ($this->postRepository->save($post)) {
            return ['success' => true, 'message' => 'Post créé avec succès'];
        }
        return ['success' => false, 'message' => "Erreur lors de la création du post"];
    }


    public function updatePost($id, $title, $content, $image, $imageCaption, $chapo): array
    {
        $post = $this->postRepository->findById($id);
        if (!$post) {
            return ['success' => false, 'message' => "Post non trouvé"];
        }


        $post->setTitle($title);
        $post->setContent($content);
        $post->setImage($image);
        $post->setCaption($imageCaption);
        $post->setChapo($chapo);
        $post->setUpdatedAt(new \DateTime());

        if ($this->postRepository->save($post)) {
            return ['success' => true, 'message' => 'Post mis à jour avec succès'];
        }
        return ['success' => false, 'message' => "Erreur lors de la mise à jour du post"];
    }


    public function deletePost($id): array
    {
        if ($this->postRepository->delete($id)) {
            return ['success' => true, 'message' => 'Post supprimé avec succès'];
        }
        return ['success' => false, 'message' => "Erreur lors de la suppression du post"];
    }

    public function validatePostData(array $data): bool
    {
        if (empty($data['title']) || strlen($data['title']) > 255) {
            return false;
        }

        if (empty($data['content'])) {
            return false;
        }

        if (!empty($data['image']) && !filter_var($data['image'], FILTER_VALIDATE_URL)) {
            return false;
        }

        if (!empty($data['caption']) && strlen($data['caption']) > 255) {
            return false;
        }

        if (!empty($data['chapo']) && strlen($data['chapo']) > 500) {
            return false;
        }

        return true;
    }


    public function findPostById(int $id): ?Post
    {
        return $this->postRepository->findById($id);
    }


    public function findAllPosts(): array
    {
        return $this->postRepository->findAll();
    }

    public function findPostsByAuthorId(int $authorId): array
    {
        return $this->postRepository->findByAuthorId($authorId);
    }
}
