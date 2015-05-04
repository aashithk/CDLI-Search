<?php


use Doctrine\ORM\Query\ResultSetMapping;
use Doctrine\ORM\Mapping as ORM;
use Doctrine\ORM\Tools\Pagination\Paginator;

/**
 * Object
 */
class Object
{
    /**
     * @var integer
     */
    private $objectId;

    /**
     * @var string
     */
    private $accessionNo;

    /**
     * @var string
     */
    private $arkNumber;

    /**
     * @var string
     */
    private $atfSource;


    /**
     * @var string
     */
    private $author;


    /**
     * @var string
     */
    private $cdliComments;


    /**
     * @var string
     */
    private $citation;

    /**
     * @var string
     */
    private $collection;

    /**
     * @var string
     */
    private $composite;


    /**
     * @var string
     */
    private $datesReferenced;

    /**
     * @var string
     */
    private $dbSource;

    /**
     * @var string
     */
    private $designation;


    /**
     * @var string
     */
    private $excavationNo;


    /**
     * @var string
     */
    private $genre;


    /**
     * @var string
     */
    private $language;


    /**
     * @var string
     */
    private $material;

    /**
     * @var string
     */
    private $museumNo;


    /**
     * @var string
     */
    private $objectRemarks;

    /**
     * @var string
     */
    private $objectType;

    /**
     * @var string
     */
    private $period;


    /**
     * @var string
     */
    private $primaryPublication;

    /**
     * @var string
     */
    private $provenience;


    /**
     * @var string
     */
    private $public;

    /**
     * @var string
     */
    private $publicAtf;

    /**
     * @var string
     */
    private $publicImages;

    /**
     * @var string
     */
    private $publicationDate;

    /**
     * @var string
     */
    private $publicationHistory;


    /**
     * @var string
     */
    private $sealId;


    /**
     * @var string
     */
    private $subgenre;

    /**
     * @var string
     */
    private $subgenreRemarks;


    /**
     * @var string
     */
    private $translationSource;


    /**
     * @var integer
     */
    private $id;

    /**
     * @var \Trans
     */
    private $trans;


    /**
     * Set objectId
     *
     * @param integer $objectId
     * @return Object
     */
    public function setObjectId($objectId)
    {
        $this->objectId = $objectId;

        return $this;
    }

    public function getObjectPId()
    {
        return Object::getObjectPIdFor($this->getObjectId());
    }

