<?php declare(strict_types = 1);

namespace WhiteDigital\ApiResource\ApiResource\Traits;

trait Groups
{
    public const ITEM = self::PREFIX . 'item';
    public const PATCH = self::PREFIX . 'patch';
    public const READ = self::PREFIX . 'read';
    public const WRITE = self::PREFIX . 'write';
}
