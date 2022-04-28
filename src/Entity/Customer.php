<?php

namespace App\Entity;

use DateTime;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;
use Doctrine\Common\Collections\Collection;
use Doctrine\Common\Collections\ArrayCollection;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * Class Customer
 * @UniqueEntity(fields={"siret"}, message="Il existe déjà un client avec ce siret")
 * @UniqueEntity(fields={"name"}, message="Il existe déjà un client avec ce nom")
 */

class Customer
{
    /**
     * @var int
     * @ORM\Id
     * @ORM\GeneratedValue
     * @ORM\Column(type="integer")
     */
    private int $id;

    /**
     * @var string
     * @ORM\Column(type="string", length=50, unique=true)
     * @Assert\NotBlank(message="Vous devez ajouter un nom")
     */
    private string $name;

    /**
     * @var string
     * @ORM\Column(type="string", length=100, unique=true)
     * @Assert\NotBlank(message="Le numéro de SIRET est obligatoire")
     * @Assert\Regex(pattern="/^[0-9]{3}\s[0-9]{3}\s[0-9]{3}\s[0-9]{5}$/", message="Le format est invalide, merci d'utiliser le format XXX XXX XXX XXXXX")
     */
    private string $siret;

    /**
     * @var DateTime
     * @ORM\Column(type="datetime", nullable=true)
     */
    private DateTime $expireAt;

    /**
     * @var bool
     * @ORM\Column(type="boolean")
     */
    private bool $isAllowed;

    /**
     * @ORM\OneToMany(targetEntity=User::class, mappedBy="customer", orphanRemoval=true)
     */
    private $users;

    public function __construct()
    {
        $this->users = new ArrayCollection();
    }


    /**
     * Get the value of isAllowed
     *
     * @return  bool
     */ 
    public function getIsAllowed()
    {
        return $this->isAllowed;
    }

    /**
     * Set the value of isAllowed
     *
     * @param  bool  $isAllowed
     *
     * @return  self
     */ 
    public function setIsAllowed(bool $isAllowed)
    {
        $this->isAllowed = $isAllowed;

        return $this;
    }

    /**
     * Get the value of expireAt
     *
     * @return  \DateTime
     */ 
    public function getExpireAt()
    {
        return $this->expireAt;
    }

    /**
     * Set the value of expireAt
     *
     * @param  \DateTime  $expireAt
     *
     * @return  self
     */ 
    public function setExpireAt(\DateTime $expireAt)
    {
        $this->expireAt = $expireAt;

        return $this;
    }

    /**
     * Get the value of siret
     *
     * @return  string
     */ 
    public function getSiret()
    {
        return $this->siret;
    }

    /**
     * Set the value of siret
     *
     * @param  string  $siret
     *
     * @return  self
     */ 
    public function setSiret(string $siret)
    {
        $this->siret = $siret;

        return $this;
    }

    /**
     * Get the value of name
     *
     * @return  string
     */ 
    public function getName()
    {
        return $this->name;
    }

    /**
     * Set the value of name
     *
     * @param  string  $name
     *
     * @return  self
     */ 
    public function setName(string $name)
    {
        $this->name = $name;

        return $this;
    }

    /**
     * Get the value of id
     *
     * @return  int
     */ 
    public function getId()
    {
        return $this->id;
    }

    /**
     * @return Collection|User[]
     */
    public function getUsers(): Collection
    {
        return $this->users;
    }

    public function addUser(User $user): self
    {
        if (!$this->users->contains($user)) {
            $this->users[] = $user;
            $user->setCustomer($this);
        }

        return $this;
    }

    public function removeUser(User $user): self
    {
        if ($this->users->removeElement($user)) {
            // set the owning side to null (unless already changed)
            if ($user->getCustomer() === $this) {
                $user->setCustomer(null);
            }
        }

        return $this;
    }
}