<?php

namespace AnVas\Tests\Unit;

use AnVas\Array2xml;
use DOMDocument;
use PHPUnit\Framework\TestCase;
use PHPUnit\Util\Xml;

class Array2xmlTest extends TestCase
{
    /**
     * @var Array2xml
     */
    protected $array2xml;

    protected function setUp(): void
    {
        $this->array2xml = new Array2xml();
        $this->array2xml->setNewTab("\t\t");
    }

    protected function tearDown(): void
    {
        $this->array2xml = null;
    }

    protected function execute($expected_xml, $actual_array)
    {
        $actual_xml = $this->array2xml->convert($actual_array);

        $actual = new DOMDocument();
        $actual->preserveWhiteSpace = false;
        $actual->loadXML($actual_xml);

        $expected = new DOMDocument();
        $expected->preserveWhiteSpace = false;
        $expected->loadXML($expected_xml);

        $this->assertEqualXML(
            $expected->firstChild, $actual->firstChild, true
        );
        $this->assertEquals($expected, $actual);
    }

    public function testConvertDefault()
    {
        $actual = [
            'item1' => 'Text 3',
            'item2' => 1234,
            'item3' => [
                'subItem1' => 'Text 1',
                'subItem2' => 'Text 2',
            ],
        ];

        $expected = '<root>
<item1>Text 3</item1>
<item2>1234</item2>
<item3>
        <subItem1>Text 1</subItem1>
        <subItem2>Text 2</subItem2>
</item3>
</root>';

        $this->execute($expected, $actual);
    }

    public function testSkipNumeric()
    {
        $actual = [
            '1234' => 'Text 3',
            'item2' => 1234,
            'item3' => [
                'subItem1' => 'Text 1',
                'subItem2' => 'Text 2',
            ],
        ];

        $expected = '<root>
<item2>1234</item2>
<item3>
        <subItem1>Text 1</subItem1>
        <subItem2>Text 2</subItem2>
</item3>
</root>';

        $this->execute($expected, $actual);
    }

    public function testNoSkipNumeric()
    {
        $actual = [
            '1234' => 'Text 3',
            'item2' => 1234,
            'item3' => [
                'subItem1' => 'Text 1',
                'subItem2' => 'Text 2',
            ],
        ];

        $expected = '<root>
<key1234>Text 3</key1234>
<item2>1234</item2>
<item3>
        <subItem1>Text 1</subItem1>
        <subItem2>Text 2</subItem2>
</item3>
</root>';

        $this->array2xml->setSkipNumeric(false);

        $this->execute($expected, $actual);
    }

    public function testFilterNumbersAll()
    {
        $actual = [
            'item1' => 'Text 3',
            'item2' => 1234,
            'item3' => [
                'subItem1' => 'Text 1',
                'subItem2' => 'Text 2',
            ],
        ];

        $expected = '<root>
<item>Text 3</item>
<item>1234</item>
<item>
        <subItem>Text 1</subItem>
        <subItem>Text 2</subItem>
</item>
</root>';

        $this->array2xml->setFilterNumbersInTags(true);

        $this->execute($expected, $actual);
    }

    public function testFilterNumbersSpecific()
    {
        $actual = [
            'foo1' => 'Text 3',
            'bar1' => 1234,
            'baz1' => [
                'subBaz1' => 'Text 1',
                'subBaz2' => 'Text 1',
                'subBaz3' => 'Text 1',
                'subBaz4' => 'Text 1',
                'subItem2' => 'Text 2',
            ],
        ];

        $expected = '<root>
<foo>Text 3</foo>
<bar1>1234</bar1>
<baz1>
        <subBaz>Text 1</subBaz>
        <subBaz>Text 1</subBaz>
        <subBaz>Text 1</subBaz>
        <subBaz>Text 1</subBaz>
        <subItem2>Text 2</subItem2>
</baz1>
</root>';

        $this->array2xml->setFilterNumbersInTags(['foo', 'subBaz']);

        $this->execute($expected, $actual);
    }

    public function testEmptyElementSyntaxFull()
    {
        $actual = ['foo' => '', 'bar' => 'text'];
        $expected = '<root><foo></foo><bar>text</bar></root>';

        $this->array2xml->setEmptyElementSyntax(Array2xml::EMPTY_FULL);

        $this->execute($expected, $actual);
    }

    public function testConvertEmptyElementSyntaxSelf()
    {
        $actual = ['foo' => '', 'bar' => 'text'];
        $expected = '<root><foo/><bar>text</bar></root>';

        $this->array2xml->setEmptyElementSyntax(Array2xml::EMPTY_SELF_CLOSING);

        $this->execute($expected, $actual);
    }

    public function testElementAttrsDefault()
    {
        $actual = ['foo' => 'Some', 'bar' => 'Text'];
        $expected = '<root><foo test="true">Some</foo><bar>Text</bar></root>';

        $this->array2xml->setElementsAttrs(['foo' => ['test' => 'true']]);

        $this->execute($expected, $actual);
    }

    public function testElementAttrsDynamic()
    {
        $actual = [
            'foo' => ['@content' => 'Some', '@attributes' => ['test' => 'true']],
            'bar' => 'Text',
        ];
        $expected = '<root><foo test="true">Some</foo><bar>Text</bar></root>';

        $this->execute($expected, $actual);
    }

