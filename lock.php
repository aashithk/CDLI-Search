<?php
require "bootstrap.php";
$user = User::getCurrentUserOrLoginAsGuest(getEM());
if (!$user->canEditTranliterations()) {
    header('Location: login.php');
    exit;
}

if (isset($_POST['pids'])) {
    // get all the request pids
    $pids = explode(",", $_POST['pids']);
    // get all the object from db
    $qb = getEM()->createQueryBuilder();
    $qb->select('o')
        ->from('Object', 'o')
        ->where($qb->expr()->in("o.objectId", $pids));
    $results = $qb->getQuery()->getResult();

    /* @var Object $object */
    foreach ($results as $object) {
        if (!$object->hasTrans()) {
            continue;
        }
        $lock = $object->isLocked();
        if ($lock != false && $lock->getAuthor() != $user) {
            //  if the object is already locked by another user, return error
            $error = "Object " . $object->getObjectPId()
                . " has already been locked by "
                . $lock->getAuthor()->getUsername() . " since " . $lock->getStartDate()->format('c');;
            break;
        } else if ($lock == false) {
            // if no one else lock the object, let the user lock the object
            Lock::lockTransByUser($object->getTrans(), $user);
        }

    }
    if (!isset($error)) {
        getEM()->flush();
        echo '{"result":"success", "msg":"' . count($results) . '"}';
    } else {
        echo '{"result":"error", "msg":"' . $error . '"}';
    }
}