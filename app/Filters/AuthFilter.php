<?php

namespace App\Filters;

use CodeIgniter\Filters\FilterInterface;
use CodeIgniter\HTTP\RequestInterface;
use CodeIgniter\HTTP\ResponseInterface;
use Firebase\JWT\JWT;
use Firebase\JWT\Key;
use Exception;
use Config\Services;

class AuthFilter implements FilterInterface
{
    /**
     * Do whatever processing this filter needs to do.
     * By default it should not return anything during
     * normal execution. However, when an abnormal state
     * is found, it should return an instance of
     * CodeIgniter\HTTP\Response. If it does, script
     * execution will end and that Response will be
     * sent back to the client, allowing for error pages,
     * redirects, etc.
     *
     * @param RequestInterface $request
     * @param array|null       $arguments
     *
     * @return RequestInterface|ResponseInterface|string|void
     */
    public function before(RequestInterface $request, $arguments = null)
    {
        try {
            $authHeader = $request->getHeaderLine('Authorization');
            $arr = explode(' ', $authHeader);
            $token = $arr[1] ?? ''; // Typically, Authorization header is "Bearer <token>"

            if (!$token) {
                return Services::response()
                    ->setStatusCode(ResponseInterface::HTTP_UNAUTHORIZED, 'No token provided');
            }

            $key = getenv('JWT_KEY'); // Ensure you have JWT_KEY in your .env file
            JWT::decode($token, new Key($key, 'HS256')); // This will throw an exception if the token is invalid

            // If needed, you can extract the token payload and set it to the request object for later use
            // $payload = JWT::decode($token, new Key($key, 'HS256'));
            // $request->setVar('jwt_payload', $payload);

        } catch (Exception $e) {
            // Token is invalid
            return Services::response()
                ->setStatusCode(ResponseInterface::HTTP_UNAUTHORIZED, 'Invalid token');
        }
    }

    /**
     * Allows After filters to inspect and modify the response
     * object as needed. This method does not allow any way
     * to stop execution of other after filters, short of
     * throwing an Exception or Error.
     *
     * @param RequestInterface  $request
     * @param ResponseInterface $response
     * @param array|null        $arguments
     *
     * @return ResponseInterface|void
     */
    public function after(RequestInterface $request, ResponseInterface $response, $arguments = null)
    {
        //
    }
}
