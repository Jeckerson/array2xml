<?php

/**
 * Array -> XML Converter Class
 *
 * Convert array to clean XML
 *
 * @category	Libraries
 * @author		Anton Vasylyev
 * @link		http://truecoder.name
 * @version		1.3
 */

class Array2xml
{
    private $writer;
    private $version 		= '1.0';
    private $encoding 		= 'UTF-8';
    private $rootName 		= 'root';
    private $rootAttrs 		= array();		//example: array('first_attr' => 'value_of_first_attr', 'second_atrr' => 'etc');
    private $emelentsAttrs 	= array(); 		//example: $attrs['element_name'][] = array('attr_name' => 'attr_value');
    private $CDataKeys		= array();
    private $newLine 		= "\n";
    private $newTab 		= "\t";
    private $numericElement = 'key';
    private $skipNumeric	= TRUE;
    private $_tabulation	= TRUE;			//TODO

    /**
     * Constructor
     *
     * Load Standard PHP Class XMLWriter and path it to variable
     * @access	public
     * @param	array
     * @return	void
     */
    public function __construct($params = array())
    {
        if (is_array($params) and !empty($params))
        {        	foreach ($params as $key => $param)
        	{        		$attr = '_'.$key;
        		if (property_exists($this, $attr))
        		{        			$this->$attr = $param;        		}        	}        }

        $this->writer = new XMLWriter();
    }

    // --------------------------------------------------------------------

	/**
	 * Converter
	 *
	 * Convert array data to XML. Last method to call
	 *
	 * @access	public
	 * @param	array
	 * @return	string
	 */
    public function convert($data)
    {
        $this->writer->openMemory();
        $this->writer->startDocument($this->version, $this->encoding);
        $this->writer->startElement($this->rootName);
        if ( ! empty($this->rootAttrs) and is_array($this->rootAttrs))
        {
        	foreach ($this->rootAttrs as $rootAttrName => $rootAttrText)
        	{
        		$this->writer->startAttribute($rootAttrName);
	        	$this->writer->text($rootAttrText);
	        	$this->writer->endAttribute();
        	}
        }

        $this->writer->text($this->newLine);

        if (is_array($data))
        {
            $this->_getXML($data);
        }

        $this->writer->endElement();
        return $this->writer->outputMemory();
    }

    // --------------------------------------------------------------------

	/**
	 * Set XML Document Version
	 *
	 * @access	public
	 * @param	string
	 * @return	void
	 */
    public function setVersion($version)
    {
        $this->version = (string)$version;
    }

    // --------------------------------------------------------------------

	/**
	 * Set Encoding
	 *
	 * @access	public
	 * @param	string
	 * @return	void
	 */
    public function setEncoding($encoding)
    {
        $this->encoding = (string)$encoding;
    }

    // --------------------------------------------------------------------

	/**
	 * Set XML Root Element Name
	 *
	 * @access	public
	 * @param	string
	 * @return	void
	 */
    public function setRootName($rootName)
    {
        $this->rootName = (string)$rootName;
    }

    // --------------------------------------------------------------------

    /**
     * Set XML Root Element Attributes
     *
     * @access	public
     * @param	array
     * @return	void
     */
    public function setRootAttrs($rootAttrs)
    {
    	$this->rootAttrs = (array)$rootAttrs;
    }

    // --------------------------------------------------------------------

    /**
     * Set Attributes of XML Elements
     *
     * @access	public
     * @param	array
     * @return	void
     */
    public function setElementsAttrs($emelentsAttrs)
    {
   		$this->emelentsAttrs = (array)$emelentsAttrs;
    }

    // --------------------------------------------------------------------

    /**
     * Set keys of array that needed to be as CData in XML document
     *
     * @access	public
     * @param	array
     * @return	void
     */
    public function setCDataKeys($CDataKeys)
    {
    	$this->CDataKeys = (array)$CDataKeys;
    }

    // --------------------------------------------------------------------

    /**
     * Set New Line
     *
     * @access	public
     * @param	string
     * @return	void
     */
    public function setNewLine($newLine)
    {    	$this->newLine = (string)$newLine;    }

    // --------------------------------------------------------------------

    /**
     * Set New Tab
     *
     * @access	public
     * @param	string
     * @return	void
     */
    public function setNewTab($newTab)
    {    	$this->newTab = (string)$newTab;    }

    // --------------------------------------------------------------------

    /**
     * Set Default Numeric Element
     *
     * @access	public
     * @param	string
     * @return	void
     */
    public function setNumericElement($numericElement)
    {    	$this->numericElement = (string)$numericElement;    }

    // --------------------------------------------------------------------

    /**
     * On/Off Skip Numeric Array Keys
     *
     * @access	public
     * @param	string
     * @return	void
     */
    public function setSkipNumeric($skipNumeric)
    {    	$this->skipNumeric = (bool)$skipNumeric;    }

    // --------------------------------------------------------------------

    /**
     * Writing XML document by passing throught array
     *
     * @access	private
     * @param	array
     * @param	int
     * @return	void
     */
    private function _getXML($data, $tabs_count = 0)
    {
        foreach ($data as $key => $val)
        {
            if (is_numeric($key))
            {
                if ($this->skipNumeric === TRUE)
                {
                	if ( ! is_array($val))
                	{
                		$tabs_count = 0;
                	}
                	else
                	{
                		if ($tabs_count > 0)
                		{
                			$tabs_count--;
                		}
                	}

                	$key = FALSE;
                }
                else
                {
                	$key = $this->numericElement.$key;
                }
            }

			if ($key !== FALSE)
			{
				$this->writer->text(str_repeat($this->newTab, $tabs_count));

				// Write element tag name
				$this->writer->startElement($key);

				// Check if there are some attributes
				if (isset($this->emelentsAttrs[$key]))
				{
	            	// Yeah, lets add them
	              	foreach ($this->emelentsAttrs[$key] as $elementAttrName => $elementAttrText)
	               	{
	               		$this->writer->startAttribute($elementAttrName);
						$this->writer->text($elementAttrText);
						$this->writer->endAttribute();
	               	}
	            }
			}

			if (is_array($val))
			{
              	if ($key !== FALSE)
              	{
              		$this->writer->text($this->newLine);
              	}

              	$tabs_count++;
              	$this->_getXML($val, $tabs_count);
              	$tabs_count--;

              	if ($key !== FALSE)
              	{
              		$this->writer->text(str_repeat($this->newTab, $tabs_count));
              	}
			}
			else
			{
              	if ($val != NULL)
              	{
               		if (isset($this->CDataKeys[$key]))
               		{
               			$this->writer->writeCData($val);
               		}
               		else
               		{
               			$this->writer->text($val);
               		}
              	}
			}

			if ($key !== FALSE)
			{
				$this->writer->endElement();
				$this->writer->text($this->newLine);
			}
        }
    }
}
//END Array to Xml Class