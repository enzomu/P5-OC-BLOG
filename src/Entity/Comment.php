<?php

namespace Enzo\P5OcBlog\Entity;

class Comment
{
    private ?int $id;
    private string $content;
    private \DateTime $createdAt;
    private int $userId;
    private int $postId;
    private bool $validated;

    public function __construct(?int $id, string $content, \DateTime $createdAt, int $userId, int $postId, bool $validated)
    {
        $this->id = $id;
        $this->content = $content;
        $this->createdAt = $createdAt;
        $this->userId = $userId;
        $this->postId = $postId;
        $this->validated = $validated;
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getContent(): string
    {
        return $this->content;
    }

    public function getCreatedAt(): \DateTime
    {
        return $this->createdAt;
    }

    public function getUserId(): int
    {
        return $this->userId;
    }

    public function getPostId(): int
    {
        return $this->postId;
    }
    public function getValidated(): int
    {
        return $this->validated;
    }
}