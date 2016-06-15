<?php

namespace AppBundle\Entity;

use FOS\UserBundle\Model\User as BaseUser;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;
use Symfony\Component\HttpFoundation\File\UploadedFile;

/**
 * @ORM\Entity
 * @ORM\Table(name="fos_user")
 * @ORM\HasLifecycleCallbacks
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

    /**
     * @var string
     *
     * @ORM\Column(name="name", type="string", length=50, nullable=true)
     * @Assert\Length(
     *     min=2,
     *     max=50,
     *     minMessage="The name is too short.",
     *     maxMessage="The name is too long.",
     *     groups={"Profile"}
     * )
     */
    protected $name;

    /**
     * @Assert\Image(
     *  allowPortrait = false,
     *  allowLandscape = false,
     *  groups={"Profile"}
     * )
     */
    protected $profilePicture;

    /**
     * @ORM\Column(name="profilePicturePath", type="string", length=255, nullable=true)
     */
    protected $profilePicturePath;

    protected $profilePictureTemp;

    /**
     * @ORM\Column(name="timezone", type="string", length=50, nullable=true)
     */
    protected $timezone;

    /**
     * @ORM\Column(name="locale", type="string", length=2, nullable=true)
     * @Assert\Locale()
     */
    protected $locale;


    public function __construct()
    {
        parent::__construct();
        // your own logic
    }

    /**
     * Set name
     *
     * @param string $name
     *
     * @return User
     */
    public function setName($name)
    {
        $this->name = $name;

        return $this;
    }

    /**
     * Get name
     *
     * @return string
     */
    public function getName()
    {
        return $this->name;
    }

    /**
     * Add comment
     *
     * @param \AppBundle\Entity\Comment $comment
     *
     * @return User
     */
    public function addComment(\AppBundle\Entity\Comment $comment)
    {
        $this->comments[] = $comment;

        return $this;
    }

    /**
     * Remove comment
     *
     * @param \AppBundle\Entity\Comment $comment
     */
    public function removeComment(\AppBundle\Entity\Comment $comment)
    {
        $this->comments->removeElement($comment);
    }

    /**
     * Get comments
     *
     * @return \Doctrine\Common\Collections\Collection
     */
    public function getComments()
    {
        return $this->comments;
    }

    /**
     * Add rating
     *
     * @param \AppBundle\Entity\Rating $rating
     *
     * @return User
     */
    public function addRating(\AppBundle\Entity\Rating $rating)
    {
        $this->ratings[] = $rating;

        return $this;
    }

    /**
     * Remove rating
     *
     * @param \AppBundle\Entity\Rating $rating
     */
    public function removeRating(\AppBundle\Entity\Rating $rating)
    {
        $this->ratings->removeElement($rating);
    }

    /**
     * Get ratings
     *
     * @return \Doctrine\Common\Collections\Collection
     */
    public function getRatings()
    {
        return $this->ratings;
    }

    /**
     * Add image
     *
     * @param \AppBundle\Entity\Image $image
     *
     * @return User
     */
    public function addImage(\AppBundle\Entity\Image $image)
    {
        $this->images[] = $image;

        return $this;
    }

    /**
     * Remove image
     *
     * @param \AppBundle\Entity\Image $image
     */
    public function removeImage(\AppBundle\Entity\Image $image)
    {
        $this->images->removeElement($image);
    }

    /**
     * Get images
     *
     * @return \Doctrine\Common\Collections\Collection
     */
    public function getImages()
    {
        return $this->images;
    }


    public function getAbsolutePath()
    {
        return null === $this->profilePicturePath
            ? null
            : $this->getUploadRootDir().'/'.$this->profilePicturePath;
    }

    public function getWebPath()
    {
        return null === $this->profilePicturePath
            ? null
            : $this->getUploadDir().'/'.$this->profilePicturePath;
    }

    protected function getUploadRootDir()
    {
        return __DIR__.'/../../../web/'.$this->getUploadDir();
    }

    protected function getUploadDir()
    {
        return 'uploads/profile';
    }

    /**
     * Sets profilePicture.
     *
     * @param UploadedFile $profilePicture
     */
    public function setProfilePicture(UploadedFile $profilePicture = null)
    {
        $this->profilePicture = $profilePicture;

        if (isset($this->profilePicturePath)) {
            $this->profilePictureTemp = $this->profilePicturePath;
            $this->profilePicturePath = null;
        } else {
            $this->profilePicturePath = 'initial';
        }
    }

    /**
     * Get profilePicture.
     *
     * @return UploadedFile
     */
    public function getProfilePicture()
    {
        return $this->profilePicture;
    }

    /**
     * @ORM\PrePersist()
     * @ORM\PreUpdate()
     */
    public function preUpload()
    {
        if (null !== $this->getProfilePicture()) {
            $filename = sha1(uniqid(mt_rand(), true));
            $this->profilePicturePath = $filename.'.'.$this->getProfilePicture()->guessExtension();
        }
    }

    /**
     * @ORM\PostPersist()
     * @ORM\PostUpdate()
     */
    public function upload()
    {
        if (null === $this->getProfilePicture()) {
            return;
        }

        $this->getProfilePicture()->move($this->getUploadRootDir(), $this->profilePicturePath);

        if (isset($this->profilePictureTemp) && file_exists($this->getUploadRootDir().'/'.$this->profilePictureTemp)) {
            unlink($this->getUploadRootDir().'/'.$this->profilePictureTemp);
            $this->profilePictureTemp = null;
        }
        $this->profilePicture = null;
    }

    /**
     * @ORM\PostRemove()
     */
    public function removeUpload()
    {
        $profilePicture = $this->getAbsolutePath();
        if ($profilePicture) {
            unlink($profilePicture);
        }
    }

    /**
     * Set profilePicturePath
     *
     * @param string $profilePicturePath
     *
     * @return User
     */
    public function setProfilePicturePath($profilePicturePath)
    {
        $this->profilePicturePath = $profilePicturePath;

        return $this;
    }

    /**
     * Get profilePicturePath
     *
     * @return string
     */
    public function getProfilePicturePath()
    {
        return $this->profilePicturePath;
    }

    /**
     * Set timezone
     *
     * @param string $timezone
     *
     * @return User
     */
    public function setTimezone($timezone)
    {
        $this->timezone = $timezone;

        return $this;
    }

    /**
     * Get timezone
     *
     * @return string
     */
    public function getTimezone()
    {
        return $this->timezone;
    }

    /**
     * Set locale
     *
     * @param string $locale
     *
     * @return User
     */
    public function setLocale($locale)
    {
        $this->locale = $locale;

        return $this;
    }

    /**
     * Get locale
     *
     * @return string
     */
    public function getLocale()
    {
        return $this->locale;
    }
}
