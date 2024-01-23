<?php
require 'model/ApiModel.php';
require 'controllers/ChoiceController.php';
require 'view/ViewClass.php';

$apiUrl = 'https://www.cheapshark.com/api/1.0/';
$sortBy = 'DealRating';

$model = new ApiModel($apiUrl, $sortBy);
$controller = new ChoiceController($model);
$view = new ViewClass();

$apiToken = isset($_GET['apikey']) ? $_GET['apikey'] : null;
if ($apiToken !== '1142007') {
    header('Content-Type: application/json');
    header('HTTP/1.0 401 Unauthorized');
    echo json_encode(['status' => 401, 'message' => 'Unauthorized']);
    exit;
}

$page = isset($_GET['page']) ? $_GET['page'] : 'deals';
$id = isset($_GET['id']) ? $_GET['id'] : null;
$title = isset($_GET['title']) ? $_GET['title'] : null;

$result = $controller->processRequest($page, $id, $title);

header('Content-Type: application/json');
echo json_encode($result, JSON_PRETTY_PRINT);

$view->render($result);
