<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

class DbUsers
{

    private $conn;

    function __construct()
    {
        require_once dirname(__FILE__) . '/db_connect.php';
        $db = new DbConnect();
        $this->conn = $db->connect();
    } 

    /**
     *   FUNCTION USER DATA
     */

    public function createUser($fname, $lname, $email, $password, $tel)
    {
        $response = array();

        // First check if user already existed in db
        if (!$this->isUserExists($email)) {
            // insert query
            $hash = $this->hashSSHA($password);
            $encrypted_password = $hash["encrypted"];
            $salt = $hash["salt"];

            $sql = "INSERT INTO user(user_first_name,user_last_name,user_email, user_password, user_salt, user_tel) ";
            $sql .= "values(?,?,?, ?, ?, ?)";
            $stmt = $this->conn->prepare($sql);
            $stmt->bind_param("ssssss", $fname, $lname, $email, $encrypted_password, $salt, $tel);
            $result = $stmt->execute();
            $user_id = $this->conn->insert_id;
            // Check for successful insertion
            if ($result) {
                // User successfully inserted
                $response["success"] = true;
                $response["message"] = "ลงทะเบียนสำเร็จ";
                $response["user"] = $this->getUser($user_id);
            } else {
                // Failed to create user
                $response["success"] = false;
                $response["message"] = "ลงทะเบียนไม่สำเร็จ";
                $response["user"] = $this->getUser(0);
            }
        } else {
            // User with same email already existed in the db
            $response["success"] = false;
            $response["message"] = "อีเมลนี้มีอยู่ในระบบแล้ว!!";
            $response["user"] = $this->getUser(0);
        }
        return $response;
    }

    public function loginUser($email, $password)
    {
        $response = array();
        $sql = "SELECT user_id, user_salt, user_password FROM user ";
        $sql .= "WHERE user_email = ?";
        $stmt = $this->conn->prepare($sql);
        $stmt->bind_param("s", $email);
        $result = $stmt->execute();

        if ($result) {
            $user = $stmt->get_result()->fetch_assoc();
            $stmt->close();
            //verifying user password
            $uid = $user['user_id'];
            $salt = $user['user_salt'];
            $encrypted_password = $user['user_password'];
            $hash = $this->checkhashSSHA($salt, $password);
            //check for password equality
            if ($encrypted_password == $hash) {
                $response["success"] = true;
                $response["user"] = $this->getUser($uid);
            } else {
                $response["success"] = false;
                $response["user"] = $this->getUser(0);
            }
        } else {
            $response["success"] = false;
            $response["user"] = $this->getUser(0);
        }
        return $response;
    }

    public function updateUser($user_id, $fname, $lname, $email, $tel, $img_user_name, $img_encode_string, $password_old, $password_new)
    {
        $response = array();

        //add images
        $password_message = "";
        $img_message = $this->SaveImageToDisk($user_id, $img_user_name, $img_encode_string);
        //echo "password_new:" . $password_new."<br/>";
        if ($password_new != null && $password_new != "") {
            $password_message = $this->updatePasswordUser($user_id, $password_old, $password_new);
        } else {
            $password_message = $this->checkPassword($user_id, $password_old);
        }

        if ($img_message["success"]) {
            if ($password_message["success"]) {
                if ($img_user_name != null && $img_user_name != "") {
                    $sql = "UPDATE user SET user_first_name = ?, user_last_name = ?, user_email = ?, user_tel = ? , user_img_name = ? ";
                    $sql .= "WHERE user_id = ?";
                    $stmt = $this->conn->prepare($sql);
                    $stmt->bind_param("sssssi", $fname, $lname, $email, $tel, $img_user_name, $user_id);
                } else {
                    $sql = "UPDATE user SET user_first_name = ?, user_last_name = ?, user_email = ?, user_tel = ? ";
                    $sql .= "WHERE user_id = ?";
                    $stmt = $this->conn->prepare($sql);
                    $stmt->bind_param("ssssi", $fname, $lname, $email, $tel, $user_id);
                }
                $result = $stmt->execute();
                if ($result) {
                    // User successfully updated
                    $response["success"] = true;
                    $response["message"] = "ปรับปรุงข้อมูลสำเร็จ";
                    $response["user"] = $this->getUser($user_id);
                } else {
                    // Failed to update user
                    $response["success"] = false;
                    $response["message"] = "ปรับปรุงข้อมูลไม่สำเร็จ";
                    $response["user"] = $this->getUser(0);
                }

                $stmt->close();
            } else {
                $response["success"] = false;
                $response["message"] = $password_message["message"];
                $response["user"] = $this->getUser(0);
            }
        } else {
            $response["success"] = false;
            $response["message"] = "เพิ่มรูปภาพไม่สำเร็จ";
            $response["user"] = $this->getUser(0);
        }
        return $response;
    }

