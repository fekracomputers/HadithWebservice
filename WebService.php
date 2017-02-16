<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

/**
 * Description of WebService
 *
 * @author softlock
 */
class WebService {
    
    public static function getCategories($request, $input)
    {
        $parentCategoryID = safeGetInt($request[2], null);
            
        $startAfterID = 0; 
        $option = safeGetString($request[3], "");
        if($option=="more")$startAfterID = safeGetInt($request[4], 0);

        $keywords = safeGetString($input["keywords"], "");
        $limit = safeGetInt($input["limit"], MAX_RESULT_COUNT);

        if(mb_strlen($keywords)>0 && $parentCategoryID==0)$parentCategoryID = null;

        $categories = UtilityDB::getCategories($keywords, $parentCategoryID, $startAfterID, $limit);

        $finalCategories = array();
        foreach ($categories as $id=>$title)
        {
            //echo "<p>$id:$title";

            $finalCategory = new stdClass();
            $finalCategory->id = intval($id);
            $finalCategory->title = $title;
            $finalCategory->nbooks = UtilityDB::countCategoryBooks($id);

            array_push($finalCategories, $finalCategory);
        }

        return json_encode($finalCategories, JSON_UNESCAPED_UNICODE|JSON_UNESCAPED_SLASHES);
    }

    public static function getAuthors($request, $input)
    {
        $startAfterID = 0; 
        $option = safeGetString($request[2], "");
        if($option=="more")$startAfterID = safeGetInt($request[3], 0);

        $keywords = safeGetString($input["keywords"], "");
        $limit = safeGetInt($input["limit"], MAX_RESULT_COUNT);
        
        $authors = UtilityDB::getAuthors($keywords, $startAfterID, $limit);

        $finalAuthors = array();
        foreach ($authors as $id=>$name)
        {
            //echo "<p>$id:$name";

            $finalAuthor = new stdClass();
            $finalAuthor->id = intval($id);
            $finalAuthor->name = $name;
            $finalAuthor->nbooks = UtilityDB::countAuthorBooks($id);

            array_push($finalAuthors, $finalAuthor);
        }

        return json_encode($finalAuthors, JSON_UNESCAPED_UNICODE|JSON_UNESCAPED_SLASHES);
    }
    
    public static function getBooks($request, $input)
    {
        $startAfterID = 0; 
        $option = safeGetString($request[2], "");
        if($option=="more")$startAfterID = safeGetInt($request[3], 0);

        $of = safeGetString($input["of"], null);
        if($of == "author" || $of == "category")
        {
            $ofData = safeGetInt($input["id"], 0);
        }
        else if($of=="books")
        {
            $ofData = safeGetIntArray($input["ids"], array());
            $ofData = json_encode($ofData);
            $ofData = str_replace("[", "(", $ofData);
            $ofData = str_replace("]", ")", $ofData);
        }
        else if($of=="mybooks")
        {
            $of = "books";
            $ofData = UtilityDB::loadUserPreference("user@server.com");
            $ofData = str_replace("[", "(", $ofData);
            $ofData = str_replace("]", ")", $ofData);
            //echo $ofData;
        }
        else
        {
            return json_encode(array("response"=>0, "reason"=>MSG_INVALID_REQUEST_FORMAT));
        }

        $keywords = safeGetString($input["keywords"], "");
        $limit = safeGetInt($input["limit"], MAX_RESULT_COUNT);

        $books = UtilityDB::getBooks($keywords, $of, $ofData, $startAfterID, $limit);

        $finalBooks = array();
        foreach ($books as $id=>$title)
        {
            //echo "<p>$id:$title";

            $finalBook = new stdClass();
            $finalBook->id = intval($id);
            $finalBook->title = $title;
            $finalBook->authors = UtilityDB::getBookAuthors($id);

            array_push($finalBooks, $finalBook);
        }

        return json_encode($finalBooks, JSON_UNESCAPED_UNICODE|JSON_UNESCAPED_SLASHES);
    }

