<?php namespace DeftCMS\Components\b1tc0re\Authorize\Providers;

use DeftCMS\Components\b1tc0re\Authorize\Providers\Exceptions\ExceptionAccessDenied;
use DeftCMS\Components\b1tc0re\Authorize\Providers\Exceptions\ExceptionInvalidState;
use DeftCMS\Components\b1tc0re\Authorize\Providers\Exceptions\ExceptionInvalidToken;
use DeftCMS\Components\b1tc0re\Authorize\Providers\Model\UserProfile;

defined('BASEPATH') OR exit('No direct script access allowed');

/**
 * Interface IProviderService
 *
 * @package     DeftCMS\Components\b1tc0re\Authorize\Providers
 * @author	    b1tc0re
 * @copyright   2018-2019 DeftCMS (https://deftcms.org/)
 * @since	    Version 0.0.2
 */
interface IProviderService
{
    /**
     * Вернуть ссылку для редиректа на получения разрешения
     * @return string
     */
    public function getAuthorizationRedirectLink() : string;

    /**
     * Вернуть данные профиля пользователя
     *
     * @return UserProfile
     *
     * @throws ExceptionAccessDenied
     * @throws ExceptionInvalidState
     * @throws ExceptionInvalidToken
     */
    public function getAuthorizationData() : UserProfile;

    /**
     * Вернуть данные профиля пользователя через access_token
     *
     * @param string $token
     * @param array $extra
     *
     * @return UserProfile
     *
     * @throws ExceptionAccessDenied
     * @throws ExceptionInvalidState
     * @throws ExceptionInvalidToken
     */
    public function getProfile(string $token, array $extra = []) : UserProfile;
}