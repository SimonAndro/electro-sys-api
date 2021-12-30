<?php
class UserModel extends Model
{
    public $authId;
    public $authUser;
    public $authOwnerId;
    public $authOwner;
    public $team;

 
    public function isLoggedin()
    {
        return $this->authId;
    }

    public function getUserid()
    {
        return $this->authId;
    }

    public function isAdmin()
    {
        if ($this->authUser and $this->authUser['role'] == 1) {
            return true;
        }

        return false;
    }

    public function getUser($id = null)
    {
        if ($id) {
            $query = $this->db->query("SELECT * FROM users WHERE (id=? OR email=?) ", $id, $id);
            return $query->fetch(PDO::FETCH_ASSOC);
        } else {
            return $this->authUser;
        }
    }

    public function listUsers($type = '')
    {
        $sql = "SELECT id,full_name FROM users WHERE status=1";
        $query = $this->db->query($sql);
        return $query->fetchAll(PDO::FETCH_ASSOC);
    }

    public function findByActivationCode($code)
    {
        $query = $this->db->query("SELECT * FROM users WHERE (activation_code=?) ", $code);
        return $query->fetch(PDO::FETCH_ASSOC);
    }

    public function findUserByEmail($email)
    {
        $query = $this->db->query("SELECT * FROM users WHERE (email=? AND status=?) ", $email, 1);
        return $query->fetch(PDO::FETCH_ASSOC);
    }

    public function findByResetCode($code)
    {
        $query = $this->db->query("SELECT * FROM users WHERE (recovery_code=?) ", $code);
        return $query->fetch(PDO::FETCH_ASSOC);
    }

    public function checkemail_address($email_address, $id = null)
    {
        $sql = "SELECT id FROM users WHERE email=?";
        $param = array($email_address);
        if ($id) {
            $sql .= " AND id != ?";
            $param[] = $id;
        }

        $query = $this->db->query($sql, $param);
        return $query->rowCount();
    }

    public function processLogin()
    {
        return true;

        $loginId = "";
        $password = "";
        if (isset($_COOKIE['loginid']) and isset($_COOKIE['user_token'])) {
            $loginId = $_COOKIE['loginid'];
            $password = $_COOKIE['user_token'];
        }
        if (isset($_SESSION['loginid']) and isset($_SESSION['user_token'])) {
            $loginId = $_SESSION['loginid'];
            $password = $_SESSION['user_token'];
        }

        if (!$loginId) {
            return false;
        }

        $query = $this->db->query("SELECT * FROM users WHERE id = ?", $loginId);
        $result = $query->fetch(\PDO::FETCH_ASSOC);

        if (!hash_check($result['password'], $password)) {
            return false;
        }

        //@TODO - Other processes for specific auth types
        $this->authId = $result['id'];
        $this->authUser = $result;
        $this->authOwnerId = ($result['is_team']) ? $result['is_team'] : $this->authId;
        $this->getOwner();

        //update user last_seen
        $this->db->query("UPDATE users SET last_seen=? WHERE id=? ", time(), $this->authId);

        $this->saveData($result['id'], $result['password'], $this->authOwnerId);

        if (isset($_SESSION['shadow_userid'])) {
            $shadowId = $_SESSION['shadow_userid'];
            $this->authId = $shadowId;
            $this->authUser = $this->getUser($shadowId);
            $this->authOwnerId = ($this->authUser['is_team']) ? $this->authUser['is_team'] : $this->authId;
            $this->getOwner();
        }
        return true;
    }

    public function loginUser($email, $password)
    {
        $query = $this->db->query("SELECT * FROM users WHERE (email = ?)  ", $email);
        $result = $query->fetch(\PDO::FETCH_ASSOC);

        if (!$result) {
            return false;
        }

        if (!hash_check($password, $result['password'])) {
            return false;
        }

        if ($result['status'] == 0) {
            $this->sendActivationLink($result['id'], $result['email'], $result['full_name']);
            exit(json_encode(array(
                'type' => 'error',
                'message' => l('please-activate-your-account'),
            )));
        }
        $this->authId = $result['id'];
        $this->authUser = $result;
        $this->authOwnerId = ($result['is_team']) ? $result['is_team'] : $this->authId;
        $this->getOwner();
        $this->saveData($result['id'], $result['password']);

        $this->db->query("UPDATE users SET last_seen=? WHERE id=? ", time(), $this->authId);
        return true;
    }

