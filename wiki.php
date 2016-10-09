<?php

$origPath = $path = ".";
if (isset($_GET["id"])) {
	$path .= $_GET["id"];
}

$urlPath = 'notes/';

$title = $path;

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

			global $urlPath;
			
			foreach ($itr as $file) {				
				if ($file->isDot()) continue;
				if ($file->isFile() && $file->getExtension() != "md") continue;
				if ($file->getFilename() === 'img') continue;
				if ($file->getFilename() === 'parsedown') continue;
				if ($file->getFilename()[0] === '.') continue;
				
				$path = substr(str_replace("\\", "/", $file->getPathname()), 2);
				$list .= "<li>";
				if ($file->hasChildren()) {
					$list .= "<b><a href='/{$urlPath}{$path}'>{$file->getFilename()}</a></b>";
					$list .= iterate($file->getChildren());
				}
				else {
					$list .= "<a href='/{$urlPath}{$path}'>{$file->getFilename()}</a>";
				}
				$list .= "</li>\n";
			}
			$list .= "</ul>";
			return $list;
		}
		
		$doc .= iterate(new RecursiveDirectoryIterator($path, FileSystemIterator::CURRENT_AS_SELF | FileSystemIterator::SKIP_DOTS));
	}
	else {
		require "./parsedown/Parsedown.php";
		require "./parsedown/MathsParsedown.php";
		$parsedown = new MathsParsedown();
		$doc = $parsedown->text(file_get_contents($path));

		$fileInfo = pathinfo(substr($path, strlen($origPath)));
		$title = $fileInfo['filename'] . ' - ' . '/' . trim(str_replace('\\', '/', $fileInfo['dirname']), '/') . '/';
	}
}


?>

<!DOCTYPE html>
<html>
	<head>
		<title><?=$title?></title>
		<link rel="stylesheet" href="/<?= $urlPath ?>style.css"/>
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
			@media print {
				#path, #up {
					display: none;
				}
				#wrapper {
					max-width: unset;
					width: unset;
				}
			}
			table th {
				text-transform: none;
			}
		</style>
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/KaTeX/0.6.0/katex.min.css">
<script src="https://cdnjs.cloudflare.com/ajax/libs/KaTeX/0.6.0/katex.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/KaTeX/0.6.0/contrib/auto-render.min.js"></script>
<script src="https://cdn.rawgit.com/google/code-prettify/master/loader/run_prettify.js?lang=ml&amp;lang=sql"></script>
	</head>
	<body>
		<a id="up" href="/<?=$urlPath . substr($path, 2, strrpos($path, "/", -2) - 1)?>">&uarr;</a>
		<div id="wrapper">
			<?php
				if ($doc != "") echo $doc;
				else echo "<p>Nothing to see</p>";
			?>
		</div>
		<pre id="path"><?=$path?></pre>

<script>renderMathInElement(document.getElementById("wrapper"), {
	delimiters: [
		{ left: "$$", right: "$$", display: true },
		{ left: "$", right: "$", display: false }
	]
});</script>
	</body>
</html>
