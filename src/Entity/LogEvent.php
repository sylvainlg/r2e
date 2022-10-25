<?php

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: "App\Repository\LogEventRepository")]
class LogEvent
{
    /**
     * @var \Ramsey\Uuid\UuidInterface
     */
    #[
        ORM\Id(),
        ORM\Column(type: "uuid", unique: true),
        ORM\GeneratedValue(strategy: "CUSTOM"),
        ORM\CustomIdGenerator(class: "Ramsey\Uuid\Doctrine\UuidGenerator")
    ]
    private $id;

    #[
        ORM\ManyToOne(targetEntity: "App\Entity\RssFeed"),
        ORM\JoinColumn(nullable: false)
    ]
    private $feed;

    #[ORM\Column(type: "datetime")]
    private $datetime;

    #[ORM\Column(type: "string", length: 255, nullable: true)]
    private $type;

    #[ORM\Column(type: "text", nullable: true)]
    private $trace;

    #[ORM\Column(type: "string", length: 255, nullable: true)]
    private $message;

    public function getId()
    {
        return $this->id;
    }

    public function getFeed(): ?RssFeed
    {
        return $this->feed;
    }

    public function setFeed(?RssFeed $feed): self
    {
        $this->feed = $feed;

        return $this;
    }

    public function getDatetime(): ?\DateTime
    {
        return $this->datetime;
    }

    public function setDatetime(\DateTime $datetime): self
    {
        $this->datetime = $datetime;

        return $this;
    }

    public function getType(): ?string
    {
        return $this->type;
    }

    public function setType(?string $type): self
    {
        $this->type = $type;

        return $this;
    }

    public function getTrace(): ?string
    {
        return $this->trace;
    }

    public function setTrace(?string $trace): self
    {
        $this->trace = $trace;

        return $this;
    }

    public function getMessage(): ?string
    {
        return $this->message;
    }

    public function setMessage(?string $message): self
    {
        $this->message = $message;

        return $this;
    }
}
