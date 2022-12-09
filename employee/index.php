<?php
// Headers
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: PUT, GET, POST, DELETE");
header("Access-Control-Allow-Headers: Origin, X-Requested-With, Content-Type, Accept, token");

// Includes
include_once('../bd.php');

class Employee {
  private function getRequestDataBody() {
    $body = file_get_contents('php://input');
    if (empty($body)) {
        return [];
    }

    $data = json_decode($body, true);
    if (json_last_error()) {
      trigger_error(json_last_error_msg());
      return [];
    }
    return $data;
  }

  private function encodeArrays (array $data) {
    if (isset($data["permissionList"])) {
      $data["permissionList"] = json_encode($data["permissionList"]);
    }
    return $data;
  }

  private function decodeArrays (array $data) {
    if (isset($data["permissionList"])) {
      $data["permissionList"] = json_decode($data["permissionList"], true);
    }
    return $data;
  }

  public function get () {
    $db = new database();
    $db->connect();
    $clausesArr = array(
      "id" => $_GET['id'],
    );
    if ($clausesArr['id'] !== null) {
      $result = $db->select('employee', $clausesArr);
    } else {
      $result = $db->selects('employee');
    }
    $db->disconnect();
    for ($i = 0; $i < count($result); $i++) {
      $result[$i] = $this->decodeArrays($result[$i]);
    }
    http_response_code(200);
    echo json_encode($result);
  }

  public function post () {
    $db = new database();
    $db->connect();
    $data = $this->getRequestDataBody();
    $data = $this->encodeArrays($data);
    $result = $db->insert('employee', $data);
    $data["id"] = $result;
    $data = $this->decodeArrays($data);
    $db->disconnect();
    http_response_code(200);
    echo json_encode($data);
  }

  public function put () {
    if (isset($_GET['id'])) {
      $db = new database();
      $db->connect();
      $data = $this->getRequestDataBody();
      $data = $this->encodeArrays($data);
      $clausesArr = array(
        "id" => $_GET['id'],
      );
      $result = $db->update('employee', $clausesArr, $data);
      $db->disconnect();
      http_response_code(200);
      echo json_encode($result);
    } else {
      http_response_code(400);
      echo json_encode(array("message" => "No id provided"));
    }
  }

  public function delete () {
    if (isset($_GET['id'])) {
      $db = new database();
      $db->connect();
      $clausesArr = array(
        "id" => $_GET['id'],
      );
      $result = $db->delete('employee', $clausesArr);
      $db->disconnect();
      http_response_code(200);
      echo json_encode($result);
    } else {
      http_response_code(400);
      echo json_encode(array("message" => "No id provided"));
    }
  }

  public function execute () {
    $method = $_SERVER['REQUEST_METHOD'];
    switch ($method) {
      case 'GET':
        $this->get();
        break;
      case 'POST':
        $this->post();
        break;
      case 'PUT':
        $this->put();
        break;
      case 'DELETE':
        $this->delete();
        break;
      default:
        echo 'Method not allowed';
        break;
    }
  }
}

$employee = new Employee();
$employee->execute();
?>