    /**
     * Set objectId
     *
     * @param integer $objectId
     * @return Pid of that object
     */
    static function getObjectPIdFor($objectId)
    {
        return "P" . str_pad($objectId, 6, "0", STR_PAD_LEFT);
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
     * Set accessionNo
     *
     * @param string $accessionNo
     * @return Object
     */
    public function setAccessionNo($accessionNo)
    {
        $this->accessionNo = $accessionNo;

        return $this;
    }

    /**
     * Get accessionNo
     *
     * @return string
     */
    public function getAccessionNo()
    {
        return $this->accessionNo;
    }


    /**
     * Set arkNumber
     *
     * @param string $arkNumber
     * @return Object
     */
    public function setArkNumber($arkNumber)
    {
        $this->arkNumber = $arkNumber;

        return $this;
    }

    /**
     * Get arkNumber
     *
     * @return string
     */
    public function getArkNumber()
    {
        return $this->arkNumber;
    }

    /**
     * Set atfSource
     *
     * @param string $atfSource
     * @return Object
     */
    public function setAtfSource($atfSource)
    {
        $this->atfSource = $atfSource;

        return $this;
    }

    /**
     * Get atfSource
     *
     * @return string
     */
    public function getAtfSource()
    {
        return $this->atfSource;
    }


    /**
     * Set author
     *
     * @param string $author
     * @return Object
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
     * Set cdliComments
     *
     * @param string $cdliComments
     * @return Object
     */
    public function setCdliComments($cdliComments)
    {
        $this->cdliComments = $cdliComments;

        return $this;
    }

    /**
     * Get cdliComments
     *
     * @return string
     */
    public function getCdliComments()
    {
        return $this->cdliComments;
    }

    /**
     * Set citation
     *
     * @param string $citation
     * @return Object
     */
    public function setCitation($citation)
    {
        $this->citation = $citation;

        return $this;
    }

    /**
     * Get citation
     *
     * @return string
     */
    public function getCitation()
    {
        return $this->citation;
    }

    /**
     * Set collection
     *
     * @param string $collection
     * @return Object
     */
    public function setCollection($collection)
    {
        $this->collection = $collection;

        return $this;
    }

    /**
     * Get collection
     *
     * @return string
     */
    public function getCollection()
    {
        return $this->collection;
    }


    /**
     * Set composite
     *
     * @param string $composite
     * @return Object
     */
    public function setComposite($composite)
    {
        $this->composite = $composite;

        return $this;
    }

    /**
     * Get composite
     *
     * @return string
     */
    public function getComposite()
    {
        return $this->composite;
    }

    /**
     * Set datesReferenced
     *
     * @param string $datesReferenced
     * @return Object
     */
    public function setDatesReferenced($datesReferenced)
    {
        $this->datesReferenced = $datesReferenced;

        return $this;
    }

    /**
     * Get datesReferenced
     *
     * @return string
     */
    public function getDatesReferenced()
    {
        return $this->datesReferenced;
    }

    /**
     * Set dbSource
     *
     * @param string $dbSource
     * @return Object
     */
    public function setDbSource($dbSource)
    {
        $this->dbSource = $dbSource;

        return $this;
    }

    /**
     * Get dbSource
     *
     * @return string
     */
    public function getDbSource()
    {
        return $this->dbSource;
    }

    /**
     * Get Designation
     *
     * @return string
     */
    public function getDesignation()
    {
        return $this->designation;
    }

    /**
     * Set excavationNo
     *
     * @param string $excavationNo
     * @return Object
     */
    public function setExcavationNo($excavationNo)
    {
        $this->excavationNo = $excavationNo;

        return $this;
    }

    /**
     * Get excavationNo
     *
     * @return string
     */
    public function getExcavationNo()
    {
        return $this->excavationNo;
    }


    /**
     * Set genre
     *
     * @param string $genre
     * @return Object
     */
    public function setGenre($genre)
    {
        $this->genre = $genre;

        return $this;
    }

    /**
     * Get genre
     *
     * @return string
     */
    public function getGenre()
    {
        return $this->genre;
    }

    /**
     * Set language
     *
     * @param string $language
     * @return Object
     */
    public function setLanguage($language)
    {
        $this->language = $language;

        return $this;
    }

    /**
     * Get language
     *
     * @return string
     */
    public function getLanguage()
    {
        return $this->language;
    }

    /**
     * Get museumNo
     *
     * @return string
     */
    public function getMaterial()
    {
        return $this->material;
    }

    /**
     * Set museumNo
     *
     * @param string $museumNo
     * @return Object
     */
    public function setMuseumNo($museumNo)
    {
        $this->museumNo = $museumNo;

        return $this;
    }

    /**
     * Get museumNo
     *
     * @return string
     */
    public function getMuseumNo()
    {
        return $this->museumNo;
    }

    /**
     * Set objectRemarks
     *
     * @param string $objectRemarks
     * @return Object
     */
    public function setObjectRemarks($objectRemarks)
    {
        $this->objectRemarks = $objectRemarks;

        return $this;
    }

    /**
     * Get objectRemarks
     *
     * @return string
     */
    public function getObjectRemarks()
    {
        return $this->objectRemarks;
    }

    /**
     * Set objectType
     *
     * @param string $objectType
     * @return Object
     */
    public function setObjectType($objectType)
    {
        $this->objectType = $objectType;

        return $this;
    }

    /**
     * Get objectType
     *
     * @return string
     */
    public function getObjectType()
    {
        return $this->objectType;
    }

    /**
     * Set period
     *
     * @param string $period
     * @return Object
     */
    public function setPeriod($period)
    {
        $this->period = $period;

        return $this;
    }

    /**
     * Get period
     *
     * @return string
     */
    public function getPeriod()
    {
        return $this->period;
    }


    /**
     * Set primaryPublication
     *
     * @param string $primaryPublication
     * @return Object
     */
    public function setPrimaryPublication($primaryPublication)
    {
        $this->primaryPublication = $primaryPublication;

        return $this;
    }

    /**
     * Get primaryPublication
     *
     * @return string
     */
    public function getPrimaryPublication()
    {
        return $this->primaryPublication;
    }

    /**
     * Set provenience
     *
     * @param string $provenience
     * @return Object
     */
    public function setProvenience($provenience)
    {
        $this->provenience = $provenience;

        return $this;
    }

    /**
     * Get provenience
     *
     * @return string
     */
    public function getProvenience()
    {
        return $this->provenience;
    }


    /**
     * Set public
     *
     * @param string $public
     * @return Object
     */
    public function setPublic($public)
    {
        $this->public = $public;

        return $this;
    }

    /**
     * Get public
     *
     * @return string
     */
    public function getPublic()
    {
        return $this->public;
    }

    /**
     * Set publicAtf
     *
     * @param string $publicAtf
     * @return Object
     */
    public function setPublicAtf($publicAtf)
    {
        $this->publicAtf = $publicAtf;

        return $this;
    }

    /**
     * Get publicAtf
     *
     * @return boolean
     */
    public function isPublicAtf()
    {

        return $this->publicAtf == "yes";
    }

    /**
     * Set publicImages
     *
     * @param string $publicImages
     * @return Object
     */
    public function setPublicImages($publicImages)
    {
        $this->publicImages = $publicImages;

        return $this;
    }

    /**
     * Get publicImages
     *
     * @return boolean
     */
    public function isPublicImages()
    {
        return $this->publicImages == "yes";
    }

    /**
     * Set publicationDate
     *
     * @param string $publicationDate
     * @return Object
     */
    public function setPublicationDate($publicationDate)
    {
        $this->publicationDate = $publicationDate;

        return $this;
    }

    /**
     * Get publicationDate
     *
     * @return string
     */
    public function getPublicationDate()
    {
        return $this->publicationDate;
    }

    /**
     * Set publicationHistory
     *
     * @param string $publicationHistory
     * @return Object
     */
    public function setPublicationHistory($publicationHistory)
    {
        $this->publicationHistory = $publicationHistory;

        return $this;
    }

    /**
     * Get publicationHistory
     *
     * @return string
     */
    public function getPublicationHistory()
    {
        return $this->publicationHistory;
    }

    /**
     * Set sealId
     *
     * @param string $sealId
     * @return Object
     */
    public function setSealId($sealId)
    {
        $this->sealId = $sealId;

        return $this;
    }

    /**
     * Get sealId
     *
     * @return string
     */
    public function getSealId()
    {
        return $this->sealId;
    }

    /**
     * Set subgenre
     *
     * @param string $subgenre
     * @return Object
     */
    public function setSubgenre($subgenre)
    {
        $this->subgenre = $subgenre;

        return $this;
    }

    /**
     * Get subgenre
     *
     * @return string
     */
    public function getSubgenre()
    {
        return $this->subgenre;
    }


    /**
     * Set translationSource
     *
     * @param string $translationSource
     * @return Object
     */
    public function setTranslationSource($translationSource)
    {
        $this->translationSource = $translationSource;

        return $this;
    }

    /**
     * Get translationSource
     *
     * @return string
     */
    public function getTranslationSource()
    {
        return $this->translationSource;
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
     * @return Object
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
        if ($this->trans instanceof Trans)
            return $this->trans;
        else
            return null;
    }

    public function hasTrans()
    {
        try {
            // use "try" to check if the trans exist
            $this->getTrans()->getText();
            return true;
        } catch (Doctrine\ORM\EntityNotFoundException $e) {
            return false;
        }
    }


    public function getFormattedObjectType()
    {

        if ($this->getObjectType() == null || strpos($this->getObjectType(), 'other (see object remarks)') === 0) {
            return ucfirst($this->getObjectRemarks());
        } else {
            return ucfirst($this->getObjectType());
        }
    }

    /**
     * getByObjectId
     *
     * @param integer $objectId
     * @return Object
     */
    static function getByObjectId($objectId)
    {
        return getEM()->createQueryBuilder()
            ->select('o')
            ->from("Object", "o")
            ->where("o.objectId = ?0")->setParameters(array($objectId))
            ->getQuery()->getOneOrNullResult();
    }

    /**
     * check if a object id is locked, if so, return the lock
     * @return boolean|Lock
     */
    public function isLocked()
    {
        /* @var Trans $trans */
        $trans = getEM()->find('Trans', $this->getObjectId());
        if (!$this->hasTrans() || count($trans->getLocks()) == 0) {
            return false;
        } else {
            return $trans->getLocks()->get(0);
        }
    }

    /**
     * Check if the user can edit this object
     * @param \User $user
     * @return boolean
     */
    public function canModifyBy($user)
    {
        $lock = $this->isLocked();
        if ($lock != false && $lock->getAuthor() != $user) {
            return false;
        }
        return true;
    }

    public function getBySearchFieldName($field)
    {
        $entityFieldName = Search::$FIELD_MAPPINGS[$field][0];
        return ($this->$entityFieldName);
    }

    // Function to get all possible image paths for a specific object.
    // $file_base is expected in the form of P000000
    // Each element returned contains
    // (1. displayable name of file, 2. location of file, 3. whether it should be a thumbnail or not).
    public function getImagePaths()
    {
        $file_base = $this->getObjectPId();

        $image_names = Array();

        if (file_exists("/Library/WebServer/Documents/cdli/dl/photo/$file_base" . ".jpg")) {
            $image_names[] = Array("photo", "photo/$file_base" . ".jpg", true);
        }
        if (file_exists("/Library/WebServer/Documents/cdli/dl/photo/$file_base" . "_e.jpg")) {
            $image_names[] = Array("envelope image", "photo/$file_base" . "_e.jpg", true);
        }
        if (file_exists("/Library/WebServer/Documents/cdli/dl/lineart/$file_base" . "_l.jpg")) {
            $image_names[] = Array("line art", "lineart/$file_base" . "_l.jpg", true);
        }
        if (file_exists("/Library/WebServer/Documents/cdli/dl/photo/$file_base" . "_d.jpg")) {
            $image_names[] = Array("detail image", "photo/$file_base" . "_d.jpg", true);
        }
        if (file_exists("/Library/WebServer/Documents/cdli/dl/photo/$file_base" . "_ed.jpg")) {
            $image_names[] = Array("detail envelope image", "photo/$file_base" . "_ed.jpg", true);
        }
        if (file_exists("/Library/WebServer/Documents/cdli/dl/lineart/$file_base" . "_ld.jpg")) {
            $image_names[] = Array("detail line art", "lineart/$file_base" . "_ld.jpg", true);
        }
        if (file_exists("/Library/WebServer/Documents/cdli/dl/photo/$file_base" . "_s.jpg")) {
            $image_names[] = Array("seal image", "photo/$file_base" . "_s.jpg", true);
        }
        if (file_exists("/Library/WebServer/Documents/cdli/dl/lineart/$file_base" . "_ls.jpg")) {
            $image_names[] = Array("seal line art", "lineart/$file_base" . "_ls.jpg", true);
        }
        //Old pdf link addition code.
       /* if (file_exists("/Library/WebServer/Documents/cdli/dl/pdf/$file_base" . ".pdf")) {
            $image_names[] = Array("commentary", "pdf/$file_base" . ".pdf", false);
        }*/

        return $image_names;
    }

    public function getImageWebAddresses()
    {

        $image_names = $this->getImagePaths();

        $image_array = Array();
        if (sizeof($image_names) > 0) {
            // display image
            if ($image_names[0][2]) {
                $image_array['display'] = "/dl/tn_" . $image_names[0][1];
                $image_array['display_addr'] = "/dl/" . $image_names[0][1];

            }
            // links for secondary images
            for ($i = 1; $i < sizeof($image_names); $i++) {
                $image_array[$image_names[$i][0]] = "/dl/" . $image_names[$i][1];
            }
        }
        return $image_array;
    }

}
