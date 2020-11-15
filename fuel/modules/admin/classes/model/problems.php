<?php
namespace Admin;

class Model_Problems extends \Model_Abstract
{
    protected static $_tabel_name = 'problem_stock';
    protected static $_primary_key = 'id';
    protected static $_rules = array(
        'images'     => 'required|array',
        'name'       => 'required',
        'setter'     => 'required',
        'grade'      => 'required',
        'wall'       => 'required',
        'publishing' => 'required',
    );

    protected static $_labels = array(
        'images'     => '課題画像',
        'name'       => '課題名',
        'setter'     => '設定者',
        'grade'      => 'グレード',
        'wall'       => 'ウォール',
        'publishing' => 'ステータス',
    );

    public function getRecords(string $sort, string $order, array $where, int $offset, int $limit, bool $isMonthly): array
    {
        $table = $isMonthly ? 'monthly_stock' : 'problem_stock';
        $year_month = $isMonthly ? ',`year_month`,SUBSTR(cast(`year_month` as char), 1, 4) year
            ,CAST(SUBSTR(cast(`year_month` as char), 5, 2) as signed) month,monthly_order' : '';
        $sql = str_replace('@table', $table, $this->select);
        $sql = str_replace('@year_month', $year_month, $sql);
        $result = \DB::select()
                    ->from(\DB::expr($sql))
                    ->where(\Querybuilder::where($where))
                    ->offset($offset)
                    ->limit($limit)
                    ->order_by($sort, $order)
                    ->execute()
                    ->as_array();
        return $result;
    }

    public function getDetail(string $id, bool $isMonthly): array
    {
        $table = $isMonthly ? 'monthly_stock' : 'problem_stock';
        $sql = str_replace('@table', $table, $this->select);
        $year_month = $isMonthly ? 
        ',SUBSTR(cast(`year_month` as char), 1, 4) year
        ,CAST(SUBSTR(cast(`year_month` as char), 5, 2) as signed) month,monthly_order' : '';
        $sql = str_replace('@year_month', $year_month, $sql);
        $result = \DB::select()
                    ->from(\DB::expr($sql))
                    ->where(array(array('id', '=', $id)))
                    ->execute()
                    ->as_array();
        return $result[0];
    }

    public function register(array $input, bool $isMonthly): bool 
    {
        try{
            $table = $isMonthly ? 'monthly_stock' : 'problem_stock';
            $sql = str_replace('@table', $table, $this->upsert);
            $user = \Session::get('user')['username'];

            $id = $input['id'] ?: $this->getSeq('problem_id_seq');

            $query = \DB::query($sql);
            $query->param('id',         $id);
            $query->param('name',       $input['name']);
            $query->param('setter',     $input['setter']);
            $query->param('grade',      $input['grade']);
            $query->param('wall',       $input['wall']);
            $query->param('publishing', $input['publishing']);
            $query->param('images',     $input['images']);
            $query->param('created_by', $user);
            $query->param('updated_by', $user);
            $res = $query->execute();

            \Logger::Write('upsert', implode('/', \Uri::segments()), 'insert: id ='.$id);
            return true;
        } catch (Exception $e) {
            \Log::warning($e->getMessage());
            return false;
        }
    }

    public function deleteRecord(string $id, bool $isMonthly = false): bool 
    {
        try{
            $table = $isMonthly ? 'monthly_stock' : 'problem_stock';
            $sql = str_replace('@table', $table, $this->delete);
            $query = \DB::query($sql);
            $query->param('id', $id);
            $res = $query->execute();

            \Logger::Write('delete', implode('/', \Uri::segments()), 'delete: id ='.$id);
            return true;
        } catch (Exception $e) { 
            \Log::warning($e->getMessage());
            return false;
        }
    }

    public function moveProblem(string $id, bool $isMonthly): bool
    {
        try{
            \DB::start_transaction();
            
            // 移動
            $sql = $isMonthly ? $this->moveToSession : $this->moveToMonthly;
            $query = \DB::query($sql);
            $query->param('id', $id);
            $res = $query->execute();

            // 削除
            $res = $this->deleteRecord($id, $isMonthly);
            if (!$res) {
                throw new Exception('failed to delete');
            }

            \DB::commit_transaction();

            \Logger::Write('insert', implode('/', \Uri::segments()), 'insert: id ='.$id);
            return true;
        } catch (Exception $e) { 
            \DB::rollback_transaction();
            \Log::warning($e->getMessage());
            return false;
        }
    }

    private $moveToMonthly =
<<<SQL
    INSERT INTO monthly_stock 
        (id, name, setter, grade, wall, `year_month`, monthly_order, publishing, images, created_at, created_by, updated_at, updated_by)
    SELECT id, name, setter, grade, wall, null, null, 0, images, CURRENT_TIMESTAMP, created_by, CURRENT_TIMESTAMP, CURRENT_TIMESTAMP
    FROM problem_stock WHERE id = :id;
SQL;

private $moveToSession =
<<<SQL
    INSERT INTO problem_stock 
        (id, name, setter, grade, wall, publishing, images, created_at, created_by, updated_at, updated_by)
    SELECT id, name, setter, grade, wall, 0, images, CURRENT_TIMESTAMP, created_by, CURRENT_TIMESTAMP, CURRENT_TIMESTAMP
    FROM monthly_stock WHERE id = :id;
SQL;

    public static $gradesQuery = 
<<<SQL
(
    SELECT 
        g.type
        ,g.code code
        ,coalesce(g.name, '未指定') name
        ,coalesce(c.name, 'grey') color
    FROM 
        (SELECT * FROM codes WHERE type = 'grade' and delete_flg = 0) g
        LEFT JOIN 
        (SELECT * FROM codes WHERE type = 'grade_color') c
        ON g.code = c.code
    ORDER BY code
) t
SQL;

public $select = 
<<<SQL
(
    SELECT 
        id
        ,name
        ,setter
        ,grade
        ,int2codename('grade', grade) grade_name
        ,int2codename('grade_color', grade) grade_color
        ,wall
        ,int2codename('wall', wall) wall_name
        ,publishing
        ,int2codename('publishing', publishing) publishing_name
        ,images
        @year_month
        ,DATE_FORMAT(created_at, '%Y/%m/%d') created_at
        ,created_at c_at
        ,created_by
    FROM @table
) t
SQL;

public $upsert = 
<<<SQL
INSERT INTO @table
(
    id
    ,name
    ,setter
    ,grade
    ,wall
    ,publishing
    ,images
    ,created_by
    ,updated_by
 )
VALUES
(
    :id
    ,:name
    ,:setter
    ,:grade
    ,:wall
    ,:publishing
    ,:images
    ,:created_by
    ,:updated_by
)
ON DUPLICATE KEY UPDATE 
    name = :name
    ,setter = :setter
    ,grade = :grade
    ,wall = :wall
    ,publishing = :publishing
    ,images = :images
    ,updated_at = CURRENT_TIMESTAMP
    ,updated_by = :updated_by
;
SQL;

public $delete = 
<<<SQL
DELETE FROM @table WHERE id = :id;
SQL;

}