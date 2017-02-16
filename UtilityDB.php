<?php

class UtilityDB {
    
    private static $mainDB = NULL;
    private static $bodyDB = NULL;
    private static $ftsDB = NULL;
    private static $usersDB = NULL;

    /**
     * @brief generate limit section of SQL statment
     */
    private static function genSQLLimit($start = -1, $limit = -1) {
        $SQLLimit = "";
        if($start>=0 && $limit>=0){
            $SQLLimit = "LIMIT $limit OFFSET $start";
        }
        return $SQLLimit;
    }

    /**
     * @brief generate limit section of SQL statment
     */
    public static function buileSQLLike($fieldName, $keywords)
    {
        if($keywords===null)return "";
        $keywords = getSearchText("".$keywords);
        if(mb_strlen($keywords)==0)return "";
        $keywords = replace($keywords, " ", "%");
        return "AND $fieldName LIKE('%$keywords%')";
    }
    
    /**
     * @brief generate limit section of SQL statment
     */
    public static function buileFTSSQLMatch($fieldName, $keywords)
    {
        if($keywords===null)return "";
        $keywords = getSearchText("".$keywords);
        if(mb_strlen($keywords)==0)return "";
        return "AND $fieldName MATCH('$keywords')";
    }
    
    /**
     * @brief generate limit section of SQL statment
     */
    private static function genSQLFTS($filter) {
        $filter = getSearchText($filter);
        $filter = mb_trim($filter);
        return mb_ereg_replace(" ", " AND ", $filter);
    }

    /**
     * @brief get main db object
     */
    private static function getMainDB() {
        global $baseDataFolder;
        
        if(UtilityDB::$mainDB!==NULL){
            return UtilityDB::$mainDB;
        }
        try
        {
            UtilityDB::$mainDB = NULL;
            $dbFilePath = $baseDataFolder."/Hadith.sqlite";
            if(!file_exists($dbFilePath))return false;
            UtilityDB::$mainDB = new SQLite3($dbFilePath);
        }
        catch(Exception $e){
            die("Cannot open main database");
        }
        return UtilityDB::$mainDB;
    }
    
    /**
     * @brief get users db object
     */
    private static function getUsersDB() {
        global $baseLocalDataFolder;
        
        if(UtilityDB::$usersDB!==NULL){
            return UtilityDB::$usersDB;
        }
        try
        {
            UtilityDB::$usersDB = NULL;
            $dbFilePath = $baseLocalDataFolder."/Users.sqlite";
            UtilityDB::$usersDB = new SQLite3($dbFilePath);
            
            if(UtilityDB::dbSQLVal(UtilityDB::$usersDB, "SELECT email, preferences FROM userspreferences LIMIT 1;")===false)
            {
                echo '<p>Drop userpreferences';
                
                UtilityDB::$usersDB->query("DROP TABLE userspreferences;");

                $results = UtilityDB::$usersDB->query("CREATE TABLE userspreferences (email TEXT NOT NULL, preferences text DEFAULTNULL, PRIMARY KEY (email));");                
            }
        }
        catch(Exception $e){
            die("Cannot open main database");
        }
        return UtilityDB::$usersDB;
    }
    
    /**
     * @brief get main db object
     */
    private static function getBodyDB() {
        global  $baseDataFolder;
        
        if(UtilityDB::$bodyDB!==NULL){
            return UtilityDB::$bodyDB;
        }
        try{
            UtilityDB::$bodyDB = NULL;
            $dbFilePath = $baseDataFolder."/HadithBody.sqlite";
            if(!file_exists($dbFilePath))return false;
            UtilityDB::$bodyDB = new SQLite3($dbFilePath);
        }
        catch(Exception $e){
            die("Cannot open main database");
        }
        return UtilityDB::$bodyDB;
    }
    
    /**
     * @brief get main db object
     */
    private static function getFTSDB() {
        global  $baseDataFolder;
        
        if(UtilityDB::$ftsDB!==NULL){
            return UtilityDB::$ftsDB;
        }
        try{
            UtilityDB::$ftsDB = new SQLite3($baseDataFolder."/HadithFTS.sqlite");
        }
        catch(Exception $e){
            die("Cannot open main database");
        }
        return UtilityDB::$ftsDB;
    }
    
