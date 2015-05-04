<?php
/*
 * The following ae the test cases for CDLI search.
 * The supposed numbers of search results is not necessarily correct though...
 */
class SearchTest extends PHPUnit_Framework_TestCase
{
    // ...

    public function testTransSearch()
    {

        // search "a-tu"
        $ret = Search::createSearch(getEM(), User::getGuest(getEM()))
            ->setTransPhrases(TransPhrase::createTransPhrasesFromText("a-tu"))
            ->setMode(Search::FULLTEXT_MODE)
            ->setLimit(1)->getResults();

        $this->assertEquals(1431, count($ret));
        $numberOfRow = 0;
        foreach ($ret as $obj) {
            $numberOfRow++;
        }
        $this->assertEquals(1, $numberOfRow);

        // test with phrase with annotation symbols
        $ret = Search::createSearch(getEM(), User::getGuest(getEM()))
            ->setTransPhrases(TransPhrase::createTransPhrasesFromText("lugal-nig2-lagar-e,1(disz) 1/2(disz)"))
            ->setMode(Search::FULLTEXT_MODE)
            ->setLimit(1)->getResults();

        $this->assertEquals(50, count($ret));

        // test with phrase with annotation symbols and in line mode
        $ret = Search::createSearch(getEM(), User::getGuest(getEM()))
            ->setTransPhrases(TransPhrase::createTransPhrasesFromText("lugal-nig2-lagar-e,1(disz) 1/2(disz)"))
            ->setMode(Search::LINE_MODE)
            ->setLimit(1)->getResults();

        $this->assertEquals(0, count($ret));

        // test line mode
        $ret = Search::createSearch(getEM(), User::getGuest(getEM()))
            ->setTransPhrases(TransPhrase::createTransPhrasesFromText("lu2-kal-la,dub-sar"))
            ->setLimit(1)->setMode(Search::LINE_MODE)->getResults();

        $this->assertEquals(31, count($ret));
        $numberOfRow = 0;
        foreach ($ret as $obj) {
            $numberOfRow++;
        }
        $this->assertEquals(1, $numberOfRow);

        // test permissions
        $ret = Search::createSearch(getEM(), User::login(getEM(), "EuniceChen", "@a88hywe"))
            ->setTransPhrases(TransPhrase::createTransPhrasesFromText("lu2-kal-la,dub-sar"))
            ->setLimit(1)->setMode(Search::LINE_MODE)->getResults();

        $this->assertEquals(34, count($ret));
        $numberOfRow = 0;
        foreach ($ret as $obj) {
            $numberOfRow++;
        }
        $this->assertEquals(1, $numberOfRow);

    }

    public function testCatalogueSearch()
    {

        // search multiple seal and composite IDs
        $ret = Search::createSearch(getEM(), User::getGuest(getEM()))
            ->setCatalogue(array("SealID" => " S002932, S002934"));
        $this->assertEquals(641, count($ret->getResults()));

        // search multiple seal and composite IDs
        $ret = Search::createSearch(getEM(), User::getGuest(getEM()))
            ->setCatalogue(array("CompositeNumber" => " Q000908,   Q000909"));
        $this->assertEquals(1368, count($ret->getResults()));

        // search multiple seal IDs
        $ret = Search::createSearch(getEM(), User::getGuest(getEM()))
            ->setCatalogue(array("SealID" => " S002932, S002934"));
        $this->assertEquals(641, count($ret->getResults()));

        // search 6 digit P numbers
        $ret = Search::createSearch(getEM(), User::getGuest(getEM()))
            ->setCatalogue(array("ObjectID" => " P000001, P000002 "));
        $this->assertEquals(2, count($ret->getResults()));


        // search Ur with ?
        $ret = Search::createSearch(getEM(), User::getGuest(getEM()))
            ->setCatalogue(array("PrimaryPublication" => "UET    ",
                "Author" => "/(^| )Eric( |$)/",
                "ObjectType" => "tablet"));
        $this->assertEquals(427, count($ret->getResults()));

        $ret = Search::createSearch(getEM(), User::getGuest(getEM()))
            ->setCatalogue(array("PrimaryPublication" => "UET    ",
                "Author" => "/(^| )Eric( |$)/",
                "ObjectType" => "tablet"));
        $this->assertEquals(427, count($ret->getResults()));

    }

    public function testLogin()
    {
        User::login(getEM(), "EuniceChen", "@a88hywe");
        $this->assertEquals(User::getCurrentUserOrLoginAsGuest(getEM())->getUsername(), "EuniceChen");
        User::loginAsGuest(getEM());
        $this->assertEquals(User::getCurrentUserOrLoginAsGuest(getEM())->getUsername(), "guest");
    }

}
