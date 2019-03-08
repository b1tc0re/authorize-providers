<?php namespace DeftCMS\Components\b1tc0re\Authorize\Providers\Model;


defined('BASEPATH') OR exit('No direct script access allowed');

/**
 * Данные о профиле пользователя
 *
 * @package     DeftCMS
 * @author	    b1tc0re
 * @copyright   2018-2019 DeftCMS (https://deftcms.org/)
 * @since	    Version 0.0.2
 */
class UserProfile
{
    /**
     * Фамилия
     * @var string
     */
    public $firstName;

    /**
     * Имя
     * @var string
     */
    public $lastName;

    /**
     * Логин
     * @var string
     */
    public $login;

    /**
     * Электроный адресс
     * @var string
     */
    public $email;

    /**
     * Дата рождения
     * @var null|\DateTime
     */
    public $birthday;

    /**
     * Пол male,female,unknown
     * @var string
     */
    public $genre;

    /**
     * Уникальный идентификатор
     * @var string
     */
    public $uniqueId;

    /**
     * Картинка пользователя
     * @var string
     */
    public $image;

    /**
     * Токен доступа
     * @var string
     */
    public $accessToken;

    /**
     * Create model from array
     * @param array $user
     * @return UserProfile
     */
    public static function createFromArray(array $user)
    {
        $instance = new static();

        foreach ($user as $property => $value)
        {
            if( property_exists($instance, $property) )
            {
                $instance->{$property} = $value;
            }
        }

        return $instance;
    }
}