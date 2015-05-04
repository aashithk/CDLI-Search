<?php
session_start();
ob_start();
header('Content-type: text/html; charset=utf-8');
// extend the timeout as some pages might take long time to load
set_time_limit(180);

?>

<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN"
    "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">

<html xmlns="http://www.w3.org/1999/xhtml">
<head>
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8"/>
    <title>Search for Cuneiform Documents</title>
    <link rel="stylesheet" type="text/css" href="cdli.css"/>
    <link rel="stylesheet" type="text/css" href="cdlisearch.css"/>
    <?php include_once("analyticstracking.php") ?>
</head>
<script src="//ajax.googleapis.com/ajax/libs/jquery/1.10.2/jquery.min.js"></script>
<script src="http://cdli.ucla.edu/js/startup_script.js"></script>
<body>
<!-- HEADER PART -->
<hr align="left" size="2" width="1000"/>
<table border="0" class="header" width="1000">
    <tr>
        <!-- left logo -->
        <td rowspan="3">
            <a href="/">
                <img alt="" width="81" height="51" border="0" src="cdli_logo.gif"/>
            </a>
        </td>

        <!-- title in the middle -->
        <td rowspan="3" width="700" align="center" valign="middle">
            <h1>SEARCH FILES</h1>
        </td>

        <!-- right part -->
        <td align="right">
            <a href="http://cdli.ucla.edu/?q=cdli-search-information">
                Search aids
            </a><br/>
            <FONT color ="#E0E0E0"><i>default sort is by <a style="color:grey;" href="http://cdli.ucla.edu/?q=cdli-search-information#designation">'Designation' </a></i></FONT><br/>

            <?php
            if (!isset($_SESSION['authenticated'])) {
                ?>
                <a target="_blank" href="login.php" link="#0099FF" alink="#0099FF" vlink="#0099FF">
                    <FONT color="#0099FF">Internal login</FONT>
                </a>
            <?php
            } else {
                $username = $_SESSION['name'];
                ?>
                <a href="accountManagement.php" link="#0099FF" alink="#0099FF" vlink="#0099FF">
                    <FONT color="#0099FF">Manage your account</FONT>
                </a>
                <br/>
                <a href="http://cdli.ucla.edu/?q=cdli-search-information" link="#0099FF" alink="#0099FF" vlink="#0099FF"
                   target=_blank">
                    <FONT color="#0099FF">User guide</FONT></a>
                &nbsp;&nbsp;&nbsp;
                <a href="http://cdli.ucla.edu/cdlisearch/search/ipadweb/entry.php" link="#0099FF" alink="#0099FF"
                   vlink="#0099FF" target=_blank">
                    <FONT color="#0099FF">cdli tablet</FONT></a>
                <br/>

                Logged in as <font color="Gray"><?php echo $username; ?></font>&nbsp;&nbsp;<br>
                <a href="logout.php" link="#0099FF" alink="#0099FF" vlink="#0099FF">
                    <FONT color="#0099FF">Log out</FONT>
                </a>
            <?php
            }
            ?>
        </td>
    </tr>
</table>

<hr align="left" size="2" width="1000"/>

<form action="search_results.php" method="get" accept-charset="utf-8" enctype="application/x-www-form-urlencoded">
<p></p>
<ul></ul>
<table border="0" class="cdli_search_fields" width="1000">
    <tr>
        <td width="200"><b> <a style="color:grey;" href="http://cdli.ucla.edu/?q=cdli-search-information#representation_of_results">Representation of results</a></b></td>
        <td align="left" width="620">
            <input type="radio" name="SearchMode" value="Text" checked="checked"/>Full
            <input type="radio" name="SearchMode" value="Line"/>Tabular
        </td>
        <td align="right" width="150">
            <input type="submit" value="Search" name="requestFrom"/>
            <input TYPE="button" OnClick=" cleanALL();" value="Clear" name="clearALL"/>
        <td>
    <tr>
</table>

