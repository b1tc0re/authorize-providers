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
class MailOAuthService extends BaseService implements IProviderService
{
    /**
     *
     * @var string
     */
    protected $oauth_url_redirect = 'https://connect.mail.ru/oauth/authorize';

    /**
     * @var string
     */
    protected $oauth_domain_token = 'connect.mail.ru';

    /**
     * @var string
     */
    protected $oauth_resource_token = 'oauth/token';

    /**
     * @var array
     */
    protected $genderMap = [
        0   => 'male',
        1   => 'female'
    ];

    /**
     * {@inheritdoc}
     */
    public function getAuthorizationRedirectLink() : string
    {
        return $this->buildRedirectLink( self::getHandlerName() );
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
                'redirect_uri'  => $this->getRedirect(self::getHandlerName()),
                'grant_type'    => 'authorization_code'
            ], 'POST');
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
        $this->getClient()->setServiceDomain('www.appsmail.ru');

        try
        {
            $params = array(
                'app_id'       => $this->clientId,
                'method'       => 'users.getInfo',
                'secure'       => '1',
                'session_key'  => $token
            );
            $params['sig'] = md5(http_build_query($params, '', '') . $this->clientSecret);

            $params = array_merge($params, $extra);

            $response = $this->getClient()->request('platform/api', $params);
        }
        catch (\GuzzleHttp\Exception\GuzzleException $ex)
        {
            throw new ExceptionInvalidToken($ex->getMessage());
        }

        $response = array_shift($response);

        return UserProfile::createFromArray([
            'firstName'     => $response['first_name'] ?? null,
            'lastName'      => $response['last_name'] ?? null,
            'login'         => $this->getLoginFromEmail($response['email'] ?? null),
            'email'         => $response['email'] ?? null,
            'birthday'      => $this->getBirthDay($response['birthday'] ?? null, 'd.m.Y'),
            'genre'         => $this->getGender($response['sex'] ?? null),
            'uniqueId'      => md5(self::getHandlerName() . 'id:' . $response['uid']),
            'image'         => $response['pic_small'] ?? null,
            'accessToken'   => $token
        ]);
    }

    /**
     * Названия провайдера
     * @return string
     */
    public function getName()
    {
        return 'mail';
    }

    /**
     * Названия обработчика
     * @return string
     */
    public static function getHandlerName()
    {
        return 'mr';
    }
}