    /**
     * @brief get sql query single value
     */
    private static function dbSQLVal($db, $sql, $echo = false) 
    {
        if($echo)echo $sql;
        
        $results = $db->query($sql);
        if($results===false){
            return false;
        }
        
        $row = $results->fetchArray();
        if($row===false){
            return false;
        }
        
        return $row[0];
    }
    
    /**
     * @brief get sql query row
     */
    private static function dbSQLRow($db, $sql, $echo = false) 
    {
        if($echo)echo $sql;
        
        $results = $db->query($sql);
        if($results===false){
            return false;
        }
        
        $row = $results->fetchArray(SQLITE3_ASSOC);
        if($row===false){
            return false;
        }
        
        return $row;
    }
    
    /**
     * @brief get sql query row
     */
    private static function dbSQLRows($db, $sql, $echo = false) 
    {
        if($echo)echo $sql;
        
        $results = $db->query($sql);
        if($results===false){
            return false;
        }
        
        $all = array();
        while($row = $results->fetchArray(SQLITE3_ASSOC)){
            $all[] = (object)$row;
        }
        
        return $all;
    }

    /**
     * @brief get sql query associative values
     */
    private static function dbSQLAssociative ($db, $sql, $echo = false) 
    {
        if($echo)echo $sql;
        
        $results = $db->query($sql);
        if($results===false){
            return false;
        }
        
        $all = array();
        while($row = $results->fetchArray(SQLITE3_NUM)){
            $all[$row[0]] = $row[1];
        }
        
        return $all;
    }
    
    /**
     * @brief get sql query array values
     */
    private static function dbSQLArray ($db, $sql, $echo = false) 
    {
        if($echo)echo $sql;
        
        $results = $db->query($sql);
        if($results===false){
            return false;
        }
        
        $all = array();
        while($row = $results->fetchArray(SQLITE3_NUM)){
            $all[] = $row[0];
        }
        
        return $all;
    }
    
    /**
     * @brief get category id given category title
     */
    public static function getCategoryID($title) {
        return UtilityDB::dbSQLVal(UtilityDB::getMainDB(), "SELECT id FROM categories WHERE title = '$title';");
    }

    /**
     * @brief get book id given book title
     */
    public static function getBookID($title) {
        return UtilityDB::dbSQLVal(UtilityDB::getMainDB(), "SELECT id FROM books WHERE title = '$title';");
    }

    /**
     * @brief get author id given author name
     */
    public static function getAuthorID($name) {
        return UtilityDB::dbSQLVal(UtilityDB::getMainDB(), "SELECT id FROM authors WHERE name = '$name';");
    }

    /**
     * @brief get categories (id, title) with given parent id, start and limit (ignore start if nulll. ignore limit if null )
     */
    public static function getCategories($keywords = null, $parentCategoryID = null, $startAfterID = null, $limit = MAX_RESULT_COUNT) 
    {        
        $sqlTitleFilter = UtilityDB::buileSQLLike("searchtext", $keywords);
        
        $sqlParentFilter = "";
        if($parentCategoryID!==null)$sqlParentFilter = "AND parentid = $parentCategoryID";
        
        $sqlStartAfter = "";
        if($startAfterID!=null)$sqlStartAfter = " AND title>(SELECT title FROM categories WHERE id = $startAfterID)";
        
        return UtilityDB::dbSQLAssociative(UtilityDB::getMainDB(), "SELECT id, title FROM categories WHERE 1 $sqlStartAfter $sqlParentFilter $sqlTitleFilter ORDER BY title LIMIT $limit;", false);
    }
        
    /**
     * @brief get authors (id, name) for given filter, start and limit (ignore start if nulll. ignore limit if null )
     */
    public static function getAuthors($keywords = null, $startAfterID = null, $limit = MAX_RESULT_COUNT) 
    {        
        $sqlNameFilter = UtilityDB::buileSQLLike("searchtext", $keywords);
        
        $sqlStartAfter = "";
        if($startAfterID!=null)$sqlStartAfter = " AND name>(SELECT name FROM authors WHERE id = $startAfterID)";

        return UtilityDB::dbSQLAssociative(UtilityDB::getMainDB(), "SELECT id, name FROM authors WHERE 1 $sqlStartAfter $sqlNameFilter ORDER BY name LIMIT $limit;", false);
    }

