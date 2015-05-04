<?php

class UpdateTrans
{
    static function update($objectId, $wholetext, $user, $credits)
    {
        // get all the parameters
        $new_translit = ltrim($wholetext); // don't do right trim. Some users want to preserve the spaces at the ends
        $editingid = $objectId;
        $optionalcredits = $credits;

        // get the trans from the DB
        /* @var Trans $trans */
        $object = Object::getByObjectId($editingid);
        if (!$object->hasTrans()) {
            // if no trans found, create a new one
            $trans = new Trans();
            $trans->setObjectId($editingid);
            $originalText = '';
        } else {
            $trans = $object->getTrans();
            // otherwise keep the original text to be stored in revHistory
            $originalText = $trans->getWholetext();
        }
        // remove lock applied to this Trans (if any)
        foreach ($trans->getLocks() as $lock) {

            Lock::unlock($lock);
        }
        // remove the locks from the database
        getEM()->flush();

        // set the text fields
        $trans->setTextFieldsWithNewWholeText($new_translit);
        if ($originalText == $trans->getWholetext()) {
            // nothing changed
            return false;
        }

        // something changed, we need to create a revision history record
        $rev = new RevHistory();
        $rev->setAuthor($user->getUsername());
        $rev->setModDate(new DateTime());
        $rev->setOriginalText($originalText);
        $rev->setNewText($new_translit);
        $rev->setCredit($optionalcredits);

        $rev->setTrans($trans);
        $trans->addRevHistory($rev);
        // save new trans content and the revision history
        getEM()->persist($rev);
        getEM()->persist($trans);
        // the calling function need to commit the changes by calling em->flush()
        // otherwise the update won't be committed
        return true;
    }
}