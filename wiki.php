<?php

$basepath = $path = ".";
if (isset($_GET["id"])) {
	$path .= $_GET["id"];
}

$urlPath = 'wiki/';

$title = $path;

function buildTreeItr($itr) {
	$arr = array();
	foreach ($itr as $file) {
		if ($file->isDot()) continue;
		if ($file->isFile() && $file->getExtension() !== "md") continue;
		if ($file->getFilename()[0] === ".") continue;
		
		$path = substr(str_replace("\\", "/", $file->getPathname()), 2);
		if ($file->hasChildren()) {
			$children = buildTreeItr($file->getChildren());
			if (count($children) > 0) {
				$arr[$file->getFilename()] = array($path, $children);
			}
		}
		else {
			$arr[$file->getFilename()] = array($path);
		}
	}
	return $arr;
}

function buildTree($path) {
	return buildTreeItr(new RecursiveDirectoryIterator($path, FileSystemIterator::CURRENT_AS_SELF | FileSystemIterator::SKIP_DOTS));
}

$doc = "";
if (!file_exists($path)){
	$doc = "<p>No such file</p>";
}
else {
	// Check for file, then dir
	if (is_dir($path)){
		// Directory found
		$tree = buildTree($path);
		
		function htmlTree($arr) {
			global $urlPath;
			$html = "<ul>";
			foreach($arr as $name => $item) {
				$html .= "<li";
				if (isset($item[1])) {
					$html .= " style='order: -1;' class='open'>";
					$html .= "<b><a href=\"{$item[0]}\">{$name}</a></b>";
					$html .= htmlTree($item[1]);
				}
				else {
					$html .= ">";
					$html .= "<a href=\"/{$urlPath}{$item[0]}\">{$name}</a>";
				}
				$html .= "</li>";
			}
			return $html."</ul>";
		}
		
		$doc .= htmlTree($tree);
	}
	else {
		require "./parsedown/MathsParsedown.php";
		$parsedown = new MathsParsedown();
		$doc = $parsedown->text(file_get_contents($path));

		$title = $path;
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

<script>
	window.addEventListener("load", function() {
		Array.from(document.getElementsByClassName("math")).forEach(function(el) {
			katex.render(el.textContent, el, {
				throwOnError: false,
				displayMode: el.classList.contains("display")
			});
		});
	});
	
	window.addEventListener("load", function() {
		var topheaders = document.getElementsByTagName("h1");
		if (topheaders.length == 1) {
			document.title = topheaders[0].textContent;
		}
		else {
			document.title = topheaders[0].textContent;
			for (var i = 1; i < topheaders.length - 1; ++i) {
				document.title += ", " + topheaders[i].textContent;
			}
			document.title += " and " + topheaders[topheaders.length - 1].textContent;
		}
	});
	
	Array.from(document.getElementsByTagName("li")).forEach(function(v) {
		if (v.classList.contains("open") || v.classList.contains("closed")) {
			v.addEventListener("click", function(e) {
				e.stopPropagation();
				var rect = v.getBoundingClientRect();
				if (e.x < rect.left || e.x > rect.right || e.y < rect.top || e.y > rect.bottom) {
					this.classList.toggle("open");
					this.classList.toggle("closed");
				}
			});
		}
	});
</script>
	</body>
</html>
