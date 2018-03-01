 <?php
	// Updates the version file to the next update available
	function updateVersionFile(){
		global $updateURL;
		$version = getCurrentVersion();
		$newerURL = $updateURL."?operation=update&buildid=".$version["buildid"];
		$ret = doGet($newerURL);
		if(!$ret){
		  echo("Failed updating version file");
		}else{
		  return file_put_contents("version",$ret[1]);
		}
	}
	// parses the version file
	function getCurrentVersion(){
		$version=json_decode(file_get_contents('version'),true);
		if($version===NULL){
		  echo("Failed loading version file");
		  die();
		}
		return $version;
	}
	// checks for the next update for the current build ID 
	function newVersionAvailable(){
		global $updateURL;
		$version = getCurrentVersion();
		$newerURL = $updateURL."?operation=update&buildid=".$version["buildid"];
		$ret = doGet($newerURL);
		if($ret[0]===200){
			return json_decode($ret[1],true);
		}
		return false;
	}

	// downloads the next update and checks the signature
	function downloadAndCheckUpdate($updateFile,$updateSignatureFile,$currentBuildID,$pubkeyPath){
		global $updateURL;
		$downloadURL = $updateURL."?operation=download&buildid=".$currentBuildID;

		$retCode = downloadFile($updateFile,$downloadURL);
		if($retCode==200){
			downloadFile($updateSignatureFile,$downloadURL."&signature");

			$key = openssl_pkey_get_public(file_get_contents($pubkeyPath));
			$hash=(hash_file("sha512",$updateFile));
			$signature=base64_decode(file_get_contents($updateSignatureFile));

			openssl_public_decrypt ($signature , $decrypted , $key);
			$decrypted = trim($decrypted);
			return ($decrypted === $hash);
		}else{
			die("Received status code $retCode from update server. Expected 200. Aborting");
		}
	}

	function doGet($url) {
		$ch = curl_init($url);
		curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 0);
		curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, 0);
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
		$content  = @curl_exec($ch);
		if(curl_errno($ch)){
			die("Network error. ".curl_error($ch));
		}
		$code = curl_getinfo($ch,CURLINFO_RESPONSE_CODE);
		curl_close($ch);
		return array($code,$content);
		}

		function downloadFile($name,$url){
		$fp = fopen($name, 'w+');
		if($fp === false){
			throw new Exception('Could not open: ' . $saveTo);
		}
		$ch = curl_init($url);
		curl_setopt($ch, CURLOPT_FILE, $fp);
		curl_setopt($ch, CURLOPT_TIMEOUT, 20);
		curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
		curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 0);
		curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, 0);
		@curl_exec($ch);
		if(curl_errno($ch)){
		  die("Downloading update failed. ".curl_error($ch));
		}
		$statusCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
		curl_close($ch);
		return $statusCode;
	}
?>
