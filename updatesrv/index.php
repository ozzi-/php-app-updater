<?php
  header('Content-Type: application/json');
  $dbjson = readDB();
  $db = parseDB($dbjson);

  if(isset($_GET['operation'])){
    $op=$_GET['operation'];
    if($op==="test"){
      http_response_code(202);
      die();
    }
    if($op==="newest"){
      $newestRelease = getNewest($db);
      echo(json_encode($newestRelease));
    }
    if($op==="update"){
	if(isset($_GET['buildid'])){
	  $release = getNext($db,$_GET['buildid']);
	  $newerAvailable = intval($release['buildid'])>intval($_GET['buildid']);
	  http_response_code($newerAvailable?200:404);
	  if($newerAvailable){
  	    echo(json_encode($release));
	  }
	  die();
	} else {
	  http_response_code(400);
          die();
        }
    }
    if($op==="download"){
      if(isset($_GET['buildid'])){
        $release = getByBID($db,$_GET['buildid']);
        if($release==NULL){
          http_response_code(404);
          die();
        }
      }else{
        $release = getNewest($db);
      }
      if(isset($_GET['signature'])){
		header('Content-Type: text/plain');
        echo($release['signature']);
        die();
      }else{
        header("Location: repo/".$release['filename']);
        die();
      }
    }
  }else{
    echo($dbjson);
  }

  function getByBID($db,$bid){
    foreach($db as $release){
      if($release['buildid']==$bid){
        return $release;
      }
    }
    return NULL;
  }

  function getNext($db,$buildID){
    foreach($db as $release){
      if($release['buildid']>$buildID){
        return $release;
      }
    }
    return false;
  }

  function getNewest($db){
    $highestRelease=NULL;
    foreach($db as $release){
       if($highestRelease==NULL){
         $highestRelease=$release;
       }else{
         $highestRelease=$release['buildid']>$highestRelease['buildid']?$release:$highestRelease;
       }
    }
    return $highestRelease;
  }

  function parseDB($dbjson){
    $db = json_decode($dbjson,true);
    if($db===NULL){
      http_response_code(500);
    }
    return $db;
  }

  function readDB(){
    $dbfile = "db.json";
    if(!file_exists($dbfile)){
      http_response_code(500);
    }
    $dbjson = file_get_contents("db.json");
    return $dbjson;
  }
?>
