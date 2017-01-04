<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="utf-8">
<meta http-equiv="X-UA-Compatible" content="IE=edge">
<meta name="viewport" content="width=device-width, initial-scale=1">

<title>@yield('title') - Spring Vnn</title>

<!-- Fonts -->
<link href="/css/vnn.css" rel="stylesheet" type="text/css">

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
<div class="topbar">
	@section('topbar')
		<!--  CC 4.0 icons-icomoon -->
		<a href="/dash/"><img class="icon" src="/img/home.png" title="Home"></a>
		<a href="/bulletin/"><img class="icon" src="/img/pushpin.png" title="Bulletins"></a>
		<a href="/keyring/"><img class="icon" src="/img/key.png" title="Keyring"></a>
		
		<span class="uri">{{ isset($uri) ? $uri : "none" }}</span>
		<a href="/dash/logout/"><img class="icon right" src="/img/switch.png" title="Logout"></a>
	@show	
</div>
@yield('actionbar')
	
<div class="content">
	@yield('content')
</div>

</body>
</html>
