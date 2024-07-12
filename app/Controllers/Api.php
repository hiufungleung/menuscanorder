<?php
namespace App\Controllers;

use App\Models\DishCategoryModel;
use App\Models\DishModel;
use App\Models\CustomisationOptionModel;
use App\Models\OptionValueModel;
use App\Models\DishAvailableOptionModel;
use App\Models\TableModel;
use App\Models\OrderModel;
use App\Models\OrderDetailModel;
use App\Models\OrderDetailCustomisationOptionModel;
use CodeIgniter\Database\Exceptions\DatabaseException;
use CodeIgniter\HTTP\ResponseInterface;
use ReflectionException;


class Api extends BaseController
{
    public function __construct()
    {
        helper('url');
        $this->session = session();
    }

    /**
     * Handles the creation and updating of dish categories.
     *
     * This method responds to both GET and POST requests.
     * GET request retrieves a dish category by its ID.
     * POST request either updates an existing dish category if CategoryID is provided,
     * or creates a new dish category if CategoryID is not provided.
     *
     * @param int|null $categoryID The ID of the dish category to retrieve or update. Null for insertion.
     * @return ResponseInterface Returns a JSON response with status and message.
     *
     * @throws ReflectionException
     */
    public function dishCategory(int $categoryID = null): ResponseInterface
    {
        $model = new DishCategoryModel();

        if ($this->request->getMethod() === 'POST') {
            if($_POST) {
                $categoryID = $_POST['CategoryID'];
                $categoryName = $_POST['CategoryName'];
                $restaurantID = $_POST['RestaurantID'];

                try {
                    if ($categoryID) {      //update
                        $model->update($categoryID, ['CategoryName' => $categoryName]);
                        return $this->response->setJSON(['status' => 200, 'message' => 'Dish Category updated successfully']);
                    } else {                // insert
                        $model->insert([
                            'RestaurantID' => $restaurantID,
                            'CategoryName' => $categoryName]);
                        $addedData = $model->where('RestaurantID', $restaurantID)->where('CategoryName', $categoryName)->first();
                        return $this->response->setJSON(['status' => 200, 'message' => 'Dish Category created successfully', 'data' => $addedData]);
                    }
                } catch (DatabaseException $e) {
                    // Handle database error, duplicate name
                    log_message('error', 'Database error: ' . $e->getMessage());
                    return $this->response->setJSON(['status' => 500, 'message' => 'A database error occurred: ' . $e->getMessage()]);
                }
            } else {
                return $this->response->setJSON(['status' => 400, 'message' => 'Category name cannot be empty']);
            }
        }

        // get category info
        $response['data'] = $model->find($categoryID);
        $response['status'] = 200;

        if (!$response) {
            return $this->response->setStatusCode(404, 'Not Found');
        }
        return $this->response->setJSON($response);
    }

    /**
     * Delete a dish category with the given categoryID
     *
     * @param int|null $categoryID The ID of the dish category to delete.
     * @return ResponseInterface Returns a JSON response with status and message.
     */
    public function deleteDishCategory(int $categoryID = null): ResponseInterface
    {
        if ($categoryID === null) {
            return $this->response->setStatusCode(404, 'Not Found');
        }

        $model = new DishCategoryModel();
        $model->delete($categoryID);
        return $this->response->setJSON(['status' => 200, 'message' => 'Dish Category deleted successfully']);
    }

    /**
     * Get all dish categories with the given restaurantID
     *
     * @param int|null $restaurantID The ID of the restaurant to get.
     * @return ResponseInterface Returns a JSON response with status and message.
     */
    public function getAllDishCategory(int $restaurantID = null): ResponseInterface
    {
        if ($restaurantID === null) {
            return $this->response->setStatusCode(404, 'Not Found');
        }

        $dishCategoryModel = new DishCategoryModel();
        $categories = $dishCategoryModel->where('RestaurantID', $restaurantID)->orderBy('CategoryName', 'ASC')->findAll();
        return $this->response->setJSON(['status' => 200, 'data' => $categories]);
    }