    public static function getNarrators($request, $input)
    {
        $startAfterID = 0; 
        $option = safeGetString($request[2], "");
        if($option=="more")$startAfterID = safeGetInt($request[3], 0);

        $keywords = safeGetString($input["keywords"], "");
        $rotba = safeGetString($input["rotba"], "");
        $limit = safeGetInt($input["limit"], MAX_RESULT_COUNT);
        $narratorsIDs = safeGetIntArray($input["ids"], null);

        $narrators = UtilityDB::getNarrators($keywords, $rotba, $narratorsIDs, $startAfterID, $limit);
        
        foreach ($narrators as $narrator){
            $narrator->nhadith = UtilityDB::countNarratorHadith($narrator->id);
        }

        return json_encode($narrators, JSON_UNESCAPED_UNICODE|JSON_UNESCAPED_SLASHES);
    }

    public static function getSubjects($request, $input)
    {
        $bookID = safeGetInt($request[2], null);
            
        $parentSubjectID = safeGetInt($request[3], null);

        $startAfterID = 0; 
        $option = safeGetString($request[4], "");
        if($option=="more")$startAfterID = safeGetInt($request[5], 0);

        $keywords = safeGetString($input["keywords"], "");
        $limit = safeGetInt($input["limit"], 1000000);

        if(mb_strlen($keywords)>0 && $parentSubjectID==0)$parentSubjectID = null;

        $subjects = UtilityDB::getSubjects($bookID, $keywords, $parentSubjectID, $startAfterID, $limit);

        $finalSubjects = array();
        foreach ($subjects as $item)
        {
            //echo "<p>$item->id:$item->title:$item->firsthadithid";
            //echo getSearchText($item->title);

            $finalSubject = new stdClass();
            $finalSubject->id = intval($item->id);
            $finalSubject->title = $item->title;
            $finalSubject->hadithid = $item->firsthadithid;

            array_push($finalSubjects, $finalSubject);
        }

        return json_encode($finalSubjects, JSON_UNESCAPED_UNICODE|JSON_UNESCAPED_SLASHES);
    }

    public static function saveUserPreference($request, $input)
    {
        $userEmail = safeGetString($input["useremail"], false);
        if($userEmail===false)
            return json_encode(array("response"=>0, "reason"=>MSG_INVALID_REQUEST_FORMAT));

        $userPreferenceList = safeGetIntArray($input["userpreferencelist"], array());
        $userPreferenceList = json_encode($userPreferenceList);

        $result = UtilityDB::saveUserPreference($userEmail, $userPreferenceList);

        if($result===false)return json_encode(array("response"=>0, "reason"=>MSG_OPERATION_FAILED));
        return json_encode(array("response"=>1));
    }
    
    public static function loadUserPreference($request, $input)
    {
        $userEmail = safeGetString($input["useremail"], false);
        if($userEmail===false)
            return json_encode(array("response"=>0, "reason"=>MSG_INVALID_REQUEST_FORMAT));

        $result = UtilityDB::loadUserPreference($userEmail);
        
        return json_encode($result, JSON_UNESCAPED_UNICODE|JSON_UNESCAPED_SLASHES);
    }
    
    public static function getBook($request, $input)
    {
        $bookID = safeGetInt($request[2], 0);
        
        $book = UtilityDB::getBookInfo($bookID);
        if($book===false)
            return json_encode(array("response"=>0, "reason"=>MSG_INVALID_INPUT));
        
        $bookCategory = UtilityDB::getCategoryInfo($book["categoryid"]);
        $bookAuthors = UtilityDB::getBookAuthors($bookID);
                
        $finalBook = new stdClass();
        $finalBook->id = $book["id"];
        $finalBook->title = $book["title"];
        $finalBook->hadithcount = UtilityDB::getHadithCount($bookID);
        $finalBook->authors = array();
        foreach ($bookAuthors as $id=>$name)
        {
            $finalBook->authors[intval($id)] = $name;
        }
        $finalBook->categoryies = array($bookCategory["id"] => $bookCategory["title"]);
        
        return json_encode($finalBook, JSON_UNESCAPED_UNICODE|JSON_UNESCAPED_SLASHES);
    }
        
    public static function getAuthor($request, $input)
    {
        $authorID = safeGetInt($request[2], 0);
        
        $author = UtilityDB::getAuthorInfo($authorID);
        if($author===false)
            return json_encode(array("response"=>0, "reason"=>MSG_INVALID_INPUT));
                       
        $finalAuthor = new stdClass();
        $finalAuthor->id = $author["id"];
        $finalAuthor->title = $author["name"];
        $finalAuthor->deathhigriyear = $author["deathhigriyear"];
        
        return json_encode($finalAuthor, JSON_UNESCAPED_UNICODE|JSON_UNESCAPED_SLASHES);
    }
    
