[PHP] Array to XML
============

Array2xml is a PHP library that converts array to valid XML.

Based on [XMLWriter](http://php.net/manual/en/book.xmlwriter.php).


Installation
------------

	require ('/path/to/libs/array2xml.php');


Usage (Ex.: RSS Last News)
----------------

Load the library and set custom configuration:

	$array2xml = new Array2xml();
	$array2xml->setRootName('rss');
	$array2xml->setRootAttrs(array('version' => '2.0'));
	$array2xml->setCDataKeys(array('description'));

Start by creating a root element:

	$data['channel']['title'] 		= 'News RSS';
	$data['channel']['link'] 		= 'http://yoursite.com/';
	$data['channel']['description'] = 'Amazing RSS News';
	$data['channel']['language']	= 'en';

Now pass elements from DB query in cycle:

	$row = $db->lastNews();
	foreach($row as $key => $lastNews)
	{
		$data['channel'][$key]['item']['title'] 		= $lastNews->title;
		$data['channel'][$key]['item']['link'] 			= 'http://yoursite.com/news/'.$lastNews->url;
		$data['channel'][$key]['item']['description'] 	= $lastNews->description;
		$data['channel'][$key]['item']['pubDate'] 		= date(DATE_RFC1123, strtotime($lastNews->added));
	}

You can also set element attributes individually like this:
	
	$data['channel'][$key]['item']['@attributes'] 		= array('AttributeName' => $attributeValue);
	
Alternatively, you can use setElementAttrs() method:

	$array2xml->setElementAttrs( array('ElementName' => array('AttributeName' => $attributeValue) ));

Note that in this case all elements with specified name will have identical attribute names and values.

If you need to include a raw XML tree somewhere, mark it's element using `$array2xml->setRawKeys(array('elementName'))`



Finally, convert and print output data to screen

	echo $array2xml->convert($data);
	exit;