    public function loginWithObject($result)
    {
        $this->authId = $result['id'];
        $this->authUser = $result;
        $this->authOwnerId = ($result['is_team']) ? $result['is_team'] : $this->authId;
        $this->getOwner();
        $this->saveData($result['id'], $result['password']);
    }

    public function changePassword($new)
    {
        $password = hash_value($new);
        $this->db->query("UPDATE users SET password=? WHERE id=? ", $password, $this->authId);
        //refresh session data now
        $this->saveData($this->authId, $password);
    }

    public function saveData($id, $password)
    {
        session_put("loginid", $id);
        session_put("user_token", hash_value($password));
        setcookie("loginid", $id, time() + 30 * 24 * 60 * 60, config('cookie_path'));
        setcookie("user_token", hash_value($password), time() + 30 * 24 * 60 * 60, config('cookie_path')); //expired in one month and extend on every request
    }

    public function logoutUser()
    {
        unset($_SESSION['loginid']);
        unset($_SESSION['user_token']);
        unset($_SESSION['shadow_userid']);
        unset($_COOKIE['loginid']);
        unset($_COOKIE['user_token']);
        setcookie("loginid", "", 1, config('cookie_path'));
        setcookie("user_token", "", 1, config('cookie_path'));
    }

    public function addUser($val, $isAdmin = false, $noActivate = false, $autoPass = false)
    {
        $exp = array(
            'password' => '',
            'email' => '',
            'full_name' => '',
            'last_name' => '',
            'phone' => '',
        );

        /**
         * @var $password
         * @var $email
         * @var $full_name
         * @var $phone
         */
        extract(array_merge($exp, $val));

        $password_raw = $password;
        $password = hash_value($password);
        $active = config('email-verification', false) ? 0 : 1;
        if ($isAdmin) {
            $active = 1;
        }

        $query = $this->db->query("INSERT INTO users (password,email,full_name,last_name,phone,created,changed,status,timezone) VALUES(?,?,?,?,?,?,?,?,?,?)", $password, $email, $full_name, $last_name, $phone, time(), time(), $active, $timezone);
        $userid = $this->db->lastInsertId();

        if (!$noActivate) {
            if ($active == 0) {
                $this->sendActivationLink($userid, $email, $full_name);
            } else {
                if($autoPass)
                {
                    $this->sendAutoPassMail($email, $full_name." ".$last_name, $password_raw);
                }else{
                    $this->sendWelcomeMail($email, $full_name);
                }
                
            }

        } else {
            $this->sendWelcomeMail($email, $full_name);
            $this->db->query("UPDATE users SET status=? WHERE id=?", 1, $userid);
        }

        return $userid;
    }

    public function adminEditUser($val, $id)
    {
        $exp = array(
            'password' => '',
            'email' => '',
            'full_name' => '',
            'last_name' => '',
            'phone' => '',
            'role' => '0',
        );

        /**
         * @var $password
         * @var $email
         * @var $full_name
         * @var $timezone
         * @var $role
         */
        extract(array_merge($exp, $val));

        $password = ($password) ? hash_value($password) : '';
        $this->db->query("UPDATE users SET full_name=?,last_name=?, phone=?,email=?,timezone=?,role=? WHERE id=?", $full_name, $last_name,$phone, $email, $timezone, $role, $id);
        if ($password) {
            $this->db->query("UPDATE users SET password=? WHERE id=?", $password, $id);
        }

        return true;
    }
    public function sendActivationLink($userid, $email, $full_name)
    {
        $code = mEncrypt('' . time() . '');
        $link = url('activate/' . $code);
        $this->db->query("UPDATE users SET activation_code=? WHERE id=?", $code, $userid);
        return Email::getInstance()->setAddress($email, $full_name)
            ->setSubject(config('activation-subject'), array('full_name' => $full_name))
            ->setMessage(config('activation-content'), array('site-name' => config('site-title', 'SmartPost'), 'full_name' => $full_name, 'activation_link' => $link))
            ->send();
    }

