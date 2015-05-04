CDLI Search
====================

CDLI Search provides three primary functions:

* Search CDLI Objects by catalogue information or transliterations (sometimes called *text* in the code)
* Maintain the CDLI transliterations and their revision histories.
* Manage user accounts and permissions (including admin, edit, can_see_private_transliteration, etc.)


The live code is located at the cdli server at the directory: **/Library/WebServer/Documents/cdli/search**. The souce code repository is hosted on **[Bitbucket](https://bitbucket.org/eyjchen/cdli_search/overview)**.

# CDLI Objects and Maintenance Scripts
A CDLI Object contains the following information:

1. **ObjectId (or P-number)**
    * ObjectID is an integer used as the unique identifier to a CDLI object. It is sometimes displayed in the format of **P-number**, which is composed of a character *'P'* followed by the ObjectID in 6-digit zero-padded format (e.g. *P000001*).
2. **Catalogue information** (e.g. *CDLI no.*, *Seal no.*, *Primary publication*, etc)
    * CDLI Search system maintains a snapshot of the catalogue information in the **cataloguesnew** table. This table can be updated using the script: */Volumes/cdli_www_2/transfers/cdlicore/dbs/Catalogue.pl*. The script loads the latest catalogue data located at */Volumes/cdli_www_2/transfers/cdlicore/dbs/cdlicat.csv* (manually uploaded by Bob) and generates a new **cataloguesnew** table in the MySql DB. This table is then joined with the **fulltrans** table to create a transcat table. However, this joint table is no longer being used.
3. **Transliteration (or text)**
    * Transliteration stored in **fulltrans** table is the meat of this system. It is is backed up daily by a periodic job: */etc/periodic/daily/998.cdli_backup*, which runs the backup script
*/Volumes/cdli_www_2/transfers/cdlicore/transliterations/Backup_FullTrans.java* to dump the transliterations. The revision histories and the credits of transliterations are stored in **revhistories** table.

# Database Structure
The CDLI Search system uses a MySql database, named **cdlidb**, hosted on *db.cdli.ucla.edu* server. There are many tables in the database, but only the following five tables are really being used:

* ###cataloguesnew
    stores the latest snapshot of catalogue data.
    * **id_text** column stores the CDLI ObjectId each entry corresponds to
    * **public**, **public_atf**, **public_images** columns specify the access rules of the object. Only the users who have sufficient permission can see the content that is not public. (More explanation in **webuser** table)
* ###fulltrans
    stores the latest version of the transliterations
    * **object_id** column stores the CDLI ObjectId
    * **wholetext** column stores the transliteration along with the comments and translations in different languages. (more explanation later)
    * **transliteration** column stores only the transliteration
    * **transliteration_for_search** column stores the transliteration with all the non-letter-and-non-numbers characters replaced with one single space(e.g. replace '#$%' with ' '). This column is used to speed up the search. (more explanation later)
    * **transliteration_clean** column is the older version of the transliteration_for_search column and is no longer not being used.
* ###revhistories
    stores the revision histories of transliterations.
* ###webuser
    store the username, hashed-password (see login.php) and their permissions. The available permissions are:
    * *admin* The super user of the system. An admin can create/modify users and their permissions in *accountManagement.php*
    * *can_download_hd_images* Allow user to download high-definition images from [download_archival_images.php](https://bitbucket.org/eyjchen/cdli_search/src/tip/download_archival_images.php?at=default)
    * *can_view_private_catalogues* Allow user to see object that is not public in the search result.
    * *can_view_private_transliterations* Allow user to see and search transliteration whose atf is not public.
    * *can_view_privateImages* Allow user to see images that are not public.
    * *can_edit_tranliterations* Allow user to edit and lock a object's transliteration.
    * *canViewIpadWeb* Allow user to access Ipad version of CDLI.
* ###lockForModify
    maintains edit locks.
    * **object_id, author**, and **start_date** columns specify which object is being locked, by whom, and when. If an object is locked, only the locking user can edit and unlock the object. However, an admin user can unlock objects locked by any users.
    * There are a few other columns in this table. They are no longer being used.

#Data Access Interface
The CDLI Search system uses an Object Relational Mapper (ORM) framework called *Doctrine* to make data access simpler. The framework maps each above-mentioned table to a PHP class (called Entity Class) that contains methods to access/modify each corresponding column. The mappings are specified in the XML files located in ***config*** folder. The entity classes are located in the ***entities***. ***bootstrap.php*** contains the logic that bootstraps the Doctrine framework, entity manager, and connections to the MySql DB. Any papge that needs to accesses database has to include this file as following:
``` php
require_once "bootstrap.php";
```

**(IMPORTANT)** Instead of using raw SQL query, every page should use the functions provided by *Doctrine* to query or create entities, and use the methods defined in the entities classes to access or update columns. See [Doctrine 2.0 Tutorial](http://docs.doctrine-project.org/projects/doctrine-orm/en/latest/tutorials/getting-started.html) for a good introduction to Doctrine framework, and see [search.php](https://bitbucket.org/eyjchen/cdli_search/src/tip/utils/Search.php?at=default#cl-223) to see how Doctrine is being used in CDLI Search.

#Source Code Directory Structure
* [root](https://bitbucket.org/eyjchen/cdli_search/src/tip/?at=default) The root directory contains the PHP pages that interact with user. The pages in this directory usually includes functionality from other PHP files in the sub-directories. The main pages are:
    * [search.php](https://bitbucket.org/eyjchen/cdli_search/src/tip/search.php?at=default) Show a panel for users to specify their search cretiria.

    * [search_results.php](https://bitbucket.org/eyjchen/cdli_search/src/tip/search_results.php?at=default) Shows the search results. This page is the meat of the CDLI Search. It invokes the search logic implemented in /utils/Search.php and display the search results using the ObjectRender in /renderers directory.

    * [edit.php](https://bitbucket.org/eyjchen/cdli_search/src/tip/edit.php?at=default) Show interface for user to edit a single transliteration.

    * [modify.php](https://bitbucket.org/eyjchen/cdli_search/src/tip/modify.php?at=default) Commit transloteration edit to the DB.

    * [revhistory.php](https://bitbucket.org/eyjchen/cdli_search/src/tip/revhistory.php?at=default) Show revision history of a transliteration.

    * [uploadTrans.php](https://bitbucket.org/eyjchen/cdli_search/src/tip/uploadTrans.php?at=default) Allow user to upload a atf files to update transliterations in bulk.

    * [accountManagement.php](https://bitbucket.org/eyjchen/cdli_search/src/tip/accountManagement.php?at=default) Allow the user to change password and unlock transliterations. For admin user, additionally show the [user management panel](https://bitbucket.org/eyjchen/cdli_search/src/tip/accountManagementTable.html) that allows add/delete/modify users and permissions.

* [/utils](https://bitbucket.org/eyjchen/cdli_search/src/tip/utils/?at=default) Contains the libraries that implement the search and update logic.
    * [Search.php](https://bitbucket.org/eyjchen/cdli_search/src/tip/utils/Search.php?at=default) implements the transliteration search logic. The user of this library creates a *Search* object and specify the seach criterias. Then, invoke *getResults()* to get the matched objects. (See [search_results.php](https://bitbucket.org/eyjchen/cdli_search/src/tip/search_results.php?at=default#cl-147))

    * [TransPhrase.php](https://bitbucket.org/eyjchen/cdli_search/src/tip/utils/TransPhrase.php?at=default) implements TransPhrase class that transforms a transliteration search phrase (e.g. "*lu2-kal-la*") to the corresponding Regular Expression Pattern and LIKE phrase (see **Transliteration Search Explained**)

    * [UpdateTrans.php](https://bitbucket.org/eyjchen/cdli_search/src/tip/utils/TransPhrase.php?at=default) implements the logic for updating a transliteration, including unlocking the object, and maintaining the revision histories.

* [/config](https://bitbucket.org/eyjchen/cdli_search/src/tip/config/?at=default) Contains the ORM mapping config files.

* [/entities](https://bitbucket.org/eyjchen/cdli_search/src/tip/entities/?at=default) Contains the entity classes mapped to each database table. A entity class contains the method to access and update database columns.

* [/renderers](https://bitbucket.org/eyjchen/cdli_search/src/tip/renderers/?at=default) Contains ObjectRenderer classes that, given a CDLI object, generates the corresponding HTML for displaying the object. (see [search_results.php](https://bitbucket.org/eyjchen/cdli_search/src/tip/search_results.php?at=default#cl-181)  for example.)

* [/vendor](https://bitbucket.org/eyjchen/cdli_search/src/tip/vendor/?at=default) Contains Doctrine library and javascript libraries.

* [/xhprof](https://bitbucket.org/eyjchen/cdli_search/src/tip/xhprof/?at=default) Contains library that profile the page loading performance (see [search_results.php](https://bitbucket.org/eyjchen/cdli_search/src/1913b127a924d961039471b1f654dc17e2f4c1c5/search_results.php?at=default#cl-10))

#Transliteration Search Explained
* In CDLI Search system, we allow users to search by transliteration. The user can do so by specifying the transliteration search phrase in the *search.php*, and the system will generate the corresponding regular expression patterns (or Regex for short) to find the matched transliterations in the **fulltrans** table.

### Transliteration Match Rules
* The search phrase might be *ama-su*, but we also want to highlight variations, like *ama#?-su*, that allows additional symbols to appear between *ama* and '*-*'. To achieve, we use the following steps to generate the regex pattern for a search phrase (or see [TransPhrase.php](https://bitbucket.org/eyjchen/cdli_search/src/tip/utils/TransPhrase.php?at=default)):

    1. Find all the tokens in a search phrase, where a token is either
        * a series of non-symbol characters (i.e. a word). For example *"ama"* or *"su"*.
        * a symbol character (e.g. "-").
    2. Then, insert regex pattern *(([^[:alnum:]\n'\\.]))\** between any two adjacent tokens to allow symbols to appear between them. (e.g. *(ama(([^[:alnum:]\n'\\.]))\*-(([^[:alnum:]\n'\\.]))\*su)*)
    3. if the first token is a word, it must be at the beginning of the line or prepended by a symbol
    4. if the last token is a word, it must be at the end of the line or followed by a symbol
    5. match any instance of "-" in search phrase with "-" or ":"
    * For example, the resulting regx pattern for *ama-su* becomes:
```sql
      transliteration REGEXP '(([^[:alnum:]\n\'\\\\.]))(ama(([^[:alnum:]\n\'\\\\.]))*(\-|\:)(([^[:alnum:]\n\'\\\\.]))*su)((([^[:alnum:]\n\'\\\\.]))| |$)'
```
### Speed Up Transliteration Search Using LIKE
* **Note**:This speed up process does not occur when the search phrase inputted is already a regex pattern because the pattern may be more complex than the one generated above.

* Running the above regex pattern against the whole fulltrans table needs significant amount time. To speed up the search, we use a LIKE phrase to filter out entries that are not possible to match the full regex pattern and reduce the number of transliterations that needs to be matched against the full pattern. This LIKE phrase is generated in the following steps:
    1. Replace one or multiple consecutive symbols in the search phrase with one single space.
    2. If the first token is a word, add a space to the beginning of the search phrase.
    3. If the last token is a word, add a space to the end of the search phrase.
    4. Sandwich the phrase with *%* symbol

* For example, the resulting LIKE Prase for *ama-su* becomes:
```sql
     transliteration_for_search LIKE '% ama su %'
```

* **Notice that** The LIKE Phrase should be matched against the *transliteration_for_search* column (or called **cleantext** in the code). This column tranforms the original transliteration using a similar logic as we use to generate the LIKE Phrase (see [Trans.php](https://bitbucket.org/eyjchen/cdli_search/src/tip/entities/Trans.php?at=default#cl-302)), so that, if an entry whose cleantext fullfils the LIKE phrase, it might match the full regular expression pattern, but if an entry whose cleantext fails to fullfil the LIKE Phrase, it is not possible to match the full regular expression pattern.

* Following the above rules. The full match query for *ama-su* becomes
```SQL
SELECT * FROM fulltrans WHERE
        transliteration_for_search LIKE '% ama su %' AND
        transliteration REGEXP '(([^[:alnum:]\n\'\\\\.]))(ama(([^[:alnum:]\n\'\\\\.]))*\-(([^[:alnum:]\n\'\\\\.]))*su)((([^[:alnum:]\n\'\\\\.]))| |$)'
```
