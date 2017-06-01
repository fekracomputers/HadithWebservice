<?php
header('HTTP/1.1 200 OK', TRUE);
header("Status: 200");
header("Content-Type: application/json; charset=UTF-8");
//header("Content-Type: text/html; charset=UTF-8");
include_once './common.php';

$method = $_SERVER['REQUEST_METHOD'];
$request = explode('/', trim($_SERVER['REQUEST_URI'],'/'));
$input = json_decode(file_get_contents('php://input'), true);

echo WebService::processCommand($method, $request, $input);
//echo WebService::processCommand("post", array("api", "getcategories", 0, "more", 3), array("keywords"=>""));
//echo WebService::processCommand("post", array("api", "getauthors", "more", 175), array("keywords"=>""));
//echo WebService::processCommand("post", array("api", "getbooks", "more", 0), array("keywords"=>"", "of"=>"category", "id"=>1));
//echo WebService::processCommand("post", array("api", "getbooks", "more", 58), array("keywords"=>"", "of"=>"author", "id"=>31));
//echo WebService::processCommand("post", array("api", "getbooks", "more", 58), array("keywords"=>"", "of"=>"mybooks", "id"=>"user@server.com"));
//echo WebService::processCommand("post", array("api", "getbooks", "more", 58), array("keywords"=>"", "of"=>"books", "ids"=>array(59, 60)));
//echo WebService::processCommand("post", array("api", "saveuserpreference"), array("useremail"=>"user@server.com", "userpreferencelist"=>array(array(59, 1), array(60, 1))));
//echo WebService::processCommand("post", array("api", "loaduserpreference"), array("useremail"=>"user@server.com"));
//echo WebService::processCommand("post", array("api", "getnarrators", "more", 0), array("keywords"=>"ابو هريرة", "rotba"=>"صحابي"));
//echo WebService::processCommand("post", array("api", "getnarrators", "more", 0), array("keywords"=>"", "rotba"=>"", "ids"=>array(31, 1)));
//echo WebService::processCommand("post", array("api", "getbooksubjects", 60, 0, "more", 0), array("keywords"=>"غسل"));
//echo WebService::processCommand("post", array("api", "getbook", 60), array());
//echo WebService::processCommand("post", array("api", "getauthor", 31), array());
//echo WebService::processCommand("post", array("api", "getnarrator", 31), array());
//echo WebService::processCommand("post", array("api", "gethadith", 19, "id", 1), array());
//echo WebService::processCommand("post", array("api", "search", 798, "more", 4), array("keywords"=>"الملك", "option"=>""));
//echo WebService::processCommand("post", array("api", "search", 0, "more", 4), array("keywords"=>"الملك", "option"=>"exact"));
//echo WebService::processCommand("post", array("api", "search", 0, "more", 135521), array("narratorid"=>"5"));
//echo WebService::processCommand("post", array("api", "getsimilarwords"), array("word"=>"الني", "limit"=>10));
//echo WebService::processCommand("post", array("api", "gettafseer", 146, 8), array());

?>