    public function sendResetLink($userid, $email, $full_name)
    {
        $code = mEncrypt('' . time() . '');
        $link = url('reset/' . $code);
        $this->db->query("UPDATE users SET recovery_code=? WHERE id=?", $code, $userid);

        return Email::getInstance()->setAddress($email, $full_name)
            ->setSubject(config('reset-subject'), array('full_name' => $full_name))
            ->setMessage(config('reset-content'), array('full_name' => $full_name, 'reset_link' => $link))
            ->send();
    }

    public function sendWelcomeMail($email, $full_name)
    {
        if (!config('enable-welcome-mail', false)) {
            return false;
        }

        return Email::getInstance()->setAddress($email, $full_name)
            ->setSubject(config('welcome-subject'), array('full_name' => $full_name))
            ->setMessage(config('welcome-content'), array('full_name' => $full_name))
            ->send();
    }

    public function updatePassword($password, $userid)
    {
        return $this->db->query("UPDATE users SET password=? WHERE id=?", hash_value($password), $userid);
    }

    public function activateUser($user)
    {
        $this->db->query("UPDATE users SET status=? WHERE id=?", 1, $user['id']);
    }

    public function deleteUser($id)
    {
        if ($id == 1) {
            return true;
        }
        //to prevent deleting admin account
        $this->db->query("DELETE FROM accounts WHERE userid=?", $id);
        $this->db->query("DELETE FROM captions WHERE userid=?", $id);

        $query = $this->db->query("SELECT * FROM files WHERE userid=?", $id);
        while ($fetch = $query->fetch(PDO::FETCH_ASSOC)) {
            if ($fetch['resize_image']) {
                delete_file(path($fetch['resize_image']));
            }
            delete_file(path($fetch['file_name']));
        }
        $this->db->query("DELETE FROM groups WHERE userid=?", $id);
        $this->db->query("DELETE FROM instagram_analytics WHERE userid=?", $id);
        $this->db->query("DELETE FROM instagram_analytics_stats WHERE userid=?", $id);
        $this->db->query("DELETE FROM posts WHERE userid=?", $id);
        $this->db->query("DELETE FROM transactions WHERE userid=?", $id);
        $this->db->query("DELETE FROM users WHERE id=?", $id);
        return true;
    }

    public function enableUser($id)
    {
        $this->db->query("UPDATE users SET status=? WHERE id=?", 1, $id);
    }

    public function disableUser($id)
    {
        $this->db->query("UPDATE users SET status=? WHERE id=?", 0, $id);
    }

    public function getAllowFileSize()
    {
        return 30;
    }

    public function userData($field)
    {
        return (isset($this->authUser[$field])) ? $this->authUser[$field] : '';
    }

    public function emailExists($email)
    {
        $query = $this->db->query("SELECT id FROM users WHERE email=? AND id !=? ", $email, $this->authId);
        return $query->rowCount();
    }

    public function saveProfile($val)
    {
        $ext = array(
            'full_name' => '',
            'last_name' => '',
            'email' => '',
            'phone' => '',
            'date_format' => '',
        );

        /**
         * @var $full_name
         * @var $email
         * @var $phone
         * @var $date_format
         */
        extract(array_merge($ext, $val));

        return $this->db->query("UPDATE users SET full_name=?,last_name=?, email=?=?,phone=?,timezone=?,date_format=? WHERE id=?", $full_name, $last_name, $email, $phone, $timezone, $date_format, $this->authId);
    }

    public function savePassword($val)
    {
        /**
         * @var $password
         */
        extract($val);

        $password = md5($password);
        $this->saveData($this->authUser['id'], $password);
        return $this->db->query("UPDATE users SET password=? WHERE id=?", $password, $this->authId);

    }
}
