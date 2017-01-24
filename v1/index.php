<?php

error_reporting(-1);
ini_set('display_errors', 'On');

require_once '../include/db_users.php';
require_once '../include/db_driver.php';
require '.././libs/Slim/Slim.php';

\Slim\Slim::registerAutoloader();

$app = new \Slim\Slim();


/**
 * USER POST REST API
 */

$app->post('/user/create', function () use ($app) {
    // check for required params
    verifyRequiredParams(array('fname', 'lname', 'email', 'password', 'tel'));

    // reading post params
    $fname = $app->request->post('fname');
    $lname = $app->request->post('lname');
    $email = $app->request->post('email');
    $password = $app->request->post('password');
    $tel = $app->request->post('tel');


    // validating email address
    validateEmail($email);

    $db = new DbUsers();
    $response = $db->createUser($fname, $lname, $email, $password, $tel);

    // echo json response
    echoRespnse(200, $response);
});

$app->post('/user/login', function () use ($app) {

    verifyRequiredParams(array('email', 'password'));

    $email = $app->request->post('email');
    $password = $app->request->post('password');

    validateEmail($email);

    $db = new DbUsers();
    $result = $db->loginUser($email, $password);

    echoRespnse(200, $result);
});

$app->post('/user/insert/rating', function () use ($app) {

    verifyRequiredParams(array('is_favorite', 'job_id'));
    $isFavorite = $app->request->post('is_favorite');
    $jobId = $app->request->post('job_id');
    $ratingValue = $app->request->post('rating_value');
    $commentDetail = $app->request->post('comment_detail');

    $db = new DbUsers();
    $result = $db->setRatingAndDriverFavorite($isFavorite, $jobId, $ratingValue, $commentDetail);

    echoRespnse(200, $result);
});

$app->post('/user/notification/:did', function ($driver_id) use ($app) {
    $db = new DbDriver();
    verifyRequiredParams(array('title', 'body'));

    $title = $app->request->post('title');
    $body = $app->request->post('body');

    require_once __DIR__ . '/../libs/fcm/fcm.php';

    $fcm = new FCM();

    $driver = $db->getDriverById($driver_id);

    $msg = array();
    $msg['title'] = $title;
    $msg['body'] = $body;


    // sending push message to single user
    $fcm->send($driver['driver_fcm_id'], $msg);

    $response['success'] = true;
    $response['message'] = "Send Notification Successfully!!!";

    echoRespnse(200, $response);
});

