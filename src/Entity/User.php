<?php
/**
 * Created by PhpStorm.
 * User: stank
 * Date: 25-Aug-18
 * Time: 18:24
 */

namespace App\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Security\Core\User\UserInterface;
use Symfony\Component\Serializer\Annotation\Groups;
use Symfony\Component\Serializer\Annotation\MaxDepth;
use Symfony\Component\Validator\Constraints as Assert;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;

/**
 * Class User
 * @ORM\Entity(repositoryClass="App\Repository\UserRepository")
 * @UniqueEntity(fields={"email"}, message="Email unavailable")
 */
class User implements UserInterface
{
    /**
     * @ORM\Id()
     * @ORM\Column(type="integer")
     * @ORM\GeneratedValue(strategy="AUTO")
     * @Groups({"public"})
     * @var int
     */
    private $id;

    /**
     * @ORM\Column(type="string", length=100, unique=true)
     * @Assert\NotBlank(message="Email is required")
     * @Assert\Email(
     *     message = "The email '{{ value }}' is not a valid email.",
     *     checkMX = true
     * )
     * @Groups({"public"})
     * @var string;
     */
    private $email;

    /**
     * @ORM\Column(type="string", length=20)
     * @Assert\NotBlank(message="Name is required")
     * @Groups({"public"})
     * @var string
     */
    private $name;

    /**
     * @ORM\Column(type="string", length=20)
     * @Assert\NotBlank(message="Last name is required")
     * @Groups({"public"})
     * @var string
     */
    private $lastName;

    /**
     * @ORM\Column(type="string", length=64)
     * @var string;
     */
    private $password;

    /**
     * @Assert\NotBlank(message="Password is required")
     * @Assert\Length(max=4096)
     * @var string
     */
    private $plainPassword;

    /**
     * @ORM\Column(type="simple_array")
     * @var array
     */
    private $roles;

    /**
     * @ORM\ManyToMany(targetEntity="User")
     * @ORM\JoinTable(name="friends",
     *     joinColumns={@ORM\JoinColumn(name="user_a_id", referencedColumnName="id")},
     *     inverseJoinColumns={@ORM\JoinColumn(name="user_b_id", referencedColumnName="id")}
     * )
     * @Groups({"public"})
     * @MaxDepth(1)
     * @var ArrayCollection
     */
    private $friends;

    /**
     * User constructor.
     */
    public function __construct()
    {
        $this->friends = new ArrayCollection();
        $this->roles = array('ROLE_USER');
    }

    /**
     * @return int
     */
    public function getId(): ?int
    {
        return $this->id;
    }

    /**
     * @param int $id
     * @return User
     */
    public function setId(int $id): User
    {
        $this->id = $id;
        return $this;
    }

    /**
     * @return string
     */
    public function getEmail(): ?string
    {
        return $this->email;
    }

    /**
     * @param string $email
     * @return User
     */
    public function setEmail(string $email): User
    {
        $this->email = $email;
        return $this;
    }

    /**
     * @return string
     */
    public function getName(): ?string
    {
        return $this->name;
    }

    /**
     * @param string $name
     * @return User
     */
    public function setName(string $name): User
    {
        $this->name = $name;
        return $this;
    }

    /**
     * @return string
     */
    public function getLastName(): ?string
    {
        return $this->lastName;
    }

    /**
     * @param string $lastName
     * @return User
     */
    public function setLastName(string $lastName): User
    {
        $this->lastName = $lastName;
        return $this;
    }

    /**
     * @return string
     */
    public function getPassword(): ?string
    {
        return $this->password;
    }

    /**
     * @param string $password
     * @return User
     */
    public function setPassword(string $password): User
    {
        $this->password = $password;
        return $this;
    }

    /**
     * @return string
     */
    public function getPlainPassword(): ?string
    {
        return $this->plainPassword;
    }

    /**
     * @param string $plainPassword
     * @return User
     */
    public function setPlainPassword(string $plainPassword): User
    {
        $this->plainPassword = $plainPassword;
        return $this;
    }

    /**
     * @return array
     */
    public function getRoles(): ?array
    {
        return $this->roles;
    }

    /**
     * @param array $roles
     * @return User
     */
    public function setRoles(array $roles): User
    {
        $this->roles = $roles;
        return $this;
    }

    /**
     * @return array
     */
    public function getFriends()
    {
        return $this->friends->toArray();
    }

    /**
     * @param User $user
     */
    public function addFriend(User $user)
    {
        if (!$this->friends->contains($user)) {
            $this->friends->add($user);
            $user->addFriend($this);
        }
    }

    /**
     * @param User $user
     */
    public function removeFriend(User $user)
    {
        if ($this->friends->contains($user)) {
            $this->friends->removeElement($user);
            $user->removeFriend($this);
        }
    }

    public function getSalt()
    {
        // bcrypt algorithm doesn't need salt
        return null;
    }

    public function getUsername()
    {
        return $this->email;
    }

    public function eraseCredentials()
    {
    }


}
