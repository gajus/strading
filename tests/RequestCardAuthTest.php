<?php
class RequestCardAuthTest extends PHPUnit_Framework_TestCase {
    private
        $credentials,
        $service,
        $order_reference;

    public function setUp () {
        $this->credentials = array(
            'site_reference' => 'test_github53934',
            'username' => 'api@anuary.com',
            'password' => '93gbjdMR'
        );

        $this->service = new \Gajus\Strading\Service($this->credentials['site_reference'], $this->credentials['username'], $this->credentials['password']);

        $factory = new \RandomLib\Factory;
        $generator = $factory->getGenerator(new \SecurityLib\Strength(\SecurityLib\Strength::MEDIUM));

        $this->order_reference = $generator->generateString(32, 'ABCDEFGHIJKLMNOPQRSTUVWXYZ');
    }

    private function loadXML ($test_name, array $replace = array()) {
        $xml = file_get_contents(__DIR__ . '/xml/' . $test_name . '.xml');

        $placeholders = array_map(function ($name) { return '{' . $name . '}'; }, array_keys($this->credentials));
        $xml = str_replace($placeholders, $this->credentials, $xml);
        
        $placeholders = array_map(function ($name) { return '{' . $name . '}'; }, array_keys($replace));
        $xml = str_replace($placeholders, $replace, $xml);

        return $xml;
    }

    /**
     * Remove all text nodes. Used to compare structure of the XML documents.
     */
    private function normaliseXML ($xml) {
        $dom = new \DOMDocument;
        $dom->preserveWhiteSpace = false;
        $dom->formatOutput = true;
        $dom->loadXML($xml);
        $xpath = new \DOMXPath($dom);

        foreach ($xpath->query('//*[not(*) and text()]') as $node) {
            $node->nodeValue = '';
            $node->removeChild($node->firstChild);
        }

        return $dom->saveXML();
    }

    public function testMakeRequest () {
        $auth = $this->service->request('card/auth');

        $auth->populate(array(
            'billing' => array(
                'amount' => 100,
                'amount[currencycode]' => 'GBP',
                'email' => 'foo@bar.baz',
                'name' => array(
                    'first' => 'Foo',
                    'last' => 'Bar'
                ),
                'payment' => array(
                    'pan' => '4111110000000211',
                    'securitycode' => '123',
                    'expirydate' => '10/2031'
                ),
                'payment[type]' => 'VISA'
            ),
            'merchant' => array(
                'orderreference' => $this->order_reference
            ),
            'customer' => array(
                'name' => array(
                        'first' => 'Foo',
                        'last' => 'Bar'
                    ),
                'email' => 'foo@bar.baz'
            )
        ), '/requestblock/request');

        $this->assertXmlStringEqualsXmlString($this->loadXML('request_card_auth/test_populate_request', ['orderreference' => $this->order_reference]), $auth->getXML());

        $response = $auth->request();

        $this->assertInstanceOf('Gajus\Strading\Response', $response);

        $xml = $this->normaliseXML($response->getXML()->asXML());

        $expected_xml_response = $this->normaliseXML($this->loadXML('request_card_auth/test_make_request'));

        $this->assertXmlStringEqualsXmlString($expected_xml_response, $xml);
    }
}