<?php
namespace Gajus\Strading;

/**
 * @link https://github.com/gajus/strading for the canonical source repository
 * @license https://github.com/gajus/strading/blob/master/LICENSE BSD 3-Clause
 */
class Service {
    private
        $interface_url,
        $site_reference,
        $username,
        $password;
    
    /**
     * @param string $site_reference The Merchant's Site Reference.
     * @param string $username
     * @param string $password
     * @param string $interface_url
     */
    public function __construct ($site_reference, $username, $password, $interface_url = 'https://webservices.securetrading.net:443/xml/') {
        $this->interface_url = $interface_url;
        $this->site_reference = $site_reference;
        $this->username = $username;
        $this->password = $password;
    }
    
    /**
     * @param string $name Request template name, e.g. "card/order".
     * @return Gajus\Strading\Request
     */
    public function request ($name) {
        $template = __DIR__ . '/template/' . $name . '.xml';
        
        if (!file_exists($template)) {
            throw new Exception\InvalidArgumentException('Request template does not exist.');
        }
        
        $xml = new \SimpleXMLElement(file_get_contents($template));

        $request = new Request($this->interface_url, $this->site_reference, $this->username, $this->password, $xml);    
        
        return $request;
    }
}