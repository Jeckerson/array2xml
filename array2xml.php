<?php

/**
 * Array -> XML Converter Class
 * Convert array to clean XML
 *
 * @category       Libraries
 * @author         Anton Vasylyev
 * @link           http://truecoder.name
 * @version        1.3
 */
class Array2xml
{
	private $writer;
	private $version = '1.0';
	private $encoding = 'UTF-8';
	private $rootName = 'root';
	private $rootAttrs = array();        //example: array('first_attr' => 'value_of_first_attr', 'second_atrr' => 'etc');
	private $rootSelf = FALSE;
	private $elementAttrs = array();        //example: $attrs['element_name'][] = array('attr_name' => 'attr_value');
	private $CDataKeys = array();
	private $newLine = "\n";
	private $newTab = "\t";
	private $numericTagPrefix = 'key';
	private $skipNumeric = TRUE;
	private $_tabulation = TRUE;            //TODO
	private $defaultTagName = FALSE;    //Tag For Numeric Array Keys
	private $rawKeys = array();
	private $emptyElementSyntax = 1;
	private $filterNumbers = FALSE;
	private $tagsToFilter = array();

	const EMPTY_SELF_CLOSING    = 1;
	const EMPTY_FULL            = 2;


	/**
	 * Constructor
	 * Load Standard PHP Class XMLWriter and path it to variable
	 *
	 * @access    public
	 * @param array $params
	 */
	public function __construct($params = array())
	{
		if (is_array($params) and !empty($params))
		{
			foreach ($params as $key => $param)
			{
				$attr = '_' . $key;
				if (property_exists($this, $attr))
				{
					$this->$attr = $param;
				}
			}
		}

		$this->writer = new \XMLWriter();
	}

	// --------------------------------------------------------------------

	/**
	 * Converter
	 * Convert array data to XML. Last method to call
	 *
	 * @access    public
	 * @param    array
	 * @return    string
	 */
	public function convert(array $data)
	{
		$this->writer->openMemory();
		$this->writer->startDocument($this->version, $this->encoding);
		$this->writer->startElement($this->rootName);
		if (!empty($this->rootAttrs) and is_array($this->rootAttrs))
		{
			foreach ($this->rootAttrs as $rootAttrName => $rootAttrText)
			{
				$this->writer->writeAttribute($rootAttrName, $rootAttrText);
			}
		}


		if ($this->rootSelf === FALSE)
		{
			$this->writer->text($this->newLine);

			if (is_array($data) AND ! empty($data))
			{
				$this->_getXML($data);
			}
		}

		$this->writer->endElement();

		return $this->writer->outputMemory();
	}

	// --------------------------------------------------------------------

	/**
	 * Set XML Document Version
	 *
	 * @access    public
	 * @param    string
	 * @return    void
	 */
	public function setVersion($version)
	{
		$this->version = (string)$version;
	}

	// --------------------------------------------------------------------

	/**
	 * Set Encoding
	 *
	 * @access    public
	 * @param    string
	 * @return    void
	 */
	public function setEncoding($encoding)
	{
		$this->encoding = (string)$encoding;
	}

	// --------------------------------------------------------------------

	/**
	 * Set XML Root Element Name
	 *
	 * @access    public
	 * @param    string
	 * @return    void
	 */
	public function setRootName($rootName)
	{
		$this->rootName = (string)$rootName;
	}

	// --------------------------------------------------------------------

	/**
	 * Set XML Root Element Attributes
	 *
	 * @access    public
	 * @param    array
	 * @return    void
	 */
	public function setRootAttrs(array $rootAttrs)
	{
		$this->rootAttrs = $rootAttrs;
	}

	// --------------------------------------------------------------------

	/**
	 * Set XML Root Self close
	 *
	 * @access    public
	 * @param    bool
	 * @return    void
	 */
	public function setRootSelf($rootSelf)
	{
		$this->rootSelf = (bool)$rootSelf;
	}

	// --------------------------------------------------------------------

	/**
	 * Set Attributes of XML Elements
	 *
	 * @access    public
	 * @param    array
	 * @return    void
	 */
	public function setElementsAttrs(array $elementAttrs)
	{
		$this->elementAttrs = $elementAttrs;
	}

	// --------------------------------------------------------------------

	/**
	 * Set keys of array that needed to be as CData in XML document
	 *
	 * @access    public
	 * @param    array
	 * @return    void
	 */
	public function setCDataKeys(array $CDataKeys)
	{
		$this->CDataKeys = $CDataKeys;
	}

	// --------------------------------------------------------------------

	/**
	 * Set keys of array that needed to be as Raw XML in XML document
	 *
	 * @access    public
	 * @param     array
	 * @return    void
	 */
	public function setRawKeys(array $rawKeys)
	{
		$this->rawKeys = $rawKeys;
	}

	// --------------------------------------------------------------------

	/**
	 * Set New Line
	 *
	 * @access    public
	 * @param    string
	 * @return    void
	 */
	public function setNewLine($newLine)
	{
		$this->newLine = (string)$newLine;
	}

