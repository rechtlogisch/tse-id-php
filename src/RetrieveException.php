<?php

declare(strict_types=1);

namespace Rechtlogisch\TseId;

use RuntimeException;
use Throwable;

class RetrieveException extends RuntimeException
{
    private string $url;

    private ?string $html = null;

    public function __construct(string $message = '', int $code = 0, ?Throwable $previous = null)
    {
        parent::__construct($message, $code, $previous);
    }

    public function addContext(string $url, ?string $html = null): self
    {
        $this->html = $html;
        $this->url = $url;

        return $this;
    }

    public function getUrl(): string
    {
        return $this->url;
    }

    public function getHtml(): ?string
    {
        return $this->html;
    }
}
