<?php
namespace Hrgruri\Icd3\Model;

class Session extends \Hrgruri\Icd3\DB
{
    private $id;
    private $token;

    public function get()
    {
        if (!$this->checkSession()) {
            $this->token = bin2hex(openssl_random_pseudo_bytes(128));
            $this->id    = $this->insertUser();
        } else {
            $this->id       = $_SESSION['id'];
            $this->token    = $_SESSION['token'];
        }
        return ['id' => $this->id, 'token' => $this->token];
    }

    private function insertUser()
    {
        $sth = $this->dbh->prepare('INSERT INTO user(token) VALUES (:token)');
        $sth->bindParam(':token', $this->token, \PDO::PARAM_STR);
        $sth->execute();
        return (int)$this->dbh->lastInsertId();
    }

    private function checkSession()
    {
        $flag = false;
        if (isset($_SESSION['id']) && isset($_SESSION['token'])) {
            $sth = $this->dbh->prepare(
                'SELECT * FROM user
                WHERE id = :id AND token = :token'
            );
            $sth->bindParam(':id', $_SESSION['id'], \PDO::PARAM_INT);
            $sth->bindParam(':token', $_SESSION['token'], \PDO::PARAM_STR);
            $sth->execute();
            $flag = $sth->rowCount() == 1 ? true : false;
        }
        return $flag;
    }
}
