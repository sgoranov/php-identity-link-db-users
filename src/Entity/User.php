<?php
declare(strict_types=1);

namespace App\Entity;

use App\Repository\UserRepository;
use App\Service\PasswordHashGenerator;
use App\Service\Validator\JsonChoice;
use App\Service\Validator\UniqueEntry;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Serializer\Attribute\Ignore;
use Symfony\Component\Validator\Constraints as Assert;
use Symfony\Component\Serializer\Annotation\Groups;

#[ORM\Entity(repositoryClass: UserRepository::class)]
#[ORM\Table(name: '`user`')]
class User
{
    #[ORM\Id]
    #[ORM\GeneratedValue(strategy: "CUSTOM")]
    #[ORM\Column(type: "uuid", unique: true)]
    #[ORM\CustomIdGenerator(class: "doctrine.uuid_generator")]
    private ?string $id = null;

    #[Groups(['create', 'update'])]
    #[Assert\NotBlank(groups: ['create'])]
    #[UniqueEntry(groups: ['create', 'update'])]
    #[Assert\Length(min: 1, max: 100, groups: ['create', 'update'])]
    #[Assert\Regex(pattern: '/^([\w0-9_-])+$/u', groups: ['create', 'update'])]
    #[ORM\Column(length: 100)]
    private string $username;

    #[Ignore]
    #[ORM\Column(name: 'password', length: 100)]
    private string $hashedPassword;

    #[Groups(['create', 'update'])]
    #[Assert\NotBlank(groups: ['create'])]
    #[Assert\Length(min: 1, max: 50, groups: ['create', 'update'])]
    private string $password;

    #[Groups(['create', 'update'])]
    #[Assert\NotBlank(groups: ['create'])]
    #[Assert\Length(min: 1, max: 100, groups: ['create', 'update'])]
    #[ORM\Column(length: 100)]
    private string $firstName;

    #[Groups(['create', 'update'])]
    #[Assert\NotBlank(groups: ['create'])]
    #[Assert\Length(min: 1, max: 100, groups: ['create', 'update'])]
    #[ORM\Column(length: 100)]
    private string $lastName;

    #[Groups(['create', 'update'])]
    #[Assert\NotBlank(groups: ['create'])]
    #[Assert\Email(groups: ['create', 'update'])]
    #[Assert\Length(min: 1, max: 100, groups: ['create', 'update'])]
    #[ORM\Column(length: 100)]
    private string $email;

    #[Groups(['create', 'update'])]
    #[Assert\Count(
        min: 0,
        max: 50,
        maxMessage: 'You cannot specify more than {{ limit }} groups',
        groups: ['create', 'update']
    )]
    #[ORM\ManyToMany(targetEntity: "Group", inversedBy: "user")]
    #[ORM\JoinTable(name: "user_group")]
    private Collection $groups;

    #[Groups(['create', 'update'])]
    #[JsonChoice(
        choices: ['client_credentials', 'password', 'authorization_code', 'refresh_token', 'implicit'],
        groups: ['create', 'update']
    )]
    #[ORM\Column(type: 'json')]
    private array $grantTypes = [];

    public function __construct()
    {
        $this->groups = new ArrayCollection();
    }

    public function getId(): ?string
    {
        return $this->id;
    }

    public function getUsername(): ?string
    {
        return $this->username;
    }

    public function setUsername(?string $username): void
    {
        $this->username = $username;
    }

    public function getHashedPassword(): ?string
    {
        return $this->hashedPassword;
    }

    public function setHashedPassword(?string $hashedPassword): void
    {
        $this->hashedPassword = $hashedPassword;
    }

    public function getFirstName(): string
    {
        return $this->firstName;
    }

    public function setFirstName(string $firstName): void
    {
        $this->firstName = $firstName;
    }

    public function getLastName(): string
    {
        return $this->lastName;
    }

    public function setLastName(string $lastName): void
    {
        $this->lastName = $lastName;
    }

    public function getEmail(): string
    {
        return $this->email;
    }

    public function setEmail(string $email): void
    {
        $this->email = $email;
    }

    public function getPassword(): string
    {
        return $this->password;
    }

    public function setPassword(string $password): void
    {
        $this->password = $password;
        $this->setHashedPassword(PasswordHashGenerator::create($password));
    }

    public function getGroups(): Collection
    {
        return $this->groups;
    }

    public function setGroups(Collection $groups): void
    {
        $this->groups = $groups;
    }

    public function getGrantTypes(): array
    {
        return $this->grantTypes;
    }

    public function setGrantTypes(array $grantTypes): void
    {
        $this->grantTypes = $grantTypes;
    }
}
