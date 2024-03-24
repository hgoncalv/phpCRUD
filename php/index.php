<?php
// Handle preflight OPTIONS request
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    header('Access-Control-Allow-Origin: *');
    header('Access-Control-Allow-Methods: GET, POST, PUT, DELETE');
    header('Access-Control-Allow-Headers: X-Requested-With, Content-Type');
    header('Content-Length: 0');
    header('Content-Type: text/plain');
    exit;
}

// Now continue with your regular code
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, POST, PUT, DELETE');
header('Access-Control-Allow-Headers: X-Requested-With, Content-Type');
header('Content-Type: application/json');

// Include necessary files
require_once "./basePath.php";
require_once BASE_PATH . "/functions/strFunctions.php";
require_once BASE_PATH . "/functions/arrayFunctions.php";
require_once BASE_PATH . "/classes/includeClasses.php";

// Define the relationship between HTTP methods and CRUD operations
$methodsRel = [
    'GET' => 'read',
    'POST' => 'create',
    'PUT' => 'update',
    'DELETE' => 'delete',
];
$pastTenceRel = [
    'GET' => 'read',
    'POST' => 'created',
    'PUT' => 'updated',
    'DELETE' => 'deleted',
];

// Define allowed HTTP methods
$request_methods_allowed = ['GET', 'POST', 'PUT', 'DELETE'];

// Function to retrieve input data based on the request method
function getInputData($request_method)
{
    $input_data = [];
    if ($request_method === 'GET') {
        $input_data = $_GET;
    } else {
        // Retrieve input data from the request body
        $input_data = json_decode(file_get_contents("php://input"), true);
        if ($request_method == "POST" && !$input_data) {
            // If JSON decoding fails for POST request, fallback to $_POST
            $input_data = $_POST;
        }
        if (!$input_data && in_array($request_method, ['PUT', 'DELETE'])) {
            // For PUT and DELETE requests, parse input from request body
            parse_str(file_get_contents('php://input'), $input_data);
        }
    }
    // If no input data is found, return an error response
    if (!$input_data) {
        http_response_code(400);
        echo json_encode(array('message' => 'Invalid input data', 'error' => true));
        exit;
    }
    return $input_data;
}

// Retrieve the request method
$request_method = $_SERVER['REQUEST_METHOD'];

// Check if the request method is allowed
if (!in_array($request_method, $request_methods_allowed)) {
    // If not allowed, return Method Not Allowed response
    http_response_code(405);
    echo json_encode(array('message' => 'Method Not Allowed', 'error' => true));
    exit;
} else {
    // Process the request
    $input_data = getInputData($request_method);

    if ($input_data['getTableNames']) {

        $db = new Database();
        echo json_encode(array('data' => $db->getTables(), 'message' => 'Tables', 'error' => true));
        exit;
    }

    $class_name = $input_data['table_name'] ?? null;
    // Check if the provided class name is valid
    if (!$class_name || !class_exists($class_name)) {
        http_response_code(400);
        echo json_encode(array('message' => 'Invalid class name', 'error' => true));
        exit;
    }

    // Instantiate the specified class
    $table = new $class_name();
    if ($input_data['table_cols']) {
        $data = $table->get_cols();
        echo json_encode(array('data' => $data, 'message' => "columns of $class_name" ?? null, 'success' => true));
        exit;
    }

    // Sanitize input data

    $input_data = hg_sanitize($input_data);

    // set properties of the class object
    foreach ($input_data as $key => $value) {
        if ($key !== 'class_name' && property_exists($table, $key)) {
            $table->$key = $input_data[$key];
        }
    }

    // Retrieve and sanitize the 'where' clause from input data
    $where = $input_data['where'] ?? null;
    // Determine the method to call based on the request method
    $methodStr = $methodsRel[$request_method];
    // Call the corresponding method on the instantiated class
    $ret = $table->$methodStr($where);

    $rowCount = is_array($ret) ? count($ret) : 0;
    $data = $ret;

    // Determine if there was an error based on the row count
    $error = ($rowCount < 1);
    // Get the past tense of the corresponding CRUD operation
    $pastTence = $pastTenceRel[$request_method];
    $message = $rowCount . " $class_name" . ($rowCount === 1 ? '' : 's') . " " . $pastTence;

    echo json_encode(array('data' => $data, 'message' => $message ?? null, 'success' => !$error));
}