$app->post('/job/insert', function () use ($app) {
    require_once __DIR__ . '/../libs/fcm/fcm.php';
    require_once __DIR__ . '/../libs/fcm/push.php';
    $response = array();
    verifyRequiredParams(array('fr_lat', 'fr_lng', 'fr_address', 'to_lat', 'to_lng', 'to_address', 'time', 'date',
        'lift_status', 'lift_price', 'lift_plus_status', 'lift_plus_price',
        'cart_status', 'cart_price', 'charge_start_price', 'charge_start_km',
        'charge_rate', 'job_status', 'job_distance', 'user_id', 'driver_id', 'total'));


    $fr_lat = $app->request->post('fr_lat');
    $fr_lng = $app->request->post('fr_lng');
    $fr_address = $app->request->post('fr_address');
    $to_lat = $app->request->post('to_lat');
    $to_lng = $app->request->post('to_lng');
    $to_address = $app->request->post('to_address');
    $time = $app->request->post('time');
    $date = $app->request->post('date');
    $lift_status = $app->request->post('lift_status');
    $lift_price = $app->request->post('lift_price');
    $lift_plus_status = $app->request->post('lift_plus_status');
    $lift_plus_price = $app->request->post('lift_plus_price');
    $cart_status = $app->request->post('cart_status');
    $cart_price = $app->request->post('cart_price');
    $charge_start_price = $app->request->post('charge_start_price');
    $charge_start_km = $app->request->post('charge_start_km');
    $charge_rate = $app->request->post('charge_rate');
    $job_status = $app->request->post('job_status');
    $job_distance = $app->request->post('job_distance');
    $image1 = $app->request->post('image1');
    $image2 = $app->request->post('image2');
    $image3 = $app->request->post('image3');
    $user_id = $app->request->post('user_id');
    $driver_id = $app->request->post('driver_id');
    $total = $app->request->post('total');

    $images = array();
    if ($image1 != null) {
        array_push($images, $image1);
    }
    if ($image2 != null) {
        array_push($images, $image2);
    }
    if ($image3 != null) {
        array_push($images, $image3);
    }

    $db = new DbUsers();
    $result = $db->insertJob($fr_lat, $fr_lng, $fr_address, $to_lat, $to_lng, $to_address, $time, $date, $lift_status,
        $lift_price, $lift_plus_status, $lift_plus_price, $cart_status, $cart_price, $charge_start_price,
        $charge_start_km, $charge_rate, $job_status, $job_distance, $images, $user_id, $driver_id);


    $fcm = new FCM();
    $push = new Push();
    $db2 = new DbDriver();
    $driver = $db2->getDriverById($driver_id);
    $regId = $driver['driver_fcm_id'];

    $title = "มีงานใหม่เข้ามา";
    $message = "ราคา " . number_format($total, 2) . " บาท ระยะทาง " . number_format($job_distance, 2) . " กิโลเมตร";

    $payload = array();
    $payload['jid'] = $result['data'];
    $payload['price'] = number_format($total, 2);
    $payload['distance'] = number_format($job_distance, 2);
    $payload['fr_name'] = $fr_address;
    $payload['to_name'] = $to_address;
    $payload['status'] = "new_job";

    $push->setTitle($title);
    $push->setMessage($message);
    $push->setImage('');
    $push->setIsBackground(FALSE);
    $push->setPayload($payload);

    $json = '';
    $json = $push->getPush();

    $fcm->send($regId, $json);
    echoRespnse(200, $result);
});

$app->post('/user/update/driver/favorite',function () use ($app){
    verifyRequiredParams(array('uid', 'did'));
    $uid = $app->request->post('uid');
    $did = $app->request->post('did');
    $db = new DbUsers();
    $result = $db->deleteDriverFavorite($uid,$did);

    echoRespnse(200, $result);
});

/**
 * USER GET REST API
 */


$app->get('/user/get/data/:uid', function ($user_id) {
    $db = new DbUsers();
    $result = $db->getUser($user_id);
    $response["success"] = true;
    $response["data"] = $result;
    echoRespnse(200, $response);
});

$app->get('/user/job/process/data/:uid', function ($uid) {
    $db = new DbUsers();
    $result = $db->getJobDataByProcess($uid);
    $response["success"] = true;
    $response["data"] = $result;
    echoRespnse(200, $response);
});

$app->get('/user/job/history/data/:uid', function ($uid) {
    $db = new DbUsers();
    $result = $db->getJobDataByHistory($uid);
    $response["success"] = true;
    $response["data"] = $result;
    echoRespnse(200, $response);
});

$app->get('/user/get/favorite/:uid', function ($uid) {
    $db = new DbUsers();
    $result = $db->getDriverFavorite($uid);
    $response["success"] = true;
    $response["data"] = $result;
    echoRespnse(200, $response);
});

/**
 * USER PUT REST API
 */

$app->put('/user/update/fcm/:uid', function ($user_id) use ($app) {
    global $app;

    verifyRequiredParams(array('user_fcm_id'));

    $user_fcm_id = $app->request->put('user_fcm_id');

    $db = new DbUsers();
    $response = $db->updateFcmUser($user_id, $user_fcm_id);

    echoRespnse(200, $response);
});

$app->put('/user/update/data/:uid', function ($user_id) use ($app) {
    verifyRequiredParams(array('fname', 'lname', 'email', 'tel', 'password_old'));
    // reading post params
    $fname = $app->request->post('fname');
    $lname = $app->request->post('lname');
    $email = $app->request->post('email');
    $tel = $app->request->post('tel');
    $img_name = $app->request->post('img_name');
    $img_encode = $app->request->post('img_encode');
    $password_old = $app->request->post('password_old');
    $password_new = $app->request->post('password_new');
    validateEmail($email);
    $db = new DbUsers();
    $response = $db->updateUser($user_id, $fname, $lname, $email, $tel, $img_name, $img_encode, $password_old, $password_new);

    echoRespnse(200, $response);
});

