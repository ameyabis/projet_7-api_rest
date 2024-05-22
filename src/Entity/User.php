<?php

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;
use App\Repository\UserRepository;
use JMS\Serializer\Annotation\Groups;
use Symfony\Component\Security\Core\User\UserInterface;
use Symfony\Component\Validator\Constraints as Assert;
use Symfony\Component\Security\Core\User\PasswordAuthenticatedUserInterface;
use Hateoas\Configuration\Annotation as Hateoas;

/**
 * @Hateoas\Relation(
 *      "self",
 *      href = @Hateoas\Route(
 *          "one_user",
 *          parameters = { "id" = "expr(object.getId())" }
 *      ),
 *      exclusion = @Hateoas\Exclusion(groups="getUsers")
 * )
 * @Hateoas\Relation(
 *      "all_users",
 *      href = @Hateoas\Route(
 *          "all_users"
 *      ),
 *      exclusion = @Hateoas\Exclusion(groups="getUsers"),
 * )
 * @Hateoas\Relation(
 *      "delete",
 *      href = @Hateoas\Route(
 *          "delete_user",
 *          parameters = { "id" = "expr(object.getId())" },
 *      ),
 *      exclusion = @Hateoas\Exclusion(groups="getUsers"),
 * )
 */
#[ORM\Entity(repositoryClass: UserRepository::class)]
#[ORM\UniqueConstraint(name: 'UNIQ_IDENTIFIER_USERNAME', fields: ['username'])]
class User implements UserInterface, PasswordAuthenticatedUserInterface
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    #[Groups(['getUsers'])]
    private int $id;

    #[ORM\Column(length: 180)]
    #[Assert\NotBlank(message: "Le nom d'utilisateur est obligatoire. Il vous servira a vous connecter.")]
    #[Assert\Length(
        min: 1,
        max: 180,
        minMessage: 'Le nom d\'utilisateur doit faire minimum {{ limit }} caratères',
        maxMessage: 'Le nom d\'utilisateur ne doit pas dépasser {{ limit }} caractères'
    )]
    private string $username;

    /**
     * @var list<string> The user roles
     */
    #[ORM\Column]
    private array $roles = [];

    /**
     * @var string The hashed password
     */
    #[ORM\Column]
    private string $password;

    #[ORM\Column(length: 255)]
    #[Groups(['getUsers'])]
    #[Assert\NotBlank(message: "Le nom d'utilisateur est obligatoire. Il vous servira a vous connecter.")]
    #[Assert\Length(
        min: 1,
        max: 180,
        minMessage: 'Le prénom doit faire minimum {{ limit }} caratères',
        maxMessage: 'Le prénom ne doit pas dépasser {{ limit }} caractères'
    )]
    private string $firstname;

    #[ORM\Column(length: 255)]
    #[Groups(['getUsers'])]
    #[Assert\NotBlank(message: "Le nom d'utilisateur est obligatoire. Il vous servira a vous connecter.")]
    #[Assert\Length(
        min: 1,
        max: 180,
        minMessage: 'Le nom doit faire minimum {{ limit }} caratères',
        maxMessage: 'Le nom ne doit pas dépasser {{ limit }} caractères'
    )]
    private string $lastname;

    #[ORM\Column(length: 255)]
    #[Groups(['getUsers'])]
    #[Assert\NotBlank(message: "Le nom d'utilisateur est obligatoire. Il vous servira a vous connecter.")]
    #[Assert\Length(
        min: 1,
        max: 180,
        minMessage: 'L\'adresse email doit faire minimum {{ limit }} caratères',
        maxMessage: 'L\'adresse email ne doit pas dépasser {{ limit }} caractères'
    )]
    private string $email;

    #[ORM\ManyToOne(inversedBy: 'users')]
    #[ORM\JoinColumn(nullable: false)]
    #[Groups(['getUsers'])]
    private Customer $customer;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getUsername(): string
    {
        return $this->username;
    }

    public function setUsername(string $username): static
    {
        $this->username = $username;

        return $this;
    }

    /**
     * A visual identifier that represents this user.
     *
     * @see UserInterface
     */
    public function getUserIdentifier(): string
    {
        return (string) $this->username;
    }

    /**
     * @see UserInterface
     *
     * @return list<string>
     */
    public function getRoles(): array
    {
        $roles = $this->roles;
        // guarantee every user at least has ROLE_USER
        $roles[] = 'ROLE_USER';

        return array_unique($roles);
    }

    /**
     * @param list<string> $roles
     */
    public function setRoles(array $roles): static
    {
        $this->roles = $roles;

        return $this;
    }

    /**
     * @see PasswordAuthenticatedUserInterface
     */
    public function getPassword(): string
    {
        return $this->password;
    }

    public function setPassword(string $password): static
    {
        $this->password = $password;

        return $this;
    }

    /**
     * @see UserInterface
     */
    public function eraseCredentials(): void
    {
        
    }

    public function getCustomer(): Customer
    {
        return $this->customer;
    }

    public function setCustomer(Customer $customer): static
    {
        $this->customer = $customer;

        return $this;
    }

    public function getFirstname(): string
    {
        return $this->firstname;
    }

    public function setFirstname(string $firstname): static
    {
        $this->firstname = $firstname;

        return $this;
    }

    public function getLastname(): string
    {
        return $this->lastname;
    }

    public function setLastname(string $lastname): static
    {
        $this->lastname = $lastname;

        return $this;
    }

    public function getEmail(): string
    {
        return $this->email;
    }

    public function setEmail(string $email): static
    {
        $this->email = $email;

        return $this;
    }
}