<table border="0" class="OUTER" CELLPADDING="0" CELLSPACING="8" width="1000">
<TR>
<TD align="left" valign="top">
    <table border="0" class="cdli_search_fields">
        <TR>
            <TD COLSPAN="3"><B><h2>Publication</h2></B></TD>
        </TR>

        <tr>
            <td>
               <a style="color:grey;" href="http://cdli.ucla.edu/?q=cdli-search-information#primary_publication"> Primary publication</a> <!--(e.g. "TRU 001")-->
            </td>
            <td>
                <input type="text" value="" name="PrimaryPublication" class="forminput" size="30"/>
                Sort by <input type="radio" name="order" value="PrimaryPublication"/>
            </td>
        </tr>

        <tr>
            <td>
               <a style="color:grey;" href="http://cdli.ucla.edu/?q=cdli-search-information#author"> Author(s) </a>
            </td>
            <td>
                <input type="text" value="" name="Author" class="forminput" size="30"/>
                Sort by <input type="radio" name="order" value="Author"/>
            </td>
        </tr>

        <tr>
            <td>
                 <a style="color:grey;" href="http://cdli.ucla.edu/?q=cdli-search-information#publication_date">Date of publication </a>
            </td>
            <td>
                <input type="text" value="" name="PublicationDate" class="forminput" size="30"/>
                Sort by <input type="radio" name="order" value="PublicationDate"/>
            </td>
        </tr>

        <tr>
            <td>
               <a style="color:grey;" href="http://cdli.ucla.edu/?q=cdli-search-information#publication_history"> Secondary publication(s)</a> <!--(e.g. "MVN 18")-->
            </td>
            <td>
                <input type="text" value="" name="SecondaryPublication" class="forminput" size="30"/>
                Sort by <input type="radio" name="order" value="SecondaryPublication"/>
            </td>
        </tr>


        <TR ROWSPAN="2">
            <TD COLSPAN="3"><B><h2>Collection information</h2></B></TD>
        </TR>

        <tr>
            <td>
                <a style="color:grey;" href="http://cdli.ucla.edu/?q=cdli-search-information#collection">Collection</a>
            </td>
            <td>
                <input type="text" value="" name="Collection" class="forminput" size="30"/>
                Sort by <input type="radio" name="order" value="Collection"/>
            </td>
        </tr>

        <tr>
            <td>
              <a style="color:grey;" href="http://cdli.ucla.edu/?q=cdli-search-information#accession_no">  Accession number </a> <!--(e.g. "K 00001")-->
            </td>
            <td>
                <input type="text" value="" name="AccessionNumber" class="forminput" size="30"/>
                Sort by <input type="radio" name="order" value="AccessionNumber"/>
            </td>
        </tr>

        <tr>
            <td>
                <a style="color:grey;" href="http://cdli.ucla.edu/?q=cdli-search-information#museum_no">Collection number</a> <!--(e.g. "VAT") -->
            </td>
            <td>
                <input type="text" value="" name="MuseumNumber" class="forminput" size="30"/>
                Sort by <input type="radio" name="order" value="MuseumNumber"/>
            </td>
        </tr>


        <TR ROWSPAN="2">
            <TD COLSPAN="3"><B><h2>Provenience</h2></B></TD>
        </TR>

        <tr>
            <td>
                <a style="color:grey;" href="http://cdli.ucla.edu/?q=cdli-search-information#provenience"> Provenience </a> <!--(e.g. "Nippur") -->
            </td>
            <td>
                <input type="text" value="" name="Provenience" class="forminput" size="30"/>
                Sort by <input type="radio" name="order" value="Provenience"/>
            </td>
        </tr>

        <tr>
            <td>
                <a style="color:grey;" href="http://cdli.ucla.edu/?q=cdli-search-information#excavation_no"> Excavation number </a> <!--(e.g. "W 20") -->
            </td>
            <td>
                <input type="text" value="" name="ExcavationNumber" class="forminput" size="30"/>
                Sort by <input type="radio" name="order" value="ExcavationNumber"/>
            </td>
        </tr>


        <TR ROWSPAN="2">
            <TD COLSPAN="3"><B><h2>Chronology</h2></B></TD>
        </TR>

        <tr>
            <td>
                <a style="color:grey;" href="http://cdli.ucla.edu/?q=cdli-search-information#period">   Period </a> <!--(e.g. "Ur III") -->
            </td>
            <td>
                <input type="text" value="" name="Period" class="forminput" size="30"/>
                Sort by <input type="radio" name="order" value="Period"/>
            </td>
        </tr>

        <tr>
            <td>
               <a style="color:grey;" href="http://cdli.ucla.edu/?q=cdli-search-information#dates_reference"> Dates referenced </a> <!--(e.g. "SH.38") -->
            </td>
            <td>
                <input type="text" value="" name="DatesReferenced" class="forminput" size="30"/>
                Sort by <input type="radio" name="order" value="DatesReferenced"/>
            </td>
        </tr>

        <TR ROWSPAN="2">
            <TD COLSPAN="3"><B><h2>Physical information</h2></B></TD>
        </TR>

        <tr>
            <td>
               <a style="color:grey;" href="http://cdli.ucla.edu/?q=cdli-search-information#object_type"> Object type </a>
            </td>
            <td>
                <input type="text" value="" name="ObjectType" class="forminput" size="30"/>
                Sort by <input type="radio" name="order" value="ObjectType"/>
            </td>
        </tr>

        <tr>
            <td>
                <a style="color:grey;" href="http://cdli.ucla.edu/?q=cdli-search-information#object_remarks">  Object remarks </a>    
            </td>
            <td>
                <input type="text" value="" name="ObjectRemarks" class="forminput" size="30"/>
            </td>
        </tr>

        <tr>
            <td>
               <a style="color:grey;" href="http://cdli.ucla.edu/?q=cdli-search-information#material"> Material </a>
            </td>
            <td>
                <input type="text" value="" name="Material" class="forminput" size="30"/>
            </td>
        </tr>
    </TABLE>