    public static function getNarrator($request, $input)
    {
        $narratorID = safeGetInt($request[2], 0);
        
        $details = UtilityDB::getNarratorDetails($narratorID);
        if($details===false)
            return json_encode(array("response"=>0, "reason"=>MSG_INVALID_INPUT));
        
        $teachers = UtilityDB::getNarratorTeachers($narratorID);
        $students = UtilityDB::getNarratorStudents($narratorID);
        $jarhandadala = UtilityDB::getNarratorsJarhAndAdala($narratorID);
        
        $narrator = new stdClass();
        $narrator->details = $details;
        $narrator->teachers = $teachers;
        $narrator->students = $students;
        $narrator->jarhandadala = $jarhandadala;
        
        return json_encode($narrator, JSON_UNESCAPED_UNICODE|JSON_UNESCAPED_SLASHES);
    }
    
    public static function getHadith($request, $input)
    {
        $bookID = safeGetInt($request[2], 0);
        $option = safeGetString($request[3], null);
        $optionValue = safeGetInt($request[4], -1);
        if($bookID==0 || $option===null || ($option!="id" && $option!="index") || $optionValue==-1)
            return json_encode(array("response"=>0, "reason"=>MSG_INVALID_REQUEST_FORMAT));
        
        $hadithID = 0;
        if($option=="id")
        {
            $hadithID = $optionValue;
        }
        else if($option=="index")
        {
            $hadithID = UtilityDB::hadithIndex2ID($bookID, $optionValue);
        }
        
        $hadith = UtilityDB::getHadith($bookID, $hadithID);
        
        $subject = UtilityDB::getHadithSubject($bookID, $hadithID);
        
        $finalHadith = new stdClass();
        $finalHadith->id = $hadithID;
        $finalHadith->subjectid = $subject["id"];
        $finalHadith->subjecttitle = $subject["title"];
        $finalHadith->nextid = UtilityDB::getNextHadithID($bookID, $hadithID);
        $finalHadith->previousid = UtilityDB::getPreviousHadithID($bookID, $hadithID);
        $finalHadith->lastid = UtilityDB::getLastHadithID($bookID, $hadithID);
        $finalHadith->firstid = UtilityDB::getFirstHadithID($bookID, $hadithID);
        $finalHadith->bookid = $bookID;
        $finalHadith->body = $hadith;
                
        return json_encode($finalHadith, JSON_UNESCAPED_UNICODE|JSON_UNESCAPED_SLASHES);
    }
    
    public static function getSimilarWords($request, $input)
    {
        $time = calcDuration();
        
        $limit = safeGetInt($input["limit"], MAX_RESULT_COUNT);
        $word = safeGetString($input["word"], "");
        $word = mb_trim($word, " \r\n");
        if(mb_strlen($word)<4)
            return json_encode(array("response"=>0, "reason"=>MSG_INVALID_INPUT));
        
        $words = UtilityDB::getSimilarWords($word, $limit);
        
        $duration = calcDuration($time);
        
        $result = new stdClass();
        $result->time = $duration;
        $result->words = $words;
        
        return json_encode($result, JSON_UNESCAPED_UNICODE|JSON_UNESCAPED_SLASHES);
    }
    
    public static function getTafseer($request, $input)
    {
        $bookID = safeGetInt($request[2], null);
        $hadithID = safeGetInt($request[3], null);
        if($bookID===null || $hadithID===null)
            return json_encode(array("response"=>0, "reason"=>MSG_INVALID_INPUT));
        
        $pages = UtilityDB::getTafseer($bookID, $hadithID);
        
        $orgBooksIDs = array();
        foreach ($pages as $page){
            array_push($orgBooksIDs, $page->tafseerbookid);
        }
        $sOrgBooksIDs = json_encode($orgBooksIDs);
        $sOrgBooksIDs = trim($sOrgBooksIDs, "[]");
        $orgBooks = UtilityDB::getOrgBooks("($sOrgBooksIDs)");
        
        $finalPages = array();
        foreach ($pages as $page)
        {
            $finalPage = new stdClass();
            $finalPage->tafseerbookid = $page->tafseerbookid;
            $finalPage->tafseerbooktitle = $orgBooks[$page->tafseerbookid];
            $finalPage->tafseerpageid = $page->tafseerpageid;

            //echo "<p>$finalPage->tafseerbookid, $finalPage->tafseerbooktitle, $finalPage->tafseerpageid:</p>";

            array_push($finalPages, $finalPage);
        }
        
        return json_encode($finalPages, JSON_UNESCAPED_UNICODE|JSON_UNESCAPED_SLASHES);
    }

