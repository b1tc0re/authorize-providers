<?php namespace DeftCMS\Components\b1tc0re\Authorize\Providers;

use DeftCMS\Components\b1tc0re\Request\RequestClient;

defined('BASEPATH') OR exit('No direct script access allowed');

/**
 * DeftCMS      Request client for providers
 *
 * @package	    DeftCMS
 * @author	    b1tc0re
 * @copyright   (c) 2017, DeftCMS (http://deftcms.org)
 * @since	    Version 0.0.1
 */
class ProviderClient extends RequestClient
{
    /**
     * Build request client to API provider
     *
     * @param string $resource resource api
     * @param array $params request params
     * @param string $method request method
     * @return array
     * @throws \GuzzleHttp\Exception\GuzzleException
     */
    public function request($resource, $params = [], $method = 'GET')
    {
        if( strtolower($method) === 'get' )
        {
            $resource .= '?' . $this->buildQueryString($params);
        }

        return $this->getServiceResponse($resource, $method, $params);
    }

    /**
     * Returns URL-encoded query string
     *
     * @note: similar to http_build_query(),
     * but transform key=>value where key == value to "?key" param.
     *
     * @param array        $queryData
     * @param string       $prefix
     * @param string       $argSeparator
     * @param int          $encType
     *
     * @return string $queryString
     */
    public function buildQueryString($queryData, $prefix = '', $argSeparator = '&', $encType = PHP_QUERY_RFC3986)
    {
        return parent::buildQueryString($queryData, $prefix, $argSeparator, $encType);
    }
}