<?php


use Doctrine\ORM\Mapping as ORM;

/**
 * Lock
 */
class Lock
{
    /**
     * @var \DateTime
     */
    private $startDate;

    /**
     * @var \DateTime
     */
    private $endDate;

    /**
     * @var integer
     */
    private $id;

    /**
     * @var \Trans
     */
    private $trans;

    /**
     * @var \User
     */
    private $author;

    /**
     * @var \User
     */
    private $endByUser;


    /**
     * Set startDate
     *
     * @param \DateTime $startDate
     * @return Lock
     */
    public function setStartDate($startDate)
    {
        $this->startDate = $startDate;

        return $this;
    }

    /**
     * Get startDate
     *
     * @return \DateTime
     */
    public function getStartDate()
    {
        return $this->startDate;
    }

    /**
     * Set endDate
     *
     * @param \DateTime $endDate
     * @return Lock
     */
    public function setEndDate($endDate)
    {
        $this->endDate = $endDate;

        return $this;
    }

    /**
     * Get endDate
     *
     * @return \DateTime
     */
    public function getEndDate()
    {
        return $this->endDate;
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
     * Set trans
     *
     * @param Trans $trans
     * @return Lock
     */
    public function setTrans($trans)
    {
        $this->trans = $trans;

        return $this;
    }

    /**
     * Get trans
     *
     * @return Trans
     */
    public function getTrans()
    {
        return $this->trans;
    }

    /**
     * Set author
     *
     * @param \User $author
     * @return Lock
     */
    public function setAuthor(\User $author = null)
    {
        $this->author = $author;

        return $this;
    }

    /**
     * Get author
     *
     * @return \User
     */
    public function getAuthor()
    {
        return $this->author;
    }

    /**
     * Set endByUser
     *
     * @param \User $endByUser
     * @return Lock
     */
    public function setEndByUser(\User $endByUser = null)
    {
        $this->endByUser = $endByUser;

        return $this;
    }

    /**
     * Get endByUser
     *
     * @return \User
     */
    public function getEndByUser()
    {
        return $this->endByUser;
    }


    static function unlock(\Lock $lock)
    {
        return getEM()->remove($lock);
    }

    static function lockTransByUser(\Trans $trans, \User $user)
    {
        $lock = new Lock();
        $lock->setTrans($trans);
        $lock->setAuthor($user);
        $lock->setStartDate(new DateTime("now"));
        getEM()->persist($lock);
    }

    /**
     * Get all active locks
     * @return \Doctrine\Common\Collections\Collection
     */
    static public function getActiveLocks()
    {

        return getEM()->createQueryBuilder()
            ->select("l")->from('Lock', 'l')->orderBy("l.trans")
            ->getQuery()->getResult();

    }

    /**
     * Get active locks that are holded by the given user
     * @param \User $user
     * @return \Doctrine\Common\Collections\Collection
     */
    static public function getActiveLocksByUser(\User $user)
    {

        return getEM()->createQueryBuilder()
            ->select("l")->from('Lock', 'l')
            ->andWhere('l.author = ?0')
            ->orderBy("l.trans")
            ->setParameters(Array($user))
            ->getQuery()->getResult();

    }
}
