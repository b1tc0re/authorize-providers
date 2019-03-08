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
class YandexOAuthService extends BaseService implements IProviderService
{
    /**
     * @var string
     */
    protected $oauth_domain_token = 'oauth.yandex.ru';

    /**
     * @var string
     */
    protected $oauth_domain_data = 'login.yandex.ru';

    /**
     *
     * @var string
     */
    protected $oauth_url_redirect = 'https://oauth.yandex.ru/authorize';

    /**
     * @var string
     */
    protected $oauth_resource_token = 'token';

    /**
     * {@inheritdoc}
     */
    public function getAuthorizationRedirectLink() : string
    {
        return $this->buildRedirectLink(self::getHandlerName());
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

        if( Engine::$DT->input->get('error') === 'access_denied' )
        {
            throw new ExceptionAccessDenied(Engine::$DT->input->get('error_description'));
        }

        $this->getClient()->setServiceDomain($this->oauth_domain_token);

        try
        {
            $token = $this->getClient()->request($this->oauth_resource_token, [
                'grant_type'    => 'authorization_code',
                'code'          => Engine::$DT->input->get('code'),
                'client_id'     => $this->clientId,
                'client_secret' => $this->clientSecret,
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
        $this->getClient()->setServiceDomain($this->oauth_domain_data);

        try
        {
            $data = $this->getClient()->request('info', [
                'oauth_token' => $token
            ]);
        }
        catch (\GuzzleHttp\Exception\GuzzleException $ex)
        {
            throw new ExceptionInvalidToken($ex->getMessage());
        }

        return UserProfile::createFromArray([
            'firstName'     => $data['first_name'] ?? null,
            'lastName'      => $data['last_name'] ?? null,
            'login'         => $data['login'] ?? null,
            'email'         => $data['default_email'] ?? null,

            'birthday'      => $this->getBirthDay($data['birthday'] ?? null, 'Y-m-d'),
            'genre'         => $this->getGender($data['sex'] ?? null),
            'uniqueId'      => md5(self::getHandlerName() . 'id:' . $data['id']),
            'image'         => $data['default_avatar_id'] ? sprintf('http://avatars.mds.yandex.net/get-yapic/%s/islands-200', $data['default_avatar_id']) : null,
            'accessToken'   => $token
        ]);
    }

    /**
     * Названия провайдера
     * @return string
     */
    public function getName()
    {
        return 'yandex';
    }

    /**
     * Названия обработчика
     * @return string
     */
    public static function getHandlerName()
    {
        return 'ya';
    }
}