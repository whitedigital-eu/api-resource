<?php declare(strict_types = 1);

namespace WhiteDigital\ApiResource\Tests;

use Symfony\Bundle\FrameworkBundle\Kernel\MicroKernelTrait;
use Symfony\Component\HttpKernel\Kernel;
use function dump;
use function getcwd;

class TestKernel extends Kernel
{
    use MicroKernelTrait;

    public function getProjectDir(): string
    {
        return getcwd();
    }

    public function getCacheDir(): string
    {
        return sys_get_temp_dir() . '/' .hash(algo: 'xxh128', data: getcwd()) . '/var/cache';
    }
}
