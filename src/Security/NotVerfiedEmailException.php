<?php
/**
 * Created by PhpStorm.
 * User: odolinski
 * Date: 13/01/2021
 * Time: 13:26
 */

namespace App\Security;


use Symfony\Component\Security\Core\Exception\CustomUserMessageAuthenticationException;

class NotVerfiedEmailException extends CustomUserMessageAuthenticationException
{


    public function __construct(
        $message  = 'This account does not appear to have a verified email',
        array $messageData,
        $code,
        \Throwable $previous)
    {
        parent::__construct($message, $messageData, $code, $previous);
    }

}