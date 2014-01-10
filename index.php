<?php
/* Load config file */
$conf = parse_ini_file('conf.ini');

/* Load Markdown parser */
require 'lib/Parsedown.php';

if ((bool)$conf['debug']) {
	include '../../lib/ref.php';
}
/* Scan dir for files */
$confiles = scandir($conf['contentdir']);

for ($i=0; $i < count($confiles); $i++) {
	if ($confiles[$i] === '.' || $confiles[$i] === '..' || $confiles[$i] === 'home.md') {
		unset($confiles[$i]);
	}	
}

/* check for files */
if (count($confiles) < 2) {
	exit("'home.md' and/or one more content file missing...");
}

/* sort and clean array */
$files = array_values($confiles);
sort($files);


/* replacements */
$ofn = ['Ae', 'Oe', 'Ue', 'ae', 'oe', 'ue', '_', '.md'];
$nfn = ['Ä', 'Ö', 'Ü', 'ä', 'ö', 'ü', ' ', ''];

/* load all files into arrays */
foreach ($files as $file) {
	if ($file !== 'home.md') {
		$ctn[] = [
			'title'   => str_replace($ofn, $nfn, utf8_encode($file)),
			'url'     => urlencode(str_ireplace('.md', '', $file)),
			'content' => file_get_contents($conf['contentdir'].'/'.$file)
		];
	}	
}


/* page management */
if (isset($_GET['p'])) {
	foreach ($ctn as $page) {
		if ($_GET['p'] === urldecode($page['url'])) {
			$current['title']   = $page['title'];
			$current['content'] = $page['content'];
		}
	}
} else {
	$current['title']   = 'Home';
	$current['content'] = file_get_contents($conf['contentdir'].'/home.md');
}
?>
<!DOCTYPE html>
<html>
<head>
	<meta charset="UTF-8">
	<title><?=$conf['title']?></title>
	<link rel="stylesheet" href="lib/bootstrap.min.css">
</head>

<body>
	<div class="navbar navbar-inverse navbar-fixed-top">
		<div class="container">
			<div class="navbar-header">
				<button class="navbar-toggle" type="button" data-toggle="collapse" data-target=".navbar-collapse">
					<span class="sr-only">Menu</span>
					<span class="icon-bar"></span><span class="icon-bar"></span><span class="icon-bar"></span>
				</button>
				<a href="index.php" class="navbar-brand"><?=$conf['title']?></a>
			</div>
			<div class="collapse navbar-collapse">
				<ul class="nav navbar-nav">
					
					<li <?php echo (!isset($_GET['p'])) ? 'class="active"' : '' ?>><a href="index.php">Home</a></li>
					<?php 
					foreach ($ctn as $page) {
						if (isset($_GET['p'])) {
							if ($_GET['p'] == $page['url']) {
								echo '<li class="active"><a href="index.php?p='.$page['url'].'">'.$page['title'].'</a></li>';
							} else {
								echo '<li><a href="index.php?p='.$page['url'].'">'.$page['title'].'</a></li>';
							}	
						} else {
							echo '<li><a href="index.php?p='.$page['url'].'">'.$page['title'].'</a></li>';
						}						
					}
					?>
				</ul>
			</div>
		</div>
	</div>
	<div class="container" style="margin-top: 60px;">
		<div class="pagecontent">
			<?= Parsedown::instance()->parse($current['content']) ?>
		</div>
	</div>
	<?php if((bool)$conf['showcopyright']): ?>
	<div id="footer">
		<div class="container">
			<p class="text-muted" style="text-align: right;">
				<small>&copy; <?=date('Y') .' '. $conf['copyright'] ?></small>
			</p>
		</div>
	</div>
	<?php endif; ?>
	<script src="lib/jquery-1.10.2.min.js"></script>
	<script src="lib/bootstrap.min.js"></script>
</body>
</html>