<?php
namespace App\Controllers;

use App\Models\RestaurantModel;
use App\Models\DishCategoryModel;
use App\Models\TableModel;
use App\Models\OrderModel;
use CodeIgniter\Database\Exceptions\DatabaseException;
use CodeIgniter\Exceptions\PageNotFoundException;
use CodeIgniter\HTTP\RedirectResponse;
use CodeIgniter\HTTP\ResponseInterface;


class ProjectController extends BaseController
{
    private string|array|bool|int|null|object|float $session;

    public function __construct()
    {
        helper('url');
        $this->session = session();
    }

    /**
     * Show the index page.
     * @return string View of index page.
     */
    public function index(): string
    {
        return view('index');
    }

    /**
     * Show the sign-up page
     * @return string|RedirectResponse The signup view
     */
    public function signup(): string|RedirectResponse
    {
        $model = new RestaurantModel();

        if ($this->request->getMethod() === 'POST') {
            $data = $this->request->getPost();

            $password = $data['Password'];
            $data['Password'] = password_hash($password, PASSWORD_DEFAULT);
            $data['isAdmin'] = 0;

            try {
                $id = $model->insert($data);
                if ($id) {
                    $this->session->setFlashdata('success', 'Sign up successfully.');
                    session()->set([
                        'isLoggedIn' => true,
                        'restaurantId' => $id,
                        'email' => $data['Email'],
                        'isAdmin' => $data['isAdmin'],
                    ]);
                    return redirect()->to('/restaurant/' . $id);
                } else {
                    $this->session->setFlashdata('error', 'Failed to sign up. Please try again.');
                    return redirect()->to('/signup');
                }
            } catch (DatabaseException $e) {
                // prevent duplicate value inserted or updated
                $this->session->setFlashdata('error', $e->getMessage());
                return redirect()->to('signup');
            } catch (\ReflectionException $e) {
                $this->session->setFlashdata('error', $e->getMessage());
                return redirect()->to('signup');
            }
        }
        return view('signup');
    }



    /**
     * Show the admin panel page.
     * @return string View of admin panel page.
     */
    public function admin(): string
    {
        $model = new RestaurantModel();

        // Handle search bar
        $search = $this->request->getGet('search');

        if (!empty($search)) {
            $condition = [];

            foreach ($model->allowedFields as $field) {
                $condition[] = "$field LIKE '%$search%'";
            }

            $whereClause = implode(" OR ", $condition);

            $restaurants = $model->where($whereClause)->orderBy('Name', "ASC")->findAll();
        } else {
            $restaurants = $model->orderBy('Name', 'ASC')->findAll();
        }

        $data['restaurants'] = $restaurants;
        return view('admin', $data);
    }


    /**
     * Show the edit page with the given restaurantID.
     * If not id provided, add a new restaurant.
     * @param int | null $id RestaurantID
     * @return string|RedirectResponse Redirect to admin panel page if successful added or edited,
     *  otherwise show the addedit View page.
     */
    public function addedit(int $id = null): string|RedirectResponse
    {
        $model = new RestaurantModel();

        if ($this->request->getMethod() === 'POST') {
            $data = $this->request->getPost();

            // update if password provided
            if (!$data['Password']) {
                unset($data['Password']);
            } else {
                $data['Password'] = password_hash($data['Password'], PASSWORD_DEFAULT);
            }
            // Convert string to int
            $data['isAdmin'] = $data['isAdmin'] == '1' ? 1 : 0;

            try {
                if ($id === null) {
                    if ($model->insert($data)) {
                        $this->session->setFlashdata('success', 'Restaurant added successfully.');
                    } else {
                        $this->session->setFlashdata('error', 'Failed to add restaurant. Please try again.');
                    }
                } else {
                    if ($model->update($id, $data)) {
                        $this->session->setFlashdata('success', 'Restaurant updated successfully.');
                    } else {
                        $this->session->setFlashdata('error', 'Failed to update restaurant. Please try again.');
                    }
                }
            } catch (DatabaseException $e) {
                // prevent duplicate value inserted or updated
                $this->session->setFlashdata('error', $e->getMessage());
                return redirect()->to('/admin/addedit/'.$id);
            } catch (\ReflectionException $e) {
                $this->session->setFlashdata('error', $e->getMessage());
                return redirect()->to('/admin/addedit/'.$id);
            }
            return redirect()->to('/admin');
        }
        $restaurant = ($id === null) ? null : $model->find($id);

        // No password returned to View
        if ($restaurant !== null) {
            unset($restaurant['Password']);
        }
        $data['restaurant'] = $restaurant;

        return view('addedit', $data);
    }

