<?php

namespace AppBundle\Entity;

use FOS\UserBundle\Model\User as BaseUser;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity
 * @ORM\Table(name="fos_user")
 */
class User extends BaseUser
{
    /**
     * @ORM\Id
     * @ORM\Column(type="integer")
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    protected $id;

    /**
     * @ORM\OneToMany(targetEntity="Comment", cascade={"persist"}, mappedBy="user")
     */
    protected $comments;

    /**
     * @ORM\OneToMany(targetEntity="Rating", cascade={"persist"}, mappedBy="user")
     */
    protected $ratings;

    /**
     * @ORM\OneToMany(targetEntity="Image", mappedBy="user", cascade={"remove"})
     */
    protected $images;


    public function __construct()
    {
        parent::__construct();
        // your own logic
    }
}
