<?php

// app/Http/Middleware/CheckUserStatus.php
class CheckUserStatus
{
    public function handle($request, Closure $next)
    {
        if (auth()->check() && !auth()->user()->isApproved()) {
            auth()->logout();
            return redirect()->route('login')->with('error', 'Account in attesa di approvazione');
        }

        return $next($request);
    }
}

