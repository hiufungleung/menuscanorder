<?php namespace App\Filters;

use CodeIgniter\HTTP\RequestInterface;
use CodeIgniter\HTTP\ResponseInterface;
use CodeIgniter\Filters\FilterInterface;
use CodeIgniter\Config\Services;

class AdminFilter implements FilterInterface
{
    public function before(RequestInterface $request, $arguments = null)
    {
        $session = Services::session();
        if (is_null($arguments)) {
            $arguments = [];
        }

        // Check if user is logged in
        if (!$session->get('isLoggedIn')) {
            // 重定向到登錄頁面
            return redirect()->to('/login');
        }
        // Check if the user is not an admin
        if (!$session->get('isAdmin')) {
            // Prepare a response object to return a message
            $response = Services::response();
            $response->setStatusCode(403); // Appropriate status code for forbidden access
            $response->setBody('Access Denied');
            return $response; // Return the response object with the message
        }
    }

    public function after(RequestInterface $request, ResponseInterface $response, $arguments = null)
    {
        // No action needed after the controller method is executed
    }
}