$app->put('/user/update/password/:uid', function ($user_id) use ($app) {
    verifyRequiredParams(array('password_old', 'password_new'));

    $password_old = $app->request->post('password_old');
    $password_new = $app->request->post('password_new');

    $db = new DbUsers();
    $response = $db->updatePasswordUser($user_id, $password_old, $password_new);
    echoRespnse(200, $response);
});

$app->put('/user/update/job/status/:jid', function ($job_id) use ($app) {
    require_once __DIR__ . '/../libs/fcm/fcm.php';
    require_once __DIR__ . '/../libs/fcm/push.php';
    $fcm = new FCM();
    $push = new Push();
    $dbDriver = new DbDriver();
    verifyRequiredParams(array('status_name', 'status_message', 'driver_id'));
    $status_name = $app->request->put('status_name');
    $status_message = $app->request->put('status_message');
    $driver_id = $app->request->put('driver_id');

    $result = $dbDriver->updateJobStatus($job_id, $status_name);
    $driver = $dbDriver->getDriverById($driver_id);
    $regIdDriver = $driver['driver_fcm_id'];

    $title = "รายงานสถานะการดำเนินการ";
    $message = $status_message;
    $payload = array();
    $payload['status'] = "cancel_job";

    $push->setTitle($title);
    $push->setMessage($message);
    $push->setImage('');
    $push->setIsBackground(FALSE);
    $push->setPayload($payload);
    $json = '';
    $json = $push->getPush();

    $fcm->send($regIdDriver, $json);

    echoRespnse(200, $result);
});
$app->put('/user/update/job/status/auto/:jid', function ($job_id) use ($app) {
    require_once __DIR__ . '/../libs/fcm/fcm.php';
    require_once __DIR__ . '/../libs/fcm/push.php';
    verifyRequiredParams(array('status_name', 'status_message', 'driver_id', 'user_id'));
    $status_name = $app->request->put('status_name');
    $status_message = $app->request->put('status_message');
    $driver_id = $app->request->put('driver_id');
    $user_id = $app->request->put('user_id');
    $dbDriver = new DbDriver();
    $dbUser = new DbUsers();
    $result = $dbDriver->updateJobStatus($job_id, $status_name);
    $fcm = new FCM();
    $push = new Push();


    $driver = $dbDriver->getDriverById($driver_id);
    $user = $dbUser->getUserById($user_id);
    $regIdDriver = $driver['driver_fcm_id'];
    $regIdUser = $user['user_fcm_id'];

    $title = "รายงานสถานะการดำเนินการ";
    $message = $status_message;

    $payload = array();
    $payload['status'] = "cancel_job";

    $push->setTitle($title);
    $push->setMessage($message);
    $push->setImage('');
    $push->setIsBackground(FALSE);
    $push->setPayload($payload);
    $json = '';
    $json = $push->getPush();

    $fcm->send($regIdDriver, $json);
    $fcm->send($regIdUser, $json);

    echoRespnse(200, $result);
});

/**
 * DRIVER POST REST API
 */

$app->post('/driver/login', function () use ($app) {

    verifyRequiredParams(array('email', 'password'));

    $email = $app->request->post('email');
    $password = $app->request->post('password');

    validateEmail($email);

    $db = new DbDriver();
    $result = $db->loginDriver($email, $password);

    echoRespnse(200, $result);
});

