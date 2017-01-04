<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="utf-8">
<meta http-equiv="X-UA-Compatible" content="IE=edge">
<meta name="viewport" content="width=device-width, initial-scale=1">

<title>Virtual node Network</title>

<!-- Fonts -->
<link href="https://fonts.googleapis.com/css?family=Raleway:100,600" rel="stylesheet" type="text/css">

<!-- Styles -->
<style>
html, body {
	background-color: #fff;
	font-family: sans-serif;
	font-weight: 100;
	height: 100vh;
	margin: 0;
}

</style>
</head>
<body>
<?php
	$hier = explode('.', $_SERVER['HTTP_HOST']);
	$node = reset($hier);
	var_dump($node);
?>
</body>
</html>
