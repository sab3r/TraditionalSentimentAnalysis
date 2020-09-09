<html>
<head>
<title>Twitter Analysis</title>
<link rel="stylesheet" href="bootstrap/dist/css/bootstrap.css">
<script type="text/javascript" src="http://code.jquery.com/jquery.min.js"></script>
<script src="bootstrap/dist/js/bootstrap.min.js"></script>
<link href='http://fonts.googleapis.com/css?family=Open+Sans:400,300,600' rel='stylesheet' type='text/css'>
<style type="text/css">
	.bs-example{
    	margin: 20px;
    }
    a
    {
    	text-decoration: none;
    }
    a:hover
    {
    	text-decoration: none;
		
    }
    .menu:hover
    {
    	background-color: #3A91CA;
    }
    .cardTitle
	 {
		font-size:16px;
		border-bottom: solid 1px #CCC;
		padding-bottom: 6px;
	 }
	 .openSans
	 {
	 	font-family: 'Open Sans',sans-serif;
	 	font-weight: 300;
	 }
	 .onHoverBlue:hover
	 {
	 	color: #3A91CA;
	 }
	 .onHoverBlue
	 {
		color:#AAA;	 
	 }
</style>
</head>
<body style="font-family:'Open Sans', sans-serif;font-weight:300;">
<div class="" style="width:100%;border-bottom:solid 0px #DDD;background-color:#2980b9;font-family:'Open Sans', sans-serif;font-weight:300;color:#FFF;position:fixed;top:0px;z-index:2;">
<div class="row" style="margin-bottom:0px;padding-bottom:0px;">


<div class="col-lg-9" style="margin-bottom:0px;padding-bottom:0px;">
<ul class="list-unstyled list-inline" style="font-size:18px;margin-bottom:0px;">
<li class="menu" style="margin-left:20px;padding-top:10px;padding-bottom:10px;margin-bottom:0px;padding-left:14px;padding-right:14px;background-color:#3A91CA;"><a href="" style="color:#FFF;">Twitter Analysis</a></li>
</ul>
</div>
<div class="col-lg-3 text-right">
<form class="input-group input-group-sm" style="margin-top:6px;margin-right:30px;">
  
  <input type="text" class="form-control" name="q" value="<?php echo $_GET['q']; ?>" style="border-radius:0px;border:0px;">
  <span class="input-group-btn">
    <button class="btn-send btn btn-default" type="submit" style="border-radius:0px;border:0px;font-family:'Open Sans',sans-serif;font-weight:400;"><span style="" class="glyphicon glyphicon-search"> </span></button>
  </span>
  
</form>
</div>
</div>
</div>

	
<?php


$DBhostname = "localhost";
$DBusername = "root";
$DBpassword = "thinkpadt420";
$DBdatabase = "seminar";

$overallPositive = 0;
$overallNegative = 0;
$positiveTweets = array();
$negativeTweets = array();

$needles = array();

$con = mysqli_connect($DBhostname,$DBusername,$DBpassword,$DBdatabase);
$result = mysqli_query($con,"SELECT * FROM prepositions");

while($row = mysqli_fetch_array($result)) {
	
	array_push($needles, " ".$row['word']." ");
	
}

$result = mysqli_query($con,"SELECT * FROM negativeCompound");

while($row = mysqli_fetch_array($result)) {
	
	array_push($needlesNegative, " ".$row['word']." ");
	
}
mysqli_query($con,"DELETE FROM tweets");





    $token = "YOUR TOKEN GOES HERE";
    $token_secret = "YOUR TOKEN SECRET GOES HERE";
    $consumer_key = "YOUR CONSUMER KEY GOES HERE";
    $consumer_secret = "YOUR CONSUMER SECRET GOES HERE";

$host = 'api.twitter.com';
$method = 'GET';
$path = '/1.1/search/tweets.json'; // api call path

$search = $_GET['q']; //'continentalgt';
$search = urlencode($search);

$query = array( // query parameters

    'q' => $search,
    'count' => '200',
    'include_entities' => 'false', 
    'lang' => 'en', 
//    'result_type' => 'popular' 
);

$oauth = array(
    'oauth_consumer_key' => $consumer_key,
    'oauth_token' => $token,
    'oauth_nonce' => (string)mt_rand(), // a stronger nonce is recommended
    
    'oauth_timestamp' => time(),
    'oauth_signature_method' => 'HMAC-SHA1',
    'oauth_version' => '1.0'
);

$oauth = array_map("rawurlencode", $oauth); // must be encoded before sorting

$query = array_map("rawurlencode", $query);

$arr = array_merge($oauth, $query); // combine the values THEN sort

asort($arr); // secondary sort (value)

ksort($arr); // primary sort (key)

// http_build_query automatically encodes, but our parameters
// are already encoded, and must be by this point, so we undo
// the encoding step

$querystring = urldecode(http_build_query($arr, '', '&'));

$url = "https://$host$path";

// mash everything together for the text to hash

$base_string = $method."&".rawurlencode($url)."&".rawurlencode($querystring);

// same with the key

$key = rawurlencode($consumer_secret)."&".rawurlencode($token_secret);

// generate the hash

$signature = rawurlencode(base64_encode(hash_hmac('sha1', $base_string, $key, true)));

// this time we're using a normal GET query, and we're only encoding the query params
// (without the oauth params)

