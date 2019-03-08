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
class VkOAuthService extends BaseService implements IProviderService
{
    /**
     *
     * @var string
     */
    protected $oauth_url_redirect = 'http://oauth.vk.com/authorize';

    /**
     * @var string
     */
    protected $oauth_domain_token = 'oauth.vk.com';

    /**
     * @var string
     */
    protected $oauth_resource_token = 'access_token';

    /**
     * @var string
     */
    protected $oauth_domain_data = 'api.vk.com';

    /**
     * @var array
     */
    protected $genderMap = [
        1 => 'female',
        2 => 'male'
    ];

    /**
     * {@inheritdoc}
     */
    public function getAuthorizationRedirectLink() : string
    {
        return $this->buildRedirectLink(self::getHandlerName(), ['scope' => 'email']);
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

        return $this->getProfile($token['access_token'], [
            'uids'  => $token['user_id'],
            'email' => $token['email']
        ]);
    }

    /**
     * {@inheritdoc}
     */
    public function getProfile(string $token, array $extra = []) : UserProfile
    {
        $this->getClient()->setServiceDomain($this->oauth_domain_data);

        try
        {
            $params = [
                'fields'        => 'uid,first_name,last_name,screen_name,sex,bdate,photo_big',
                'access_token'  => $token,
                'v'             => '5.92'
            ];

            $params = array_merge($params, $extra);

            $data = $this->getClient()->request('method/users.get', $params);
        }
        catch (\GuzzleHttp\Exception\GuzzleException $ex)
        {
            throw new ExceptionInvalidToken($ex->getMessage());
        }

        $response = array_shift($data['response']);

        return UserProfile::createFromArray([
            'firstName'     => $response['first_name'] ?? null,
            'lastName'      => $response['last_name'] ?? null,
            'login'         => $response['screen_name'] ?? null,
            'email'         => $extra['email'] ?? null,
            'birthday'      => $this->getBirthDay($response['bdate'] ?? null , 'd.n.Y'),
            'genre'         => $this->getGender($response['sex'] ?? null),
            'uniqueId'      => md5(self::getHandlerName() . 'id:' . $response['id']),
            'image'         => $response['photo_big'] ?? null,
            'accessToken'   => $token
        ]);
    }

    /**
     * Названия провайдера
     * @return string
     */
    public function getName()
    {
        return 'vkontakte';
    }

    /**
     * Названия обработчика
     * @return string
     */
    public static function getHandlerName()
    {
        return 'vk';
    }
}