    /**
     *  Handles the creation and updating of customisation options.
     *
     *  This method responds to both GET and POST requests.
     *  GET request retrieves a customisation option by its ID.
     *  POST request either updates an existing customisation option if optionID is provided,
     *  or creates a new customisation option if optionID is not provided.
     *
     * @param int|null $optionID
     * @return ResponseInterface Returns a JSON response with status and message.
     *
     * @throws ReflectionException
     */
    public function customisationOption(int $optionID = null): ResponseInterface
    {
        $db = \Config\Database::connect();
        $dataProcessor = new DataProcessor();
        $customisationOptionModel = new CustomisationOptionModel();
        $optionValueModel = new OptionValueModel();
        if ($this->request->getMethod() === 'POST') {
            $json = $this->request->getJSON();

            // Handle Option
            $optionID = $json->Option->OptionID;
            $optionName =  $json->Option->OptionName;
            $restaurantID = $json->Option->RestaurantID;

            try {
                if ($optionID) {  // update
                    $customisationOptionModel->update($optionID, ['OptionName' => $optionName]);
                } else {          // insert
                    $optionID = $customisationOptionModel->insert([
                        'RestaurantID' => $restaurantID,
                        'OptionName' => $optionName
                    ]);
                    if (!$optionID) {
                        return $this->response->setJSON(['status' => 400, 'message' => 'Failed to create customisation option']);
                    }
                }
            } catch (DatabaseException $e) {
                log_message('error', 'Database error: ' . $e->getMessage());
            }

            // Handle Values
            $existingValues = $optionValueModel->where('OptionID', $optionID)->findAll();
            $existingValueIds = array_column($existingValues, 'ValueID');
            $submittedValues = [];

            foreach ($json->Values as $value) {
                if (isset($value->ValueID)) {
                    $submittedValues[] = $value->ValueID;
                }
            }

            //Delete unsubmitted Values
            $valuesToDelete = array_diff($existingValueIds, $submittedValues);
            foreach ($valuesToDelete as $valueID) {
                $optionValueModel->delete($valueID);
            }

            foreach ($json->Values as $value) {
                $valueData = [
                    'OptionID' => $optionID,
                    'ValueName' => $value->ValueName,
                    'ExtraPrice' => $value->ExtraPrice
                ];

                // Try update, if not exists, insert
                $existingValue = $optionValueModel->where(['OptionID' => $optionID, 'ValueName' => $value->ValueName])->first();
                if ($existingValue) {
                    $optionValueModel->update($existingValue['ValueID'], $valueData);
                } else {
                    $optionValueModel->insert($valueData);
                }
            }
            $response = $dataProcessor->customisationOptionForWebView($restaurantID, $optionID)[$optionID];

            return $this->response->setJSON(['status' => 200, 'message' => 'Customisation option processed successfully', 'data' => $response]);
        } else {
            $response['Option'] = $customisationOptionModel->find($optionID);
            $response['Values'] = $optionValueModel->where('OptionID', $optionID)->findAll();

            if (!$response) {
                return $this->response->setStatusCode(404, 'Not Found');
            }
            return $this->response->setJSON(['status' => 200, 'data' => $response]);
        }

    }


    /**
     * Get all customisation options with the given restaurantID
     *
     * @param int|null $restaurantID The ID of the restaurant to get.
     * @return ResponseInterface Returns a JSON response with status and message.
     */
    public function getAllCustomisationOptions(int $restaurantID = null): ResponseInterface
    {
        $customisationOptionModel = new customisationOptionModel();
        $response['data'] = $customisationOptionModel->where('RestaurantID', $restaurantID)->orderBy('OptionName', 'ASC')->findAll();
        $response['status'] = 200;
        return $this->response->setJSON($response);
    }

    /**
     * Delete a customisation option with the given categoryID
     *
     * @param int|null $optionID The ID of the dish category to delete.
     * @return ResponseInterface Returns a JSON response with status and message.
     */
    public function deleteCustomisationOption(int $optionID = null): ResponseInterface
    {
        $model = new CustomisationOptionModel();
        $model->delete($optionID);
        return $this->response->setJSON(['status' => 200, 'message' => 'Customisation option deleted successfully']);
    }