    public static function countCategoryBooks($categoryID) 
    {
        return UtilityDB::dbSQLVal(UtilityDB::getMainDB(), "SELECT count(*) FROM bookscategories WHERE categoryid = $categoryID;", false);
    }

    public static function countAuthorBooks($authorID) 
    {
        return UtilityDB::dbSQLVal(UtilityDB::getMainDB(), "SELECT count(*) FROM booksauthors WHERE authorid = $authorID;", false);
    }

    public static function countNarratorHadith($narratorID) 
    {
        return UtilityDB::dbSQLVal(UtilityDB::getMainDB(), "SELECT count(*) FROM hadithnarrators WHERE narratorid = $narratorID;", false);
    }

    /**
     * @brief get books (id, title) for given filter, category id, author id, start and limit (ignore start if nulll. ignore limit if null )
     */
    public static function getBooks($keywords = null, $of = null, $ofData = null, $startAfterID = null, $limit = MAX_RESULT_COUNT) 
    {        
        $sqlTitleFilter = UtilityDB::buileSQLLike("searchtext", $keywords);
        
        $sqlOf = "";
        if($of=="category")
        {
            if(intval($ofData)>0)
                $sqlOf = "AND id IN (SELECT bookid FROM bookscategories WHERE categoryid = $ofData)";
        }
        else if($of=="author")$sqlOf = "AND id IN (SELECT bookid FROM booksauthors WHERE authorid = $ofData)";
        else if($of=="books")$sqlOf = "AND id IN $ofData";
        else return false;
        
        $sqlStartAfter = "";
        if($startAfterID!=null)$sqlStartAfter = " AND title>(SELECT title FROM books WHERE id = $startAfterID)";
        
        return UtilityDB::dbSQLAssociative(UtilityDB::getMainDB(), "SELECT id, title FROM books WHERE 1 $sqlOf $sqlStartAfter $sqlParentFilter $sqlTitleFilter ORDER BY title LIMIT $limit;", false);
    }
    

    /**
     * @brief get narrators (id, name) for given filter, start and limit (ignore start if nulll. ignore limit if null )
     */
    public static function getNarrators($keywords = null, $rotba = null, $narratorsIDs = null, $startAfterID = null, $limit = MAX_RESULT_COUNT)
    {
        $sqlSearchTextFilter = UtilityDB::buileFTSSQLMatch("searchtext", $keywords);
        if(strlen("".$rotba)>0)$sqlRotbaFilter = " AND rotba = '$rotba' ";
        
        $sqlStartAfter = "";
        if($startAfterID!=null)$sqlStartAfter = " AND id > $startAfterID ";

        if($narratorsIDs==null)
            $ids = UtilityDB::dbSQLArray(UtilityDB::getFTSDB(), "SELECT id FROM narratorsfts WHERE 1 $sqlStartAfter $sqlSearchTextFilter $sqlRotbaFilter ORDER BY id LIMIT $limit;", false);
        else
            $ids = $narratorsIDs;
        
        $sIDs = trim(json_encode($ids), "[]");
        
        return UtilityDB::dbSQLRows(UtilityDB::getMainDB(), "SELECT id, name, lakab, rotba, gender ,higrideathyear, higribirthyear FROM narrators WHERE id IN($sIDs) ORDER BY id;", false);
        
    }
    
    /**
     * @brief get subjects (id, title) for given book id under given subject parent id
     */
    public static function getSubjects($bookID, $keywords = null, $parentSubjectID = null, $startAfterID = null, $limit = MAX_RESULT_COUNT) 
    {
        $sqlTitleFilter = UtilityDB::buileSQLLike("title", $keywords);
        
        $sqlParentFilter = "";
        if($parentSubjectID!==null)$sqlParentFilter = "AND parentid = $parentSubjectID";
        
        $sqlStartAfter = "";
        if($startAfterID!=null)$sqlStartAfter = " AND id>$startAfterID";

        return UtilityDB::dbSQLRows(UtilityDB::getMainDB(), "SELECT id, title, firsthadithid FROM subjects WHERE bookid = $bookID $sqlParentFilter $sqlTitleFilter $sqlStartAfter ORDER BY id LIMIT $limit;", false);
    }
    
