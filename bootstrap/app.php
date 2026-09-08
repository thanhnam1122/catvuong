<?php

use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;

class VercelApplication extends Application
{
    public function storagePath($path = '')
    {
        $storagePath = (isset($_ENV['VERCEL']) || isset($_SERVER['VERCEL']) || getenv('VERCEL') || !is_writable(dirname(__DIR__) . DIRECTORY_SEPARATOR . 'storage'))
            ? '/tmp/storage'
            : dirname(__DIR__) . DIRECTORY_SEPARATOR . 'storage';

        return $path != '' ? $storagePath . DIRECTORY_SEPARATOR . ltrim($path, DIRECTORY_SEPARATOR) : $storagePath;
    }

    public function getCachedServicesPath()
    {
        return (isset($_ENV['VERCEL']) || isset($_SERVER['VERCEL']) || getenv('VERCEL'))
            ? '/tmp/bootstrap/cache/services.php'
            : parent::getCachedServicesPath();
    }

    public function getCachedPackagesPath()
    {
        return (isset($_ENV['VERCEL']) || isset($_SERVER['VERCEL']) || getenv('VERCEL'))
            ? '/tmp/bootstrap/cache/packages.php'
            : parent::getCachedPackagesPath();
    }

    public function getCachedConfigPath()
    {
        return (isset($_ENV['VERCEL']) || isset($_SERVER['VERCEL']) || getenv('VERCEL'))
            ? '/tmp/bootstrap/cache/config.php'
            : parent::getCachedConfigPath();
    }

    public function getCachedRoutesPath()
    {
        return (isset($_ENV['VERCEL']) || isset($_SERVER['VERCEL']) || getenv('VERCEL'))
            ? '/tmp/bootstrap/cache/routes-v7.php'
            : parent::getCachedRoutesPath();
    }

    public function getCachedEventsPath()
    {
        return (isset($_ENV['VERCEL']) || isset($_SERVER['VERCEL']) || getenv('VERCEL'))
            ? '/tmp/bootstrap/cache/events.php'
            : parent::getCachedEventsPath();
    }
}

return VercelApplication::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware): void {
        //
    })
    ->withExceptions(function (Exceptions $exceptions): void {
        //
    })
    ->create();

