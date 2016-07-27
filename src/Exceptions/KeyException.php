<?php

namespace aldumas\Collections\Exceptions;


class KeyException extends \RuntimeException {
    public function __construct($key, $code=0, $previous=null) {
        parent::__construct("key: $key", $code, $previous);
    }
}
