<p>IP Adresses of all displays</p>
<?php


	$link = mysql_connect("localhost", "xibo", "xibo");
	if (!$link)
	{
	    die('Could not connect: ' . mysql_error());
	}

	$selected_db = mysql_select_db("xibo");
	if (!$selected_db )
	{
	    die('Could not select: ' . mysql_error());
	}


	$query = "SELECT * FROM display";
	$result = mysql_query($query, $link);


	if(!$result)
	{
       	$message  = 'Invalid query: ' . mysql_error() . "\n";
	    $message .= 'Whole query: ' . $query;
    	die($message);
    }

	$num=mysql_numrows($result);

	$i=0;
	while ($i < $num)
	{
		$clientaddress = mysql_result($result,$i,"ClientAddress");
		echo '<p>'.$clientaddress."</p>";
		$i++;
	}

	mysql_close($link);
?>