$app->post('/driver/create', function () use ($app) {

    verifyRequiredParams(array('fname', 'lname', 'email', 'password', 'tel', 'id_card', 'address', 'sex', 'province',
        'typeCar', 'brandCar', 'modelCar', 'colorCar', 'plateCar', 'provincePlateCar', 'liftStatus', 'liftPrice',
        'liftPlusStatus', 'liftPlusPrice', 'cartStatus', 'cartPrice', 'startPrice', 'startKm', 'chargeRate'));

    $fname = $app->request->post('fname');
    $lname = $app->request->post('lname');
    $email = $app->request->post('email');
    $password = $app->request->post('password');
    $tel = $app->request->post('tel');
    $id_card = $app->request->post('id_card');
    $address = $app->request->post('address');
    $sex = $app->request->post('sex');
    $province = $app->request->post('province');

    ////////////////////////////////////////////////

    $typeCar = $app->request->post('typeCar');
    $brandCar = $app->request->post('brandCar');
    $modelCar = $app->request->post('modelCar');
    $colorCar = $app->request->post('colorCar');
    $plateCar = $app->request->post('plateCar');
    $provincePlateCar = $app->request->post('provincePlateCar');
    $liftStatus = $app->request->post('liftStatus');
    $liftPrice = $app->request->post('liftPrice');
    $liftPlusStatus = $app->request->post('liftPlusStatus');
    $liftPlusPrice = $app->request->post('liftPlusPrice');
    $cartStatus = $app->request->post('cartStatus');
    $cartPrice = $app->request->post('cartPrice');
    $startPrice = $app->request->post('startPrice');
    $startKm = $app->request->post('startKm');
    $chargeRate = $app->request->post('chargeRate');

    $db = new DbDriver();

    $result = $db->createDriver($fname, $lname, $email, $password, $tel, $id_card, $address, $sex, $province,
        $typeCar, $brandCar, $modelCar, $colorCar, $plateCar, $provincePlateCar, $liftStatus, $liftPrice,
        $liftPlusStatus, $liftPlusPrice, $cartStatus, $cartPrice, $startPrice, $startKm, $chargeRate);
    echoRespnse(200, $result);
});

$app->post('/driver/position', function () use ($app) {
    $response = array();

    verifyRequiredParams(array('province', 'lat', 'lng', 'distance', 'typeCar'));

    $province = $app->request->post('province');
    $lat = $app->request->post('lat');
    $lng = $app->request->post('lng');
    $distance = $app->request->post('distance');
    $typeCar = $app->request->post('typeCar');

    $db = new DbDriver();

    $result = $db->getDriverDataAfterSort($province, $lat, $lng, $distance, $typeCar);
    //$result = $db->calculateDistancePosition($province, $lat, $lng,  $typeCar);
    $response["success"] = TRUE;
    $response["driver"] = $result;
    echoRespnse(200, $response);
});

$app->post('/driver/notification/:uid', function ($user_id) use ($app) {
    require_once __DIR__ . '/../libs/fcm/fcm.php';
    require_once __DIR__ . '/../libs/fcm/push.php';
    $fcm = new FCM();
    $push = new Push();
    $db2 = new DbUsers();

    verifyRequiredParams(array('body'));
    $body = $app->request->post('body');

    $user = $db2->getUserById($user_id);
    $regIdUser = $user['user_fcm_id'];

    $title = "รายงานสถานะการดำเนินการ";
    $message = $body;
    $payload = array();
    $payload['status'] = "update_job";

    $push->setTitle($title);
    $push->setMessage($message);
    $push->setImage('');
    $push->setIsBackground(FALSE);
    $push->setPayload($payload);
    $json = '';
    $json = $push->getPush();

    $fcm->send($regIdUser, $json);

    $response['success'] = true;
    $response['message'] = "Send Notification Successfully!!!";

    echoRespnse(200, $response);
});
/**
 * DRIVER GET REST API
 */

$app->get('/driver/job/process/data/:did', function ($did) {
    $db = new DbDriver();
    $result = $db->getJobDataByStatus($did);
    $response["success"] = true;
    $response["data"] = $result;
    echoRespnse(200, $response);
});
$app->get('/driver/s/s/:id', function ($did) {
    $db = new DbDriver();
    $result = $db->getProvinceName($did);
    $response["success"] = true;
    $response["data"] = $result;
    echoRespnse(200, $response);
});

