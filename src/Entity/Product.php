<?php

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;
use App\Repository\ProductRepository;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;
use Symfony\Component\Serializer\Annotation\Groups;
use Symfony\Component\Validator\Constraints as Assert;
use Symfony\Component\Serializer\Annotation as Serializer;
use Hateoas\Configuration\Annotation as Hateoas;

/**
 * Class Product
 * @package App\Entity
 * @ORM\Entity(repositoryClass=ProductRepository::class)
 * @UniqueEntity(fields={"name"}, message="This name already exists")
 * @Hateoas\Relation(
 *     "self",
 *     href = @Hateoas\Route(
 *          "product_details",
 *          parameters = { "id" = "expr(object.getId())" },
 *          absolute = true
 *     ),
 *     exclusion = @Hateoas\Exclusion(groups={"products:list"}),
 * )
 * @Hateoas\Relation(
 *     "products:list",
 *     href = @Hateoas\Route(
 *          "products_list",
 *          absolute = true
 *     ),
 *     exclusion = @Hateoas\Exclusion(groups={"product:details"}),
 * )
 */

class Product
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
     * @ORM\Column(type="string", length=255, unique=true)
     * @Assert\NotBlank(message="You have to name the product")
     * @Assert\Length(min=3, minMessage="The name must contain at least {{ limit }} characters")
     * @Groups("products:list");
     * @Groups("product:details");
     * @Serializer\Groups({"products:list", "product:details"})
     */
    private string $name;

    /**
     * @var \DateTimeImmutable
     * @ORM\Column(type="datetime_immutable")
     * @Groups("products_list");
     */
    private \DateTimeImmutable $createdAt;

    /**
     * @ORM\Column(type="text", nullable=true)
     * @Groups("products:list");
     * @Groups("product:details");
     */
    private $description;

    /**
     * @var float
     * @ORM\Column(type="float")
     * @Groups("products:list");
     * @Groups("product:details");
     */
    private float $price;
    
    /**
     * @var string
     * @ORM\Column(type="string", length=255, nullable=true)
     * @Groups("products:list");
     * @Groups("product:details");
     */
    private string $color;
    
    /**
     * @var string
     * @ORM\Column(type="string", length=255)
     * @Groups("products:list");
     * @Groups("product:details");
     */
    private string $brand;  

    /**
     * @var int
     * @ORM\Column(type="integer")
     * @Groups("product:details");
     */
    private int $availableQuantity;

    /**
     * Get the value of availableQuantity
     *
     * @return  int
     */ 
    public function getAvailableQuantity()
    {
        return $this->availableQuantity;
    }

    /**
     * Set the value of availableQuantity
     *
     * @param  int  $availableQuantity
     *
     * @return  self
     */ 
    public function setAvailableQuantity(int $availableQuantity)
    {
        $this->availableQuantity = $availableQuantity;

        return $this;
    }

    /**
     * Get the value of brand
     *
     * @return  string
     */ 
    public function getBrand()
    {
        return $this->brand;
    }

    /**
     * Set the value of brand
     *
     * @param  string  $brand
     *
     * @return  self
     */ 
    public function setBrand(string $brand)
    {
        $this->brand = $brand;

        return $this;
    }

    /**
     * Get the value of color
     *
     * @return  string
     */ 
    public function getColor()
    {
        return $this->color;
    }

    /**
     * Set the value of color
     *
     * @param  string  $color
     *
     * @return  self
     */ 
    public function setColor(string $color)
    {
        $this->color = $color;

        return $this;
    }

    /**
     * Get the value of price
     *
     * @return  float
     */ 
    public function getPrice()
    {
        return $this->price;
    }

    /**
     * Set the value of price
     *
     * @param  float  $price
     *
     * @return  self
     */ 
    public function setPrice(float $price)
    {
        $this->price = $price;

        return $this;
    }

    /**
     * Get the value of createdAt
     *
     * @return  \DateTimeImmutable
     */ 
    public function getCreatedAt()
    {
        return $this->createdAt;
    }

    /**
     * Set the value of createdAt
     *
     * @param  \DateTimeImmutable  $createdAt
     *
     * @return  self
     */ 
    public function setCreatedAt(\DateTimeImmutable $createdAt)
    {
        $this->createdAt = $createdAt;

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
     * Get the value of description
     */ 
    public function getDescription()
    {
        return $this->description;
    }

    /**
     * Set the value of description
     *
     * @return  self
     */ 
    public function setDescription($description)
    {
        $this->description = $description;

        return $this;
    }
}