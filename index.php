<?php
    include_once("helpers.php");
	
	$triggersFile="triggers.php"; # defines what filename will be considered for running the post-unzip installation step
	$updateFile="update.zip"; # how the downloaded update shall be called
	$updateSignatureFile="signature"; # how the temporary signature file shall be called
	$updateURL = "https://127.0.0.1/updatesrv/index.php"; # the URL to the update server
	$pubkeyPath = "public_key.pem"; # path to the public key

	if(isset($_GET['step'])){
		$step=$_GET['step'];
		if($step==="download"){
		  handleDownload($updateFile,$updateSignatureFile,$pubkeyPath);
		}
		if($step==="install"){
			handleInstall($updateFile,$updateSignatureFile,$triggersFile);
		}
	}else{
		handleDefault($updateURL);
	}

	function handleDefault($updateURL){
		$version = getCurrentVersion();
		echo("<b>Currently installed version: ".$version["version"]." (".$version["buildid"].")</b><br>");
		$test = doGet($updateURL."?operation=test");
		if($test[0]!=202){
			echo("Connection to update server failed. Please check the configured URL<br>");
		}else{
			$nv=newVersionAvailable();
			if($nv!==false){
				echo("<b>Newer version found :&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;".$nv['version']."(".$nv['buildid'].")</b><br>");
				echo("<br>".$nv['version']." Release Notes:<br><i>".$nv['releasenotes']."</i><br><br>");
				echo('<a href="index.php?step=download">Download</a>');
			}else{
				echo("You are using the newest version.<br><br>Release notes:<br>");
				echo("<i>".$version['releasenotes']."</i>");
			}
		}
	}
  
	function handleInstall($updateFile,$updateSignatureFile,$triggersFile){
		if(!file_exists($updateFile)||!file_exists($updateSignatureFile)){
			echo("Missing Update File");
			echo('<a href="index.php?step=download")>Download</a>');
		}else{
			$zip = new ZipArchive;
			$res = $zip->open($updateFile);
			if ($res===TRUE) {
				$path = pathinfo(realpath($updateFile), PATHINFO_DIRNAME);
				$zip->extractTo($path);
				$zip->close();
				echo("Starting Update<br>");
				if(file_exists($triggersFile)){
					echo("Running triggers<br><i>");
					include($triggersFile);
					echo("</i><br>");
				}
				echo("Installation complete.<br>");
				echo("<br>");
				?><form action="index.php?p=update" method="POST">
					<button>Continue</button>
				</form> <?php
			}else{
				echo("Update failed - ".$res."<br>");
			}
			$cu1=unlink($updateFile);
			$cu2=unlink($updateSignatureFile);
			$cu3=true;
			if(file_exists($triggersFile)){
				$cu3=unlink($triggersFile);
			}
			if(!$cu1||!$cu2||!$cu3){
				echo("Failed to remove update files (".$cu1."-".$cu2."-".$cu3.")<br>");
			}
			$updatedVersionFile = updateVersionFile();
			if(!$updatedVersionFile){
				echo("Failed to update version file<br>");
			}
		}
	}

	function handleDownload($updateFile,$updateSignatureFile,$pubkeyPath){
		$nv=newVersionAvailable();
		$downloadOK = downloadAndCheckUpdate($updateFile,$updateSignatureFile,$nv['buildid'],$pubkeyPath);
		if($downloadOK){
			echo("Downloaded newest version<br>");
			echo("Signature OK<br>");
			echo('<a href="index.php?step=install">Install</a>');
		}else{
			echo("<b>Signature invalid or download corrupt</b> - Aborting");
		}
	}
  
?>