    public function testElementAttrsDynamicZeroContent()
    {
        $actual = [
            'foo' => ['@content' => '0', '@attributes' => ['test' => 'true']],
            'bar' => 'Text',
        ];
        $expected = '<root><foo test="true">0</foo><bar>Text</bar></root>';

        $this->execute($expected, $actual);
    }

    public function testRootAttrs()
    {
        $actual = ['foo' => 'text'];
        $expected = '<root someAttr="value" someOtherAttr="some value"><foo>text</foo></root>';

        $this->array2xml->setRootAttrs(['someAttr' => 'value', 'someOtherAttr' => 'some value']);

        $this->execute($expected, $actual);
    }

    public function testRootName()
    {
        $actual = ['foo' => 'text'];
        $expected = '<products><foo>text</foo></products>';

        $this->array2xml->setRootName('products');

        $this->execute($expected, $actual);
    }

    public function testNonAssocArrayDefault()
    {
        $actual = [1, 5, 2, 'some', null];

        $expected1_xml = '<root></root>';

        $actual_xml = $this->array2xml->convert($actual);

        $actual = new DOMDocument;
        $actual->preserveWhiteSpace = false;
        $actual->loadXML($actual_xml);

        $expected1 = new DOMDocument;
        $expected1->preserveWhiteSpace = false;
        $expected1->loadXML($expected1_xml);

        $this->assertEqualXML(
            $expected1->firstChild, $actual->firstChild, true
        );
    }

    public function testNonAssocArrayConfigured()
    {
        $actual = [1, 5, 2, 'some', null];
        $expected_xml = '<root>
<key0>1</key0>
<key1>5</key1>
<key2>2</key2>
<key3>some</key3>
<key4></key4>
</root>';

        $this->array2xml->setEmptyElementSyntax(Array2xml::EMPTY_FULL);
        $this->array2xml->setSkipNumeric(false);

        $this->execute($expected_xml, $actual);
    }

    public function testCyrillic()
    {
        $actual = ['itemOne' => 'Один', 'itemTwo' => 'Демо строка'];
        $expected_xml = '<root><itemOne>Один</itemOne><itemTwo>Демо строка</itemTwo></root>';

        $this->execute($expected_xml, $actual);
    }

    public function testInvalidAttributesDynamic()
    {
        $actual = [
            'foo' => ['@content' => 'Some'],
            'bar' => 'Text',
        ];

        $expected_xml = '<root><foo></foo><bar>Text</bar></root>';

        $actual_xml = $this->array2xml->convert($actual);

        $actual = new DOMDocument;
        $actual->preserveWhiteSpace = false;
        $actual->loadXML($actual_xml);

        $expected = new DOMDocument;
        $expected->preserveWhiteSpace = false;
        $expected->loadXML($expected_xml);

        $this->assertEqualXML(
            $expected->firstChild, $actual->firstChild, true
        );
    }

    /**
     * As current method will be removed since PHPUnit v10
     *
     * @see https://github.com/sebastianbergmann/phpunit/issues/4091
     *
     * @param \DOMElement $expectedElement
     * @param \DOMElement $actualElement
     * @param bool $checkAttributes
     * @param string $message
     */
    private function assertEqualXML(\DOMElement $expectedElement, \DOMElement $actualElement, bool $checkAttributes = false, string $message = ''): void
    {
        $expectedElement = Xml::import($expectedElement);
        $actualElement   = Xml::import($actualElement);

        static::assertSame(
            $expectedElement->tagName,
            $actualElement->tagName,
            $message
        );

        if ($checkAttributes) {
            static::assertSame(
                $expectedElement->attributes->length,
                $actualElement->attributes->length,
                \sprintf(
                    '%s%sNumber of attributes on node "%s" does not match',
                    $message,
                    !empty($message) ? "\n" : '',
                    $expectedElement->tagName
                )
            );

            for ($i = 0; $i < $expectedElement->attributes->length; $i++) {
                $expectedAttribute = $expectedElement->attributes->item($i);
                $actualAttribute   = $actualElement->attributes->getNamedItem($expectedAttribute->name);

                \assert($expectedAttribute instanceof \DOMAttr);

                if (!$actualAttribute) {
                    static::fail(
                        \sprintf(
                            '%s%sCould not find attribute "%s" on node "%s"',
                            $message,
                            !empty($message) ? "\n" : '',
                            $expectedAttribute->name,
                            $expectedElement->tagName
                        )
                    );
                }
            }
        }

        Xml::removeCharacterDataNodes($expectedElement);
        Xml::removeCharacterDataNodes($actualElement);

        static::assertSame(
            $expectedElement->childNodes->length,
            $actualElement->childNodes->length,
            \sprintf(
                '%s%sNumber of child nodes of "%s" differs',
                $message,
                !empty($message) ? "\n" : '',
                $expectedElement->tagName
            )
        );

        for ($i = 0; $i < $expectedElement->childNodes->length; $i++) {
            $this->assertEqualXML(
                $expectedElement->childNodes->item($i),
                $actualElement->childNodes->item($i),
                $checkAttributes,
                $message
            );
        }
    }
}