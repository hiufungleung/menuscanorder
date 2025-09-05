<?php namespace App\Filters;

use CodeIgniter\HTTP\RequestInterface;
use CodeIgniter\HTTP\ResponseInterface;
use CodeIgniter\Filters\FilterInterface;
use CodeIgniter\Config\Services;

class RestaurantFilter implements FilterInterface
{
    public function before(RequestInterface $request, $arguments = null)
    {
        $session = Services::session();

        if (!$session->get('isLoggedIn')) {
            return redirect()->to('/login')->with('error', 'Please log in to continue');
        }

        if (!$session->get('isAdmin')) {
            // Check if this user is trying to request others' page.
            $uri = $request->getUri();
            $segments = $uri->getSegments();

            if (isset($segments[1]) && is_numeric($segments[1])) {
                $restaurantID = (int) $segments[1];

                if ($session->get('restaurantId') != $restaurantID) {
                    return redirect()->to('/restaurant/' . $session->get('restaurantId'))->with('error', 'Access Denied');
                }
            }
        }
    }

    public function after(RequestInterface $request, ResponseInterface $response, $arguments = null)
    {
        // No action needed after the controller method is executed
    }
}