    /**
     * Handles the creation and updating of dishes.
     *
     * This method responds to both GET and POST requests.
     * GET request retrieves a dish by its ID.
     * POST request either updates an existing dish if dishID is provided,
     * or creates a new dish if dishID is not provided.
     *
     * @param int|null $dishID The ID of the dish to retrieve or update. Null for insertion.
     * @return ResponseInterface Returns a JSON response with status and message.
     *
     * @throws ReflectionException
     */
    public function dish(int $dishID = null): ResponseInterface
    {
        $dataProcessor = new DataProcessor();
        $db = \Config\Database::connect();
        $dishModel = new DishModel();
        $dishAvailableOptionModel = new DishAvailableOptionModel();

        if ($this->request->getMethod() === 'POST') {
            if ($_POST) {
                $restaurantID = $this->request->getPost('RestaurantID');
                $dishID = $this->request->getPost('DishID');
                $categoryID = $this->request->getPost('CategoryID');
                $description = $this->request->getPost('Description');
                $dishName = $this->request->getPost('DishName');
                $basePrice = $this->request->getPost('BasePrice');
                $optionIDs = $this->request->getPost('OptionIDs');

                try {
                    if ($dishID) {
                        // update if dishID exists
                        $dishModel->update($dishID, [
                            'CategoryID' => $categoryID,
                            'Description' => $description,
                            'BasePrice' => $basePrice,
                            'DishName' => $dishName,
                        ]);
                    } else {
                        // insert if dishID not exists
                        $dishID = $dishModel->insert([
                            'CategoryID' => $categoryID,
                            'Description' => $description,
                            'BasePrice' => $basePrice,
                            'DishName' => $dishName,
                        ]);
                    }

                    // before reinsert all available options, delete all existing options
                    $dishAvailableOptionModel->where('DishID', $dishID)->delete();

                    if ($optionIDs) {
                        foreach ($optionIDs as $optionID) {
                            $dishAvailableOptionModel->insert([
                                'DishID' => $dishID,
                                'OptionID' => $optionID,
                            ]);
                        }
                    }
                } catch (DatabaseException $e) {
                    return $this->response->setJSON(['status' => 500, 'message' => 'Database error: ' . $e->getMessage()]);
                }

                $response = $dataProcessor->dishForWebView($restaurantID, $dishID)[$dishID];
                return $this->response->setJSON(['status' => 200, 'message' => 'Dish updated successfully', 'data' => $response]);
            } else {
                return $this->response->setStatusCode(404, 'Not Found');
            }


        } else {
            $query = $db->table('DISHES as DI')
                ->select('DI.DishID, DI.DishName, DC.CategoryID, DC.CategoryName, DI.Description, DI.BasePrice, CO.OptionID, CO.OptionName')
                ->join('DISH_CATEGORIES as DC', 'DI.CategoryID = DC.CategoryID', 'inner')
                ->join('DISH_AVAILABLE_OPTIONS as DAO', 'DI.DishID = DAO.DishID', 'left')
                ->join('CUSTOMISATION_OPTIONS as CO', 'DAO.OptionID = CO.OptionID', 'left')
                ->where('DI.DishID', $dishID);
            $dishes = $query->get()->getResultArray();

            $finalDish = [];

            foreach ($dishes as $dish) {
                if (!isset($finalDish['DishID'])) {
                    // 如果最终数组还没有基本信息，初始化它
                    $finalDish = [
                        'DishID' => $dish['DishID'],
                        'DishName' => $dish['DishName'],
                        'CategoryID' => $dish['CategoryID'],
                        'CategoryName' => $dish['CategoryName'],
                        'Description' => $dish['Description'],
                        'BasePrice' => $dish['BasePrice'],
                        'Options' => []
                    ];
                }
                // add options to 'Options'
                if (!empty($dish['OptionID'])) {
                    $finalDish['Options'][] = [
                        'OptionID' => $dish['OptionID'],
                        'OptionName' => $dish['OptionName'] // 如果你也需要选项名称
                    ];
                }
            }

            $response['data'] = $finalDish;
            $response['status'] = 200;
            return $this->response->setJSON($response);
        }

    }


    /**
     * Delete a dish with the given dishID
     *
     * @param int|null $dishID The ID of the dish to delete.
     * @return ResponseInterface Returns a JSON response with status and message.
     */
    public function deleteDish(int $dishID = null): ResponseInterface
    {
        $model = new DishModel();
        $model->delete($dishID);
        return $this->response->setJSON(['status' => 200, 'message' => 'Dish deleted successfully']);
    }

