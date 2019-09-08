<?php declare(strict_types=1);

namespace DoctrineFixtures\Tests\Doctrine\Entities;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\Mapping as ORM;
use JsonSerializable;
use stdClass;

/**
 * Class Users
 * @package Tests\Doctrine\Entities
 *
 * @ORM\Entity
 * @ORM\Table(name="accounts")
 */
class Accounts implements JsonSerializable
{
    /**
     * @var int
     *
     * @ORM\Id
     * @ORM\Column(name="id", type="integer", options={"comment":"The users unique id"})
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    protected $id;

    /**
     * @var string
     * @ORM\Column(name="name", type="string", length=50)
     */
    protected $name;

    /**
     * @var string
     * @ORM\Column(name="type", type="string", length=10)
     */
    protected $type;

    /**
     * @var ArrayCollection
     * @ORM\OneToMany(targetEntity="Users", mappedBy="account")
     */
    protected $users;

    /**
     * @return stdClass
     */
    public function jsonSerialize(): array
    {
        return [
            'id' => $this->id,
            'name' => $this->name,
            'type' => $this->type,
        ];
    }
}
