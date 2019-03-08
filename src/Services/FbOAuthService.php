<?php namespace DeftCMS\Components\b1tc0re\Authorize\Providers\Services;

use DeftCMS\Components\b1tc0re\Authorize\Providers\BaseService;
use DeftCMS\Components\b1tc0re\Authorize\Providers\Exceptions\ExceptionAccessDenied;
use DeftCMS\Components\b1tc0re\Authorize\Providers\Exceptions\ExceptionInvalidState;
use DeftCMS\Components\b1tc0re\Authorize\Providers\Exceptions\ExceptionInvalidToken;
use DeftCMS\Components\b1tc0re\Authorize\Providers\IProviderService;
use DeftCMS\Components\b1tc0re\Authorize\Providers\Model\UserProfile;
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
class FbOAuthService extends BaseService implements IProviderService
{
    /**
     *
     * @var string
     */
    protected $oauth_url_redirect = 'https://www.facebook.com/dialog/oauth';

    /**
     * @var string
     */
    protected $oauth_domain_token = 'graph.facebook.com';

    /**
     * @var string
     */
    protected $oauth_resource_token = 'oauth/access_token';

    /**
     * {@inheritdoc}
     */
    public function getAuthorizationRedirectLink() : string
    {
        return $this->buildRedirectLink(self::getHandlerName(), [
            'scope' => implode(',', [ 'email', 'user_birthday', 'user_gender' ])
        ]);
    }

    /**
     * {@inheritdoc}
     */
    public function getAuthorizationData() : UserProfile
    {
        if( Engine::$DT->input->get('state') !== $this->getState(self::getHandlerName()) )
        {
            throw new ExceptionInvalidState('State parameters is required');
        }

        if( Engine::$DT->input->get('error') )
        {
            throw new ExceptionAccessDenied(Engine::$DT->input->get('error_description'));
        }

        $this->getClient()->setServiceDomain($this->oauth_domain_token);

        try
        {
            $token = $this->getClient()->request($this->oauth_resource_token, [
                'code'          => Engine::$DT->input->get('code'),
                'client_id'     => $this->clientId,
                'client_secret' => $this->clientSecret,
                'redirect_uri'  => $this->getRedirect(self::getHandlerName())
            ]);
        }
        catch (\GuzzleHttp\Exception\GuzzleException $ex)
        {
            throw new ExceptionInvalidToken($ex->getMessage());
        }

        return $this->getProfile($token['access_token']);
    }

    /**
     * {@inheritdoc}
     */
    public function getProfile(string $token, array $extra = []) : UserProfile
    {
        try
        {
            $params = [
                'appsecret_proof'   => hash_hmac('sha256', $token, $this->clientSecret),
                'access_token'      => $token,
                'fields'            => implode(',', [ 'id', 'email', 'name', 'first_name', 'last_name', 'birthday', 'gender' ])
            ];

            $params = array_merge($params, $extra);

            $response = $this->getClient()->request('me', $params);
        }
        catch (\GuzzleHttp\Exception\GuzzleException $ex)
        {
            throw new ExceptionInvalidToken($ex->getMessage());
        }

        return UserProfile::createFromArray([
            'firstName'     => $response['first_name'] ?? null,
            'lastName'      => $response['last_name'] ?? null,
            'login'         => $this->getLoginFromEmail($response['email'] ?? null),
            'email'         => $response['email'] ?? null,
            'birthday'      => $this->getBirthDay($response['birthday'] ?? null, 'm/d/Y'),
            'genre'         => $this->getGender($response['gender'] ?? null),
            'uniqueId'      => md5(self::getHandlerName() . 'id:' . $response['id']),
            'image'         => null,
            'accessToken'   => $token
        ]);
    }

    /**
     * Названия провайдера
     * @return string
     */
    public function getName()
    {
        return 'facebook';
    }

    /**
     * Названия обработчика
     * @return string
     */
    public static function getHandlerName()
    {
        return 'fb';
    }
}