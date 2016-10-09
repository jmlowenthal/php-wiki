<?php

$path = ".";
if (isset($_GET["id"])) {
	$path .= $_GET["id"];
}

$doc = "";
if (!file_exists($path)){
	$doc = "<p>No such file</p>";
}
else {
	// Check for file, then dir
	if (is_dir($path)){
		// Directory found
		function iterate($itr) {
			$list = "<ul>";
			
			foreach ($itr as $file) {				
				if ($file->isDot()) continue;
				if ($file->isFile() && $file->getExtension() != "md") continue;
				
				$path = substr(str_replace("\\", "/", $file->getPathname()), 2);
				
				$isFolder = $file->hasChildren();
				$list .= "<li".($isFolder ? " style=\"order: -1\"":"")."><a href='/wiki/".$path."'>";
				if ($isFolder) {
					$list .= "<b>".$file->getFilename()."</b>";
					$list .= iterate($file->getChildren());
				}
				else {
					$list .= $file->getFilename();
				}
				$list .= "</a></li>";
			}
			$list .= "</ul>";
			return $list;
		}
		
		$doc .= iterate(new RecursiveDirectoryIterator($path, FileSystemIterator::CURRENT_AS_SELF | FileSystemIterator::SKIP_DOTS));
	}
	else {
		$exts = [
			"markdown.extensions.abbr",
			"markdown.extensions.footnotes",
			"markdown.extensions.tables",
			"markdown.extensions.sane_lists",
			"markdown.extensions.smarty",
			"markdown.extensions.toc",
			"markdown.extensions.fast-katex"
		];
		$cmd = "python -m markdown";
		foreach ($exts as $ext) {
			$cmd .= " -x ".$ext;
		}
		$cmd .= " \"".$path."\"";
		
		$handle = popen($cmd, "r");
		$doc .= fread($handle, 30000000);
		pclose($handle);
	}
}

?>

<!DOCTYPE html>
<html>
	<head>
		<title><?=$path?></title>
		<link rel="stylesheet" href="/wiki/style.css"/>
		<style>
			#path {
				position: fixed;
				bottom: 0;
				right: 0;
				padding: 0.5rem;
			}
			#wrapper {
				margin-bottom: 30px;
			}
			#up {
				position: fixed;
				display: block;
				width: 30px;
				text-align: center;
				vertical-align: middle;
				top: 0;
				left: 0;
				padding: 0.5em 1em;
				text-decoration: none;
			}
		</style>
	</head>
	<body>
		<a id="up" href="/wiki/<?=substr($path, 2, strrpos($path, "/", -2) - 1)?>">&uarr;</a>
		<div id="wrapper">
			<?php
				if ($doc != "") echo $doc;
				else echo "<p>Nothing to see</p>";
			?>
		</div>
		<pre id="path"><?=$path?></pre>
	</body>
</html>