$app->get('/driver/job/history/data/:did', function ($did) {
    $db = new DbDriver();
    $result = $db->getJobDataByHistory($did);
    $response["success"] = true;
    $response["data"] = $result;
    echoRespnse(200, $response);
});

$app->get('/driver/job/discard/data/:did', function ($did) {
    $db = new DbDriver();
    $result = $db->getJobDataByDiscard($did);
    $response["success"] = true;
    $response["data"] = $result;
    echoRespnse(200, $response);
});

$app->get('/driver/job/id/:jid', function ($job_id) {
    $db = new DbDriver();
    $result = $db->getJobDataByJobId($job_id);
    $response["success"] = true;
    $response["data"] = $result;
    echoRespnse(200, $response);
});

$app->get('/driver/favorite/user/:uid', function ($uid) {
    $db = new DbDriver();
    $result = $db->getDriverFavorite($uid);
    $response["success"] = true;
    $response["data"] = $result;
    echoRespnse(200, $response);
});

$app->get('/driver/get/comment/:did', function ($did) {
    $db = new DbDriver();
    $result = $db->getCommentAndRatingByDriverId($did);
    $response["success"] = true;
    $response["data"] = $result;
    echoRespnse(200, $response);
});

$app->get('/driver/get/sum/rating/:did', function ($did) {
    $db = new DbDriver();
    $result = $db->getSumRatingArrayByDriverId($did);
    $response["success"] = true;
    $response["data"] = $result;
    echoRespnse(200, $response);
});

/**
 * DRIVER PUT REST API
 */

$app->put('/driver/update/fcm/:did', function ($driver_id) use ($app) {
    global $app;

    verifyRequiredParams(array('driver_fcm_id'));

    $driver_fcm_id = $app->request->put('driver_fcm_id');

    $db = new DbDriver();
    $response = $db->updateFcmDriver($driver_id, $driver_fcm_id);

    echoRespnse(200, $response);
});

$app->put('/driver/update/job/status/:jid', function ($job_id) use ($app) {
    require_once __DIR__ . '/../libs/fcm/fcm.php';
    require_once __DIR__ . '/../libs/fcm/push.php';
    $fcm = new FCM();
    $push = new Push();
    $db2 = new DbUsers();
    $db = new DbDriver();

    verifyRequiredParams(array('status_name', 'status_message', 'user_id'));
    $status_name = $app->request->put('status_name');
    $status_message = $app->request->put('status_message');
    $user_id = $app->request->put('user_id');


    $result = $db->updateJob2Status($job_id, $status_name);

    $user = $db2->getUserById($user_id);
    $regIdUser = $user['user_fcm_id'];

    $title = "รายงานสถานะการดำเนินการ";
    $message = $status_message;
    $payload = array();
    $payload['status'] = "update_job";

    $push->setTitle($title);
    $push->setMessage($message);
    $push->setImage('');
    $push->setIsBackground(FALSE);
    $push->setPayload($payload);
    $json = '';
    $json = $push->getPush();

    $fcm->send($regIdUser, $json);
    echoRespnse(200, $result);
});

$app->put('/driver/update/job/status2/:jid', function ($job_id) use ($app) {
    require_once __DIR__ . '/../libs/fcm/fcm.php';
    require_once __DIR__ . '/../libs/fcm/push.php';
    $db = new DbDriver();
    $db2 = new DbUsers();
    $fcm = new FCM();
    $push = new Push();

    verifyRequiredParams(array('status_name', 'status_message', 'user_id', 'driver_id'));
    $status_name = $app->request->put('status_name');
    $status_message = $app->request->put('status_message');
    $user_id = $app->request->put('user_id');
    $driver_id = $app->request->put('driver_id');

    $result = $db->updateJobStatus($job_id, $status_name);
    $user = $db2->getUserById($user_id);
    $regIdUser = $user['user_fcm_id'];

    $title = "รายงานสถานะการดำเนินการ";
    $message = $status_message;
    $payload = array();
    $payload['jid'] = $job_id;
    $payload['did'] = $driver_id;
    $payload['status'] = "end_job";

    $push->setTitle($title);
    $push->setMessage($message);
    $push->setImage('');
    $push->setIsBackground(FALSE);
    $push->setPayload($payload);
    $json = '';
    $json = $push->getPush();

    $fcm->send($regIdUser, $json);
    echoRespnse(200, $result);
});