    /**
     * @brief get book (id, title) for given book id
     */
    public static function getBookInfo($bookID){
        return UtilityDB::dbSQLRow(UtilityDB::getMainDB(), "SELECT * FROM books WHERE id = $bookID;");
    }
    
    /**
     * @brief get category (id, title, ...) for given category id
     */
    public static function getCategoryInfo($categoryID){
        return UtilityDB::dbSQLRow(UtilityDB::getMainDB(), "SELECT * FROM categories WHERE id = $categoryID;");
        
    }
    
    /**
     * @brief get author (id, name, information, birthhigriyear, deathhigriyear, ...) for given author id
     */
    public static function getAuthorInfo($authorID){
        return UtilityDB::dbSQLRow(UtilityDB::getMainDB(), "SELECT * FROM authors WHERE id = $authorID;");
    }
    
    /**
     * @brief get narrator (id, name, information, birthhigriyear, deathhigriyear, ...) for given author id
     */
    public static function getNarratorName($narratorID){
        return UtilityDB::dbSQLVal(UtilityDB::getMainDB(), "SELECT name FROM narrators WHERE id = $narratorID;");
    }
    
    /**
     * @brief get narrator details(id, name, information, birthhigriyear, deathhigriyear, ...) for given author id
     */
    public static function getNarratorDetails($narratorID){
        return UtilityDB::dbSQLRow(UtilityDB::getMainDB(), "SELECT * FROM narrators WHERE id = $narratorID;");
    }
    
    /**
     * @brief get narrator teachers (id, name) for given narrator id
     */
    public static function getNarratorTeachers($narratorID) {
        return UtilityDB::dbSQLRows(UtilityDB::getMainDB(), "SELECT id, name, lakab, rotba, higrideathyear, higribirthyear FROM narrators WHERE id IN(SELECT teacherid FROM narratorsteachers WHERE narratorid = $narratorID) ORDER BY id;");
    }
    
    /**
     * @brief get narrator teachers (id, name) for given narrator id
     */
    public static function getNarratorStudents($narratorID) {
        return UtilityDB::dbSQLRows(UtilityDB::getMainDB(), "SELECT id, name, lakab, rotba, higrideathyear, higribirthyear FROM narrators WHERE id IN(SELECT studentid FROM narratorsstudents WHERE narratorid = $narratorID) ORDER BY id;");
    }
        
    /**
     * @brief get narrator teachers (id, name) for given narrator id
     */
    public static function getNarratorsJarhAndAdala($narratorID)
    {
        return UtilityDB::dbSQLRows(UtilityDB::getMainDB(), "SELECT num, name, opinion FROM narratorsjarhandadala WHERE id = $narratorID ORDER BY num;");
    }
    
    
    /**
     * @brief get book (id, title) for given book id
     */
    public static function accessBook($bookID) {
        return UtilityDB::getMainDB()->exec("UPDATE books SET accesscount = accesscount + 1 WHERE id = $bookID;");
    }
    
    /**
     * @brief get book (id, title) for given book id
     */
    public static function accessHadith($bookID) {
        return UtilityDB::getMainDB()->exec("UPDATE hadith SET accesscount = accesscount + 1 WHERE id = $bookID;");
    }
    
    /**
     * @brief get authors (id, name) for given book id
     */
    public static function getBookAuthors($bookID) {
        return UtilityDB::dbSQLRows(UtilityDB::getMainDB(), "SELECT id, name FROM authors WHERE id IN(SELECT authorid FROM booksauthors WHERE bookid = $bookID) ORDER BY name;");
    }
    