    public function updateFcmUser($user_id, $user_fcm_id)
    {
        $response = array();
        $stmt = $this->conn->prepare("UPDATE user SET user_fcm_id = ? WHERE user_id = ?");
        $stmt->bind_param("si", $user_fcm_id, $user_id);

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

    public function SaveImageToDisk($user_id, $img_user_name, $img_encode_string)
    {
        $response = array();
        if ($img_encode_string != null && $img_encode_string != "") {
            // TODO: ดึงข้อมูลจากฐานข้อมูลมาลบ

            $sql_img = "SELECT user_img_name from user WHERE user_id = ?";
            $stmt_img = $this->conn->prepare($sql_img);
            $stmt_img->bind_param("i", $user_id);
            $stmt_img->execute();
            $stmt_img->bind_result($img_name);
            $stmt_img->fetch();

            if ($img_name != null && $img_name != "") {
                $fileold = '../images/users/' . $img_name;
                //ตรวจสอบว่ามีไฟล์อยู่หรือไหม ถ้ามีลบออกก่อน
                if (file_exists($fileold)) {
                    unlink($fileold);
                }
            }
            $stmt_img->close();
            $img_decode_string = base64_decode($img_encode_string);
            $path = '../images/users/' . $img_user_name;
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

    public function SaveMultiImageToDisk($job_id, $img_encode_string)
    {
        $count = count($img_encode_string);
        $status = 0;
        $response = array();
        if ($count > 0) {
            for ($i = 0; $i < $count; $i++) {
                $sql = "INSERT INTO image_job(image_job_name,job_id) VALUES (?,?)";
                $stmt = $this->conn->prepare($sql);
                $img_name = $job_id . "_" . $i . ".jpg";
                $stmt->bind_param("si", $img_name, $job_id);
                $stmt->execute();
                $img_decode_string = base64_decode($img_encode_string[$i]);
                $path = '../images/jobs/' . $img_name;
                $file = fopen($path, 'wb');
                $is_written = fwrite($file, $img_decode_string);
                fclose($file);
                if ($is_written > 0) {
                    $status++;
                }
            }

            /*  $sql_img = "SELECT user_img_name from user WHERE user_id = ?";
              $stmt_img = $this->conn->prepare($sql_img);
              $stmt_img->bind_param("i", $user_id);
              $stmt_img->execute();
              $stmt_img->bind_result($img_name);
              $stmt_img->fetch();
              if ($img_name != null && $img_name != "") {
                  $fileold = '../images/users/' . $img_name;
                  //ตรวจสอบว่ามีไฟล์อยู่หรือไหม ถ้ามีลบออกก่อน
                  if (file_exists($fileold)) {
                      unlink($fileold);
                  }
              }
              $stmt_img->close();*/


            if ($status == $count) {
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

    public function checkPassword($user_id, $password_old)
    {
        $response = array();
        $sql = "SELECT user_salt, user_password ";
        $sql .= "FROM user WHERE user_id = ?";
        $stmt = $this->conn->prepare($sql);
        $stmt->bind_param("i", $user_id);
        $result = $stmt->execute();

        if ($result) {

            $user = $stmt->get_result()->fetch_assoc();
            $stmt->close();
            $salt = $user['user_salt'];
            $encrypted_password = $user['user_password'];
            $hash = $this->checkhashSSHA($salt, $password_old);
            //echo $salt;
            if ($encrypted_password == $hash) {
                $response["success"] = true;
                $response["message"] = "Password Matching";
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

    public function updatePasswordUser($user_id, $password_old, $password_new)
    {
        $sql = "SELECT user_salt, user_password ";
        $sql .= "FROM user WHERE user_id = ?";
        $stmt = $this->conn->prepare($sql);
        $stmt->bind_param("i", $user_id);
        $result = $stmt->execute();

        if ($result) {
            $response = array();
            $user = $stmt->get_result()->fetch_assoc();
            $stmt->close();
            $salt = $user['user_salt'];
            $encrypted_password = $user['user_password'];
            $hash = $this->checkhashSSHA($salt, $password_old);
            // echo " pass " . $encrypted_password."<br/>";
//            echo " hash " . $hash."<br/>";
            if ($encrypted_password == $hash) {
                $hash_new = $this->hashSSHA($password_new);
                $encrypted_password_new = $hash_new["encrypted"];
                $salt_new = $hash_new["salt"];

                $sql = "UPDATE user SET user_password = ? , user_salt = ? WHERE user_id = ?";
                $stmt = $this->conn->prepare($sql);
                $stmt->bind_param("ssi", $encrypted_password_new, $salt_new, $user_id);
                if ($stmt->execute()) {
                    $response["success"] = true;
                    $response["message"] = "Update Password Successfully";
                } else {
                    $response["success"] = false;
                    $response["message"] = "Update Password not successfully1";
                }
            } else {
                $response["success"] = false;
                $response["message"] = "Update Password not successfully2";
            }
        } else {
            $response["success"] = false;
            $response["message"] = "Update Password not successfully3";
        }

        return $response;
    }

    public function getUser($user_id)
    {
        $users = array();
        $sql = "SELECT user_first_name, user_last_name, user_email , user_tel, ";
        $sql .= "user_fcm_id, user_img_name, user_created_at, user_change_at ";
        $sql .= "FROM user WHERE user_id = ?";
        $stmt = $this->conn->prepare($sql);
        $stmt->bind_param("i", $user_id);
        if ($stmt->execute()) {
            $stmt->bind_result($fname, $lname, $email, $tel, $fcm_id, $img_name, $created_at, $user_change_at);
            $stmt->fetch();

            $user = array();
            $user["user_id"] = $user_id;
            $user["user_fname"] = $fname;
            $user["user_lname"] = $lname;
            $user["user_email"] = $email;
            $user["user_tel"] = $tel;
            $user["user_fcm_id"] = $fcm_id;
            $user["user_created_at"] = $created_at;
            $user["user_change_at"] = $user_change_at;
            $user["user_img_name"] = $img_name;
            $stmt->close();
            array_push($users, $user);
        }
        return $users;
    }

    public function getUserById($user_id)
    {
        $user = array();
        $sql = "SELECT user_first_name, user_last_name, user_email , user_tel, ";
        $sql .= "user_fcm_id, user_img_name, user_created_at, user_change_at ";
        $sql .= "FROM user WHERE user_id = ?";
        $stmt = $this->conn->prepare($sql);
        $stmt->bind_param("i", $user_id);
        if ($stmt->execute()) {
            $stmt->bind_result($fname, $lname, $email, $tel, $fcm_id, $img_name, $created_at, $user_change_at);
            $stmt->fetch();

            $user["user_id"] = $user_id;
            $user["user_fname"] = $fname;
            $user["user_lname"] = $lname;
            $user["user_email"] = $email;
            $user["user_tel"] = $tel;
            $user["user_fcm_id"] = $fcm_id;
            $user["user_created_at"] = $created_at;
            $user["user_change_at"] = $user_change_at;
            $user["user_img_name"] = $img_name;
            $stmt->close();

        }
        return $user;
    }

    public function getDriverFavorite($user_id)
    {
        $driver = array();
        $sql = "SELECT driver_first_name,driver_last_name,driver_img_name,driver_id,user_id,driver_detail_type FROM view_data_driver_favorite ";
        $sql .= " WHERE user_id = ?";
        $stmt = $this->conn->prepare($sql);
        $stmt->bind_param("i", $user_id);
        if ($stmt->execute()) {
            $result = $stmt->get_result();
            while ($data = $result->fetch_assoc()) {
                $sql2 = "SELECT avg(job_rating) as rating_avg,count(*) as rating_count FROM job WHERE driver_id = ?";
                $stmt2 = $this->conn->prepare($sql2);
                $stmt2->bind_param("i", $data['driver_id']);
                if ($stmt2->execute()) {
                    $data2 = $stmt2->get_result()->fetch_assoc();
                    $tmp = array();
                    $tmp["driver_first_name"] = $data['driver_first_name'];
                    $tmp["driver_last_name"] = $data['driver_last_name'];
                    $tmp["driver_img_name"] = $data['driver_img_name'];
                    $tmp["driver_id"] = $data['driver_id'];
                    $tmp["user_id"] = $data['user_id'];
                    $tmp["driver_detail_type"] = $data['driver_detail_type'];
                    $tmp["rating_avg"] = $data2['rating_avg'];
                    $tmp["rating_count"] = $data2['rating_count'];
                    array_push($driver, $tmp);
                }
            }
        }
        return $driver;
    }


    /**
     * FUNCTION USER JOB
     */


    public function insertJob($fr_lat, $fr_lng, $fr_address, $to_lat, $to_lng, $to_address, $time, $date, $lift_status,
                              $lift_price, $lift_plus_status, $lift_plus_price, $cart_status, $cart_price, $charge_start_price,
                              $charge_start_km, $charge_rate, $job_status, $job_distance, $images, $user_id, $driver_id)
    {

        $response = array();
        $sql = "INSERT INTO job(job_from_latitude, job_from_longitude,job_from_name_address,
 job_to_latitude, job_to_longitude,job_to_name_address,
 job_time, job_date, job_service_lift_status,job_service_lift_price, job_service_lift_plus_status, 
 job_service_lift_plus_price, job_service_cart_status,job_service_cart_price, job_charge_start_price, job_charge_start_km,
  job_charge, job_status_name,job_distance, user_id, driver_id) VALUES (?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,? ) ";
        $stmt = $this->conn->prepare($sql);
        $stmt->bind_param("ddsddssssisisiiiisdii", $fr_lat, $fr_lng, $fr_address, $to_lat, $to_lng, $to_address, $time, $date, $lift_status,
            $lift_price, $lift_plus_status, $lift_plus_price, $cart_status, $cart_price, $charge_start_price,
            $charge_start_km, $charge_rate, $job_status, $job_distance, $user_id, $driver_id);
        $result = $stmt->execute();

        if ($result) {
            $job_id = $this->conn->insert_id;
            $img_message = $this->SaveMultiImageToDisk($job_id, $images);
            if ($img_message["success"]) {
                $response["success"] = true;
                $response["data"] = $job_id;
                $response["message"] = "success img_message: ";
            } else {
                $response["success"] = false;
                $response["data"] = 0;
                $response["message"] = "not success";
            }


        } else {
            $response["success"] = false;
            $response["data"] = 0;
            $response["message"] = "not success";
        }
        return $response;
    }

    public function deleteDriverFavorite($uid, $did)
    {
        $response = array();
        $sql2 = "DELETE FROM driver_favorite WHERE user_id = ? and driver_id = ?";
        $stmt2 = $this->conn->prepare($sql2);
        $stmt2->bind_param("ii", $uid, $did);
        $result2 = $stmt2->execute();
        if ($result2) {
            $response["success"] = true;
            $response["message"] = "delete success";
        } else {
            $response["success"] = false;
            $response["message"] = "delete not success";
        }
        return $response;
    }

    public function setRatingAndDriverFavorite($isFavorites, $job_id, $ratingValue, $commentText)
    {
        $count = 0;
        $response = array();
        $sql = "SELECT user_id,driver_id FROM job WHERE job_id=?";
        $stmt = $this->conn->prepare($sql);
        $stmt->bind_param("i", $job_id);
        $result = $stmt->execute();
        if ($result) {
            $data = $stmt->get_result()->fetch_assoc();
            $stmt->close();
            if ($isFavorites == "true") {
                $sql2 = "INSERT INTO driver_favorite(user_id,driver_id) VALUES (?,?)";
                $stmt2 = $this->conn->prepare($sql2);
                $stmt2->bind_param("ii", $data['user_id'], $data['driver_id']);
                $result2 = $stmt2->execute();
                if ($result2) {
                    $count++;
                }
                $stmt2->close();
            } else {
                $sql2 = "DELETE FROM driver_favorite WHERE user_id = ? and driver_id = ?";
                $stmt2 = $this->conn->prepare($sql2);
                $stmt2->bind_param("ii", $data['user_id'], $data['driver_id']);
                $result2 = $stmt2->execute();
                if ($result2) {
                    $count++;
                }
                $stmt2->close();
            }
            if ($commentText != null) {
                $sql3 = "UPDATE job SET job_comment = ? WHERE job_id = ?";
                $stmt3 = $this->conn->prepare($sql3);
                $stmt3->bind_param("si", $commentText, $job_id);
                $result3 = $stmt3->execute();
                if ($result3) {
                    $count++;
                }
                $stmt3->close();
            } else {
                $count++;
            }
            if ($ratingValue != null) {
                $sql4 = "UPDATE job SET job_rating = ? WHERE job_id = ?";
                $stmt4 = $this->conn->prepare($sql4);
                $stmt4->bind_param("di", $ratingValue, $job_id);
                $result4 = $stmt4->execute();
                if ($result4) {
                    $count++;
                }
                $stmt4->close();
            } else {
                $count++;
            }
            if ($count == 3) {
                $response["success"] = true;
                $response["message"] = "Insert Rating Data and Driver Favorite Success";
            } else {
                $response["success"] = false;
                $response["message"] = "Insert Rating Data and Driver Favorite Not Success";
            }

        } else {
            $response["success"] = false;
            $response["message"] = "Insert Rating Data and Driver Favorite Not Success";
        }
        return $response;
    }

    public function updateJob($job_id, $job_status)
    {
        $response = array();
        $sql = "UPDATE job SET job_status_name = ? WHERE job_id=?";
        $stmt = $this->conn->prepare($sql);
        $stmt->bind_param("si", $job_id, $job_status);
        $result = $stmt->execute();
        if ($result) {
            $response["success"] = true;
            $response["message"] = "Update Success";
        } else {
            $response["success"] = false;
            $response["message"] = "Update not Success";
        }
        return $response;
    }

    public function jobData($user_id)
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

    public function getJobData($user_id)
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
        
        driver_id,driver_first_name,driver_last_name,driver_email,driver_tel,
        driver_address,driver_sex,driver_province,driver_img_name,driver_fcm_id,
        
        driver_detail_type,driver_detail_brand,driver_detail_model,driver_detail_color,
        driver_detail_license_plate,driver_detail_province_license_plate
        
        
         FROM view_data_job WHERE user_id = ? ORDER BY job_id DESC";
        $stmt = $this->conn->prepare($sql);
        $stmt->bind_param("i", $user_id);
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

                $tmp["driver_id"] = $data['driver_id'];
                $tmp["driver_first_name"] = $data['driver_first_name'];
                $tmp["driver_last_name"] = $data['driver_last_name'];
                $tmp["driver_email"] = $data['driver_email'];
                $tmp["driver_tel"] = $data['driver_tel'];
                $tmp["driver_address"] = $data['driver_address'];
                $tmp["driver_sex"] = $data['driver_sex'];
                $province_name = $this->getProvinceName($data['driver_province']);
                $tmp["driver_province"] = $province_name;
                $tmp["driver_img_name"] = $data['driver_img_name'];
                $tmp["driver_fcm_id"] = $data['driver_fcm_id'];

                $tmp["driver_detail_type"] = $data['driver_detail_type'];
                $tmp["driver_detail_brand"] = $data['driver_detail_brand'];
                $tmp["driver_detail_model"] = $data['driver_detail_model'];
                $tmp["driver_detail_color"] = $data['driver_detail_color'];
                $tmp["driver_detail_license_plate"] = $data['driver_detail_license_plate'];
                $tmp["driver_detail_province_license_plate"] = $data['driver_detail_province_license_plate'];

                array_push($jobs, $tmp);
            }
        }
        $stmt->close();
        return $jobs;
    }

    public function getJobDataByProcess($user_id)
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
        
        driver_id,driver_first_name,driver_last_name,driver_email,driver_tel,
        driver_address,driver_sex,driver_province,driver_img_name,driver_fcm_id,
        
        driver_detail_type,driver_detail_brand,driver_detail_model,driver_detail_color,
        driver_detail_license_plate,driver_detail_province_license_plate
        
         FROM view_data_job WHERE user_id = ? and  (job_status_name!='เสร็จสิ้น' and job_status_name!='ยกเลิก') ORDER BY job_id DESC";
        $stmt = $this->conn->prepare($sql);
        $stmt->bind_param("i", $user_id);
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

                $tmp["driver_id"] = $data['driver_id'];
                $tmp["driver_first_name"] = $data['driver_first_name'];
                $tmp["driver_last_name"] = $data['driver_last_name'];
                $tmp["driver_email"] = $data['driver_email'];
                $tmp["driver_tel"] = $data['driver_tel'];
                $tmp["driver_address"] = $data['driver_address'];
                $tmp["driver_sex"] = $data['driver_sex'];
                $province_name = $this->getProvinceName($data['driver_province']);
                $tmp["driver_province"] = $province_name;
                $tmp["driver_img_name"] = $data['driver_img_name'];
                $tmp["driver_fcm_id"] = $data['driver_fcm_id'];

                $tmp["driver_detail_type"] = $data['driver_detail_type'];
                $tmp["driver_detail_brand"] = $data['driver_detail_brand'];
                $tmp["driver_detail_model"] = $data['driver_detail_model'];
                $tmp["driver_detail_color"] = $data['driver_detail_color'];
                $tmp["driver_detail_license_plate"] = $data['driver_detail_license_plate'];
                $tmp["driver_detail_province_license_plate"] = $data['driver_detail_province_license_plate'];

                array_push($jobs, $tmp);
            }
        }
        $stmt->close();
        return $jobs;
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

    public function getJobDataByHistory($user_id)
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
        
        driver_id,driver_first_name,driver_last_name,driver_email,driver_tel,
        driver_address,driver_sex,driver_province,driver_img_name,driver_fcm_id,
        
        driver_detail_type,driver_detail_brand,driver_detail_model,driver_detail_color,
        driver_detail_license_plate,driver_detail_province_license_plate,
        job_comment,job_rating
        
         FROM view_data_job WHERE user_id = ? and  (job_status_name='เสร็จสิ้น' OR job_status_name='ยกเลิก') ORDER BY job_id DESC";
        $stmt = $this->conn->prepare($sql);
        $stmt->bind_param("i", $user_id);
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

                $tmp["driver_id"] = $data['driver_id'];
                $tmp["driver_first_name"] = $data['driver_first_name'];
                $tmp["driver_last_name"] = $data['driver_last_name'];
                $tmp["driver_email"] = $data['driver_email'];
                $tmp["driver_tel"] = $data['driver_tel'];
                $tmp["driver_address"] = $data['driver_address'];
                $tmp["driver_sex"] = $data['driver_sex'];
                $province_name = $this->getProvinceName($data['driver_province']);
                $tmp["driver_province"] = $province_name;
                $tmp["driver_img_name"] = $data['driver_img_name'];
                $tmp["driver_fcm_id"] = $data['driver_fcm_id'];

                $tmp["driver_detail_type"] = $data['driver_detail_type'];
                $tmp["driver_detail_brand"] = $data['driver_detail_brand'];
                $tmp["driver_detail_model"] = $data['driver_detail_model'];
                $tmp["driver_detail_color"] = $data['driver_detail_color'];
                $tmp["driver_detail_license_plate"] = $data['driver_detail_license_plate'];
                $tmp["driver_detail_province_license_plate"] = $data['driver_detail_province_license_plate'];
                $tmp["job_comment"] = $data['job_comment'];
                $tmp["job_rating"] = $data['job_rating'];

                array_push($jobs, $tmp);
            }
        }
        $stmt->close();
        return $jobs;
    }

    /**
     * FUNCTION UNITIES
     */

//function check email same
    private function isUserExists($email)
    {
        $stmt = $this->conn->prepare("SELECT user_id from user WHERE user_email = ?");
        $stmt->bind_param("s", $email);
        $stmt->execute();
        $stmt->store_result();
        $num_rows = $stmt->num_rows;
        $stmt->close();

        return $num_rows > 0;
    }

//function hash password
    public function hashSSHA($password)
    {
        $salt = sha1(rand());
        $salt = substr($salt, 0, 10);
        $encrypted = base64_encode(sha1($password . $salt, true) . $salt);
        $hash = array("salt" => $salt, "encrypted" => $encrypted);
        return $hash;
    }

//function check hash password
    public function checkhashSSHA($salt, $password)
    {
        $hash = base64_encode(sha1($password . $salt, true) . $salt);
        return $hash;
    }

}

?>

