<?php

namespace App\Http\Middleware;

use App\Services\AuditTrailService;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class AuditTrailMiddleware
{
    public function __construct(
        private readonly AuditTrailService $auditTrailService,
    ) {
    }

    public function handle(Request $request, Closure $next): Response
    {
        /** @var Response $response */
        $response = $next($request);

        $this->auditTrailService->logRequest($request, $response);

        return $response;
    }
}
