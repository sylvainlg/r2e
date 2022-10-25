<?php

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;

#[ORM\Entity(repositoryClass: "App\Repository\RssFeedRepository")]
class RssFeed
{
    #[
        ORM\Id,
        ORM\GeneratedValue,
        ORM\Column(type: "integer")
    ]
    private $id;

    // add your own fields

    #[
        ORM\Column(type: "string", length: 2000),
        Assert\NotBlank()
    ]
    private $url;

    #[ORM\Column(type: "datetime", name: "last_update", nullable: true)]
    private $lastUpdate;

    #[
        ORM\ManyToOne(targetEntity: "App\Entity\User", inversedBy: "feeds"),
        ORM\JoinColumn(nullable: false)
    ]
    private $user;

    #[ORM\Column(type: "string", length: 255, nullable: true)]
    private $title;

    #[ORM\Column(
        type: "string",
        length: 10,
        enumType: FEED_STATUS::class,
        options: ["default" => "OK"]
    )]
    private $status = FEED_STATUS::OK;

    #[ORM\Column(type: "boolean", nullable: true)]
    private $enabled = true;

    public function getId()
    {
        return $this->id;
    }

    public function getUrl()
    {
        return $this->url;
    }

    public function setUrl($purl)
    {
        $this->url = $purl;
    }

    public function getUser(): User
    {
        return $this->user;
    }

    public function setUser(?User $puser)
    {
        $this->user = $puser;
    }

    public function getLastUpdate()
    {
        return $this->lastUpdate;
    }

    public function setLastUpdate(\Datetime $pdatetime)
    {
        $this->lastUpdate = $pdatetime;
    }

    public function getTitle(): ?string
    {
        return $this->title;
    }

    public function setTitle(?string $title): self
    {
        $this->title = $title;

        return $this;
    }

    public function getStatus(): ?FEED_STATUS
    {
        return $this->status;
    }

    public function setStatus(FEED_STATUS $status): self
    {
        $this->status = $status;

        return $this;
    }

    public function getDisplayName()
    {
        return preg_replace('#https?://(?:www\.)?([^/]+)/.*#', '$1', $this->getUrl());
    }

    public function getDisplayPath()
    {
        return preg_replace('#https?://(?:www\.)?[^/]+(/.*)#', '$1', $this->getUrl());
    }

    public function getEnabled(): ?bool
    {
        return $this->enabled;
    }

    public function setEnabled(?bool $enabled): self
    {
        $this->enabled = $enabled;

        return $this;
    }
}
