<?php


use Doctrine\ORM\Mapping as ORM;

/**
 * User
 */
class User
{
    /**
     * @var string
     */
    private $username;

    /**
     * @var string
     */
    private $password;

    /**
     * @var string
     */
    private $collectionPassword;

    /**
     * @var \DateTime
     */
    private $createdDate;

    /**
     * @var string
     */
    private $creator;

    /**
     * @var boolean
     */
    private $admin;

    /**
     * @var string
     */
    private $filtering;

    /**
     * @var boolean
     */
    private $canDownloadHdImages = false;

    /**
     * @var boolean
     */
    private $canViewPrivateCatalogues = false;

    /**
     * @var boolean
     */
    private $canViewPrivateTransliterations = false;

    /**
     * @var boolean
     */
    private $canEditTranliterations = false;

    /**
     * @var boolean
     */
    private $canViewPrivateImages = false;

    /**
     * @var boolean
     */
    private $canViewIpadWeb = false;
    /**
     * @var integer
     */
    private $id;

    /**
     * @var \Doctrine\Common\Collections\Collection
     */
    private $locks;

    /**
     * Constructor
     */
    public function __construct()
    {
        $this->locks = new \Doctrine\Common\Collections\ArrayCollection();
    }


    /**
     * Set username
     *
     * @param string $username
     * @return User
     */
    public function setUsername($username)
    {
        $this->username = $username;

        return $this;
    }

    /**
     * Get username
     *
     * @return string
     */
    public function getUsername()
    {
        return $this->username;
    }

    /**
     * Set password
     *
     * @param string $password
     * @return User
     */
    public function setPlainTextPassword($password)
    {
        $this->password = sha1($this->getUsername() . trim($password));
        return $this;
    }

    /**
     * Get password
     *
     * @return string
     */
    public function getPassword()
    {
        return $this->password;
    }

    /**
     * Set collection password
     *
     * @param string $collectionPassword
     * @return User
     */
    public function setCollectionPassword($collectionPassword)
    {
        $this->collectionPassword = $collectionPassword;
        return $this;
    }

    /**
     * Get collection password
     *
     * @return string
     */
    public function getCollectionPassword()
    {
        return $this->collectionPassword;
    }

    /**
     * Set createdDate
     *
     * @param \DateTime $createdDate
     * @return User
     */
    public function setCreatedDate($createdDate)
    {
        $this->createdDate = $createdDate;

        return $this;
    }

    /**
     * Get createdDate
     *
     * @return \DateTime
     */
    public function getCreatedDate()
    {
        return $this->createdDate;
    }

    /**
     * Set creator
     *
     * @param string $creator
     * @return User
     */
    public function setCreator($creator)
    {
        $this->creator = $creator;

        return $this;
    }

    /**
     * Get creator
     *
     * @return string
     */
    public function getCreator()
    {
        return $this->creator;
    }

    /**
     * Set admin
     *
     * @param boolean $admin
     * @return User
     */
    public function setAdmin($admin)
    {
        $this->admin = $admin;

        return $this;
    }

    /**
     * Get admin
     *
     * @return boolean
     */
    public function getAdmin()
    {
        return $this->admin;
    }

    /**
     * Set filtering
     *
     * @param string $filtering
     * @return User
     */
    public function setFiltering($filtering)
    {
        $this->filtering = $filtering;

        return $this;
    }

    /**
     * Get filtering
     *
     * @return string
     */
    public function getFiltering()
    {
        return $this->filtering;
    }

    public function getAdditionalFilters()
    {
        $ret = array();
        foreach (explode("\n", $this->getFiltering()) as $clause) {
            if (trim($clause) == '')
                continue;
            if (strpos($clause, '~') !== FALSE) {
                $sep = strpos($clause, '~');
                $field = trim(substr($clause, 0, $sep));
                $field = Search::$FIELD_MAPPINGS[$field][0];
                $value = '%' . trim(substr($clause, $sep + 1)) . '%';

                $ret[] = array('field' => $field, 'value' => $value, "operator" => 'LIKE');

            } else if (strpos($clause, '=') !== FALSE) {
                $sep = strpos($clause, '=');
                $field = trim(substr($clause, 0, $sep));
                $field = Search::$FIELD_MAPPINGS[$field][0];
                $value = trim(substr($clause, $sep + 1));
                $ret[] = array('field' => $field, 'value' => $value, "operator" => '=');
            }
        }
        return $ret;
    }

    /**
     * Set canDownloadHdImages
     *
     * @param boolean $canDownloadHdImages
     * @return User
     */
    public function setCanDownloadHdImages($canDownloadHdImages)
    {
        $this->canDownloadHdImages = $canDownloadHdImages;

        return $this;
    }

    /**
     * Get canDownloadHdImages
     *
     * @return boolean
     */
    public function canDownloadHdImages()
    {
        return $this->canDownloadHdImages;
    }