    /**
     * @brief get page for the given book id, part number, page number
     */
    public static function getDBInfo() {
        $dbInfo = array();
        $dbInfo["nbooks"] = UtilityDB::dbSQLVal(UtilityDB::getMainDB(), "SELECT count(*) FROM books;");
        $dbInfo["ncategories"] = UtilityDB::dbSQLVal(UtilityDB::getMainDB(), "SELECT count(*) FROM categories;");
        $dbInfo["nauthors"] = UtilityDB::dbSQLVal(UtilityDB::getMainDB(), "SELECT count(*) FROM authors;");
        $dbInfo["nnarrators"] = UtilityDB::dbSQLVal(UtilityDB::getMainDB(), "SELECT count(*) FROM narrators;");
        $dbInfo["nhadith"] = UtilityDB::dbSQLVal(UtilityDB::getMainDB(), "SELECT count(*) FROM hadith;");
        return $dbInfo;
    }
    
    /**
     * @brief get hadith body for given book id and hadith id
     */
    public static function getHadith($bookID, $hadithID) {
        return UtilityDB::dbSQLVal(UtilityDB::getBodyDB(), "SELECT body FROM hadithbody WHERE bookid = $bookID and id = $hadithID;", false);
    }
    
    /**
     * @brief get hadith body for given book id and hadith id
     */
    public static function getHadithSubject($bookID, $hadithID) {
        return UtilityDB::dbSQLRow(UtilityDB::getMainDB(), "SELECT id, title FROM subjects WHERE id  = (SELECT subjectid FROM hadith WHERE bookid = $bookID and id = $hadithID);", false);
    }

    /**
     * @brief get hadith details for given book id and hadith id
     */
    public static function getHadithDetails($bookID, $hadithID){
        return UtilityDB::dbSQLRow(UtilityDB::getMainDB(), "SELECT * FROM hadith WHERE bookid = $bookID and id = $hadithID;");
    }
    
    /**
     * @brief get hadith Search Parameter for given book id and hadith id
     */
    public static function getHadithSearchParameter($bookID, $hadithID){
        return UtilityDB::dbSQLRow(UtilityDB::getFTSDB(), "SELECT * FROM hadithfts WHERE bookid = $bookID and hadithid = $hadithID;");
    }

    /**
     * @brief get first hadith id given book id
     */
    public static function hadithIndex2ID($bookID, $index) {
        return UtilityDB::dbSQLVal(UtilityDB::getMainDB(), "SELECT id FROM hadith WHERE bookid = $bookID ORDER BY id LIMIT 1 OFFSET $index;");
    }
    
    /**
     * @brief get first hadith id given book id
     */
    public static function getFirstHadithID($bookID) {
        return UtilityDB::dbSQLVal(UtilityDB::getMainDB(), "SELECT id FROM hadith WHERE bookid = $bookID ORDER BY id LIMIT 1;");
    }

    /**
     * @brief get last hadith id given book id
     */
    public static function getLastHadithID($bookID) {
        return UtilityDB::dbSQLVal(UtilityDB::getMainDB(), "SELECT id FROM hadith WHERE bookid = $bookID ORDER BY id DESC LIMIT 1;");
    }

    /**
     * @brief get previous hadith id given book id and hadith id
     */
    public static function getPreviousHadithID($bookID, $hadithID) {
        return UtilityDB::dbSQLVal(UtilityDB::getMainDB(), "SELECT id FROM hadith WHERE bookid = $bookID and id<$hadithID ORDER BY id DESC LIMIT 1;");
    }

    /**
     * @brief get next hadith id given book id and hadith id
     */
    public static function getNextHadithID($bookID, $hadithID) {
        return UtilityDB::dbSQLVal(UtilityDB::getMainDB(), "SELECT id FROM hadith WHERE bookid = $bookID and id>$hadithID ORDER BY id LIMIT 1;");
    }

    /**
     * @brief get hadith count given book id
     */
    public static function getHadithCount($bookID) {
        return UtilityDB::dbSQLVal(UtilityDB::getMainDB(), "SELECT count(*) FROM hadith WHERE bookid = $bookID;");
    }
    
