<?php

declare(strict_types=1);

namespace Symfony\Component\HttpFoundation;

if (!class_exists(Cookie::class, false)) {
    class Cookie
    {
        public function __construct(
            public string $name,
            public ?string $value = null,
            public int|string $expire = 0,
            public ?string $path = '/',
            public ?string $domain = null,
            public ?bool $secure = false,
            public bool $httpOnly = true,
            public bool $raw = false,
            public ?string $sameSite = null
        ) {
        }
    }
}