$app->put('/driver/update/info/:did', function ($driver_id) use ($app) {
    verifyRequiredParams(array('fname', 'lname', 'email', 'password_old', 'tel', 'id_card', 'address', 'sex', 'province'));
    // reading post params
    $fname = $app->request->post('fname');
    $lname = $app->request->post('lname');
    $email = $app->request->post('email');
    $tel = $app->request->post('tel');
    $id_card = $app->request->post('id_card');
    $address = $app->request->post('address');
    $sex = $app->request->post('sex');
    $province = $app->request->post('province');
    $password_old = $app->request->post('password_old');

    //non required
    $img_driver_name = $app->request->post('img_name');
    $img_encode_string = $app->request->post('img_encode');
    $password_new = $app->request->post('password_new');
    $db = new DbDriver();
    $response = $db->updateDriverInfo($driver_id, $fname, $lname, $email, $tel, $id_card, $address,
        $sex, $province, $img_driver_name, $img_encode_string, $password_old, $password_new);

    echoRespnse(200, $response);
});
$app->put('/driver/update/car/:did', function ($driver_id) use ($app) {
    verifyRequiredParams(array('type_car', 'brand_car', 'model_car', 'color_car', 'plate_car', 'province_plate_car', 'password_old'));
    // reading post params
    $typeCar = $app->request->put('type_car');
    $brandCar = $app->request->put('brand_car');
    $modelCar = $app->request->put('model_car');
    $colorCar = $app->request->put('color_car');
    $plateCar = $app->request->put('plate_car');
    $provincePlateCar = $app->request->put('province_plate_car');
    $password_old = $app->request->put('password_old');

    $db = new DbDriver();
    $response = $db->updateDriverDetailCar($driver_id, $typeCar, $brandCar, $modelCar, $colorCar,
        $plateCar, $provincePlateCar, $password_old);

    echoRespnse(200, $response);
});
$app->put('/driver/update/service/:did', function ($driver_id) use ($app) {
    verifyRequiredParams(array('lift_status', 'lift_price', 'lift_plus_status', 'lift_plus_price', 'cart_status',
        'cart_price', 'start_price', 'start_km', 'charge_rate', 'password_old'));
    // reading post params
    $liftStatus = $app->request->put('lift_status');
    $liftPrice = $app->request->put('lift_price');
    $liftPlusStatus = $app->request->put('lift_plus_status');
    $liftPlusPrice = $app->request->put('lift_plus_price');
    $cartStatus = $app->request->put('cart_status');
    $cartPrice = $app->request->put('cart_price');
    $startPrice = $app->request->put('start_price');
    $startKm = $app->request->put('start_km');
    $chargeRate = $app->request->put('charge_rate');
    $password_old = $app->request->put('password_old');

    $db = new DbDriver();
    $response = $db->updateDriverDetailService($driver_id, $liftStatus, $liftPrice, $liftPlusStatus,
        $liftPlusPrice, $cartStatus, $cartPrice, $startPrice, $startKm, $chargeRate, $password_old);

    echoRespnse(200, $response);
});
$app->put('/driver/update/position/:did', function ($driver_id) use ($app) {
    verifyRequiredParams(array('lat', 'lng'));
    // reading post params
    $lat = $app->request->put('lat');
    $lng = $app->request->put('lng');
    $db = new DbDriver();
    $response = $db->updatePositionService($driver_id, $lat, $lng);

    echoRespnse(200, $response);
});
$app->get('/test/text', function () use ($app) {
    echo "test text/plain";
});
$app->get('/test/comment/all', function () use ($app) {
        $replier = array();
        $replys_data = array();
        $reply_data = array();
        $replys = array();


        $reply_data["fbId"] = "1025235";
        $reply_data["reply"] = "ความคิดเห็นที่ 1";
        $reply_data["timestamp"] = "11:00";
        array_push($replys_data ,$reply_data);
           $reply_data["fbId"] = "223213";
         $reply_data["reply"] = "ความคิดเห็นที่ 2";
        $reply_data["timestamp"] = "11:37";
        array_push($replys_data ,$reply_data);
           $reply_data["fbId"] = "12312311";
         $reply_data["reply"] = "ความคิดเห็นที่ 3";
        $reply_data["timestamp"] = "15:28";
        array_push($replys_data ,$reply_data);
           $reply_data["fbId"] = "3232412";
         $reply_data["reply"] = "ความคิดเห็นที่ 4";
        $reply_data["timestamp"] = "23:00";
        array_push($replys_data ,$reply_data);
        $replys["post"] = "ข้อความที่ 1";
        $replys["replys"] = $replys_data;
        array_push($replier,$replys);


 $replys_data = array();
        $reply_data = array();
        $replys = array();

        $reply_data["fbId"] = "756546546";
        $reply_data["reply"] = "ความคิดเห็นที่ 5";
        $reply_data["timestamp"] = "22:00";
        array_push($replys_data ,$reply_data);
        $reply_data["fbId"] = "546434234";
        $reply_data["reply"] = "ความคิดเห็นที่ 6";
        $reply_data["timestamp"] = "20:37";
        array_push($replys_data ,$reply_data);

        $replys["post"] = "ข้อความที่ 2";
        $replys["replys"] = $replys_data;
        array_push($replier,$replys);

 $replys_data = array();
        $reply_data = array();
        $replys = array();

   $reply_data["fbId"] = "10252424";
        $reply_data["reply"] = "ความคิดเห็นที่ 7";
        $reply_data["timestamp"] = "14:00";
        array_push($replys_data ,$reply_data);
           $reply_data["fbId"] = "2502747";
         $reply_data["reply"] = "ความคิดเห็นที่ 8";
        $reply_data["timestamp"] = "12:37";
        array_push($replys_data ,$reply_data);
           $reply_data["fbId"] = "3712104";
         $reply_data["reply"] = "ความคิดเห็นที่ 9";
        $reply_data["timestamp"] = "09:37";
        array_push($replys_data ,$reply_data);

        $replys["post"] = "ข้อความที่ 3";
        $replys["replys"] = $replys_data;
        array_push($replier,$replys);









        $response = array();
        $response['fbIdOwner'] = "1020202"; 
        $response['replier'] = $replier;
 echoRespnse(200, $response);
});

