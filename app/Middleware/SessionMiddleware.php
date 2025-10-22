<?php

namespace App\Middleware;

use PDO;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;
use Psr\Http\Server\MiddlewareInterface;
use Psr\Http\Server\RequestHandlerInterface as RequestHandler;

class SessionMiddleware implements MiddlewareInterface
{
    private $pdo;

    public function __construct(PDO $pdo)
    {
        $this->pdo = $pdo;
    }

    public function process(Request $request, RequestHandler $handler): Response
    {
        $cookies = $request->getCookieParams();
        $token = $cookies['focus_session'] ?? null;

        if ($token) {
            $stmt = $this->pdo->prepare(
                'SELECT user_id FROM sessions WHERE token = :token AND expires_at > CURRENT_TIMESTAMP'
            );
            $stmt->execute(['token' => $token]);
            $session = $stmt->fetch(PDO::FETCH_ASSOC);

            if ($session) {
                $request = $request->withAttribute('userId', $session['user_id']);
            }
        }

        return $handler->handle($request);
    }
}
