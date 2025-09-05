<?php
namespace App\Controllers;

use App\Models\CustomisationOptionModel;
use App\Models\DishModel;
use App\Models\OptionValueModel;
use App\Models\OrderDetailCustomisationOptionModel;
use App\Models\OrderDetailModel;
use App\Models\OrderModel;
use App\Models\RestaurantModel;
use App\Models\TableModel;
use CodeIgniter\Exceptions\PageNotFoundException;
use Config\Database;

class DataProcessor extends BaseController {
    public function __construct()
    {
        helper('url');
        $this->session = session();
    }

    /**
     * Create an array that stores all necessary info for customisation option in a row for web view.
     * If only restaurantID provided, return all options in that restaurant.
     * If optionID provided, return the option.
     * @param int|null $restaurantID The id of restaurant
     * @param int|null $optionID The id of the option
     * @return array contains OptionID, OptionName, RestaurantID, ValueID, ValueName (+$ExtraPrice)
     */
    public function customisationOptionForWebView(int $restaurantID = null, int $optionID = null): array
    {
        $db = Database::connect();

        if (!$optionID) {
            $query = $db->table('CUSTOMISATION_OPTIONS as C')
                ->select('C.OptionID, C.OptionName, C.RestaurantID, O.ValueID, O.ValueName, O.ExtraPrice')
                ->join('OPTION_VALUES as O', 'O.OptionID = C.OptionID', 'left')
                ->where('C.RestaurantID', $restaurantID)
                ->orderBy('C.OptionName', 'ASC')
                ->orderBy('O.ValueName', 'ASC');
        } else {
            $query = $db->table('CUSTOMISATION_OPTIONS as C')
                ->select('C.OptionID, C.OptionName, C.RestaurantID, O.ValueID, O.ValueName, O.ExtraPrice')
                ->join('OPTION_VALUES as O', 'O.OptionID = C.OptionID', 'left')
                ->where('C.OptionID', $optionID)
                ->orderBy('O.ValueName', 'ASC');
        }

        $customisationOptions = $query->get()->getResultArray();

        // merge value names and extra prices for the same option name
        $mergedCustomisationOptions = [];
        foreach ($customisationOptions as $customisationOption) {
            $optionID = $customisationOption['OptionID'] ?? null;

            if ($customisationOption['ExtraPrice']) {
                $formattedPrice = ($customisationOption['ExtraPrice'] >= 0 ? "+$" : "-$") . abs($customisationOption['ExtraPrice']);
                $valueWithPrice = $customisationOption['ValueName'] . ' (' . $formattedPrice . ')';
            } else {
                $valueWithPrice = 'Not added yet.';
            }

            if (!isset($mergedCustomisationOptions[$optionID])) {
                $mergedCustomisationOptions[$optionID] = [
                    'OptionID' => $customisationOption['OptionID'],
                    'OptionName' => $customisationOption['OptionName'],
                    'ValuesPrices' => [],
                    'RestaurantID' => $customisationOption['RestaurantID']
                ];
            }
            $mergedCustomisationOptions[$optionID]['ValuesPrices'][] = $valueWithPrice;
        }
        foreach ($mergedCustomisationOptions as $index => $option) {
            $mergedCustomisationOptions[$index]['ValuesPrices'] = implode(", " . "\t", $option['ValuesPrices']);
        }
        return $mergedCustomisationOptions;
    }


    /**
     * Create an array that stores all necessary info for dish in a row for web view.
     * If only restaurantID provided, return all options in that restaurant.
     * If dishID provided, return the dish info.
     * @param int|null $restaurantID The id of restaurant
     * @param int|null $dishID The id of dish
     * @return array contains DishID, DishName, RestaurantID, CategoryID, CategoryName, Description, BasePrice, Options
     */
    public function dishForWebView(int $restaurantID = null, int $dishID = null): array
    {
        $db = Database::connect();
        if (!$dishID) {
            $query = $db->table('DISHES as DI')
                ->select('DC.RestaurantID, DC.CategoryID, DC.CategoryName, DI.DishID, DI.DishName, DI.Description, DI.BasePrice, DAO.OptionID, CO.OptionName')
                ->join('DISH_CATEGORIES as DC', 'DI.CategoryID = DC.CategoryID', 'inner')
                ->join('DISH_AVAILABLE_OPTIONS as DAO', 'DI.DishID = DAO.DishID', 'left')
                ->join('CUSTOMISATION_OPTIONS as CO', 'DAO.OptionID = CO.OptionID', 'left')
                ->where('DC.RestaurantID', $restaurantID)
                ->orderBy('DishName','ASC');
        } else {
            $query = $db->table('DISHES as DI')
                ->select('DC.RestaurantID, DC.CategoryID, DC.CategoryName, DI.DishID, DI.DishName, DI.Description, DI.BasePrice, DAO.OptionID, CO.OptionName')
                ->join('DISH_CATEGORIES as DC', 'DI.CategoryID = DC.CategoryID', 'inner')
                ->join('DISH_AVAILABLE_OPTIONS as DAO', 'DI.DishID = DAO.DishID', 'left')
                ->join('CUSTOMISATION_OPTIONS as CO', 'DAO.OptionID = CO.OptionID', 'left')
                ->where('DC.RestaurantID', $restaurantID)
                ->where('DI.DishID', $dishID);
        }

        $dishes = $query->get()->getResultArray();

        // merge available options for the same dish
        $mergedDishes = [];
        foreach ($dishes as $dish) {
            $dishID = $dish['DishID'];
            $optionName = $dish['OptionName'];
            if (!isset($mergedDishes[$dish['DishID']])) {
                $mergedDishes[$dishID] = [
                    'RestaurantID' => $dish['RestaurantID'],
                    'CategoryID' => $dish['CategoryID'],
                    'DishID' => $dish['DishID'],
                    'CategoryName' => $dish['CategoryName'],
                    'DishName' => $dish['DishName'],
                    'Description' => $dish['Description'],
                    'BasePrice' => $dish['BasePrice'],
                    'OptionName' => []
                ];
            }
            $mergedDishes[$dishID]['OptionName'][] = $optionName;
        }
        foreach ($mergedDishes as $index => $mergedDish) {
            $mergedDishes[$index]['OptionName'] = implode(', ', $mergedDish['OptionName']);
            if ($mergedDishes[$index]['OptionName'] == '') {
                $mergedDishes[$index]['OptionName'] = "Not added yet.";
            }
        }
        return $mergedDishes;
    }

