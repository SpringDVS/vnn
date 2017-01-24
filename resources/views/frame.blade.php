<html>
<head>
<title>Frame Test</title>
<script src="http://code.jquery.com/jquery-3.1.1.min.js"
		integrity="sha256-hVVnYaiADRTO2PzUGmuLJr8BLUSjGIZsDYGmIJLv2b8="
		crossorigin="anonymous">
</script>
			  
<script async type="text/javascript">
	function run_ajax() {
		$.get( "/frame/api/", function( data ) {
			alert( data );
		});
		return false;
	}

</script>
</head>
<body>
	This is at vnodes.sec9<br>
	<input type="button" onclick="run_ajax()">
</body>
</html>