function verifyRequiredParams($required_fields)
{
    $error = false;
    $error_fields = "";
    $request_params = array();
    $request_params = $_REQUEST;
    // Handling PUT request params
    if ($_SERVER['REQUEST_METHOD'] == 'PUT') {
        $app = \Slim\Slim::getInstance();
        parse_str($app->request()->getBody(), $request_params);
    }
    foreach ($required_fields as $field) {
        if (!isset($request_params[$field]) || strlen(trim($request_params[$field])) <= 0) {
            $error = true;
            $error_fields .= $field . ', ';
        }
    }

    if ($error) {
        // Required field(s) are missing or empty
        // echo error json and stop the app
        $response = array();
        $app = \Slim\Slim::getInstance();
        $response["success"] = FALSE;
        $response["message"] = 'Required field(s) ' . substr($error_fields, 0, -2) . ' is missing or empty';
        echoRespnse(400, $response);
        $app->stop();
    }
}

function validateEmail($email)
{
    $app = \Slim\Slim::getInstance();
    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $response["success"] = FALSE;
        $response["message"] = 'Email address is not valid';
        echoRespnse(400, $response);
        $app->stop();
    }
}

function IsNullOrEmptyString($str)
{
    return (!isset($str) || trim($str) === '');
}

function echoRespnse($status_code, $response)
{
    $app = \Slim\Slim::getInstance();
    // Http response code
    $app->status($status_code);

    // setting response content type to json
    $app->contentType('application/json');

    echo json_encode($response);
}

$app->run();
?>