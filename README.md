[PHP] Array to XML
============

Array2xml is a PHP library that converts array to valid XML.

Based on [XMLWriter](http://php.net/manual/en/book.xmlwriter.php).


Requirements
------------

* PHP 5.3+
* XMLWriter

Installation
------------

	require_once ('/path/to/array2xml.php');


Usage (Ex.: RSS Last News)
----------------

Load the library and set custom configuration:

	$array2xml = new Array2xml();
	$array2xml->setRootName('rss');
	$array2xml->setRootAttrs(array('version' => '2.0'));
	$array2xml->setCDataKeys(array('description'));

Start by creating a root element:

	$data['channel']['title']        = 'News RSS';
	$data['channel']['link']         = 'http://yoursite.com/';
	$data['channel']['description']  = 'Amazing RSS News';
	$data['channel']['language']     = 'en';

Now pass elements from DB query in cycle:

	$row = $db->lastNews();
	foreach($row as $key => $lastNews)
	{
		$data['channel'][$key]['item']['title']       = $lastNews->title;
		$data['channel'][$key]['item']['link']        = 'http://yoursite.com/news/'.$lastNews->url;
		$data['channel'][$key]['item']['description'] = $lastNews->description;
		$data['channel'][$key]['item']['pubDate']     = date(DATE_RFC1123, strtotime($lastNews->added));
	}

You can also set element attributes individually. 
The example below appends an attribute `AttributeName` to `item` node:
	
	$data['channel'][$key]['item']['@attributes'] 		= array('AttributeName' => $attributeValue);
	
Or, if your node doesn't have children, you can use this:
    
    $data['channel]['@attributes'] = array('AttributeName' => $attributeValue);
    $data['channel]['@content']    = 'Content of channel node';
    
This will set both attributes and the content of `channel` node.
	
	
	
Alternatively, you can use setElementsAttrs() method:

	$array2xml->setElementsAttrs( array('ElementName' => array('AttributeName' => $attributeValue) ));

*Note that in this case all elements with specified names will have identical attribute names and values.*

If you need to include a raw XML tree somewhere, mark it's element using `$array2xml->setRawKeys(array('elementName'))`



Finally, convert and print output data to screen

	echo $array2xml->convert($data);
	
Configuration
----------------

You can easily configure this lib to fit your specific use case using setters described below.

#### setVersion(string $version)
Sets XML version header.
#### setEncoding(string $encoding)
Sets XML encoding
#### setRootName(string $rootName)
Set XML Root Element Name 
#### setRootAttrs(array $rootAttrs)
Set XML Root Element Attributes
#### setElementsAttrs(array $attrs)
Set Attributes of every XML Elements that matches the given names.
Example argument: `['elementName' => ['someAttr' => 'attrValue']]`
#### setCDataKeys(array $elementNames)
Marking given elements as CData ones
#### setRawKeys(array $elementNames)
Marking given elements as raw ones
#### setNumericTagPrefix(string $prefix)
Set default prefix for numeric nodes
#### setSkipNumeric(bool $skipNumeric)
On/Off Skip numeric nodes
#### setEmptyElementSyntax(const)
In some cases you might want to control the exact syntax of empty elements.

By default, nodes that are empty or equal to null are using self-closing syntax(`<foo/>`).

You can override this behavior using `Array2xml::EMPTY_FULL` to force using closing tag(`<foo></foo>`).

Available agruments are `Array2xml::EMPTY_SELF_CLOSING` or `Array2xml::EMPTY_FULL`

#### setFilterNumbersInTags(bool|array $data)
Remove numbers from element names.

Possible args are: 

- `boolean TRUE` to remove numbers from ALL elements
- `array` contains node names that need filtering.

This is a easy workaround to have identically named elements in your XML built from an array.

For example, let's build an XML with 3 `image` nodes:

    //list of our images
    $images = array('image1.jpg', 'image3.jpg', 'image3.jpg');
    // input array
    $data = array(); 
    for($i=0;$i<3;$i++){
    	$data['image'.$i] = $images[$i];
    }
    $array2xml = new array2xml();
    $array2xml->setFilterNumbersInTags(array('image'));
    
    $xml = array2xml->convert($data);
    
That's it! Now we have a nasty XML with 3 identically named nodes in it.

Testing
----------------
    phpunit Tests/Array2xmlTest.php