    /**
     * @brief search hadith for given filter
     */
    public static function searchHadith($bookID = 0, $keywords = "", $option = "", $startAfterID = 0, $limit = MAX_RESULT_COUNT) 
    {
        if($option=="exact")
            $sqlBodyFilter = UtilityDB::buileFTSSQLMatch("searchbody", $keywords);
        else
            $sqlBodyFilter = UtilityDB::buileSQLLike("searchbody", $keywords);

        $sqlBookFilter = "";
        if($bookID!=0)$sqlBookFilter = "AND bookid = $bookID";
        
        $sqlStartAfter = "";
        if($startAfterID!=null)$sqlStartAfter = " AND docid > $startAfterID";

        return UtilityDB::dbSQLRows(UtilityDB::getFTSDB(), "SELECT docid, bookid, hadithid, searchbody FROM hadithfts WHERE 1 $sqlBookFilter $sqlStartAfter $sqlBodyFilter LIMIT $limit;", false);
    }
    
    /**
     * @brief search hadith for given narrator
     */
    public static function searchHadithByNarrator($bookID = 0, $narratorID = 0, $startAfterID = 0, $limit = MAX_RESULT_COUNT) 
    {
        $ftsDB = UtilityDB::getFTSDB();

        $sqlStartAfter = "";
        if($startAfterID!=null){
            $result = (object)UtilityDB::dbSQLRow($ftsDB, "SELECT c0bookid, c1hadithid FROM hadithfts_content WHERE docid = $startAfterID;", false);
            $sqlStartAfter = " AND ( (bookid=$result->c0bookid AND hadithid > $result->c1hadithid) OR bookid>$result->c0bookid)";
        }

        $rows = UtilityDB::dbSQLRows(UtilityDB::getMainDB(), "SELECT DISTINCT bookid, hadithid FROM hadithnarrators WHERE narratorid = $narratorID $sqlStartAfter ORDER BY bookid, hadithid LIMIT $limit;", false);
        
        $hadithList = array();
        foreach ($rows as $row){
            $result = UtilityDB::dbSQLRow($ftsDB, "SELECT docid, c2searchbody FROM hadithfts_content WHERE c0bookid = $row->bookid AND c1hadithid = $row->hadithid;", false);

            $hadith = new stdClass();
            $hadith->docid = $result["docid"];
            $hadith->bookid = $row->bookid;
            $hadith->hadithid = $row->hadithid;
            $hadith->searchbody = $result["c2searchbody"];
            
            array_push($hadithList, $hadith);
        }

        return $hadithList;
    }
    
    public static function saveUserPreference($userEmail, $userPreferenceList)
    {
        $db = UtilityDB::getUsersDB();
        
        $result = intval(UtilityDB::dbSQLVal($db, "SELECT count(*) FROM userspreferences WHERE email='$userEmail'"));
        if($result===0)
        {
            return $db->query("INSERT INTO userspreferences(email, preferences) VALUES('$userEmail', '$userPreferenceList');");
        }
        else
        {
            return $db->query("UPDATE userspreferences SET preferences = '$userPreferenceList' WHERE email = '$userEmail';");
        }
    }
    
    public static function loadUserPreference($userEmail)
    {
        return UtilityDB::dbSQLVal(UtilityDB::getUsersDB(), "SELECT preferences FROM userspreferences WHERE email='$userEmail'");
    }        
    
    public static function getSimilarWords($word, $limit = MAX_RESULT_COUNT)
    {
        return UtilityDB::dbSQLArray(UtilityDB::getFTSDB(), "SELECT word FROM words WHERE word like ('%$word%') LIMIT $limit;", false);
    }  
    
    public static function getOrgBooks($booksIDs = "") 
    {        
        return UtilityDB::dbSQLAssociative(UtilityDB::getMainDB(), "SELECT id, title FROM orgbooks WHERE id IN $booksIDs ORDER BY title;", false);
    }

    public static function getTafseer($bookID, $hadithID)
    {
        return UtilityDB::dbSQLRows(UtilityDB::getMainDB(), "SELECT tafseerbookid, tafseerpageid FROM tafseer, (SELECT orgbookid, orgpageid FROM orghadith WHERE bookid = $bookID and hadithid = $hadithID) AS T WHERE tafseer.bookid = T.orgbookid AND tafseer.pageid = T.orgpageid;", false);
    }  
}
?>
