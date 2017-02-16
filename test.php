<?php
header('HTTP/1.1 200 OK', TRUE);
header("Status: 200");
header("Content-Type: text/html; charset=UTF-8");

include_once './common.php';

function callAPI($url, $data = false)
{
    $curl = curl_init();

    curl_setopt($curl, CURLOPT_URL, $url);
    curl_setopt($curl, CURLOPT_POST, 1);
    curl_setopt($curl, CURLOPT_POSTFIELDS, $data);
    

    return curl_exec($curl);
}

$data = array("keywords"=>"إِنَّمَا الْأَعْمَالُ بِالنِّيَّاتِ ، وَإِنَّمَا لِكُلِّ امْرِئٍ مَا نَوَى ، فَمَنْ كَانَتْ هِجْرَتُهُ إِلَى دُنْيَا يُصِيبُهَا أَوْ إِلَى امْرَأَةٍ يَنْكِحُهَا ، فَهِجْرَتُهُ إِلَى مَا هَاجَرَ إِلَيْهِ", "option"=>"exact");
$data = json_encode($data, JSON_UNESCAPED_UNICODE|JSON_UNESCAPED_SLASHES);
echo callAPI("http://booksapi.islam-db.com/api/search/0/more/0", $data);

?>
