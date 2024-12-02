<?php

namespace Enzo\P5OcBlog\Entity;

class Post
{
    private ?int $id;
    private string $title;
    private string $content;
    private ?string $image;
    private ?string $caption;
    private ?string $chapo;
    private \DateTime $createdAt;
    private ?\DateTime $updatedAt;
    private int $userId;

    public function __construct(?int $id, string $title, string $content, ?string $image, ?string $caption, ?string $chapo, int $userId, \DateTime $createdAt, ?\DateTime $updatedAt = null)
    {
        $this->id = $id;
        $this->title = $title;
        $this->content = $content;
        $this->image = $image;
        $this->caption = $caption;
        $this->chapo = $chapo;
        $this->createdAt = $createdAt;
        $this->updatedAt = $updatedAt;
        $this->userId = $userId;
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getTitle(): string
    {
        return $this->title;
    }

    public function setTitle(string $title): void
    {
        $this->title = $title;
    }

    public function getContent(): string
    {
        return $this->content;
    }

    public function setContent(string $content): void
    {
        $this->content = $content;
    }

    public function getImage(): ?string
    {
        return $this->image;
    }

    public function setImage(?string $image): void
    {
        $this->image = $image;
    }

    public function getcaption(): ?string
    {
        return $this->caption;
    }

    public function Setcaption(?string $caption): void
    {
        $this->caption = $caption;
    }

    public function getChapo(): ?string
    {
        return $this->chapo;
    }

    public function setChapo(?string $chapo): void
    {
        $this->chapo = $chapo;
    }

    public function getCreatedAt(): \DateTime
    {
        return $this->createdAt;
    }

    public function getUpdatedAt(): ?\DateTime
    {
        return $this->updatedAt;
    }

    public function setUpdatedAt(?\DateTime $updatedAt): void
    {
        $this->updatedAt = $updatedAt;
    }

    public function getUserId(): int
    {
        return $this->userId;
    }
}