    /**
     * Handles the creation and updating of tables.
     *
     * This method responds to both GET and POST requests.
     * GET request retrieves a table by its ID.
     * POST request either updates an existing table if tableID is provided,
     * or creates a new table if tableID is not provided.
     *
     * @param int|null $tableID The ID of the dish category to retrieve or update. Null for insertion.
     * @return ResponseInterface Returns a JSON response with status and message.
     *
     * @throws ReflectionException
     */
    public function table(int $tableID = null): ResponseInterface
    {
        $model = new TableModel();

        if ($this->request->getMethod() === 'POST') {
            if ($_POST) {
                $restaurantID = $this->request->getPost('RestaurantID');
                $tableNumber = $this->request->getPost('TableNumber');
                $tableCapacity = $this->request->getPost('Capacity');
                $tableID = $this->request->getPost('TableID');

                try {
                    if ($tableID) {      //update
                        $model->update($tableID, [
                            'TableNumber' => $tableNumber,
                            'Capacity' => $tableCapacity
                        ]);
                    } else {                // insert
                        $tableID = $model->insert([
                            'RestaurantID' => $restaurantID,
                            'TableNumber' => $tableNumber,
                            'Capacity' => $tableCapacity
                        ]);
                    }
                } catch (DatabaseException $e) {
                    return $this->response->setJSON(['status' => 500, 'message' => 'Database error: ' . $e->getMessage()]);
                }

                $response = [
                    'RestaurantID' => $restaurantID,
                    'TableID' => $tableID,
                    'TableNumber' => $tableNumber,
                    'Capacity' => $tableCapacity,
                ];
                return $this->response->setJSON(['status' => 200, 'message' => 'Dish Category created successfully', 'data' => $response]);

            } else {
                return $this->response->setJSON(['status' => 400, 'message' => 'Category name cannot be empty']);
            }
        }
            // Get table info
            $data = $model->find($tableID);

            if (!$data) {
                return $this->response->setStatusCode(404, 'Not Found');
            }

            $response['data'] = $data;
            $response['status'] = 200;
            return $this->response->setJSON($response);
        }


    /**
     * Get details of a dish with the given dishID
     *
     * @param int|null $dishID The ID of the dish to get.
     * @return ResponseInterface Returns a JSON response with status and message.
     */
    public function getDishDetails(int $dishID = null): ResponseInterface
    {
        $dishModel = new DishModel();
        $customisationOptionModel = new CustomisationOptionModel();
        $optionValueModel = new OptionValueModel();
        $dishAvailableOptionModel = new DishAvailableOptionModel();

        // Get the dish info
        $dish = $dishModel->find($dishID);

        if (!$dish) {
            return $this->response->setStatusCode(404, 'Dish not found');
        }

        // Get all available options
        $availableOptions = $dishAvailableOptionModel->where('DishID', $dishID)->findAll();
        $optionDetails = [];

        // process options array
        foreach ($availableOptions as $option) {
            $optionID = $option['OptionID'];
            $optionName = $customisationOptionModel->find($optionID)['OptionName'];
            $optionValues = $optionValueModel->where('OptionID', $optionID)->findAll();

            foreach ($optionValues as $value) {
                $optionDetails[$optionName][] = [
                    'ValueID' => $value['ValueID'],
                    'ValueName' => $value['ValueName'],
                    'ExtraPrice' => $value['ExtraPrice'],
                    'OptionID' => $optionID
                ];
            }
        }

        $data = [
            'DishID' => $dish['DishID'],
            'DishName' => $dish['DishName'],
            'Description' => $dish['Description'],
            'AvailableOptions' => $optionDetails,
            'BasePrice' => $dish['BasePrice'],
        ];

        $response['data'] = $data;
        $response['status'] = 200;
        return $this->response->setJSON($response);
    }


    /**
     * Calculate the total price with the given dishes.
     * This method responds to POST requests.
     * POST request calculate the total price with the given dish details
     *
     * @return ResponseInterface Returns a JSON response with status and message.
     */
    public function calculateDishesPrice(): ResponseInterface
    {
        $dishes = $_POST['Dishes'];
        if (!$dishes) {
            return $this->response->setStatusCode(400, 'Bad request');
        }

        $dataProcessor = new DataProcessor();
        $response['data'] = $dataProcessor->priceCalculator($dishes);
        $response['status'] = 200;

        return $this->response->setJSON($response);

    }

