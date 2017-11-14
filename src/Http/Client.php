<?php

namespace ZfThreeSocialAuth\Http;

use Zend\Http\Client as ZendClient;
use Zend\Http\Request;
use ZfThreeSocialAuth\Uri\Http as SocialHttp;

Class Client extends ZendClient
{

    public function doCleanSend(Request $request = null)
    {
        if ($request !== null) {
            $this->setRequest($request);
        }

        $this->redirectCounter = 0;

        $adapter = $this->getAdapter();

        // Send the first request. If redirected, continue.
        do {
            // uri
            $originalUri = $this->getUri();

            // query
            $query = $this->getRequest()->getQuery();
            if (!empty($query)) {
                $queryArray = $query->toArray();
                $uri = new SocialHttp(null);
                $uri->setScheme($originalUri->getScheme());
                $uri->setUserInfo($originalUri->getUserInfo());
                $uri->setHost($originalUri->getHost());
                $uri->setPort($originalUri->getPort());
                $uri->setPath($originalUri->getPath());
                $uri->setQuery($queryArray);
                $uri->setFragment($originalUri->getFragment());
            }
            // If we have no ports, set the defaults
            if (!$uri->getPort()) {
                $uri->setPort($uri->getScheme() == 'https' ? 443 : 80);
            }

            // method
            $method = $this->getRequest()->getMethod();

            // this is so the correct Encoding Type is set
            $this->setMethod($method);

            // body
            $body = $this->prepareBody();

            // headers
            $headers = $this->prepareHeaders($body, $uri);

            $secure = $uri->getScheme() == 'https';
            // cookies
            $cookie = $this->prepareCookies($uri->getHost(), $uri->getPath(), $secure);
            if ($cookie->getFieldValue()) {
                $headers['Cookie'] = $cookie->getFieldValue();
            }

            // check that adapter supports streaming before using it
            if (is_resource($body) && !($adapter instanceof Client\Adapter\StreamInterface)) {
                throw new Client\Exception\RuntimeException('Adapter does not support streaming');
            }

            // calling protected method to allow extending classes
            // to wrap the interaction with the adapter

            $response = parent::doRequest($uri, $method, $secure, $headers, $body);

            if (!$response) {
                throw new Exception\RuntimeException('Unable to read response, or response is empty');
            }

            if ($this->config['storeresponse']) {
                $this->lastRawResponse = $response;
            } else {
                $this->lastRawResponse = null;
            }
            $response = $this->getResponse()->fromString($response);


            // Get the cookies from response (if any)
            $setCookies = $response->getCookie();
            if (!empty($setCookies)) {
                $this->addCookie($setCookies);
            }

            // If we got redirected, look for the Location header
            if ($response->isRedirect() && ($response->getHeaders()->has('Location'))) {
                // Avoid problems with buggy servers that add whitespace at the
                // end of some headers
                $location = trim($response->getHeaders()->get('Location')->getFieldValue());

                // Check whether we send the exact same request again, or drop the parameters
                // and send a GET request
                if ($response->getStatusCode() == 303 ||
                        ((!$this->config['strictredirects']) && ($response->getStatusCode() == 302 ||
                        $response->getStatusCode() == 301))) {
                    $this->resetParameters(false, false);
                    $this->setMethod(Request::METHOD_GET);
                }

                // If we got a well formed absolute URI
                if (($scheme = substr($location, 0, 6)) &&
                        ($scheme == 'http:/' || $scheme == 'https:')) {
                    // setURI() clears parameters if host changed, see #4215
                    $this->setUri($location);
                } else {
                    // Split into path and query and set the query
                    if (strpos($location, '?') !== false) {
                        list($location, $query) = explode('?', $location, 2);
                    } else {
                        $query = '';
                    }
                    $this->getUri()->setQuery($query);

                    // Else, if we got just an absolute path, set it
                    if (strpos($location, '/') === 0) {
                        $this->getUri()->setPath($location);
                        // Else, assume we have a relative path
                    } else {
                        // Get the current path directory, removing any trailing slashes
                        $path = $this->getUri()->getPath();
                        $trimmedPath = rtrim(substr($path, 0, strrpos($path, '/')), "/");
                        $this->getUri()->setPath($trimmedPath . '/' . $location);
                    }
                }
                ++$this->redirectCounter;
            } else {
                // If we didn't get any location, stop redirecting
                break;
            }
        } while ($this->redirectCounter <= $this->config['maxredirects']);

        $this->response = $response;
        return $response;
    }

    public function setRawParams($raw)
    {
        $this->getRequest()->getQuery()->fromArray($raw);
    }

}
