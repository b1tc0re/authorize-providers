<?php namespace DeftCMS\Components\b1tc0re\Authorize\Providers;

use DeftCMS\Engine;

defined('BASEPATH') OR exit('No direct script access allowed');

/**
 * Base service for authorize provider
 *
 * @package     DeftCMS
 * @author	    b1tc0re
 * @copyright   2018-2019 DeftCMS (https://deftcms.org/)
 * @since	    Version 0.0.2
 */
abstract class BaseService
{
    /**
     * Client id
     * @var null|string
     */
    protected $clientId          = NULL;

    /**
     * Client secret
     * @var null|string
     */
    protected $clientSecret      = NULL;

    /**
     * Forwarding address for receiving a response from the service
     * @var string
     */
    protected $oauth_url_redirect = NULL;

    /**
     * Request client
     * @var null|ProviderClient
     */
    protected $client = NULL;

    /**
     * Идентификатор хранилишя
     * @var string
     */
    private $storage_id = 'authorize:storage';

    /**
     * @var array
     */
    protected $genderMap = [
        'male'      => 'male',
        'female'    => 'female'
    ];

    /**
     * YandexOAuthService constructor.
     * @param array $options
     */
    public function __construct(array $options)
    {
        if( !$options['client_secret'] || !$options['client_id'] )
        {
            throw new \InvalidArgumentException('Parameters client_secret and client_id is required');
        }

        if( $options['client_secret'] )
        {
            $this->clientSecret = $options['client_secret'];
        }

        if( $options['client_id'] )
        {
            $this->clientId = $options['client_id'];
        }
    }

    /**
     * Return client request
     * @return ProviderClient
     */
    protected function getClient()
    {
        $this->client || ($this->client = new ProviderClient());

        return $this->client;
    }

    /**
     * Generate and save state
     * @param string $name
     * @return string
     */
    public function getState($name)
    {
        $storage = $this->getStorage();

        if( !array_key_exists($name, $storage) )
        {
            $storage[$name] = md5(uniqid() . microtime() . rand() . Engine::$DT->config->item('encryption_key'));
            Engine::$DT->session->set_userdata($this->storage_id, $storage);
        }

        return $storage[$name];
    }

    /**
     * Return authorize storage
     * @return array
     */
    protected function getStorage()
    {
        return Engine::$DT->session->userdata($this->storage_id) ?? [];
    }

    /**
     * Build redirect link
     *
     * @param string $handler
     * @param array $extra
     *
     * @return string
     */
    protected function buildRedirectLink($handler, $extra = [])
    {
        $params['response_type']    = 'code';
        $params['client_id']        = $this->clientId;
        $params['state']            = $this->getState($handler);
        $params['redirect_uri']     = $this->getRedirect($handler);

        $params = array_merge($params, $extra);

        return $this->oauth_url_redirect. '?' .$this->getClient()->buildQueryString($params);
    }

    /**
     * Return redirect uri
     * @param string $handler
     * @return string
     */
    protected function getRedirect($handler)
    {
        return base_url(sprintf('/login/social/%s/code/', $handler ));
    }

    /**
     * @param string|null $time
     * @param string $format
     * @return \DateTime|null
     */
    protected function getBirthDay($time, $format)
    {
        if( $time === null ) {
            return null;
        }

        return \DateTime::createFromFormat($format, $time) ?? null;
    }

    /**
     * Explode email and get login
     * @param string $email
     * @return string
     */
    protected function getLoginFromEmail($email)
    {
        if( $email === null )
        {
            return null;
        }

        return explode('@', $email)[0];
    }

    /**
     * Return gender
     * @param string $value
     * @return string
     */
    protected function getGender($value)
    {
        if( $value === null ) {
            return 'unknown';
        }

        return array_key_exists($value, $this->genderMap) ? $this->genderMap[$value]  : 'unknown';

    }
}