</TD>

<TD align="left" valign="top">
    <DIV ID="AdvSearch" style="display:block">
        <TABLE border="0" class="cdli_search_fields">
            <TR ROWSPAN="2">
                <TD COLSPAN="3"><B><h2>Text content</h2></B></TD>
            </TR>
            <tr>
                <td>
                  <a style="color:grey;" href="http://cdli.ucla.edu/?q=cdli-search-information#text_content">  Transliteration </a>
                </td>
                <td>
                    <input type="text" name="TextSearch" class="forminput" size="30"/>
                </td>
            </tr>

            <TR>
                <TD>
                <td valign="top">
                    <DIV ID="ONELINE">
                        <input type="checkbox" ID="sosl" name="singleLine" value="true"/>Single line search (default:
                        full text)<br/>
                    </DIV>
                    <DIV ID="CaseSensitive">
                        <input type="checkbox" ID="casesensitive" name="caseSensitive" value="true"/>Case sensitive
                        search
                    </DIV>
                    &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;Use commas between multiple items (more <a
                        href="http://cdli.ucla.edu/?q=cdli-search-information">here</a>)<br /><br />
                </TD>
            </TR>

            <tr>
                <td>
                   <a style="color:grey;" href="http://cdli.ucla.edu/?q=cdli-search-information#text_content"> Translation </a>
                </TD>
                <td>
                    <input type="text" value="" name="TranslationSearch" class="forminput" size="30"/>
                </td>
            </tr>

            <tr>
                <td>
                    <a style="color:grey;" href="http://cdli.ucla.edu/?q=cdli-search-information#text_content">  Comment</a>
                </TD>
                <td>
                    <input type="text" value="" name="CommentSearch" class="forminput" size="30"/>
                </td>
            </tr>
            <tr>
                <td>
                   <a style="color:grey;" href="http://cdli.ucla.edu/?q=cdli-search-information#text_content"> Structure </a>
                </TD>
                <td>
                    <input type="text" value="" name="StructureSearch" class="forminput" size="30"/>
                </td>
            </tr>
            <tr>
                <td>
                    &nbsp;
                </TD>
            </tr>

            <tr>
                <td>
                   <a style="color:grey;" href="http://cdli.ucla.edu/?q=cdli-search-information#language"> Language </a>
                </TD>
                <td>
                    <input type="text" value="" name="Language" class="forminput" size="30"/>
                </td>
            </tr>

            <tr>
                <td>
                   <a style="color:grey;" href="http://cdli.ucla.edu/?q=cdli-search-information#genre">  Genre </a> <!--(e.g. "lexical")-->
                </td>
                <td>
                    <input type="text" value="" name="Genre" class="forminput" size="30"/>
                    Sort by <input type="radio" name="order" value="Genre"/>
                </td>
            </tr>

            <tr>
                <td>
                   <a style="color:grey;" href="http://cdli.ucla.edu/?q=cdli-search-information#subgenre"> Sub-genre </a>
                </td>
                <td>
                    <input type="text" value="" name="SubGenre" class="forminput" size="30"/>
                    Sort by <input type="radio" name="order" value="SubGenre"/>
                </td>
            </tr>
            <tr>
                <td>
                   <a style="color:grey;" href="http://cdli.ucla.edu/?q=cdli-search-information#composite_id"> Composite number </a>
                </td>
                <td>
                    <input type="text" value="" name="CompositeNumber" class="forminput" size="30"/>
                    Sort by <input type="radio" name="order" value="CompositeNumber"/>
                </td>
            </tr>
            <tr>
                <td>
                   <a style="color:grey;" href="http://cdli.ucla.edu/?q=cdli-search-information#seal_id"> Seal number </a>
                </TD>
                <td>
                    <input type="text" value="" name="SealID" class="forminput" size="30"/>
                    Sort by <input type="radio" name="order" value="SealID"/>
                </td>
            </tr>

            <TR ROWSPAN="2">
                <TD COLSPAN="3"><B><h2>CDLI data</h2></B></TD>
            </TR>

            <tr>
                <td>
                    <a style="color:grey;" href="http://cdli.ucla.edu/?q=cdli-search-information#id_text">  CDLI number </a>
                </td>
                <td>
                    <input type="text" value="" name="ObjectID" class="forminput" size="30"/>
                    Sort by <input type="radio" name="order" value="ObjectID"/>
                </td>
            </tr>

            <tr>
                <td>
                    <a style="color:grey;" href="http://cdli.ucla.edu/?q=cdli-search-information#atf_source"> ATF source  </a>
                </td>
                <td>
                    <input type="text" value="" name="ATFSource" class="forminput" size="30"/>
                </TD>
            </tr>

            <tr>
                <td>
                   <a style="color:grey;" href="http://cdli.ucla.edu/?q=cdli-search-information#db_source"> Catalogue source </a>
                </td>
                <TD>
                    <input type="text" value="" name="CatalogueSource" class="forminput" size="30"/>
                </TD>
            </tr>

            <tr>
                <td>
                   <a style="color:grey;" href="http://cdli.ucla.edu/?q=cdli-search-information#translation_source"> Translation source </a>
                </td>
                <TD>
                    <input type="text" value="" name="TranslationSource" class="forminput" size="30"/>
                </TD>
            </tr>
        </TABLE>
    </DIV>
