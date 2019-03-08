<?php namespace DeftCMS\Components\b1tc0re\Authorize\Providers;

defined('BASEPATH') OR exit('No direct script access allowed');


/**
 * Выбор обработчика авторизации
 *
 *
 * @package     DeftCMS
 * @author	    b1tc0re
 * @copyright   2018 DeftCMS (https://deftcms.org/)
 * @since	    Version 0.0.1
 */
class ServiceFactory
{
    /**
     * Действительные обработчики
     * @var array
     */
    protected static $validHandlers = [ ];

    /**
     * Создать желаемый обработчик данных на основе $handler
     *
     * @param string $handler
     * @param array $options
     * @return IProviderService
     */
    public static function getHandler(string $handler = null, $options = [])
    {
        self::invoke();

        if ( ! isset(self::$validHandlers) || ! is_array(self::$validHandlers))
        {
            throw Exceptions\ExceptionFactory::forInvalidHandlers();
        }
        $handler = strtolower($handler);

        if ( !array_key_exists($handler, self::$validHandlers) )
        {
            throw Exceptions\ExceptionFactory::forHandlerNotFound($handler);
        }

        $adapter = new self::$validHandlers[$handler]($options);

        return $adapter;
    }

    /**
     * Заполнить self::$validHandlers
     */
    public static function invoke()
    {
        self::$validHandlers[Services\YandexOAuthService::getHandlerName()] = Services\YandexOAuthService::class;
        self::$validHandlers[Services\VkOAuthService::getHandlerName()] = Services\VkOAuthService::class;
        self::$validHandlers[Services\FbOAuthService::getHandlerName()] = Services\FbOAuthService::class;
        self::$validHandlers[Services\MailOAuthService::getHandlerName()] = Services\MailOAuthService::class;
        self::$validHandlers[Services\GoogleOAuthService::getHandlerName()] = Services\GoogleOAuthService::class;
    }

    /**
     * Вернуть все обработчики
     * @return array
     */
    public static function getHandlers()
    {
        self::invoke();
        return self::$validHandlers;
    }
}