    /**
     * Delete restaurant with the given id
     * @param int $id The id of restaurant
     * @return RedirectResponse
     */
    public function delete(int $id): RedirectResponse
    {
        $model = new RestaurantModel();

        if ($model->delete($id)) {
            $this->session->setFlashdata('success', 'Restaurant deleted successfully.');
        } else {
            $this->session->setFlashdata('error', 'Failed to delete restaurant. Please try again.');
        }
        return redirect()->to('/admin');
    }


    /**
     * Return the restaurant view Page with the given restaurantID
     * @param int $restaurantID The id of restaurant
     * @return string
     */
    public function restaurant(int $restaurantID): string
    {
        $dataProcessor = new DataProcessor();
        $restaurantModel = new RestaurantModel();
        $dishCategoryModel = new DishCategoryModel();
        $tableModel = new TableModel();

        // Fetch user details by user_id
        $data['restaurant'] = $restaurantModel->find($restaurantID);

        // Ensure user exists
        if (!$data['restaurant']) {
            throw new PageNotFoundException('Restaurant Not Found');
        }

        // Fetch related data
        $data['dishCategories'] = $dishCategoryModel->where('RestaurantID', $restaurantID)->orderBy('CategoryName', 'ASC')->findAll();

        // Dish Info
        $mergedDishes = $dataProcessor->dishForWebView($restaurantID, null);
        $data['dishes'] = $mergedDishes;

        // Customisation Info
        $mergedCustomisationOptions = $dataProcessor->customisationOptionForWebView($restaurantID, null);
        $data['customisationOptions'] = $mergedCustomisationOptions;

        // Table Info
        $data['tables'] = $tableModel->where('RestaurantID', $restaurantID)->orderBy('TableNumber', 'ASC')->findAll();

        $data['name'] = $data['restaurant']['Name']; // Needed for the base template
        $data['restaurantID'] = $restaurantID;

        return view('restaurant_view', $data);
    }


