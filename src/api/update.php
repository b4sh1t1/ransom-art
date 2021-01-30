<?php
include_once './php/database.class.php';
include_once './php/query.class.php';
header('Content-Type: application/json; charset=UTF-8');
if (isset($_SERVER['REQUEST_METHOD']) && strtolower($_SERVER['REQUEST_METHOD']) === 'post') {
    $raw = file_get_contents('php://input');
    if ($raw) {
        $data = @json_decode($raw);
        if ($data) {
            $response = array(
                'status' => 'ok',
                'message' => ''
            );
            if (isset($data->user) && isset($data->machine) && isset($data->email) && isset($data->bitcoin)) {
                $parameters = array(
                    'user' => trim($data->user),
                    'machine' => trim($data->machine),
                    'email' => trim($data->email),
                    'bitcoin' => trim($data->bitcoin)
                );
                mb_internal_encoding('UTF-8');
                if (mb_strlen($parameters['user']) >= 1 && mb_strlen($parameters['user']) <= 30 && mb_strlen($parameters['machine']) >= 1 && mb_strlen($parameters['machine']) <= 30 && mb_strlen($parameters['email']) >= 1 && mb_strlen($parameters['email']) <= 300 && mb_strlen($parameters['bitcoin']) >= 1 && mb_strlen($parameters['bitcoin']) <= 60) {
                    $params = array(
                        'user' => $parameters['user'],
                        'machine' => $parameters['machine']
                    );
                    $data = Query::select('SELECT `email`, `bitcoin` FROM `data` WHERE `user` = :user AND `machine` = :machine', $params, 'single');
                    if ($data === false) {
                        $response['status'] = 'error';
                        $response['message'] = 'Database error. Try again later.';
                    } else if ($data === 0) {
                        $response['status'] = 'error';
                        $response['message'] = 'Record not found. Try again.';
                    } else {
                        if (!$data['email'] && !$data['bitcoin']) {
                            $params['email'] = strtolower($parameters['email']);
                            $params['bitcoin'] = $parameters['bitcoin'];
                            $params['payment_date'] = date('Y-m-d H:i:s', time());
                            if (Query::update('UPDATE `data` SET `email` = :email, `bitcoin` = :bitcoin, `payment_date` = :payment_date WHERE `user` = :user AND `machine` = :machine', $params) === false) {
                                $response['status'] = 'error';
                                $response['message'] = 'Database error. Try again later.';
                            }
                        } else {
                            $response['status'] = 'error';
                            $response['message'] = 'You can submit only once. Sorry...';
                        }
                    }
                } else {
                    $response['status'] = 'error';
                    $response['message'] = 'Invalid data. Try again.';
                }
            } else {
                $response['status'] = 'error';
                $response['message'] = 'Required data is missing. Try again.';
            }
            echo json_encode($response, JSON_PRETTY_PRINT);
        }
    }
}
?>
