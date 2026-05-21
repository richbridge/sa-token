<?php

declare(strict_types=1);

namespace SaToken\Tests\Fixtures;

use Symfony\Component\HttpFoundation\Cookie;

class SymfonyHeaderBagStub
{
    /**
     * @var array<string, string>
     */
    public array $values = [];

    /**
     * @var array<int, Cookie>
     */
    public array $cookies = [];

    public function set(string $name, string $value): void
    {
        $this->values[$name] = $value;
    }

    public function setCookie(Cookie $cookie): void
    {
        $this->cookies[] = $cookie;
    }
}
