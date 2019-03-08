<?php namespace DeftCMS\Components\b1tc0re\Authorize\Providers\Exceptions;

use DeftCMS\Engine;

defined('BASEPATH') OR exit('No direct script access allowed');

/**
 * Исключаения
 *
 * @package     DeftCMS
 * @author	    b1tc0re
 * @copyright   2018 DeftCMS (https://deftcms.org/)
 * @since	    Version 0.0.1
 */
class ExceptionFactory extends \RuntimeException
{
    /**
     * Исключения вызывается когда обработчик недействительный
     * @return ExceptionFactory
     */
    public static function forInvalidHandlers()
    {
        $message = lang('Factory invalid handlers');
        Engine::$Log->error($message);

        return new static($message);
    }

    /**
     * Исключения вызывается когда обработчик не найден
     * @param string $handler
     * @return ExceptionFactory
     */
    public static function forHandlerNotFound($handler)
    {
        $message = sprintf(lang('Factory handler %s not found'), $handler);
        Engine::$Log->error($message);

        return new static($message);
    }
}