$url .= "?".http_build_query($query);
$url=str_replace("&amp;","&",$url); //Patch by @Frewuill

$oauth['oauth_signature'] = $signature; // don't want to abandon all that work!

ksort($oauth); // probably not necessary, but twitter's demo does it

// also not necessary, but twitter's demo does this too

function add_quotes($str) { return '"'.$str.'"'; }
$oauth = array_map("add_quotes", $oauth);

// this is the full value of the Authorization line

$auth = "OAuth " . urldecode(http_build_query($oauth, '', ', '));

// if you're doing post, you need to skip the GET building above
// and instead supply query parameters to CURLOPT_POSTFIELDS

$options = array( CURLOPT_HTTPHEADER => array("Authorization: $auth"),
                  //CURLOPT_POSTFIELDS => $postfields,

                  CURLOPT_HEADER => false,
                  CURLOPT_URL => $url,
                  CURLOPT_RETURNTRANSFER => true,
                  CURLOPT_SSL_VERIFYPEER => false);

// do our business

$feed = curl_init();
curl_setopt_array($feed, $options);
$json = curl_exec($feed);
curl_close($feed);

$twitter_data = json_decode($json,true);


$i=0;

while($twitter_data['statuses'][$i]['text']) {
$getit = $twitter_data['statuses'][$i]['text'];
//echo $getit."<br /><br />";
$original = $getit;


mysqli_query($con,"INSERT INTO tweets(text) VALUES ('". mysql_escape_string($getit)."')");

$getit = " ".$getit;															// Adding a space before the text for preposition error correction

$getit = str_replace(" No. ", "number", $getit);					// Change No. to number
$getit = str_replace(" no. ", "number", $getit);					// Change no. to number

$getit = str_replace("RT ", "", $getit);								// Remove all the 'RT' from tweets

$getit = strtolower($getit);												// Change to lower case

$getit = str_replace($needlesNegative, "not", $getit);			// Replace negative compounds with not

$getit = preg_replace("/@\S+/", "", $getit);							// Remove mentions from tweets
//$getit = preg_replace("/[^\00-\255]/","",$getit);

$getit = trim(preg_replace('/[^\x21-\x7E]+/', ' ', $getit));   // Remove non-English ascii characters

$getit = preg_replace("/http\S+/", "", $getit);						// Remove links from tweets 

$getit = preg_replace("#[[:punct:]]#", "", $getit);				// Remove all the punctuation marks

$getit = str_replace($needles, " ", $getit);							// Remove all prepositions and articles 







$getit = preg_replace("/\s+/"," ",$getit);
//echo $getit . "<br /><br />";

$positive = 0;
$negative = 0;
$getit = explode(" ", $getit);
foreach ($getit as $data) {
	$result = mysqli_query($con,"SELECT * FROM dictionary WHERE word='$data' AND type<>'n' AND rank<6 ORDER BY pos+neg ASC");
	if(mysqli_num_rows($result)>=1) {
		$row = mysqli_fetch_array($result);
		$positive = $positive + $row['pos']*(6-$row['rank']);
		$negative = $negative + $row['neg']*(6-$row['rank']);
//    echo $data." ". $row['pos'] ." ". $row['neg'] . "<br />";
 }
 
}
//echo "Positive :".$positive;
//echo "Negative :".$negative;

if($positive > $negative) {
	array_push($positiveTweets,$original);
	$overallPositive++;
}
elseif($positive < $negative)  {
	array_push($negativeTweets,$original);
	$overallNegative++;
}

//$overallPositive = $overallPositive + $positive;
//$overallNegative = $overallNegative + $negative;
//echo "<br /><br />";
$i++;

}

//echo "<br /><br />Overall Positive : ".$overallPositive."<br />";
//echo "<br /><br />Overall Negative : ".$overallNegative."<br />";

mysqli_close($con);
?>

<div class="container" style="height:100%;margin-top:40px;">
	
	
	<div class="col-lg-6 text-center" style="margin-top:100px;">
		<div style="font-size:50px;">Positive</div>
		<div class="text-success" style="font-size:40px;"><?php echo round($overallPositive*100/($overallPositive+$overallNegative),2)."%" ?></div>
	</div>
	<div class="col-lg-6 text-center" style="margin-top:100px;">
		<div style="font-size:50px;">Negative</div>
		<div class="text-danger" style="font-size:40px;"><?php echo round($overallNegative*100/($overallPositive+$overallNegative),2)."%" ?></div>
	</div>
	
</div>	
	
	<div class="conatainer">
	<div class="col-lg-offset-1 col-lg-10">
	<div class="col-lg-6 text-center" style="margin-top:60px;">
		<div class="col-lg-12" style="font-weight:400;">
		<?php
			foreach($positiveTweets as $tweet)
			{
				echo "<div class='text-left' style='border-bottom:solid 1px #EEE;padding-top:10px;padding-bottom:10px; font-size:16px;'>".$tweet."</div>";
			}
		?>
		</div>
	</div>
	<div class="col-lg-6 text-center" style="margin-top:60px;">
		<div class="col-lg-12" style="font-weight:400;">
		<?php
			foreach($negativeTweets as $tweet)
			{
				echo "<div class='text-left' style='border-bottom:solid 1px #EEE;padding-top:10px;padding-bottom:10px; font-size:16px;'>".$tweet."</div>";
			}
		?>
		</div>
	</div>
	</div>
	</div>
	
	

</body>
</html>
