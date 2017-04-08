<?php

// make sure browsers see this page as utf-8 encoded HTML
header('Content-Type: text/html; charset=utf-8');

$limit = 10;
$query = isset($_REQUEST['q']) ? $_REQUEST['q'] : false;
$results = false;
$sort = isset($_REQUEST['sort']) ? $_REQUEST['sort'] : "";
$baseUrl = "/Library/WebServer/Documents/solr-6.5.0/crawl_data/";
$map = (isset($map)) ? $map : initMap($baseUrl);


function initMap($baseUrl) {
	$file = fopen("mapCNNDataFile.csv", "r");
	$map = array();
	while(! feof($file)){
		$line = fgetcsv($file);
		$map[$baseUrl . $line[0]] = $line[1];
	}
	return $map;
}

if ($query)
{
  // The Apache Solr Client library should be on the include path
  // which is usually most easily accomplished by placing in the
  // same directory as this script ( . or current directory is a default
  // php include path entry in the php.ini)
  require_once('solr-php-client/Apache/Solr/Service.php');

  // create a new solr service instance - host, port, and webapp
  // path (all defaults in this example)
  $solr = new Apache_Solr_Service('localhost', 8983, '/solr/myexample');

  // if magic quotes is enabled then stripslashes will be needed
  if (get_magic_quotes_gpc() == 1)
  {
    $query = stripslashes($query);
  }

  // in production code you'll always want to use a try /catch for any
  // possible exceptions emitted  by searching (i.e. connection
  // problems or a query parsing error)
  try
  {
    $results = $solr->search($query, 0, $limit, array("sort" => $sort));
  }
  catch (Exception $e)
  {
    // in production you'd probably log or email this error to an admin
    // and then show a special message to the user but for this example
    // we're going to show the full exception
    die("<html><head><title>SEARCH EXCEPTION</title><body><pre>{$e->__toString()}</pre></body></html>");
  }
}

?>
<html>
  <head>
    <title>PHP Solr Client</title>
	<style>
		.link {
			text-decoration:none;
		}
		.link:hover{
			text-decoration:underline;
		}
		.entry {
			margin-top:0px;
			margin-bottom:0px;
		}
	</style>
  </head>
  <body>
    <form  accept-charset="utf-8" method="get">
	 <div style="height:35px;width:635px;position:relative;margin:auto;">
      <input id="q" name="q" type="text" value="<?php echo htmlspecialchars($query, ENT_QUOTES, 'utf-8'); ?>" style="border:1px solid blue;padding:0px;height:35px;width:600px;position:absolute;left:0px;top:0px;font-size:15px;"/>
	  <input type="image" src="pictures/search.png" name="submit" border="0" alt="submit" height="35px" width="35px" style="position:absolute;left:600px"/>
  	 </div>
     <br>
<?php
if ($sort == "") {
?>
	<div style="margin:auto;width:635px;">
	  <input id="sort" name="sort" type="radio" value="pageRankFile desc"> pagerank method
	  <input id="sort" name="sort" type="radio" value="" checked> default Solr method
	 </div>
<?php
	} else {
?>
  <div style="margin:auto;width:635px;">	
  	<input id="sort" name="sort" type="radio" value="pageRankFile desc" checked> pagerank method
  	<input id="sort" name="sort" type="radio" value=""> default Solr method
  </div>
<?php
	}
?>
    </form>
<?php

// display results
if ($results)
{
  $total = (int) $results->response->numFound;
  $start = min(1, $total);
  $end = min($limit, $total);
?>
    <div>Results <?php echo $start; ?> - <?php echo $end;?> of <?php echo $total; ?>:</div>
    <ol>
<?php
  // iterate result documents
  //-------
  $ids = array();
  $count = 0;
  //-----
  foreach ($results->response->docs as $doc)
  {
	  //--------
	  $ids[$count++] = $doc->id;
	  //-------
?>
      <li>
<?php
    
	
?>
          <div>
            <div style="font-size:25px;font-weight:bold;"><a class="link entry" href="<?php echo htmlspecialchars($map[$doc->id], ENT_NOQUOTES, 'utf-8');?>" target="_blank"><?php echo htmlspecialchars($doc->title, ENT_NOQUOTES, 'utf-8'); ?></a></div>
			<a class="link entry" href="<?php echo htmlspecialchars($map[$doc->id], ENT_NOQUOTES, 'utf-8'); ?>" target="_blank" style="color:green;margin-top:0px;"><?php echo htmlspecialchars($map[$doc->id], ENT_NOQUOTES, 'utf-8'); ?></a>
			<p style="color:DimGrey;" class="entry"><?php echo htmlspecialchars($doc->description, ENT_NOQUOTES, 'utf-8'); ?></p>
            <p style="color:#5c5c8a;" class="entry"><?php echo htmlspecialchars($doc->id, ENT_NOQUOTES, 'utf-8'); ?></p>
          </div>


      </li>
<?php
  }
?>
    </ol>
<!-- -------- -->
<!-- <br/>
<br/>
<br/>
<br/>
<br/>
<br/>
<div>
<span>id:</span></br>
<?php
foreach($ids as $id) {
?>
	<span><?php echo htmlspecialchars($id, ENT_NOQUOTES, 'utf-8'); ?></span><br>
<?php
}
?>
</div> -->
<!-- -------- -->
<?php
}
?>

  </body>
</html>