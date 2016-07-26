<?php
namespace Hrgruri\Icd3\Model;

use Hrgruri\Rarcs\BooksClient as Client;
use Hrgruri\Rarcs\Asset\Book as Asset;

class Book extends Model
{
    private static $DB  = 'books';
    private static $URL = 'http://www.dh-jac.net/db1/books/results-detail.php?f1=';

    /**
     * assetテーブルに登録済みかを確認
     * @param string $id ARC管理番号
     * @return bool
     */
    private function existsAsset(string $id) : bool
    {
        $result = $this->capsule->table('asset')
            ->join('db', 'asset.db', '=', 'asset.db')
            ->where('db.name', '=', self::$DB)
            ->where('asset.name', '=', $id)
            ->exists();
        return $result;
    }

    /**
     * infoテーブルに登録済みかを確認
     * @param string $id ARC管理番号
     * @return bool
     */
    private function existsInfo(string $id) : bool
    {
        return $this->capsule->table('info')
            ->join('asset', 'info.asset_id', '=', 'asset.id')
            ->join('db', 'asset.db', '=', 'db.id')
            ->where('asset.name', '=', $id)
            ->where('db.name', '=', self::$DB)
            ->exists();
    }

    /**
     * dbテーブルからDBのIDを取得する
     * @param  string $name DB名
     * @return int
     */
    private function getDB(string $name) : int
    {
        return $this->capsule->table('db')
            ->select(['id', 'name'])
            ->where('name', '=', $name)
            ->first()
            ->id;
    }

    /**
     * assertテーブルにインサート
     * @param string $id ARC管理番号
     * @return int asset.id
     */
    private function insertAsset(string $id)
    {
        if ($this->existsAsset($id)) {
            $result = $this->capsule->table('asset')
                ->select('asset.id', 'asset.name')
                ->join('db', 'asset.db', '=', 'db.id')
                ->where('db.name', '=', self::$DB)
                ->where('asset.name', '=', $id)
                ->first()
                ->id;
        } else {
            $db = $this->getDB(self::$DB);
            $result = $this->capsule->table('asset')
                ->InsertGetId([
                    'db'    =>  $this->getDB(self::$DB),
                    'name'  =>  $id
                ]);
        }
        return $result;
    }

    /**
     * 資料の情報をDBに入れる
     * @param  string $id       ARC管理番号
     * @return \Hrgruri\Rarcs\Asset\Book
     */
    private function insertInfo(string $id) : Asset
    {
        if ($this->existsInfo($id)) {
            $asset = $this->getInfo($id);
        } else {
            $asset_id   = $this->insertAsset($id);
            $asset      = (new Client())->getDetail($id);
            $this->capsule->table('info')->insert([
                'asset_id'  =>  $asset_id,
                'artist'    =>  $asset->author,
                'title'     =>  $asset->title,
                'cover'     =>  $asset->cover
            ]);
        }
        return $asset;
    }

    /**
     * 資料情報をDBから取得する
     * @param  string $id  ARC管理番号
     * @return \Hrgruri\Rarcs\Asset\Book
     */
    private function selectInfo(string $id) : Asset
    {
        $std = $this->capsule->table('info')
            ->select('asset.id', 'asset.name', 'info.artist', 'info.title', 'info.cover')
            ->join('asset', 'asset.id', '=', 'info.asset_id')
            ->join('db', 'asset.db', '=', 'db.id')
            ->where('db.name', '=', self::$DB)
            ->where('asset.name', '=', $id)
            ->first();
        $asset = new Asset($id, self::$URL.$id, $std->title, $std->cover, $std->artist, null, null);
        return $asset;
    }

    /**
     * 資料情報を取得する
     * @param  string $id  ARC管理番号
     * @return \Hrgruri\Rarcs\Asset\Book
     */
    public function getInfo(string $id) : Asset
    {
        if ($this->existsAsset($id) && $this->existsInfo($id)) {
            $asset  = $this->selectInfo($id);
        } else {
            $asset  = $this->insertInfo($id);
        }
        return $asset;
    }
}
