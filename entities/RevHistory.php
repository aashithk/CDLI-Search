<?php


use Doctrine\ORM\Mapping as ORM;

/**
 * RevHistory
 */
class RevHistory
{
    /**
     * @var string
     */
    private $author;

    /**
     * @var \DateTime
     */
    private $modDate;

    /**
     * @var string
     */
    private $originalText;

    /**
     * @var string
     */
    private $newText;

    /**
     * @var string
     */
    private $credit;

    /**
     * @var integer
     */
    private $id;

    /**
     * @var \Trans
     */
    private $trans;


    /**
     * Set author
     *
     * @param string $author
     * @return RevHistory
     */
    public function setAuthor($author)
    {
        $this->author = $author;

        return $this;
    }

    /**
     * Get author
     *
     * @return string
     */
    public function getAuthor()
    {
        return $this->author;
    }

    /**
     * Set modDate
     *
     * @param \DateTime $modDate
     * @return RevHistory
     */
    public function setModDate($modDate)
    {
        $this->modDate = $modDate;

        return $this;
    }

    /**
     * Get modDate
     *
     * @return \DateTime
     */
    public function getModDate()
    {
        return $this->modDate;
    }

    /**
     * Set originalText
     *
     * @param string $originalText
     * @return RevHistory
     */
    public function setOriginalText($originalText)
    {
        $this->originalText = $originalText;

        return $this;
    }

    /**
     * Get originalText
     *
     * @return string
     */
    public function getOriginalText()
    {
        return $this->originalText;
    }

    /**
     * Set newText
     *
     * @param string $newText
     * @return RevHistory
     */
    public function setNewText($newText)
    {
        $this->newText = $newText;

        return $this;
    }

    /**
     * Get newText
     *
     * @return string
     */
    public function getNewText()
    {
        return $this->newText;
    }

    /**
     * Set credit
     *
     * @param string $credit
     * @return RevHistory
     */
    public function setCredit($credit)
    {
        $this->credit = $credit;

        return $this;
    }

    /**
     * Get credit
     *
     * @return string
     */
    public function getCredit()
    {
        return $this->credit;
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
     * @param \Trans $trans
     * @return RevHistory
     */
    public function setTrans(\Trans $trans = null)
    {
        $this->trans = $trans;

        return $this;
    }

    /**
     * Get trans
     *
     * @return \Trans
     */
    public function getTrans()
    {
        return $this->trans;
    }

}