    /**
     * Calculate price based on all dishes provided and corresponding amounts.
     * @param $dishes
     * @return array totalPrice and all dishes provided
     */
    public function priceCalculator($dishes): array
    {
        $dishModel = new DishModel();
        $optionValueModel = new OptionValueModel();
        $response = [];
        $totalPrice = 0;

        foreach ($dishes as $dishData) {
            $dishID = $dishData['DishID'];
            $quantity = $dishData['Quantity'];
            $selectedValuesID = $dishData['SelectedValuesID'] ?? [];
            $dish = $dishModel->find($dishID);
            $basePrice = $dish['BasePrice'];
            $unitPrice = $basePrice;

            if ($selectedValuesID) {
                foreach ($selectedValuesID as $valueID) {
                    $value = $optionValueModel->find($valueID);
                    if ($valueID) {
                        $unitPrice += $value['ExtraPrice'];
                    }
                }
            }
            $response['Dishes'][] = [
                'DishID' => $dishID,
                'ValueID' => $dishData['SelectedValuesID'] ?? [],
                'UnitPrice' => $unitPrice * 1,
                'Quantity' => $quantity * 1
            ];

            $totalPrice += $quantity * $unitPrice;
        }

        $response['TotalPrice'] = $totalPrice;

        return $response;
    }

    /**
     * Get the order details with the given orderID.
     * @param int $orderID The orderID to get details.
     * @return array contains each ordered dish and its details and selected options.
     */
    public function getOrderDetails(int $orderID): array
    {
        $orderModel = new OrderModel();
        $orderDetailModel = new OrderDetailModel();
        $orderDetailCustomisationOptionModel = new OrderDetailCustomisationOptionModel();
        $dishModel = new DishModel();
        $customisationOptionModel = new CustomisationOptionModel();
        $optionValueModel = new OptionValueModel();
        $restaurantModel = new RestaurantModel();
        $tableModel = new TableModel();

        // Get basic info of an order
        $order = $orderModel->find($orderID);
        $restaurant = $restaurantModel->find($order['RestaurantID']);

        $table = $tableModel->find($order['TableID']);

        if (!$order) {
            throw new PageNotFoundException('Order Not Found');
        }

        // Get order details
        $orderDetails = $orderDetailModel->where('OrderID', $orderID)->findAll();

        // Get all the customisation options for each dish
        $detailedOrder = [];
        foreach ($orderDetails as $detail) {
            $dish = $dishModel->find($detail['DishID']);
            $customisationOptions = $orderDetailCustomisationOptionModel->where('OrderDetailID', $detail['OrderDetailID'])->findAll();

            $options = [];
            $unitPrice = $dish['BasePrice'];
            foreach ($customisationOptions as $option) {
                $value = $optionValueModel->find($option['ValueID']);
                $option = $customisationOptionModel->find($value['OptionID']);
                $unitPrice += $value['ExtraPrice']; // Calculate unit price including extra price
                $options[] = [
                    'OptionName' => $option['OptionName'],
                    'ValueName' => $value['ValueName'],
                    'ExtraPrice' => $value['ExtraPrice']
                ];
            }

            $detailedOrder[] = [
                'DishName' => $dish['DishName'],
                'UnitPrice' => $unitPrice,
                'Quantity' => $detail['Quantity'],
                'CustomisationOptions' => $options
            ];
        }

        $data['restaurantID'] = $restaurant['RestaurantID'];
        $data['restaurantName'] = $restaurant['Name'];
        $data['tableNumber'] = $table['TableNumber'];
        $data['order'] = $order;
        $data['detailedOrder'] = $detailedOrder;
        return $data;
    }

}
