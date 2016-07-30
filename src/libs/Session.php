<?php
namespace Hrgruri\Icd3;

class Session extends \Hrgruri\Icd3\Model\Model
{
    private $id;
    private $token;

    public function start()
    {
        if(!isset($_SESSION)){
            session_start();
            // $_SESSION = [];
        }
    }

    public function get()
    {
        if (!$this->checkSession()) {
            $token = bin2hex(openssl_random_pseudo_bytes(128));
            $id    = $this->insertUser($token);
            $_SESSION['id']     = $id;
            $_SESSION['token']  = $token;
        } else {
            $id       = $_SESSION['id'];
            $token    = $_SESSION['token'];
        }
        return ['id' => $id, 'token' => $token];
    }

    private function insertUser(string $token)
    {
        $result = $this->capsule->table('user')
            ->InsertGetId([
                'token'=> $token
            ]);
        return $result;
    }

    private function checkSession()
    {
        $flag = false;
        if (isset($_SESSION['id']) && isset($_SESSION['token'])) {
            $data = $this->capsule->table('user')
                ->where('id', '=', $_SESSION['id'])
                ->where('token', '=', $_SESSION['token'])
                ->get();
            $flag = count($data) == 1 ? true : false;
        }
        return $flag;
    }
}
