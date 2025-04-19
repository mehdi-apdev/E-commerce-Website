<?php
// app/middlewares/Middleware.php

/**
 * Middleware base class
 * 
 * Base abstract class for all middlewares in the application.
 * Middlewares intercept requests before they reach controllers
 * to perform checks like authentication, authorization, etc.
 */
abstract class Middleware
{
    /**
     * Handle the incoming request
     * 
     * @param array $request The request data ($_REQUEST)
     * @param callable $next The next middleware or controller
     * @return mixed
     */
    abstract public function handle($request, $next);
}