<?php
	
	require_once 'functions.php';
	
	$config=read_config();
	$chain=@$_GET['chain'];
	
	if (strlen($chain))
		$name=@$config[$chain]['name'];
	else
		$name='';

?>
<html>
	<head>
		<meta http-equiv="Content-Type" content="text/html; charset=utf-8">
		<meta http-equiv="X-UA-Compatible" content="IE=edge">
		<meta name="viewport" content="width=device-width, initial-scale=1">
		
		<title>Demo Verify Certification</title>
		<!--
			<link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/3.3.6/css/bootstrap.min.css" integrity="sha384-1q8mTJOASx8j1Au+a5WDVnPi2lkFfwwEAa8hDDdjZlpLegxhjVME1fgjWPGmkzs7" crossorigin="anonymous">
			<link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/3.3.6/css/bootstrap-theme.min.css" integrity="sha384-fLW2N01lMqjakBkx3l/M9EahuwpSfeNvV63J5ezn3uZzapT0u7EYsXMjQV+0En5r" crossorigin="anonymous">
		-->
		<link rel="stylesheet" href="bootstrap.min.css">
		<link rel="stylesheet" href="styles.css">
		<style>	body {background-color: #eeeeee;}</style>
		<script src="https://ajax.googleapis.com/ajax/libs/jquery/1.11.3/jquery.min.js"></script>
		<script src="https://maxcdn.bootstrapcdn.com/bootstrap/3.3.6/js/bootstrap.min.js" integrity="sha384-0mSbJDEHialfmuBBQP6A4Qrprq5OVfW37PRR3j5ELqxss1yVqOtnepnHVP9aJ7xS" crossorigin="anonymous"></script>
	</head>
	<body>
		<div class="container">
			<h1><a href="./">Demo UIT<?php if (strlen($name)) { ?> &ndash; <?php echo html($name)?><?php } ?></a></h1>
<?php
	if (strlen($chain)) {
		$name=@$config[$chain]['name'];
?>
			
			<nav class="navbar navbar-default">
				<div id="navbar" class="navbar-collapse collapse">
					<ul class="nav navbar-nav">
						<li><a href="./?chain=<?php echo html($chain)?>" class="pair-first">Kiểm tra thông tin</a></li>
						<li><a href="./?chain=<?php echo html($chain)?>&page=nodeinfo" class="pair-first">NodeInfo</a></li>
					</ul>
				</div>
			</nav>

<?php
		set_multichain_chain($config[$chain]);
		
		switch (@$_GET['page']) {
			case 'label':
			case 'nodeinfo':
			case 'asset-file':
				require_once 'page-'.$_GET['page'].'.php';
				break;
				
			default:
				require_once 'page-default.php';
				break;
		}
		
	} else {
?>
			<p class="lead"><br/>Choose an available node to get started:</p>
		
			<p>
<?php
		foreach ($config as $chain => $rpc)
			if (isset($rpc['rpchost']))
				echo '<p class="lead"><a href="./?chain='.html($chain).'">'.html($rpc['name']).'</a><br/>';
?>
			</p>
<?php
	}
?>
		</div>

	<script src="fileUpload.js"></script>
	</body>
</html>
