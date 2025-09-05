<?php namespace App\Controllers;

use App\Models\RestaurantModel;
use CodeIgniter\Controller;
use Config\View;
use  \CodeIgniter\HTTP\RedirectResponse;

class Auth extends BaseController
{
    protected string|array|false $base_url;

    /**
     * Class Construct Function
     */
    public function __construct()
    {
        $this->base_url = getenv('app.baseURL');
    }

    /**
     * Show login form
     * @return string Login Page
     */
    public function login(): string
    {
        return view('login');
    }

    /**
     * Handle login request
     * @return RedirectResponse
     */
    public function processLogin(): RedirectResponse
    {
        $restaurantModel = new RestaurantModel();
        $email = $this->request->getPost('email');
        $password = $this->request->getPost('password');

        // search user
        $user = $restaurantModel->where('Email', $email)->first();

        if ($user && password_verify($password, $user['Password'])) {
            // set session data
            session()->set([
                'isLoggedIn' => true,
                'restaurantId' => $user['RestaurantID'],
                'email' => $user['Email'],
                'isAdmin' => $user['isAdmin']
            ]);

            // redirect based on isAdmin
            if ($user['isAdmin']) {
                return redirect()->to('/admin');
            } else {
                return redirect()->to('/restaurant/' . $user['RestaurantID']);
            }
        } else {
            return redirect()->back()->with('error', 'Invalid email or password');
        }
    }

    /**
     * Logs the user out by clearing session data.
     * @return RedirectResponse Redirect to index.
     */
    public function logout(): RedirectResponse
    {
        // Get session service
        $session = session();
        // Remove session data
        $session->remove(['isLoggedIn', 'restaurantId', 'email', 'isAdmin']);
        return redirect()->to('/');
    }
}
