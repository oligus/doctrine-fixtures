<?php declare(strict_types=1);

namespace DoctrineFixtures\Tests\Doctrine\Entities;

use Doctrine\ORM\Mapping as ORM;
use JsonSerializable;
use stdClass;

/**
 * Class Users
 * @package Tests\Doctrine\Entities
 *
 * @ORM\Entity
 * @ORM\Table(name="users")
 */
class Users implements JsonSerializable
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
     * @ORM\Column(name="user_name", type="string", length=50)
     */
    protected $userName;

    /**
     * @var Accounts
     * @ORM\ManyToOne(targetEntity="Accounts", inversedBy="users")
     */
    protected $account;

    /**
     * @return stdClass
     */
    public function jsonSerialize(): array
    {
        return [
            'id' => $this->id,
            'userName' => $this->userName,
            'account' => $this->account,
        ];
    }
}