    /**
     * Submit order with the given order details from POST
     * This method responds to both POST requests.
     * POST request send order
     * @throws ReflectionException
     */
    public function sendOrder(): ResponseInterface
    {
        $dishes = $_POST['Dishes'];
        if (!$dishes) {
            return $this->response->setStatusCode(400, 'Bad request');
        }

        $dataProcessor = new DataProcessor();
        $response = $dataProcessor->priceCalculator($dishes);

        // create order
        $orderModel = new OrderModel();
        $orderDetailModel = new OrderDetailModel();
        $orderDetailCustomisationOptionModel = new OrderDetailCustomisationOptionModel();

        $orderData = [
            'RestaurantID' => $_POST['RestaurantID'],
            'CustomerName' => $_POST['CustomerName'],
            'TotalPrice' => $response['TotalPrice'],
            'TableID' => $_POST['TableID'],
            'Comment' => $_POST['Comment'] ?? '',
            'Status' => 'Pending'
        ];

        try {
            $orderID = $orderModel->insert($orderData);
            if(!$orderID) {
                return $this->response->setStatusCode(500, 'Fail to create order');
            }
            $orderNumber = $orderModel->find($orderID)['OrderNumber'];

            // insert order details
            foreach ($response['Dishes'] as $dish) {
                $orderDetailData = [
                    'OrderID' => $orderID,
                    'DishID' => $dish['DishID'],
                    'Quantity' => $dish['Quantity'],
                ];
                $orderDetailID = $orderDetailModel->insert($orderDetailData);
                if (!$orderDetailID) {
                    return $this->response->setStatusCode(500, 'Failed to create order detail');
                }

                // insert order detail customisation options
                foreach ($dish['ValueID'] as $valueID) {
                    $orderDetailCustomisationOptionData = [
                        'OrderDetailID' => $orderDetailID,
                        'ValueID' => $valueID,
                    ];
                    $orderDetailCustomisationOptionModel->insert($orderDetailCustomisationOptionData);
                }
            }
        } catch (DatabaseException $e) {
            return $this->response->setJSON(['status' => 500, 'message' => 'Database error: ' . $e->getMessage()]);
        }

        return $this->response->setJSON([
            'status' => 200,
            'message' => 'Order created successfully',
            'data' => [
                'OrderID' => $orderID,
                'RestaurantID' => $_POST['RestaurantID'],
                'OrderNumber' => $orderNumber,
            ]
        ]);
    }

    /**
     * Get order details with the given orderID
     *
     * @param int|null $orderID The ID of the order to get.
     * @return ResponseInterface Returns a JSON response with status and message.
     */
    public function getOrderDetails(int $orderID = null): ResponseInterface
    {
        $dataProcessor = new DataProcessor();
        $data = $dataProcessor->getOrderDetails($orderID);
        $response['data'] = $data;
        $response['status'] = 200;
        return $this->response->setJSON($response);
    }

    /**
     * Get all customisation options with the given restaurantID
     *
     * POST request get the orderID and change its status to completed
     * @return ResponseInterface Returns a JSON response with status and message.
     * @throws ReflectionException
     */
    public function changeOrderStatus(): ResponseInterface
    {
        if ($this->request->getMethod() == 'POST') {
            $orderID = $this->request->getPost('OrderID');
            $action = $this->request->getPost('Action');

            $status = ['finalise' => 'Completed', 'cancel' => 'Cancelled'];

            $orderModel = new OrderModel();
            if (!$orderID) {
                return $this->response->setStatusCode(400, 'Bad request');
            }
            $order = $orderModel->find($orderID);
            if ($order == null) {
                return $this->response->setStatusCode(404, 'Order not found');
            }
            $orderStatus = $order['Status'];
            if ($orderStatus != 'Pending') {
                return $this->response->setStatusCode(404, 'Order status is not pending');
            }

            try{
                $orderModel->update($orderID, ['Status' => $status[$action]]);
            } catch (DatabaseException $e) {
                return $this->response->setJSON(['status' => 500, 'message' => 'Database error: ' . $e->getMessage()]);
            }
            return $this->response->setJSON([
                'status' => 200,
                'message' => 'Order completed successfully'
            ]);
        } else {
            return $this->response->setStatusCode(400, 'Bad request');
        }
    }
}
