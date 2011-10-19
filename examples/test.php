<?php
require_once '../DomaintoolsAPI.class.php';
// services => whois, whois/history, hosting-history, reverse-ip, name-server-domains, reverse-whois, domain-suggestions, domain-search, mark-alert, registrant-alert

$request = new DomaintoolsAPI();

$response = $request->from('domain-profile')
                    ->domain('domaintools.com')
                    ->withType('html')
                    ->execute();

die($response);
//$response->getRequest()->from('domain')

//echo $request->execute();

//$response = new DomaintoolsAPIResponse($request);
//echo $response->toJson();
