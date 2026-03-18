<?php

namespace Haigha\Exception;

/**
 * @author Igor Mukhin <igor.mukhin@gmail.com>
 */
class FileNotFoundException extends RuntimeException
{
    public function __construct(string $path)
    {
        parent::__construct(sprintf(
        	"File '%s' doesn't exists.",
        	$path
        ));
    }
}