	// --------------------------------------------------------------------

	/**
	 * Set New Tab
	 *
	 * @access    public
	 * @param    string
	 * @return    void
	 */
	public function setNewTab($newTab)
	{
		$this->newTab = (string)$newTab;
	}

	// --------------------------------------------------------------------

	/**
	 * Set Default Numeric Tag Prefix
	 *
	 * @access    public
	 * @param    string
	 * @return    void
	 */
	public function setNumericTagPrefix($numericTagPrefix)
	{
		$this->numericTagPrefix = (string)$numericTagPrefix;
	}

	// --------------------------------------------------------------------

	/**
	 * On/Off Skip Numeric Array Keys
	 *
	 * @access    public
	 * @param    string
	 * @return    void
	 */
	public function setSkipNumeric($skipNumeric)
	{
		$this->skipNumeric = (bool)$skipNumeric;
	}

	// --------------------------------------------------------------------

	/**
	 * Tag For Numeric Array Keys
	 *
	 * @access    public
	 * @param    string
	 * @return    void
	 */
	public function setDefaultTagName($defaultTagName)
	{
		$this->defaultTagName = (string)$defaultTagName;
	}

	// --------------------------------------------------------------------

	/**
	 *  Set preferred syntax of empty elements.
	 *  <element></element> or self-closing <element/>
	 *
	 * @access   public
	 * @param    const Array2xml::EMPTY_SELF_CLOSING or Array2xml::EMPTY_FULL
	 * @return   void
	 */
	public function setEmptyElementSyntax($syntax)
	{
		$this->emptyElementSyntax = $syntax;
	}

	// --------------------------------------------------------------------

	/**
	 *  Remove numbers from tag names.
	 *  Useful if you need to have identically named elements in your XML
	 *
	 *  You can pass either boolean TRUE to remove numbers from ALL tags
	 *  or pass an ARRAY with element names in it(without numbers)
	 *  to filter only specific elements.
	 *
	 * @access   public
	 * @param    bool|array
	 * @return   void
	 */
	public function setFilterNumbersInTags($data)
	{
		if(is_bool($data))
		{
			$this->filterNumbers = $data;
		}
		elseif(is_array($data))
		{
			$this->tagsToFilter = $data;
		}
		else
		{
			throw new \InvalidArgumentException('$data must be a type of boolean or array');
		}
	}

	// --------------------------------------------------------------------

	/**
	 * Writing XML document by passing through array
	 *
	 * @access    private
	 * @param    array
	 * @param    int
	 * @return    void
	 */
	private function _getXML(&$data, $tabs_count = 0)
	{

		foreach ($data as $key => $val)
		{
			unset($data[$key]);

			// Skip attribute param
			if (substr($key, 0, 1) == '@')
			{
				continue;
			}

			if (is_numeric($key))
			{
				if($this->defaultTagName !== FALSE)
				{
					$key = $this->defaultTagName;
				}
				elseif($this->skipNumeric === TRUE)
				{
					if (!is_array($val))
					{
						$tabs_count = 0;
					}
					else
					{
						if ($tabs_count > 0)
						{
							$tabs_count --;
						}
					}

					continue;
				}
				else
				{
					$key = $this->numericTagPrefix . $key;
				}
			}

			if($this->filterNumbers === TRUE
				|| in_array(preg_replace('#[0-9]*#', '', $key), $this->tagsToFilter))
			{
				//remove numbers
				$key = preg_replace('#[0-9]*#', '', $key);
			}


			if ($key !== FALSE)
			{
				$this->writer->text(str_repeat($this->newTab, $tabs_count));


				// Write element tag name
				$this->writer->startElement($key);

				// Check if there are some attributes
				if (isset($this->elementAttrs[$key]) || isset($val['@attributes']))
				{
					if(isset($val['@attributes']) && is_array($val['@attributes']))
					{
						$attributes = $val['@attributes'];
					}
					else
					{
						$attributes = $this->elementAttrs[$key];
					}


					// Yeah, lets add them
					foreach ($attributes as $elementAttrName => $elementAttrText)
					{
						$this->writer->startAttribute($elementAttrName);
						$this->writer->text($elementAttrText);
						$this->writer->endAttribute();
					}

					if(
						!empty($val['@content'])
						&& is_string($val['@content'])
						&& isset($val['@attributes'])
					)
					{
						$val = $val['@content'];
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
				if ($val != NULL || $val === 0)
				{
					if (isset($this->CDataKeys[$key])
						|| array_search($key, $this->CDataKeys) !== FALSE)
					{
						$this->writer->writeCData($val);
					}
					elseif(array_search($key, $this->rawKeys) !== false)
					{
						$this->writer->writeRaw($val);
					}
					else
					{
						$this->writer->text($val);
					}
				}elseif($this->emptyElementSyntax === self::EMPTY_FULL)
				{
					$this->writer->text('');
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