    /**
     * Set canViewPrivateCatalogues
     *
     * @param boolean $canViewPrivateCatalogues
     * @return User
     */
    public function setCanViewPrivateCatalogues($canViewPrivateCatalogues)
    {
        $this->canViewPrivateCatalogues = $canViewPrivateCatalogues;

        return $this;
    }

    /**
     * Get canViewPrivateCatalogues
     *
     * @return boolean
     */
    public function canViewPrivateCatalogues()
    {
        return $this->canViewPrivateCatalogues;
    }

    /**
     * Set canViewPrivateTransliterations
     *
     * @param boolean $canViewPrivateTransliterations
     * @return User
     */
    public function setCanViewPrivateTransliterations($canViewPrivateTransliterations)
    {
        $this->canViewPrivateTransliterations = $canViewPrivateTransliterations;

        return $this;
    }

    /**
     * Get canViewPrivateTransliterations
     *
     * @return boolean
     */
    public function canViewPrivateTransliterations()
    {
        return $this->canViewPrivateTransliterations;
    }

    /**
     * Set canEditTranliterations
     *
     * @param boolean $canEditTranliterations
     * @return User
     */
    public function setCanEditTranliterations($canEditTranliterations)
    {
        $this->canEditTranliterations = $canEditTranliterations;

        return $this;
    }

    /**
     * Get canEditTranliterations
     *
     * @return boolean
     */
    public function canEditTranliterations()
    {
        return $this->canEditTranliterations;
    }

    /**
     * Set canViewPrivateImages
     *
     * @param boolean $canViewPrivateImages
     * @return User
     */
    public function setCanViewPrivateImages($canViewPrivateImages)
    {
        $this->canViewPrivateImages = $canViewPrivateImages;

        return $this;
    }

    /**
     * Get canViewPrivateImages
     *
     * @return boolean
     */
    public function canViewPrivateImages()
    {
        return $this->canViewPrivateImages;
    }

    /**
     * Set canViewIpadWeb
     *
     * @param boolean $canViewIpadWeb
     * @return User
     */
    public function setCanViewIpadWeb($canViewIpadWeb)
    {
        $this->canViewIpadWeb = $canViewIpadWeb;

        return $this;
    }

    /**
     * Get canViewIpadWeb
     *
     * @return boolean
     */
    public function canViewIpadWeb()
    {
        return $this->canViewIpadWeb;
    }

    /**
     * Get id
     *
     * @return integer
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * Add locks
     *
     * @param \Lock $locks
     * @return User
     */
    public function addLock(\Lock $locks)
    {
        $this->locks[] = $locks;

        return $this;
    }

    /**
     * Remove locks
     *
     * @param \Lock $locks
     */
    public function removeLock(\Lock $locks)
    {
        $this->locks->removeElement($locks);
    }

    /**
     * Get locks
     *
     * @return \Doctrine\Common\Collections\Collection
     */
    public function getLocks()
    {
        return $this->locks;
    }


    public function getByFieldName($field)
    {

        return ($this->$field);
    }

    public function setByFieldName($value, $field)
    {
        $setter = "set" . ucfirst($field);
        $this->$setter($value);
    }

    public static function isLogin(Doctrine\ORM\EntityManager $em)
    {

        return User::getCurrentUserOrLoginAsGuest($em)->getUsername() != "guest";
    }

    /**
     * @param \Doctrine\ORM\EntityManager $em
     * @param $username
     * @return \User
     */
    public static function queryByUsername(Doctrine\ORM\EntityManager $em, $username)
    {
        $qb = $em->createQueryBuilder();
        return $qb->select("u")
            ->from("User", "u")
            ->andWhere("u.username = ?0")
            ->setParameters(array($username))
            ->getQuery()->getOneOrNullResult();
    }

    public static function getGuest(Doctrine\ORM\EntityManager $em)
    {
        return User::queryByUsername($em, "guest");
    }

    public static function loginAsGuest(Doctrine\ORM\EntityManager $em)
    {
        /* @var User $user */
        $user = User::getGuest($em);
        $em->detach($user);
        $_SESSION['User'] = $user->getId();
        return $user;
    }

    /**
     * @param \Doctrine\ORM\EntityManager $em
     * @return \User
     */
    public static function getCurrentUserOrLoginAsGuest(Doctrine\ORM\EntityManager $em)
    {
        if (isset($_SESSION['User'])) {
            $user = $_SESSION['User'];
            $user = $em->find("User", $user);
            return $user;
        }
        return User::loginAsGuest($em);
    }

    /**
     * @param \Doctrine\ORM\EntityManager $em
     * @return \User
     */
    public static function login(Doctrine\ORM\EntityManager $em, $username, $password)
    {
        $pwd = sha1($username . trim($password));
        $qb = $em->createQueryBuilder();

        $qb->select('u')
            ->from('User', 'u')
            ->andWhere("u.username = ?0")
            ->andWhere("u.password = ?1")
            ->setParameters(array($username, $pwd));
        $user = $qb->getQuery()->getOneOrNullResult();
        if ($user == null) {
            return null;
        } else {
            $_SESSION['User'] = $user->getId();
            return $user;
        }
    }
}
