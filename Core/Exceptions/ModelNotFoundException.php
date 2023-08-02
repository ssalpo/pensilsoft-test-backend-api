<?php

namespace Core\Exceptions;

final class ModelNotFoundException extends \Exception
{
    protected $message = 'Model not found';
    protected $code = 404;
}
