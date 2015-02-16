[PHP] Array to XML
============

Array2xml is a PHP library that convers array to valid XML.

Based on [XMLWriter](http://php.net/manual/en/book.xmlwriter.php).


Installation
------------

	require ('/path/to/libs/array2xml.php');


Usage (Ex.: RSS Last News)
----------------

Load the library and set custom configuration

	$array2xml = new Array2xml();
	$array2xml->setRootName('rss');
	$array2xml->setRootAttrs(array('version' => '2.0'));
	$array2xml->setCDataKeys(array('description' => TRUE));

Start to create first root elements

	$data['channel']['title'] 		= 'News RSS';
	$data['channel']['link'] 		= 'http://yoursite.com/';
	$data['channel']['description'] = 'Amazing RSS News';
	$data['channel']['language']	= 'en';

Now, pass elements from DB query in cycle

	$row = $db->lastNews();
	foreach($row as $key => $lastNews)
	{
		$data['channel'][$key]['item']['title'] 		= $lastNews->title;
		$data['channel'][$key]['item']['link'] 			= 'http://yoursite.com/news/'.$lastNews->url;
		$data['channel'][$key]['item']['description'] 	= $lastNews->description;
		$data['channel'][$key]['item']['pubDate'] 		= date(DATE_RFC1123, strtotime($lastNews->added));
	}

And finally, convert and print output data to screen

	echo $array2xml->convert($data);
	exit;