    public static function search($request, $input)
    {
        $bookID = safeGetInt($request[2], 0);
        
        $startAfterID = 0; 
        $option = safeGetString($request[3], "");
        if($option=="more")$startAfterID = safeGetInt($request[4], 0);

        $keywords = safeGetString($input["keywords"], "");
        $narratorID = safeGetInt($input["narratorid"], 0);
        $limit = safeGetInt($input["limit"], MAX_RESULT_COUNT);
        $option = safeGetString($input["option"], "");
        
        $time = calcDuration();

        if($narratorID==0)
            $hadithList = UtilityDB::searchHadith($bookID, $keywords, $option, $startAfterID, $limit);
        else
            $hadithList = UtilityDB::searchHadithByNarrator($bookID, $narratorID, $startAfterID, $limit);
        
        $duration = calcDuration($time);
        
        $booksIDs = array();
        foreach ($hadithList as $hadith) {
            if(!in_array($hadith->bookid, $booksIDs))
                array_push($booksIDs, $hadith->bookid);
        }
        $booksIDs = array_unique($booksIDs);
        $sBooksIDs = trim(json_encode($booksIDs), "[]");
        $books = UtilityDB::getBooks(null, "books", "($sBooksIDs)");

        $finalHadithList = array();
        foreach ($hadithList as $hadith)
        {
            $finalHdith = new stdClass();
            $finalHdith->id = $hadith->docid;
            $finalHdith->hadithid = $hadith->hadithid;
            $finalHdith->bookid = $hadith->bookid;
            $finalHdith->booktitle = $books[$hadith->bookid];
            $finalHdith->shortbody = $hadith->searchbody;
            $finalHdith->searchtime = $duration;

            //echo "<p>$finalHdith->id: ($finalHdith->bookid - $finalHdith->hadithid) ($finalHdith->booktitle) ($finalHdith->shortbody)";

            array_push($finalHadithList, $finalHdith);
        }

        return json_encode($finalHadithList, JSON_UNESCAPED_UNICODE|JSON_UNESCAPED_SLASHES);
    }

    public static function processCommand($method, $request, $input)
    {
        if(count($request)<2 || strtolower($request[0])!="api")
            return json_encode(array("response"=>0, "reason"=>MSG_INVALID_REQUEST_FORMAT));

        $command = strtolower($request[1]);
        
        if($command=="getcategories")
        {
            return WebService::getCategories($request, $input);
        }
        else if($command=="getauthors")
        {
            return WebService::getAuthors($request, $input);
        }
        else if($command=="getbooks")
        {
            return WebService::getBooks($request, $input);
        }
        else if($command=="getnarrators")
        {
            return WebService::getNarrators($request, $input);
        }
        else if($command=="getbooksubjects")
        {
            return WebService::getSubjects($request, $input);
        }
        else if($command=="getbook")
        {
            return WebService::getBook($request, $input);
        }
        else if($command=="getauthor")
        {
            return WebService::getAuthor($request, $input);
        }
        else if($command=="getnarrator")
        {
            return WebService::getNarrator($request, $input);
        }
        else if($command=="search")
        {
            return WebService::search($request, $input);
        }
        else if($command=="gethadith")
        {
            return WebService::getHadith($request, $input);
        }
        else if($command=="saveuserpreference")
        {
            return WebService::saveUserPreference($request, $input);
        }
        else if($command=="loaduserpreference")
        {
            return WebService::loadUserPreference($request, $input);
        }
        else if($command=="getsimilarwords")
        {
            return WebService::getSimilarWords($request, $input);
        }
        else if($command=="gettafseer")
        {
            return WebService::getTafseer($request, $input);
        }
    }
}