</TD>
</TR>
</TABLE>
</br>
<div>
    <a target="_blank" href=http://oracc.museum.upenn.edu/util/atfproc.html>
        <FONT color="#0099FF">Check/convert your atf files: Oracc</FONT>
    </a>
    </br>
    <a target="_blank" href=signfilter.php>
        <FONT color="#0099FF">Check/convert your atf files: CDLI</FONT>
    </a>

</div>
</form>

<?php
if (isset($_SESSION['authenticated'])) {
    ?>
    <div id="uploadTrans">
        <form action="uploadTrans.php" method="post" enctype="multipart/form-data" id="uploadForm" name="upliadsw">
            <label id="upload_trans" for="image">Upload your transliteration file:</label>
            <input type="file" name="file" id="image"/>
            <input type="submit" name="upload" value="Process"/>
            ( Optional Credit: <input type="text" name="credit" id="credit"/> )
        </form>
    </div>
<?php
}
?>

<script language="JavaScript">


    // before upload transliteration
    $("#uploadForm").submit(function (event) {
        var credit = $("#credit").val();
        if (credit != "") {
            var res = confirm("The credit will go to " + credit + ". Are you sure?");
            if (res == false) {
                event.preventDefault();
            }
        }

    });
    function cleanALL() {
        var formInputs = document.getElementsByTagName('input');
        for (var i = 0; i < formInputs.length; i++) {
            var theInput = formInputs[i];
            if (theInput.type == 'text') {
                theInput.value = "";
            }
        }
    }
</script>

</body>
</html>
