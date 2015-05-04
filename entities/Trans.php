<?php


use Doctrine\ORM\Mapping as ORM;

/**
 * Trans
 */
class Trans
{
    /**
     * @var string
     */
    private $text;

    /**
     * @var string
     */
    private $cleantext;

    /**
     * @var string
     */
    private $oldCleantext;

    /**
     * @var string
     */
    private $wholetext;

    /**
     * @var integer
     */
    private $objectId;

    /**
     * @var \Doctrine\Common\Collections\Collection
     */
    private $revHistories;

    /**
     * @var \Doctrine\Common\Collections\Collection
     */
    private $locks;

    /**
     * Constructor
     */
    public function __construct()
    {
        $this->revHistories = new \Doctrine\Common\Collections\ArrayCollection();
        $this->locks = new \Doctrine\Common\Collections\ArrayCollection();

    }

    /**
     * Set text
     *
     * @param string $text
     * @return Trans
     */
    public function setText($text)
    {
        $this->text = $text;

        return $this;
    }

    /**
     * Get text
     *
     * @return string
     */
    public function getText()
    {
        return $this->text;
    }

    /**
     * Set cleantext
     *
     * @param string $cleantext
     * @return Trans
     */
    public function setCleantext($cleantext)
    {
        $this->cleantext = $cleantext;

        return $this;
    }

    /**
     * Get cleantext
     *
     * @return string
     */
    public function getCleantext()
    {
        return $this->cleantext;
    }

    /**
     * Set oldCleantext
     *
     * @param string $oldCleantext
     * @return Trans
     */
    public function setOldCleantext($oldCleantext)
    {
        $this->oldCleantext = $oldCleantext;

        return $this;
    }

    /**
     * Get oldCleantext
     *
     * @return string
     */
    public function getOldCleantext()
    {
        return $this->oldCleantext;
    }

    /**
     * Set wholetext
     *
     * @param string $wholetext
     * @return Trans
     */
    public function setWholetext($wholetext)
    {
        $this->wholetext = $wholetext;

        return $this;
    }

    /**
     * Get wholetext
     *
     * @return string
     */
    public function getWholetext()
    {
        return trim($this->wholetext, "'");
    }

    /**
     * Set objectId
     *
     * @param integer $objectId
     * @return Trans
     */
    public function setObjectId($objectId)
    {
        $this->objectId = $objectId;

        return $this;
    }

    /**
     * Get objectId
     *
     * @return integer
     */
    public function getObjectId()
    {
        return $this->objectId;
    }

    /**
     * Add revHistories
     *
     * @param \RevHistory $revHistories
     * @return Trans
     */
    public function addRevHistory(\RevHistory $revHistories)
    {
        $this->revHistories[] = $revHistories;

        return $this;
    }

    /**
     * Remove revHistories
     *
     * @param \RevHistory $revHistories
     */
    public function removeRevHistory(\RevHistory $revHistories)
    {
        $this->revHistories->removeElement($revHistories);
    }

    /**
     * Get revHistories
     *
     * @return \Doctrine\Common\Collections\Collection
     */
    public function getRevHistories()
    {
        return $this->revHistories;
    }

    /**
     * Add locks
     *
     * @param \Lock $locks
     * @return Trans
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

    /**
     * Is locked by any user
     *
     * @return boolean
     */
    public function isLocked()
    {
        return count($this->getLocks()) > 0;
    }


    public function getPartialRevHistories()
    {
        // return rev histories with only part of the fields
        // in DESC modDate order
        $qb = getEM()->createQueryBuilder();
        return $qb->select('partial rev.{id,credit,modDate,author}')
            ->from('RevHistory', 'rev')
            ->andWhere($qb->expr()->eq("rev.trans", $this->getObjectId()))
            ->orderBy("rev.modDate", "DESC")
            ->getQuery()
            ->getResult();
    }

    public function setTextFieldsWithNewWholeText($wholetext)
    {
        // remove redundant newlines
        $wholetext = preg_replace("/(\n|\r)+/", "\n", $wholetext);

        // set whole text
        $this->setWholetext($wholetext);
        // set text
        $this->setText(Trans::getTextForWholeText($wholetext));

        // set clean text
        $this->setCleantext(Trans::getCleanedTextForText($this->getText()));

        /* update the old clean text (for backward-compatibility only!!) */

        // remove symbols and create clean_translit (only for backward-compaitable)
        $pattern = "/[" . preg_quote('[]()<>!?#|*') . "]*/";
        // so called "clean_text" in the db table
        $clean_translit = preg_replace($pattern, "", $this->getText());
        $this->oldCleantext = $clean_translit;
    }

    static public function getTextForWholeText($text)
    {
        $lines = array();
        foreach (explode("\n", $text) as $line) {
            // a text line always starts with a number, only include those lines that are text.
            if (preg_match("/^[0-9]+/", $line)) {
                // if it is a text line
                $pattern = "/^[0-9]+\\'?\\.? */";
                $replacement = "";
                // remove the number and the dot follow the number
                $line = preg_replace($pattern, $replacement, $line);
                $lines[] = " $line ";
            }
        }
        // join all the line with a space between each of them
        return join("\n", $lines);
    }

    static public function getCleanedTextForText($text)
    {
        $lines = array();
        foreach (explode("\n", $text) as $line) {
            // reduce all consecutive non-letter or non-numbers characters to single space
            $pattern = "/[^\\p{L}\\d]+/u";
            $replacement = " ";
            $line = preg_replace($pattern, $replacement, $line);
            $lines[] = $line;
        }
        // join all the line with a space between each of them
        return " " . join(" \n ", $lines) . " ";
    }
}