    /**
     * Renders the ordering view with a list of dishes categorized by their categories for a specific restaurant and table.
     *
     * This function handles the preparation of data required to display the ordering page. It retrieves the table and
     * restaurant details based on provided parameters, validates their existence, and fetches the dishes organized
     * by categories available in the specified restaurant. It redirects to an error page if any crucial data is missing
     * or incorrect.
     *
     * Uses global $_GET for input parameters.
     * @return string Returns a view. If parameters are missing or incorrect, it returns an error view.
     *
     */
    public function ordering(): string
    {
        $db = \Config\Database::connect();
        $params = array_change_key_case($_GET, CASE_LOWER);
        $restaurantID = $params['restaurantid'] ?? null;
        $tableNumber = $params['tablenumber'] ?? null;

        if (!$restaurantID || !$tableNumber) {
            $data['message'] = 'Restaurant or Table Number not found';
            return view('errors/html/error_404', $data);
        }

        $tableModel = new TableModel();
        $restaurantModel = new RestaurantModel();
        $dishCategoryModel = new DishCategoryModel();

        $table = $tableModel->where(['TableNumber' => $tableNumber, 'RestaurantID' => $restaurantID])->first();
        $restaurant = $restaurantModel->find($restaurantID);

        if (!$table) {
            $data['message'] = 'Table Number not found';
            return view('errors/html/error_404', $data);
        }

        $tableID = $table['TableID'];
        $data['tableID'] = $tableID;
        $data['tableNumber'] = $tableNumber;
        $data['restaurantID'] = $restaurantID;
        $data['restaurantName'] = $restaurant['Name'];

        // sidebar
        $dishCategories = $dishCategoryModel->where('RestaurantID', $restaurantID)->orderBy('CategoryName', 'ASC')->findAll();
        $data['dishCategories'] = $dishCategories;

        $query = $db->table('DISHES as d')
            ->select('d.*, c.CategoryName')
            ->join('DISH_CATEGORIES as c', 'd.CategoryID = c.CategoryID', 'inner')
            ->where('c.RestaurantID', $restaurantID)
            ->orderBy('c.CategoryName', 'ASC')
            ->orderBy('d.DishName', 'ASC');

        $queryResults = $query->get()->getResultArray();

        $categorisedDishes = [];
        foreach ($queryResults as $row) {
            $categoryName = $row['CategoryName']; // 使用数组访问方式
            if (!isset($categorisedDishes[$categoryName])) {
                $categorisedDishes[$categoryName] = [];
            }
            $categorisedDishes[$categoryName][] = $row;
        }
        $data['categorisedDishes'] = $categorisedDishes;

        return view('ordering_view', $data);
    }

    /**
     * Renders the order status views with a specific order.
     * This function handles the order status page.
     * Uses global $_GET for input parameters. If no orderID provided.
     * Using restaurantID and orderNUmber to retrieve orderID.
     * @param int|null $orderID OrderID.
     * @return string|ResponseInterface a view. If parameters are missing or incorrect, it returns an error view.
     */
    public function orderstatus(int $orderID = null): string|ResponseInterface
    {
        $dataProcessor = new DataProcessor();
        $orderModel = new OrderModel();

        // retrieve orderID with the restaurantID and orderNumber
        if (!$orderID) {
            $restaurantID = $_GET['restaurantID'] ?? null;
            $orderNumber = $_GET['orderNumber'] ?? null;
            $order = $orderModel
                ->where(['RestaurantID' => $restaurantID, 'OrderNumber' => $orderNumber])
                ->first();
            if (!$order) {
                $data['message'] = 'Order not found.';
                return view('errors/html/error_404', $data);
            }
            $orderID = $order['OrderID'];

        }
        $data = $dataProcessor->getOrderDetails($orderID);

        if (!$data) {
            return $this->response->setStatusCode(404);
        }

        if ($data['order']['Comment'] === '') {
            $data['order']['Comment'] = 'No note leaves.';
        }
        return view('order_status', $data);
    }

    /**
     * Render the view of restaurant order management with the given restaurantID.
     * @param int $restaurantID RestaurantID
     * @return string THe view of restaurant order management page.
     */
    public function restaurantOrderManagement(int $restaurantID): string
    {
        $orderModel = new OrderModel();
        $restaurantModel = new RestaurantModel();
        $tableModel = new TableModel();
        $restaurant = $restaurantModel->find($restaurantID);
        $data['restaurant'] = $restaurant;

        $orders = $orderModel->where('RestaurantID', $restaurantID)->orderBy('OrderTime', 'DESC')->findAll();

        // If no comment leaves in an order, add "No note leaves." to the comment part for view.
        for ($i = 0; $i < sizeof($orders); $i++) {
            $tableID = $orders[$i]['TableID'];
            $orders[$i]['TableNumber'] = $tableModel->find($tableID)['TableNumber'];
            if ($orders[$i]['Comment'] === '') {
                $orders[$i]['Comment'] = 'No note leaves.';
            }
        }
        $data['orders'] = $orders;
        $data['name'] = $restaurant['Name'];
        return view('restaurant_order_management', $data);
    }
}