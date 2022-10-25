<?php

namespace App\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Security\Core\User\PasswordAuthenticatedUserInterface;
use Symfony\Component\Security\Core\User\UserInterface;
use Symfony\Component\Validator\Constraints as Assert;

#[ORM\Entity(repositoryClass: "App\Repository\UserRepository")]
class User implements UserInterface, PasswordAuthenticatedUserInterface
{
    #[
        ORM\Id(),
        ORM\GeneratedValue(),
        ORM\Column(type: "integer")
    ]
    private $id;

    #[ORM\Column(type: "string", length: 180, unique: true)]
    private $email;

    #[ORM\Column(type: "json")]
    private $roles = [];

    /**
     * @var string The hashed password
     */
    #[
        ORM\Column(type: "string"),
        Assert\NotBlank
    ]
    private $password;

    #[
        ORM\OneToMany(targetEntity: "App\Entity\RssFeed", mappedBy: "user", orphanRemoval: true)
    ]
    private $feeds;

    #[ORM\Column(type: "boolean")]
    private $groupErrorMail = 0;

    #[ORM\Column(type: "boolean")]
    private $sendEmailOnError = 1;

    public function __construct()
    {
        $this->feeds = new ArrayCollection();
    }

    public function getUserIdentifier(): string
    {
        return (string) $this->getId();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getEmail(): ?string
    {
        return $this->email;
    }

    public function setEmail(string $email): self
    {
        $this->email = $email;

        return $this;
    }

    /**
     * A visual identifier that represents this user.
     *
     * @see UserInterface
     */
    public function getUsername(): string
    {
        return (string) $this->email;
    }

    /**
     * @see UserInterface
     */
    public function getRoles(): array
    {
        $roles = $this->roles;
        // guarantee every user at least has ROLE_USER
        $roles[] = 'ROLE_USER';

        return array_unique($roles);
    }

    public function setRoles(array $roles): self
    {
        $this->roles = $roles;

        return $this;
    }

    /**
     * @see UserInterface
     */
    public function getPassword(): string
    {
        return (string) $this->password;
    }

    public function setPassword(string $password): self
    {
        $this->password = $password;

        return $this;
    }

    /**
     * @see UserInterface
     */
    public function getSalt()
    {
        // not needed when using the "bcrypt" algorithm in security.yaml
    }

    /**
     * @see UserInterface
     */
    public function eraseCredentials()
    {
        // If you store any temporary, sensitive data on the user, clear it here
        // $this->plainPassword = null;
    }

    /**
     * @return Collection|RssFeed[]
     */
    public function getFeeds(): Collection
    {
        return $this->feeds;
    }

    public function addFeed(RssFeed $feed): self
    {
        if (!$this->feeds->contains($feed)) {
            $this->feeds[] = $feed;
            $feed->setUser($this);
        }

        return $this;
    }

    public function removeFeed(RssFeed $feed): self
    {
        if ($this->feeds->contains($feed)) {
            $this->feeds->removeElement($feed);
            // set the owning side to null (unless already changed)
            if ($feed->getUser() === $this) {
                $feed->setUser(null);
            }
        }

        return $this;
    }

    public function getGroupErrorMail(): ?bool
    {
        return $this->groupErrorMail;
    }

    public function setGroupErrorMail(bool $groupErrorMail): self
    {
        $this->groupErrorMail = $groupErrorMail;

        return $this;
    }

    public function getSendEmailOnError(): ?bool
    {
        return $this->sendEmailOnError;
    }

    public function setSendEmailOnError(bool $sendEmailOnError): self
    {
        $this->sendEmailOnError = $sendEmailOnError;

        return $this;
    }
}
