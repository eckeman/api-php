<?php
require_once __DIR__.'/../DomaintoolsAPI.class.php';

class DomaintoolsAPITest extends PHPUnit_Framework_TestCase
{

  private function getTransport($url, $fixture_path, $status = 200) {
    $transport = $this->getMock('RestServiceInterface'); 

    $transport->expects($this->any())
                ->method('get')
                ->with($url)
                ->will($this->returnValue(file_get_contents($fixture_path)));
                
    $transport->expects($this->any())
                ->method('getStatus')
                ->will($this->returnValue($status));
    
    return $transport;  
  }
  
  
  /**
   * Checks the transport object is really called
   */
  public function testTransportCallsOnGet() {
  
    $configuration = new DomaintoolsAPIConfiguration(__DIR__.'/../api.ini');
    
    $request = new DomaintoolsAPI($configuration);

    $request->withType('xml')
            ->from('domain-profile')
            ->domain('domaintools.com');
  
    $transport = $this->getMock('RestServiceInterface'); 
    
    $transport->expects($this->once())
          ->method('get')
          ->with($request->debug())
          ->will($this->returnValue(file_get_contents(__DIR__.'/fixtures/domain-profile/domaintools.com/good.json')));
    
    $request->setTransport($transport);
    try {
      $response  = $request->execute();
    } catch(Exception $e) {
      return;
    }
  }

  /**
   * Checks ServiceException raised if unknown serviceName
   */
  public function testServiceExceptionIfUnknownServiceName() {
    
    $configuration = new DomaintoolsAPIConfiguration(__DIR__.'/../api.ini');
    $request = new DomaintoolsAPI($configuration);
    try {
      $request->from('unknwonService');
    } catch (ServiceException $e) {
      $this->assertTrue($e->getMessage() == ServiceException::UNKNOWN_SERVICE_NAME);
    }
  }
  
  /**
   * Checks ServiceException raised if unknown returnType
   */
  public function testJsonCallIfUnknownReturnType() {
    
    $configuration = new DomaintoolsAPIConfiguration(__DIR__.'/../api.ini');
    $request       = new DomaintoolsAPI($configuration);
    
    $request->withType('unknownType')->domain('domaintools.com');
    
    $url           = $request->debug();
    $fixture_path  = __DIR__.'/fixtures/domain-profile/domaintools.com/good.json';
    $transport     = $this->getTransport($url, $fixture_path, 200);
    
    $request->setTransport($transport);    
    
    $json          = $request->execute();
    
    $this->assertTrue(is_array(json_decode($json,true)));
  }
  
  /**
   * Checks ServiceException raised if invalid options
   */
  public function testServiceExceptionIfInvalidOptions() {
    
    $configuration = new DomaintoolsAPIConfiguration(__DIR__.'/../api.ini');
    $request       = new DomaintoolsAPI($configuration);
    try {
      $request->where('invalidOptions');
    } catch (ServiceException $e) {
      $this->assertTrue($e->getMessage() == ServiceException::INVALID_OPTIONS);
    }
  }
  
  /**
   * Checks username and key are (really) added to options
   */
  public function testAddCredentialsForUnsecureAuthentication() {
    
    $config        = parse_ini_file(__DIR__.'/../api.ini');
    $configuration = new DomaintoolsAPIConfiguration($config);
    $configuration->set('secureAuth', false);

    $request       = new DomaintoolsAPI($configuration);    
    $request->addCredentialsOptions();

    $options       = $request->getOptions();
    
    $this->assertTrue(
      $config['username'] == $options['api_username'] &&
      $config['key']      == $options['api_key']
    );
  }
       
}