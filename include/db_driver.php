<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

class DbDriver
{

    private $conn;

    function __construct()
    {
        require_once dirname(__FILE__) . '/db_connect.php';
        $db = new DbConnect();
        $this->conn = $db->connect();
    }

    /**
     *   FUNCTION DRIVER DATA
     */


    public function createDriver($fname, $lname, $email, $password, $tel, $id_card, $address, $sex, $province,
                                 $typeCar, $brandCar, $modelCar, $colorCar, $plateCar, $provincePlateCar, $liftStatus, $liftPrice,
                                 $liftPlusStatus, $liftPlusPrice, $cartStatus, $cartPrice, $startPrice, $startKm, $chargeRate)
    {
        $response = array();
        if (!$this->isDriverExists($email)) {
            // insert query
            $hash = $this->hashSSHA($password);
            $encrypted_password = $hash["encrypted"];
            $salt = $hash["salt"];
            $pr_id = $this->getProvinceId($province);
            $sql = "INSERT INTO driver (driver_first_name,driver_last_name,driver_email,driver_password,driver_salt
                                  ,driver_tel,driver_id_card,driver_address,driver_sex,driver_province) ";
            $sql .= " VALUES (?,?,?,?,?,?,?,?,?,?)";
            $stmt = $this->conn->prepare($sql);
            $stmt->bind_param("sssssssssi", $fname, $lname, $email, $encrypted_password, $salt,
                $tel, $id_card, $address, $sex, $pr_id);
            $result = $stmt->execute();
            if ($result) {

                $driver_id = $this->conn->insert_id;
                $stmt->close();
                $pr_id2 = $this->getProvinceId($provincePlateCar);
                $sql2 = "INSERT INTO driver_detail (driver_detail_type,driver_detail_brand,driver_detail_model,
                                          driver_detail_color,driver_detail_license_plate,driver_detail_province_license_plate,
                                            driver_detail_service_lift_status,driver_detail_service_lift_price,
                                            driver_detail_service_lift_plus_status,driver_detail_service_lift_plus_price,
                                            driver_detail_service_cart_status,driver_detail_service_cart_price,driver_detail_charge_start_price,
                                            driver_detail_charge_start_km,driver_detail_charge,driver_id) ";
                $sql2 .= " VALUES (?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?)";
                $stmt2 = $this->conn->prepare($sql2);
                $stmt2->bind_param("sssssssssssssssi", $typeCar, $brandCar, $modelCar, $colorCar, $plateCar, $pr_id2,
                    $liftStatus, $liftPrice, $liftPlusStatus, $liftPlusPrice, $cartStatus, $cartPrice, $startPrice, $startKm,
                    $chargeRate, $driver_id);
                $result2 = $stmt2->execute();
                if ($result2) {
                    $response["success"] = true;
                    $response["message"] = "ลงทะเบียนสำเร็จ";
                    $response["data"] = $this->getDriverWithDetail($driver_id);

                } else {
                    $response["success"] = false;
                    $response["message"] = "ลงทะเบียนไม่สำเร็จ";
                    $response["data"] = $this->getDriverWithDetail(0);
                }

                $stmt2->close();
            } else {
                $response["success"] = false;
                $response["message"] = "ลงทะเบียนไม่สำเร็จ";
                $response["data"] = $this->getDriverWithDetail(0);
            }

        } else {
            $response["success"] = false;
            $response["message"] = "อีเมลนี้มีอยู่ในระบบแล้ว!!";
            $response["data"] = $this->getDriverWithDetail(0);
        }
        return $response;
    }

    public function loginDriver($email, $password)
    {
        $response = array();
        $isEmail = $this->isDriverExists($email);
        if ($isEmail) {
            $sql = "SELECT driver_id, driver_salt, driver_password FROM driver ";
            $sql .= " WHERE driver_email = ?";
            $stmt = $this->conn->prepare($sql);
            $stmt->bind_param("s", $email);
            $result = $stmt->execute();

            if ($result) {
                $driver = $stmt->get_result()->fetch_assoc();
                $stmt->close();
                //verifying user password
                $did = $driver['driver_id'];
                $salt = $driver['driver_salt'];
                $encrypted_password = $driver['driver_password'];
                $hash = $this->checkhashSSHA($salt, $password);
                //check for password equality
                if ($encrypted_password == $hash) {
                    $response["success"] = true;
                    $response["data"] = $this->getDriverWithDetail($did);
                    $response["message"] = "เข้าสู่ระบบสำเร็จ!!";
                } else {
                    $response["success"] = false;
                    $response["data"] = $this->getDriverWithDetail(0);
                    $response["message"] = "รหัสผ่านไม่ถูกต้อง!!";
                }
            } else {
                $response["success"] = false;
                $response["data"] = $this->getDriverWithDetail(0);
                $response["message"] = "ไม่มีอีเมลนี้อยู่ในระบบ!!";
            }
        } else {
            $response["success"] = false;
            $response["data"] = $this->getDriverWithDetail(0);
            $response["message"] = "ไม่มีอีเมลนี้อยู่ในระบบ!!";
        }
        return $response;
    }

    public function updateDriverInfo($driver_id, $fname, $lname, $email, $tel, $id_card, $address, $sex, $province, $img_driver_name, $img_encode_string, $password_old, $password_new)
    {
        $response = array();
        //add images
        $password_message = "";
        $img_message = $this->SaveImageToDisk($driver_id, $img_driver_name, $img_encode_string);
        //echo "password_new:" . $password_new."<br/>";
        if ($password_new != null && $password_new != "") {
            $password_message = $this->updatePasswordDriver($driver_id, $password_old, $password_new);
        } else {
            $password_message = $this->checkPassword($driver_id, $password_old);
        }

        if ($img_message["success"]) {
            if ($password_message["success"]) {
                $pr_id = $this->getProvinceId($province);
                if ($img_driver_name != null && $img_driver_name != "") {
                    $sql = "UPDATE driver SET driver_first_name = ?, driver_last_name = ?, driver_email = ?, driver_tel = ?,
                            driver_id_card=? ,  driver_address=?, driver_sex= ?,driver_province=?, driver_img_name = ? ";
                    $sql .= " WHERE driver_id = ?";
                    $stmt = $this->conn->prepare($sql);
                    $stmt->bind_param("sssssssssi", $fname, $lname, $email, $tel, $id_card, $address, $sex, $pr_id,
                        $img_driver_name, $driver_id);
                } else {
                    $sql = "UPDATE driver SET driver_first_name = ?, driver_last_name = ?, driver_email = ?, driver_tel = ?,
                            driver_id_card=? ,  driver_address=?, driver_sex= ?,driver_province=? ";
                    $sql .= " WHERE driver_id = ?";
                    $stmt = $this->conn->prepare($sql);
                    $stmt->bind_param("ssssssssi", $fname, $lname, $email, $tel, $id_card, $address, $sex, $pr_id,
                        $driver_id);
                }
                $result = $stmt->execute();
                if ($result) {
                    // User successfully updated
                    $response["success"] = true;
                    $response["message"] = "ปรับปรุงข้อมูลสำเร็จ";
                    $response["data"] = $this->getDriverWithDetail($driver_id);
                } else {
                    // Failed to update user
                    $response["success"] = false;
                    $response["message"] = "ปรับปรุงข้อมูลไม่สำเร็จ";
                    $response["data"] = $this->getDriverWithDetail(0);
                }

                $stmt->close();
            } else {
                $response["success"] = false;
                $response["message"] = $password_message["message"];
                $response["data"] = $this->getDriverWithDetail(0);
            }
        } else {
            $response["success"] = false;
            $response["message"] = "เพิ่มรูปภาพไม่สำเร็จ";
            $response["data"] = $this->getDriverWithDetail(0);
        }
        return $response;
    }

    public function updateDriverDetailCar($driver_id, $typeCar, $brandCar, $modelCar, $colorCar, $plateCar, $provincePlateCar, $password_old)
    {
        $response = array();
        $password_message = $this->checkPassword($driver_id, $password_old);

        if ($password_message["success"]) {
            $pr_id2 = $this->getProvinceId($provincePlateCar);
            $sql = "UPDATE driver_detail SET driver_detail_type = ?, driver_detail_brand=? ,driver_detail_model=? ,driver_detail_color= ?,
                 driver_detail_license_plate=?, driver_detail_province_license_plate=? ";
            $sql .= "  WHERE driver_id=?";
            $stmt = $this->conn->prepare($sql);
            $stmt->bind_param("ssssssi", $typeCar, $brandCar, $modelCar, $colorCar, $plateCar, $pr_id2, $driver_id);
            $result = $stmt->execute();
            if ($result) {
                // User successfully updated
                $response["success"] = true;
                $response["message"] = "ปรับปรุงข้อมูลสำเร็จ";
                $response["data"] = $this->getDriverWithDetail($driver_id);
            } else {
                // Failed to update user
                $response["success"] = false;
                $response["message"] = "ปรับปรุงข้อมูลไม่สำเร็จ";
                $response["data"] = $this->getDriverWithDetail(0);
            }
            $stmt->close();
        } else {
            $response["success"] = false;
            $response["message"] = "รหัสผ่านไม่ถูกต้อง";
            $response["data"] = $this->getDriverWithDetail(0);
        }
        return $response;
    }

    public function updateDriverDetailService($driver_id, $liftStatus, $liftPrice, $liftPlusStatus,
                                              $liftPlusPrice, $cartStatus, $cartPrice, $startPrice, $startKm, $chargeRate, $password_old)
    {
        $response = array();
        $password_message = $this->checkPassword($driver_id, $password_old);

        if ($password_message["success"]) {
            $sql = "UPDATE driver_detail SET driver_detail_service_lift_status = ?, driver_detail_service_lift_price=? ,
driver_detail_service_lift_plus_status=? ,driver_detail_service_lift_plus_price= ?,
                 driver_detail_service_cart_status=?, driver_detail_service_cart_price=? ,
                 driver_detail_charge_start_price=?,driver_detail_charge_start_km=?,
                 driver_detail_charge=? ";
            $sql .= "  WHERE driver_id=?";
            $stmt = $this->conn->prepare($sql);
            $stmt->bind_param("sisisiiiii", $liftStatus, $liftPrice, $liftPlusStatus,
                $liftPlusPrice, $cartStatus, $cartPrice, $startPrice, $startKm, $chargeRate, $driver_id);
            $result = $stmt->execute();
            if ($result) {
                // User successfully updated
                $response["success"] = true;
                $response["message"] = "ปรับปรุงข้อมูลสำเร็จ";
                $response["data"] = $this->getDriverWithDetail($driver_id);
            } else {
                // Failed to update user
                $response["success"] = false;
                $response["message"] = "ปรับปรุงข้อมูลไม่สำเร็จ";
                $response["data"] = $this->getDriverWithDetail(0);
            }

            $stmt->close();
        } else {
            $response["success"] = false;
            $response["message"] = "รหัสผ่านไม่ถูกต้อง";
            $response["data"] = $this->getDriverWithDetail(0);
        }
        return $response;
    }

    public function updatePositionService($driver_id, $lat, $lng)
    {
        $response = array();
        $sql = "UPDATE driver SET driver_position_latitude=?,driver_position_longitude=? WHERE driver_id=?";
        $stmt = $this->conn->prepare($sql);
        $stmt->bind_param("ddi", $lat, $lng, $driver_id);
        $result = $stmt->execute();
        if ($result) {
            $response["success"] = true;
            $response["message"] = "ปรับปรุงข้อมูลสำเร็จ";

        } else {
            $response["success"] = false;
            $response["message"] = "ปรับปรุงข้อมูลไม่สำเร็จ";
        }
        $stmt->close();
        return $response;
    }


    public function updateFcmDriver($driver_id, $driver_fcm_id)
    {
        $response = array();
        $stmt = $this->conn->prepare("UPDATE driver SET driver_fcm_id = ? WHERE driver_id = ?");
        $stmt->bind_param("si", $driver_fcm_id, $driver_id);

        if ($stmt->execute()) {
            // User successfully updated
            $response["success"] = true;
            $response["message"] = 'FCM registration ID updated successfully';
        } else {
            // Failed to update user
            $response["success"] = false;
            $response["message"] = "Failed to update FCM registration ID";
            $stmt->error;
        }
        $stmt->close();

        return $response;
    }

    public function updatePasswordDriver($driver_id, $password_old, $password_new)
    {
        $sql = "SELECT driver_salt, driver_password ";
        $sql .= "FROM driver WHERE driver_id = ?";
        $stmt = $this->conn->prepare($sql);
        $stmt->bind_param("i", $driver_id);
        $result = $stmt->execute();

        if ($result) {
            $response = array();
            $driver = $stmt->get_result()->fetch_assoc();
            $stmt->close();
            $salt = $driver['driver_salt'];
            $encrypted_password = $driver['driver_password'];
            $hash = $this->checkhashSSHA($salt, $password_old);
            // echo " pass " . $encrypted_password."<br/>";
//            echo " hash " . $hash."<br/>";
            if ($encrypted_password == $hash) {
                $hash_new = $this->hashSSHA($password_new);
                $encrypted_password_new = $hash_new["encrypted"];
                $salt_new = $hash_new["salt"];

                $sql = "UPDATE driver SET driver_password = ? , driver_salt = ? WHERE driver_id = ?";
                $stmt = $this->conn->prepare($sql);
                $stmt->bind_param("ssi", $encrypted_password_new, $salt_new, $driver_id);
                if ($stmt->execute()) {
                    $response["success"] = true;
                    $response["message"] = "ปรับปรุงรหัสผ่านสำเร็จ";
                } else {
                    $response["success"] = false;
                    $response["message"] = "ปรับปรุงรหัสผ่านไม่สำเร็จ";
                }
            } else {
                $response["success"] = false;
                $response["message"] = "รหัสผ่านไม่ตรงกัน";
            }
        } else {
            $response["success"] = false;
            $response["message"] = "ปรับปรุงรหัสผ่านไม่สำเร็จ";
        }
        $stmt->close();
        return $response;
    }

    public function SaveImageToDisk($driver_id, $img_driver_name, $img_encode_string)
    {
        $response = array();
        if ($img_encode_string != null && $img_encode_string != "") {
            // TODO: ดึงข้อมูลจากฐานข้อมูลมาลบ

            $sql_img = "SELECT driver_img_name from driver WHERE driver_id = ?";
            $stmt_img = $this->conn->prepare($sql_img);
            $stmt_img->bind_param("i", $driver_id);
            $stmt_img->execute();
            $stmt_img->bind_result($img_name);
            $stmt_img->fetch();

            if ($img_name != null && $img_name != "") {
                $fileold = '../images/driver/' . $img_name;
                //ตรวจสอบว่ามีไฟล์อยู่หรือไหม ถ้ามีลบออกก่อน
                if (file_exists($fileold)) {
                    unlink($fileold);
                }
            }
            $stmt_img->close();
            $img_decode_string = base64_decode($img_encode_string);
            $path = '../images/driver/' . $img_driver_name;
            $file = fopen($path, 'wb');
            $is_written = fwrite($file, $img_decode_string);
            fclose($file);

            if ($is_written > 0) {
                $response["success"] = true;
                $response["message"] = "add image successfully";
            } else {
                $response["success"] = false;
                $response["message"] = "add image not successfully";
            }
        } else {
            $response["success"] = true;
        }
        return $response;
    }

    public function checkPassword($driver_id, $password_old)
    {
        $response = array();
        $sql = "SELECT driver_salt, driver_password ";
        $sql .= "FROM driver WHERE driver_id = ?";
        $stmt = $this->conn->prepare($sql);
        $stmt->bind_param("i", $driver_id);
        $result = $stmt->execute();

        if ($result) {

            $driver = $stmt->get_result()->fetch_assoc();
            $stmt->close();
            $salt = $driver['driver_salt'];
            $encrypted_password = $driver['driver_password'];
            $hash = $this->checkhashSSHA($salt, $password_old);
            //echo $salt;
            if ($encrypted_password == $hash) {
                $response["success"] = true;
                $response["message"] = "รหัสผ่านตรงกัน";
            } else {
                $response["success"] = false;
                $response["message"] = "รหัสผ่านไม่ถูกต้อง";
            }
        } else {
            $response["success"] = false;
            $response["message"] = "ไม่สามารถติดต่อกับฐานข้อมูลได้";
        }
        return $response;
    }

    public function getProvinceId($province_name)
    {
        $data_province = "";
        $sql_province = "SELECT province_id FROM province WHERE province_name = ?";
        $stmt_province = $this->conn->prepare($sql_province);
        $stmt_province->bind_param("s", $province_name);
        if ($stmt_province->execute()) {
            $data_province = $stmt_province->get_result()->fetch_assoc();
        }
        $stmt_province->close();
        return $data_province['province_id'];
    }

    public function getProvinceName($province_id)
    {
        $data_province2 = "";
        $sql_province2 = "SELECT province_name FROM province WHERE province_id =?";
        $stmt_province2 = $this->conn->prepare($sql_province2);
        $stmt_province2->bind_param('s', $province_id);
        if ($stmt_province2->execute()) {
            $data_province2 = $stmt_province2->get_result()->fetch_assoc();
        }

        $stmt_province2->close();
        return $data_province2['province_name'];
    }

    public function getDriverAllByProvince($province, $typeCar)
    {
        $driver = array();
        $sql_province = "SELECT province_id FROM province WHERE province_name = ?";
        $stmt_province = $this->conn->prepare($sql_province);
        $stmt_province->bind_param("s", $province);
        if ($stmt_province->execute()) {
            $data_province = $stmt_province->get_result()->fetch_assoc();
            $sql = "SELECT driver_id, driver_position_latitude, driver_position_longitude ";
            $sql .= "FROM view_data_driver WHERE driver_province = ? and driver_detail_type = ?";
            $stmt = $this->conn->prepare($sql);
            $stmt->bind_param("is", $data_province['province_id'], $typeCar);
            if ($stmt->execute()) {
                $result = $stmt->get_result();
                while ($data = $result->fetch_assoc()) {
                    $tmp = array();
                    $tmp["driver_id"] = $data['driver_id'];
                    $tmp["driver_lat"] = $data['driver_position_latitude'];
                    $tmp["driver_lng"] = $data['driver_position_longitude'];

                    array_push($driver, $tmp);
                }
                $stmt->close();
            }
            $stmt_province->close();
        } else {
            // handle error
        }
        return $driver;
    }

    public function calculateDistancePosition($province, $lat, $lng, $typeCar)
    {
        $driver = $this->getDriverAllByProvince($province, $typeCar);

        $result = array();
        for ($i = 0; $i < count($driver); $i++) {
            $data = $driver[$i];
            $url = "https://maps.googleapis.com/maps/api/distancematrix/json?origins=" . $lat . "," . $lng . "&destinations=" . $data["driver_lat"] . "," . $data["driver_lng"] . "&mode=driving&language=pl-PL";
            $ch = curl_init();
            curl_setopt($ch, CURLOPT_URL, $url);
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
            curl_setopt($ch, CURLOPT_PROXYPORT, 3128);
            curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 0);
            curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, 0);
            $response = curl_exec($ch);
            if ($response === FALSE) {
                die('Curl failed: ' . curl_error($ch));
            }
            curl_close($ch);
            $response_a = json_decode($response, true);
            $distances = $response_a['rows'][0]['elements'][0]['distance']['value'];
            $tmp = array();
            $tmp["driver_id"] = $data["driver_id"];
            $tmp["driver_distances"] = $distances;
            array_push($result, $tmp);
        }
        return $result;
    }

    public function sortByDistances($province, $lat, $lng, $typeCar)
    {
        $data = $this->calculateDistancePosition($province, $lat, $lng, $typeCar);
        $tmp = array();
        $driver = array();
        for ($i = 0; $i < count($data); $i++) {
            $tmp[$data[$i]["driver_id"]] = $data[$i]["driver_distances"];
        }
        asort($tmp);
        foreach ($tmp as $key => $value) {
            $result = array();
            $result["driver_id"] = $key;
            $result["driver_distances"] = $value;

            array_push($driver, $result);
        }
        return $driver;
    }

    public function getDriverDataAfterSort($province, $lat, $lng, $distance, $typeCar)
    {
        $data = $this->sortByDistances($province, $lat, $lng, $typeCar);
        $driver = array();
        for ($i = 0; $i < count($data); $i++) {
            $tmp = array();
            if ($data[$i]["driver_distances"] <= ($distance * 1000)) {
                $tmp = $this->getDriver($data[$i]["driver_id"], $data[$i]["driver_distances"]);

                array_push($driver, $tmp);
            }
        }

        return $driver;
    }

    public function getDriverFavorite($user_id)
    {
        $i = 0;
        $data = array();
        $sql = "SELECT user_id,driver_id FROM driver_favorite WHERE user_id = ?";
        $stmt = $this->conn->prepare($sql);
        $stmt->bind_param("i", $user_id);
        if ($stmt->execute()) {
            $result = $stmt->get_result();
            while ($data2 = $result->fetch_assoc()) {
                $tmp = array();
                $tmp["user_id"] = $data2['user_id'];
                $tmp["driver_id"] = $data2['driver_id'];
                array_push($data, $tmp);
                $i++;
            }
            if ($i == 0) {
                $tmp = array();
                $tmp["user_id"] = null;
                $tmp["driver_id"] = null;
                array_push($data, $tmp);
            }
        } else {
            $tmp = array();
            $tmp["user_id"] = null;
            $tmp["driver_id"] = null;
            array_push($data, $tmp);
        }
        return $data;
    }

    public function getDriver($driver_id, $distance)
    {
        $rating = $this->getSumRatingByDriverId($driver_id);
        $sql = "SELECT  driver_first_name, driver_last_name, driver_email, driver_tel, driver_fcm_id, driver_address, ";
        $sql .= "driver_sex, driver_province , driver_img_name, driver_created_at, driver_change_at, ";
        $sql .= "driver_detail_id,driver_detail_type, driver_detail_brand, driver_detail_model, driver_detail_color, ";
        $sql .= "driver_detail_license_plate, driver_detail_province_license_plate,driver_detail_service_lift_status, driver_detail_service_lift_price, ";
        $sql .= "driver_detail_service_lift_plus_status, driver_detail_service_lift_plus_price, driver_detail_service_cart_status, ";
        $sql .= "driver_detail_service_cart_price, driver_detail_charge_start_price, driver_detail_charge_start_km, driver_detail_charge ";
        $sql .= "FROM view_data_driver WHERE driver_id = ?";
        $stmt = $this->conn->prepare($sql);
        $stmt->bind_param("s", $driver_id);
        if ($stmt->execute()) {
            $stmt->bind_result($fname, $lname, $email, $tel, $fcm_id, $address, $sex, $province, $img_name, $create_at, $change_at,
                $detail_id, $detail_type, $detail_brand, $detail_model, $detail_color, $detail_license_plate, $detail_province_license_plate,
                $detail_service_lift_status, $detail_service_lift_price, $detail_service_lift_plus, $detail_service_lift_plus_price
                , $detail_service_cart, $detail_service_cart_price, $detail_charge_start, $detail_charge_start_km, $detail_charge);
            $stmt->fetch();
            $stmt->close();
            $provinceName = $this->getProvinceName($detail_province_license_plate);
            $tmp = array();
            $tmp["driver_id"] = $driver_id;
            $tmp["driver_fname"] = $fname;
            $tmp["driver_lname"] = $lname;
            $tmp["driver_email"] = $email;
            $tmp["driver_tel"] = $tel;
            $tmp["driver_fcm_id"] = $fcm_id;
            $tmp["driver_address"] = $address;
            $tmp["driver_sex"] = $sex;
            $tmp["driver_province"] = $province;
            $tmp["driver_img_name"] = $img_name;
            $tmp["driver_created_at"] = $create_at;
            $tmp["driver_change_at"] = $change_at;
            $tmp["driver_detail_id"] = $detail_id;
            $tmp["driver_detail_type"] = $detail_type;
            $tmp["driver_detail_brand"] = $detail_brand;
            $tmp["driver_detail_model"] = $detail_model;
            $tmp["driver_detail_color"] = $detail_color;
            $tmp["driver_detail_license_plate"] = $detail_license_plate;
            $tmp["driver_detail_province_license_plate"] = $provinceName;
            $tmp["driver_detail_service_lift_status"] = $detail_service_lift_status;
            $tmp["driver_detail_service_lift_price"] = $detail_service_lift_price;
            $tmp["driver_detail_service_lift_plus_status"] = $detail_service_lift_plus;
            $tmp["driver_detail_service_lift_plus_price"] = $detail_service_lift_plus_price;
            $tmp["driver_detail_service_cart_status"] = $detail_service_cart;
            $tmp["driver_detail_service_cart_price"] = $detail_service_cart_price;
            $tmp["driver_detail_charge_start_price"] = $detail_charge_start;
            $tmp["driver_detail_charge_start_km"] = $detail_charge_start_km;
            $tmp["driver_detail_charge"] = $detail_charge;
            $tmp["driver_distance"] = $distance;
            $tmp["driver_rating_avg"] = $rating["rating_avg"];
            $tmp["driver_rating_count"] = $rating["rating_count"];
            return $tmp;
        } else {
            return null;
        }
    }

    public function getDriverWithDetail($driver_id)
    {

        $data = array();
        $sql = "SELECT  driver_first_name, driver_last_name, driver_email, driver_tel, driver_fcm_id,driver_id_card, driver_address, ";
        $sql .= "driver_sex,driver_position_latitude,driver_position_longitude, driver_province , driver_img_name, driver_created_at, driver_change_at, ";
        $sql .= "driver_detail_id,driver_detail_type, driver_detail_brand, driver_detail_model, driver_detail_color, ";
        $sql .= "driver_detail_license_plate, driver_detail_province_license_plate,driver_detail_service_lift_status,driver_detail_service_lift_price, ";
        $sql .= "driver_detail_service_lift_plus_status, driver_detail_service_lift_plus_price, driver_detail_service_cart_status, ";
        $sql .= "driver_detail_service_cart_price, driver_detail_charge_start_price, driver_detail_charge_start_km, driver_detail_charge ";
        $sql .= "FROM view_data_driver WHERE driver_id = ?";

        $stmt = $this->conn->prepare($sql);
        $stmt->bind_param("i", $driver_id);
        if ($stmt->execute()) {
            $stmt->bind_result($fname, $lname, $email, $tel, $fcm_id, $driver_id_card, $address, $sex, $lat, $lng, $province, $img_name, $create_at, $change_at,
                $detail_id, $detail_type, $detail_brand, $detail_model, $detail_color, $detail_license_plate, $detail_province_license_plate,
                $detail_service_lift_status, $detail_service_lift, $detail_service_lift_plus, $detail_service_lift_plus_price
                , $detail_service_cart, $detail_service_cart_price, $detail_charge_start, $detail_charge_start_km, $detail_charge);
            $stmt->fetch();
            $stmt->close();
            $provinceName = $this->getProvinceName($province);
            $plate_provinceName = $this->getProvinceName($detail_province_license_plate);

            $tmp = array();
            $tmp["driver_id"] = $driver_id;
            $tmp["driver_fname"] = $fname;
            $tmp["driver_lname"] = $lname;
            $tmp["driver_email"] = $email;
            $tmp["driver_tel"] = $tel;
            $tmp["driver_fcm_id"] = $fcm_id;
            $tmp["driver_id_card"] = $driver_id_card;
            $tmp["driver_address"] = $address;
            $tmp["driver_sex"] = $sex;
            $tmp["driver_position_latitude"] = $lat;
            $tmp["driver_position_longitude"] = $lng;
            $tmp["driver_province"] = $provinceName;
            $tmp["driver_img_name"] = $img_name;
            $tmp["driver_created_at"] = $create_at;
            $tmp["driver_change_at"] = $change_at;
            $tmp["driver_detail_id"] = $detail_id;
            $tmp["driver_detail_type"] = $detail_type;
            $tmp["driver_detail_brand"] = $detail_brand;
            $tmp["driver_detail_model"] = $detail_model;
            $tmp["driver_detail_color"] = $detail_color;
            $tmp["driver_detail_license_plate"] = $detail_license_plate;
            $tmp["driver_detail_province_license_plate"] = $plate_provinceName;
            $tmp["driver_detail_service_lift_status"] = $detail_service_lift_status;
            $tmp["driver_detail_service_lift_price"] = $detail_service_lift;
            $tmp["driver_detail_service_lift_plus_status"] = $detail_service_lift_plus;
            $tmp["driver_detail_service_lift_plus_price"] = $detail_service_lift_plus_price;
            $tmp["driver_detail_service_cart_status"] = $detail_service_cart;
            $tmp["driver_detail_service_cart_price"] = $detail_service_cart_price;
            $tmp["driver_detail_charge_start_price"] = $detail_charge_start;
            $tmp["driver_detail_charge_start_km"] = $detail_charge_start_km;
            $tmp["driver_detail_charge"] = $detail_charge;


            array_push($data, $tmp);
            return $data;
        } else {
            return null;
        }

    }

    public function getDriverById($driver_id)
    {
        $sql = "SELECT  driver_first_name, driver_last_name, driver_email, driver_tel, driver_fcm_id, driver_address, ";
        $sql .= "driver_sex, driver_province , driver_img_name, driver_created_at, driver_change_at ";
        $sql .= "FROM view_data_driver WHERE driver_id = ?";
        $stmt = $this->conn->prepare($sql);
        $stmt->bind_param("s", $driver_id);
        if ($stmt->execute()) {
            $stmt->bind_result($fname, $lname, $email, $tel, $fcm_id, $address, $sex, $province, $img_name, $create_at, $change_at);
            $stmt->fetch();
            $tmp = array();
            $tmp["driver_id"] = $driver_id;
            $tmp["driver_fname"] = $fname;
            $tmp["driver_lname"] = $lname;
            $tmp["driver_email"] = $email;
            $tmp["driver_tel"] = $tel;
            $tmp["driver_fcm_id"] = $fcm_id;
            $tmp["driver_address"] = $address;
            $tmp["driver_sex"] = $sex;
            $tmp["driver_province"] = $province;
            $tmp["driver_img_name"] = $img_name;
            $tmp["driver_created_at"] = $create_at;
            $tmp["driver_change_at"] = $change_at;
            return $tmp;
        } else {
            return null;
        }
    }


    public function getCommentAndRatingByDriverId($driver_id)
    {
        $data = array();
        $sql = "SELECT user_id,user_first_name,user_last_name,";
        $sql .= " user_img_name,driver_id,driver_first_name,driver_last_name,driver_img_name,job_comment,job_rating,job_created_at  FROM view_data_job WHERE driver_id = ? and job_rating IS NOT NULL ORDER BY job_id DESC ";
        $stmt = $this->conn->prepare($sql);
        $stmt->bind_param("i", $driver_id);
        if ($stmt->execute()) {
            $result = $stmt->get_result();
            while ($data2 = $result->fetch_assoc()) {
                $tmp = array();
                $tmp["user_id"] = $data2['user_id'];
                $tmp["user_first_name"] = $data2['user_first_name'];
                $tmp["user_last_name"] = $data2['user_last_name'];
                $tmp["user_img_name"] = $data2['user_img_name'];
                $tmp["driver_id"] = $data2['driver_id'];
                $tmp["driver_first_name"] = $data2['driver_first_name'];
                $tmp["driver_last_name"] = $data2['driver_last_name'];
                $tmp["driver_img_name"] = $data2['driver_img_name'];
                $tmp["job_comment"] = $data2['job_comment'];
                $tmp["job_rating"] = $data2['job_rating'];
                $tmp["job_created_at"] = $data2['job_created_at'];
                array_push($data, $tmp);
            }
        }
        return $data;
    }

    public
    function getSumRatingByDriverId($driver_id)
    {
        $data = array();
        $sql = "SELECT AVG(job_rating) as rating_avg, COUNT(job_rating) as rating_count FROM job WHERE driver_id = ? and job_rating IS NOT NULL ";
        $stmt = $this->conn->prepare($sql);
        $stmt->bind_param("i", $driver_id);
        if ($stmt->execute()) {
            $result = $stmt->get_result()->fetch_assoc();
            $data["rating_avg"] = $result['rating_avg'];
            $data["rating_count"] = $result['rating_count'];
            return $data;
        }
        return null;
    }

    public
    function getSumRatingArrayByDriverId($driver_id)
    {
        $data = array();
        $sql = "SELECT AVG(job_rating) as rating_avg, COUNT(job_rating) as rating_count FROM job WHERE driver_id = ? ";
        $stmt = $this->conn->prepare($sql);
        $stmt->bind_param("i", $driver_id);
        if ($stmt->execute()) {
            $result = $stmt->get_result()->fetch_assoc();
            $tmp = array();
            $tmp["rating_avg"] = $result['rating_avg'];
            $tmp["rating_count"] = $result['rating_count'];
            array_push($data, $tmp);
            return $data;
        }
        return null;
    }

    /**
     *   FUNCTION DRIVER JOB
     */

    public
    function updateJobStatus($job_id, $status)
    {
        $response = array();
        $stmt = $this->conn->prepare("UPDATE job SET job_status_name = ? WHERE job_id = ?");
        $stmt->bind_param("si", $status, $job_id);
        if ($stmt->execute()) {
            $response["success"] = true;
            $response["message"] = 'successfully';
        } else {
            // Failed to update user
            $response["success"] = false;
            $response["message"] = "Failed";
            $stmt->error;
        }
        return $response;
    }

    public
    function jobData($user_id)
    {
        $response = array();
        $data = $this->getJobData($user_id);
        if ($data != null) {
            $response["success"] = true;
            $response["data"] = $data;
        } else {
            $response["success"] = false;
            $response["data"] = $this->getJobData(0);
        }
        return $response;

    }

    public
    function getJobDataByJobId($job_id)
    {
        $images = array();
        $jobs = array();
        $sql = "SELECT 
        job_id,
        
        job_from_latitude,job_from_longitude,job_from_name_address,
        job_to_latitude,job_to_longitude,job_to_name_address,
        
        job_time,job_date,
        
        job_service_lift_status,job_service_lift_price,
        job_service_lift_plus_status,job_service_lift_plus_price,
        job_service_cart_status,job_service_cart_price,
        
        job_charge_start_price,job_charge_start_km,job_charge,
        job_status_name,job_distance,job_favorite,
        
        user_id,user_first_name,user_last_name,user_email,user_tel,
         user_img_name,user_fcm_id,driver_detail_type
         FROM view_data_job WHERE job_id = ? ORDER BY job_id DESC";
        $stmt = $this->conn->prepare($sql);
        $stmt->bind_param("i", $job_id);
        if ($stmt->execute()) {
            $result = $stmt->get_result();
            $data = $result->fetch_assoc();
            $sql2 = "SELECT image_job_name FROM image_job WHERE job_id=?";
            $stmt2 = $this->conn->prepare($sql2);
            $stmt2->bind_param("i", $job_id);
            $stmt2->execute();
            $result2 = $stmt2->get_result();
            while ($data2 = $result2->fetch_assoc()) {
                array_push($images, $data2['image_job_name']);
            }
            $tmp = array();
            $tmp["job_id"] = $data['job_id'];
            $tmp["job_from_latitude"] = $data['job_from_latitude'];
            $tmp["job_from_longitude"] = $data['job_from_longitude'];
            $tmp["job_from_name_address"] = $data['job_from_name_address'];

            $tmp["job_to_latitude"] = $data['job_to_latitude'];
            $tmp["job_to_longitude"] = $data['job_to_longitude'];
            $tmp["job_to_name_address"] = $data['job_to_name_address'];

            $tmp["job_time"] = $data['job_time'];
            $tmp["job_date"] = $data['job_date'];

            $tmp["job_service_lift_status"] = $data['job_service_lift_status'];
            $tmp["job_service_lift_price"] = $data['job_service_lift_price'];

            $tmp["job_service_lift_plus_status"] = $data['job_service_lift_plus_status'];
            $tmp["job_service_lift_plus_price"] = $data['job_service_lift_plus_price'];

            $tmp["job_service_cart_status"] = $data['job_service_cart_status'];
            $tmp["job_service_cart_price"] = $data['job_service_cart_price'];

            $tmp["job_charge_start_price"] = $data['job_charge_start_price'];
            $tmp["job_charge_start_km"] = $data['job_charge_start_km'];
            $tmp["job_charge"] = $data['job_charge'];

            $tmp["job_status_name"] = $data['job_status_name'];
            $tmp["job_distance"] = $data['job_distance'];
            $tmp["job_favorite"] = $data['job_favorite'];

            $tmp["user_id"] = $data['user_id'];
            $tmp["user_first_name"] = $data['user_first_name'];
            $tmp["user_last_name"] = $data['user_last_name'];
            $tmp["user_email"] = $data['user_email'];
            $tmp["user_tel"] = $data['user_tel'];
            $tmp["user_img_name"] = $data['user_img_name'];
            $tmp["user_fcm_id"] = $data['user_fcm_id'];

            $tmp["driver_detail_type"] = $data['driver_detail_type'];
            $tmp["job_img_1"] = "";
            $tmp["job_img_2"] = "";
            $tmp["job_img_3"] = "";
            if (count($images) > 0) {
                for ($i = 0; $i < count($images); $i++) {
                    if ($i == 0)
                        $tmp["job_img_1"] = $images[$i];
                    if ($i == 1)
                        $tmp["job_img_2"] = $images[$i];
                    if ($i == 2)
                        $tmp["job_img_3"] = $images[$i];
                }
            }
            array_push($jobs, $tmp);
        }

        $stmt->close();
        return $jobs;
    }

    public
    function getJobDataByStatus($driver_id)
    {
        $jobs = array();
        $sql = "SELECT 
        job_id,
        
        job_from_latitude,job_from_longitude,job_from_name_address,
        job_to_latitude,job_to_longitude,job_to_name_address,
        
        job_time,job_date,
        
        job_service_lift_status,job_service_lift_price,
        job_service_lift_plus_status,job_service_lift_plus_price,
        job_service_cart_status,job_service_cart_price,
        
        job_charge_start_price,job_charge_start_km,job_charge,
        job_status_name,job_distance,job_created_at,job_favorite,
        
        user_id,user_first_name,user_last_name,user_email,user_tel,
         user_img_name,user_fcm_id,driver_detail_type
         FROM view_data_job WHERE driver_id = ? and  (job_status_name='รอการยืนยัน' OR job_status_name='รอการดำเนินการ')  ORDER BY job_id DESC";
        $stmt = $this->conn->prepare($sql);
        $stmt->bind_param("i", $driver_id);
        if ($stmt->execute()) {
            $result = $stmt->get_result();
            while ($data = $result->fetch_assoc()) {
                $tmp = array();
                $tmp["job_id"] = $data['job_id'];
                $tmp["job_from_latitude"] = $data['job_from_latitude'];
                $tmp["job_from_longitude"] = $data['job_from_longitude'];
                $tmp["job_from_name_address"] = $data['job_from_name_address'];

                $tmp["job_to_latitude"] = $data['job_to_latitude'];
                $tmp["job_to_longitude"] = $data['job_to_longitude'];
                $tmp["job_to_name_address"] = $data['job_to_name_address'];

                $tmp["job_time"] = $data['job_time'];
                $tmp["job_date"] = $data['job_date'];

                $tmp["job_service_lift_status"] = $data['job_service_lift_status'];
                $tmp["job_service_lift_price"] = $data['job_service_lift_price'];

                $tmp["job_service_lift_plus_status"] = $data['job_service_lift_plus_status'];
                $tmp["job_service_lift_plus_price"] = $data['job_service_lift_plus_price'];

                $tmp["job_service_cart_status"] = $data['job_service_cart_status'];
                $tmp["job_service_cart_price"] = $data['job_service_cart_price'];

                $tmp["job_charge_start_price"] = $data['job_charge_start_price'];
                $tmp["job_charge_start_km"] = $data['job_charge_start_km'];
                $tmp["job_charge"] = $data['job_charge'];

                $tmp["job_status_name"] = $data['job_status_name'];
                $tmp["job_distance"] = $data['job_distance'];
                $tmp["job_created_at"] = $data['job_created_at'];
                $tmp["job_favorite"] = $data['job_favorite'];

                $tmp["user_id"] = $data['user_id'];
                $tmp["user_first_name"] = $data['user_first_name'];
                $tmp["user_last_name"] = $data['user_last_name'];
                $tmp["user_email"] = $data['user_email'];
                $tmp["user_tel"] = $data['user_tel'];
                $tmp["user_img_name"] = $data['user_img_name'];
                $tmp["user_fcm_id"] = $data['user_fcm_id'];

                $tmp["driver_detail_type"] = $data['driver_detail_type'];
                array_push($jobs, $tmp);
            }
        }
        $stmt->close();
        return $jobs;
    }

    public
    function getJobDataByHistory($driver_id)
    {
        $jobs = array();
        $sql = "SELECT 
        job_id,
        
        job_from_latitude,job_from_longitude,job_from_name_address,
        job_to_latitude,job_to_longitude,job_to_name_address,
        
        job_time,job_date,
        
        job_service_lift_status,job_service_lift_price,
        job_service_lift_plus_status,job_service_lift_plus_price,
        job_service_cart_status,job_service_cart_price,
        
        job_charge_start_price,job_charge_start_km,job_charge,
        job_status_name,job_distance,job_favorite,
        
        user_id,user_first_name,user_last_name,user_email,user_tel,
         user_img_name,user_fcm_id,driver_detail_type
         FROM view_data_job WHERE driver_id = ? and job_status_name='เสร็จสิ้น'  ORDER BY job_id DESC";
        $stmt = $this->conn->prepare($sql);
        $stmt->bind_param("i", $driver_id);
        if ($stmt->execute()) {
            $result = $stmt->get_result();
            while ($data = $result->fetch_assoc()) {
                $tmp = array();
                $tmp["job_id"] = $data['job_id'];
                $tmp["job_from_latitude"] = $data['job_from_latitude'];
                $tmp["job_from_longitude"] = $data['job_from_longitude'];
                $tmp["job_from_name_address"] = $data['job_from_name_address'];

                $tmp["job_to_latitude"] = $data['job_to_latitude'];
                $tmp["job_to_longitude"] = $data['job_to_longitude'];
                $tmp["job_to_name_address"] = $data['job_to_name_address'];

                $tmp["job_time"] = $data['job_time'];
                $tmp["job_date"] = $data['job_date'];

                $tmp["job_service_lift_status"] = $data['job_service_lift_status'];
                $tmp["job_service_lift_price"] = $data['job_service_lift_price'];

                $tmp["job_service_lift_plus_status"] = $data['job_service_lift_plus_status'];
                $tmp["job_service_lift_plus_price"] = $data['job_service_lift_plus_price'];

                $tmp["job_service_cart_status"] = $data['job_service_cart_status'];
                $tmp["job_service_cart_price"] = $data['job_service_cart_price'];

                $tmp["job_charge_start_price"] = $data['job_charge_start_price'];
                $tmp["job_charge_start_km"] = $data['job_charge_start_km'];
                $tmp["job_charge"] = $data['job_charge'];

                $tmp["job_status_name"] = $data['job_status_name'];
                $tmp["job_distance"] = $data['job_distance'];
                $tmp["job_favorite"] = $data['job_favorite'];

                $tmp["user_id"] = $data['user_id'];
                $tmp["user_first_name"] = $data['user_first_name'];
                $tmp["user_last_name"] = $data['user_last_name'];
                $tmp["user_email"] = $data['user_email'];
                $tmp["user_tel"] = $data['user_tel'];
                $tmp["user_img_name"] = $data['user_img_name'];
                $tmp["user_fcm_id"] = $data['user_fcm_id'];

                $tmp["driver_detail_type"] = $data['driver_detail_type'];
                array_push($jobs, $tmp);
            }
        }
        $stmt->close();
        return $jobs;
    }

    public
    function getJobDataByDiscard($driver_id)
    {
        $jobs = array();
        $sql = "SELECT 
        job_id,
        
        job_from_latitude,job_from_longitude,job_from_name_address,
        job_to_latitude,job_to_longitude,job_to_name_address,
        
        job_time,job_date,
        
        job_service_lift_status,job_service_lift_price,
        job_service_lift_plus_status,job_service_lift_plus_price,
        job_service_cart_status,job_service_cart_price,
        
        job_charge_start_price,job_charge_start_km,job_charge,
        job_status_name,job_distance,job_favorite,
        
        user_id,user_first_name,user_last_name,user_email,user_tel,
         user_img_name,user_fcm_id,driver_detail_type
         FROM view_data_job WHERE driver_id = ? and job_status_name='ยกเลิก'  ORDER BY job_id DESC";
        $stmt = $this->conn->prepare($sql);
        $stmt->bind_param("i", $driver_id);
        if ($stmt->execute()) {
            $result = $stmt->get_result();
            while ($data = $result->fetch_assoc()) {
                $tmp = array();
                $tmp["job_id"] = $data['job_id'];
                $tmp["job_from_latitude"] = $data['job_from_latitude'];
                $tmp["job_from_longitude"] = $data['job_from_longitude'];
                $tmp["job_from_name_address"] = $data['job_from_name_address'];

                $tmp["job_to_latitude"] = $data['job_to_latitude'];
                $tmp["job_to_longitude"] = $data['job_to_longitude'];
                $tmp["job_to_name_address"] = $data['job_to_name_address'];

                $tmp["job_time"] = $data['job_time'];
                $tmp["job_date"] = $data['job_date'];

                $tmp["job_service_lift_status"] = $data['job_service_lift_status'];
                $tmp["job_service_lift_price"] = $data['job_service_lift_price'];

                $tmp["job_service_lift_plus_status"] = $data['job_service_lift_plus_status'];
                $tmp["job_service_lift_plus_price"] = $data['job_service_lift_plus_price'];

                $tmp["job_service_cart_status"] = $data['job_service_cart_status'];
                $tmp["job_service_cart_price"] = $data['job_service_cart_price'];

                $tmp["job_charge_start_price"] = $data['job_charge_start_price'];
                $tmp["job_charge_start_km"] = $data['job_charge_start_km'];
                $tmp["job_charge"] = $data['job_charge'];

                $tmp["job_status_name"] = $data['job_status_name'];
                $tmp["job_distance"] = $data['job_distance'];
                $tmp["job_favorite"] = $data['job_favorite'];

                $tmp["user_id"] = $data['user_id'];
                $tmp["user_first_name"] = $data['user_first_name'];
                $tmp["user_last_name"] = $data['user_last_name'];
                $tmp["user_email"] = $data['user_email'];
                $tmp["user_tel"] = $data['user_tel'];
                $tmp["user_img_name"] = $data['user_img_name'];
                $tmp["user_fcm_id"] = $data['user_fcm_id'];

                $tmp["driver_detail_type"] = $data['driver_detail_type'];
                array_push($jobs, $tmp);
            }
        }
        $stmt->close();
        return $jobs;
    }

    public
    function getJobData($driver_id)
    {
        $jobs = array();
        $sql = "SELECT 
        job_id,
        
        job_from_latitude,job_from_longitude,job_from_name_address,
        job_to_latitude,job_to_longitude,job_to_name_address,
        
        job_time,job_date,
        
        job_service_lift_status,job_service_lift_price,
        job_service_lift_plus_status,job_service_lift_plus_price,
        job_service_cart_status,job_service_cart_price,
        
        job_charge_start_price,job_charge_start_km,job_charge,
        job_status_name,job_distance,job_favorite,
        
        user_id,user_first_name,user_last_name,user_email,user_tel,
         user_img_name,user_fcm_id,driver_detail_type
         FROM view_data_job WHERE driver_id = ? ORDER BY job_id DESC";
        $stmt = $this->conn->prepare($sql);
        $stmt->bind_param("i", $driver_id);
        if ($stmt->execute()) {
            $result = $stmt->get_result();
            while ($data = $result->fetch_assoc()) {
                $tmp = array();
                $tmp["job_id"] = $data['job_id'];
                $tmp["job_from_latitude"] = $data['job_from_latitude'];
                $tmp["job_from_longitude"] = $data['job_from_longitude'];
                $tmp["job_from_name_address"] = $data['job_from_name_address'];

                $tmp["job_to_latitude"] = $data['job_to_latitude'];
                $tmp["job_to_longitude"] = $data['job_to_longitude'];
                $tmp["job_to_name_address"] = $data['job_to_name_address'];

                $tmp["job_time"] = $data['job_time'];
                $tmp["job_date"] = $data['job_date'];

                $tmp["job_service_lift_status"] = $data['job_service_lift_status'];
                $tmp["job_service_lift_price"] = $data['job_service_lift_price'];

                $tmp["job_service_lift_plus_status"] = $data['job_service_lift_plus_status'];
                $tmp["job_service_lift_plus_price"] = $data['job_service_lift_plus_price'];

                $tmp["job_service_cart_status"] = $data['job_service_cart_status'];
                $tmp["job_service_cart_price"] = $data['job_service_cart_price'];

                $tmp["job_charge_start_price"] = $data['job_charge_start_price'];
                $tmp["job_charge_start_km"] = $data['job_charge_start_km'];
                $tmp["job_charge"] = $data['job_charge'];

                $tmp["job_status_name"] = $data['job_status_name'];
                $tmp["job_distance"] = $data['job_distance'];
                $tmp["job_favorite"] = $data['job_favorite'];

                $tmp["user_id"] = $data['user_id'];
                $tmp["user_first_name"] = $data['user_first_name'];
                $tmp["user_last_name"] = $data['user_last_name'];
                $tmp["user_email"] = $data['user_email'];
                $tmp["user_tel"] = $data['user_tel'];
                $tmp["user_img_name"] = $data['user_img_name'];
                $tmp["user_fcm_id"] = $data['user_fcm_id'];

                $tmp["driver_detail_type"] = $data['driver_detail_type'];
                array_push($jobs, $tmp);
            }
        }
        $stmt->close();
        return $jobs;
    }


    /**
     * FUNCTION UNITIES
     */

    private
    function isDriverExists($email)
    {
        $stmt = $this->conn->prepare("SELECT driver_id from driver WHERE driver_email = ?");
        $stmt->bind_param("s", $email);
        $stmt->execute();
        $stmt->store_result();
        $num_rows = $stmt->num_rows;
        $stmt->close();

        return $num_rows > 0;
    }

//function hash password
    public
    function hashSSHA($password)
    {
        $salt = sha1(rand());
        $salt = substr($salt, 0, 10);
        $encrypted = base64_encode(sha1($password . $salt, true) . $salt);
        $hash = array("salt" => $salt, "encrypted" => $encrypted);
        return $hash;
    }

//function check hash password
    public
    function checkhashSSHA($salt, $password)
    {
        $hash = base64_encode(sha1($password . $salt, true) . $salt);
        return $hash;
    }

}

?>

