<?PHP

class PointLogic
{
    public static $type = 'point';

    /**
     * ポイントを付与する処理
     * 
     * @param string $mid ユーザーID
     * @param int $point 付与するポイント
     * @param string $description 説明
     */
    public static function addPoints($mid, $point, $description)
    {
        $db = GMList::getDB(self::$type);
        $rec = $db->getNewRecord();

        $db->setData($rec, 'mid', $mid);
        $db->setData($rec, 'point', $point);
        $db->setData($rec, 'description', $description);
        $db->setData($rec, 'point_type', 'article'); // 固定値として「article」をセット
        $db->setData($rec, 'regist', time());        // 現在の時刻をセット

        $db->addRecord($rec);
    }

    /**
     * 現在ログインしているユーザーの合計ポイントを取得
     * 
     * @return int
     */
    public static function getUserPoints()
    {
        // DB取得
        $db = GMList::getDB(self::$type);
    
        // 正しい方法でmidを取得（現在ログイン中のcUserのmid）
        $mid = SystemUtil::getTableData('cUser', self::getLoginUserId(), 'mid');
    
        // テーブル取得・midによる絞り込み
        $table = $db->getTable();
        $table = $db->searchTable($table, 'mid', '=', $mid);
    
        // 絞り込んだ状態でポイント合計を計算
        $totalPoints = $db->getSum('point', $table);
    
        return $totalPoints;
    }

    /**
     * ユーザーが保有しているポイント履歴を取得
     *
     * @param string $mid
     * @param int $limit
     * @return array
     */
    public static function getUserPointHistory($mid, $limit = 10)
    {
        $db = GMList::getDB(self::$type);
        $table = $db->searchTable($db->getTable(), 'mid', '=', $mid);
        $table = $db->sortTable($table, 'regist', 'desc');
        $table = $db->limitOffset($table, 0, $limit);

        $row = $db->getRow($table);
        $dataList = [];

        for ($i = 0; $i < $row; $i++) {
            $rec = $db->getRecord($table, $i);
            self::formatInfoData($rec);
            $dataList[] = $rec;
        }

        return $dataList;
    }

    /**
     * ログインしているユーザーのIDを取得
     *
     * @return string|null
     */
    public static function getLoginUserId()
    {
        return isset($_SESSION['jc2loginid']) ? $_SESSION['jc2loginid'] : null;
    }

    /**
     * レコードの情報を整形
     * 不要な情報の削除や時間のフォーマット処理を追加できます
     *
     * @param array &$rec
     */
    public static function formatInfoData(&$rec)
    {
        if (isset($rec['shadow_id'])) { unset($rec['shadow_id']); }
        if (isset($rec['delete_key'])) { unset($rec['delete_key']); }

        if (isset($rec['regist'])) {
            $rec['regist_formatted'] = date('Y-m-d H:i:s', $rec['regist']);
        }
    }

    /**
     * 特定ユーザーのポイントを再計算（集計）して返す
     *
     * @param string $mid
     * @return int
     */
    public static function calculateUserPoints($mid)
    {
        $db = GMList::getDB(self::$type);
        $table = $db->searchTable($db->getTable(), 'mid', '=', $mid);
        return (int)$db->getSum($table, 'point');
    }

    /**
     * ユーザーに対してのポイント履歴を全削除
     *
     * @param string $mid
     */
    public static function deleteUserPoints($mid)
    {
        $db = GMList::getDB(self::$type);
        $table = $db->searchTable($db->getTable(), 'mid', '=', $mid);
        $db->deleteTable($table);
    }

    /**
     * 特定企業が現在保有しているポイントを取得
     * 
     * @param string $mid 企業ユーザーのmid
     * @return int
     */
    public static function getCurrentPoints($mid)
    {
        $db = GMList::getDB(self::$type);
        $table = $db->searchTable($db->getTable(), 'mid', '=', $mid);
        return (int)$db->getSum('point', $table);
    }
}