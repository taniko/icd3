<?php
namespace Hrgruri\Icd3\Model;

use Hrgruri\Icd3\Dbh;

class Session
{
    private $id;
    private $token;

    public static function get()
    {
        if (!self::checkSession()) {
            $token = bin2hex(openssl_random_pseudo_bytes(128));
            $id    = self::insertUser($token);
        } else {
            $id       = $_SESSION['id'];
            $token    = $_SESSION['token'];
        }
        return ['id' => $id, 'token' => $token];
    }

    private static function insertUser(string $token)
    {
        $dbh = Dbh::get();
        $sth = $dbh->prepare('INSERT INTO user(token) VALUES (:token)');
        $sth->bindParam(':token', $token, \PDO::PARAM_STR);
        $sth->execute();
        return (int)$dbh->lastInsertId();
    }

    private static function checkSession()
    {
        $flag = false;
        if (isset($_SESSION['id']) && isset($_SESSION['token'])) {
            $dbh = Dbh::get();
            $sth = $dbh->prepare(
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
