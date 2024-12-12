<?php

declare(strict_types=1);

namespace CodeRhapsodie\IbexaDataflowBundle\Exception;

class NoMatchFoundException extends \Exception
{
}
class_alias(NoMatchFoundException::class, 'CodeRhapsodie\EzDataflowBundle\Exception\NoMatchFoundException');
