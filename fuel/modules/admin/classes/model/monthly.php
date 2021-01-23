<?php
namespace Admin;

class Model_Monthly extends \Model_Abstract
{
    protected static $_tabel_name = 'months';
    protected static $_primary_key = 'id';
    protected static $_rules = array(
        'year_month' => 'required',
        'publishing' => 'required'
    );

    protected static $_labels = array(
        'year_month' => '年月',
        'publishing' => '公開/非公開'
    );

    public function getRecords(string $sort, string $order, array $where): array
    {
        $result = \DB::select()
                    ->from(\DB::expr($this->select))
                    ->where(\Querybuilder::where($where))
                    ->order_by($sort, $order)
                    ->execute()
                    ->as_array();
        return $result;
    }

    public function getDetail(string $yearMonth): array
    {
        $result = \DB::select()
                    ->from(\DB::expr($this->select))
                    ->where(array(array('year_month', '=', $yearMonth)))
                    ->execute()
                    ->as_array();
        return $result;
    }

    public function getProblem(string $yearMonth): array
    {
        $select = 
<<<SQL
(
    SELECT id, name, setter, int2codename('grade', grade) grade_name, int2codename('grade_color', grade) grade_color
        ,int2codename('wall', wall) wall_name, int2codename('publishing', publishing) publishing_name
        ,images, `year_month`, monthly_order, COALESCE(c.comment_count, 0) comments
    FROM monthly_stock
    LEFT JOIN (SELECT problem_id, COUNT(id) comment_count FROM comments GROUP BY problem_id) c ON monthly_stock.id = c.problem_id
) t
SQL;
        $ps = \DB::select()
            ->from(\DB::expr($select))->where('year_month', '=', $yearMonth)->order_by('monthly_order', 'asc')
            ->execute()->as_array();

        return $ps;
    }

    public function getStocks(): array
    {
        $sql = 
<<<SQL
(
    SELECT DISTINCT grade, int2codename('grade', grade) grade_name FROM monthly_stock 
    WHERE `year_month` IS NULL AND monthly_order IS NULL
    ORDER BY grade DESC
) t
SQL;

        $grades = \DB::select()
                ->from(\DB::expr($sql))
                ->execute()
                ->as_array();

        $result = array();

        foreach ($grades as $key => $value) {
            $sql = 
<<<SQL
(
    SELECT id, name, setter, grade, int2codename('grade', grade) grade_name
        ,int2codename('grade_color', grade) grade_color, int2codename('wall', wall) wall_name
        , images, created_at, COALESCE(c.comment_count, 0) comments
    FROM monthly_stock
    LEFT JOIN (SELECT problem_id, COUNT(id) comment_count FROM comments GROUP BY problem_id) c ON monthly_stock.id = c.problem_id
    WHERE `year_month` IS NULL AND monthly_order IS NULL
    ORDER BY grade DESC, created_at 
) t
SQL;

            $stock = \DB::select()
                    ->from(\DB::expr($sql))
                    ->where(array(array('grade', '=', $value['grade'])))
                    ->execute()
                    ->as_array();

            foreach($stock as $k => $v) {
                $stock[$k] = $this->decodeImage($v);
            }

            $result[$value['grade_name']] = $stock;
        }
        return $result;
    }

    public function register(array $input): bool 
    {
        try {
            \DB::start_transaction();

            $user = \Session::get('user')['username'];

            $yearMonth = $input['year_month'];
            $publishing = $input['publishing'];
            $query = \DB::query($this->upsert);
            $query->param('year_month', $yearMonth);
            $query->param('publishing', $publishing);
            $query->param('created_by', $user);
            $query->param('updated_by', $user);
            $res = $query->execute();

            $query = \DB::query($this->resetOrder);
            $query->param('year_month', $yearMonth);
            $query->param('updated_by', $user);
            $res = $query->execute();

            // 課題の順序を更新
            $problems = array_key_exists('problems', $input) ? $input['problems'] : array();
            if (count($problems) > 0) {
                $ids = [];
                $orders = [];
    
                foreach ($problems as $key => $value) {
                    $val = json_decode($value, true);
                    if (is_numeric($val['id'])) {
                        $ids[] = $val['id'];
                    }
                    if (is_numeric($val['order'])) {
                        $orders[] = $val['order'];
                    }
                }

                if (count($ids) > 0 && count($ids) == count($orders)) {
                    $query = \DB::query($this->updateOrder);
                    $query->param('orders', \DB::expr(implode(',', $orders)));
                    $query->param('year_month', $yearMonth);
                    $query->param('publishing', $publishing);
                    $query->param('ids', \DB::expr(implode(',', $ids)));
                    $res = $query->execute();
                }
            }

            \DB::commit_transaction();

            \Logger::Write('upsert', implode('/', \Uri::segments()), 'upsert: year_month ='.$yearMonth);
            return true;
        } catch (Exception $e) {
            \DB::rollback_transaction();
            \Log::warning($e->getMessage());
            return false;
        }
    }

    public function deleteRecord(string $id, bool $isMonthly = false): bool 
    {
        try {
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

    public $select = 
<<<SQL
(
    SELECT 
        m.year_month
        ,SUBSTR(cast(m.year_month as char), 1, 4) year
        ,CAST(SUBSTR(cast(m.year_month as char), 5, 2) as signed) month
        ,m.problem_num
        ,m.publishing
        ,int2codename('publishing', m.publishing) is_published
        ,m.created_at
        ,coalesce(p.cnt, 0) cnt
        ,v.cnt v_cnt
        ,n.cnt n_cnt
        ,d.cnt d_cnt
        ,p.cnt / m.problem_num as created
        ,CASE WHEN p.cnt / m.problem_num = 1 THEN 'green'
              WHEN p.cnt / m.problem_num < 1 AND p.cnt / m.problem_num >= 2 / 3 THEN 'olive'
              WHEN p.cnt / m.problem_num < 2 / 3 AND p.cnt / m.problem_num >= 1 / 3 THEN 'yellow'
              ELSE 'red'
         END as progress_color
    FROM months m
    LEFT JOIN (SELECT `year_month`, coalesce(count(`year_month`), 0) cnt
        FROM monthly_stock GROUP BY `year_month`) p ON m.year_month = p.year_month
    LEFT JOIN (SELECT `year_month`, coalesce(count(`year_month`), 0) cnt
        FROM monthly_stock WHERE wall = 1 GROUP BY `year_month`) v ON m.year_month = v.year_month
    LEFT JOIN (SELECT `year_month`, coalesce(count(`year_month`), 0) cnt
        FROM monthly_stock WHERE wall = 2 GROUP BY `year_month`) n ON m.year_month = n.year_month
    LEFT JOIN (SELECT `year_month`, coalesce(count(`year_month`), 0) cnt
        FROM monthly_stock WHERE wall = 3 GROUP BY `year_month`) d ON m.year_month = d.year_month
) t
SQL;


private $upsert = 
<<<SQL
INSERT INTO months
(
    `year_month`
    ,publishing
    ,created_at
    ,created_by
    ,updated_at
    ,updated_by
 )
VALUES
(
    :year_month
    ,:publishing
    ,CURRENT_TIMESTAMP
    ,:created_by
    ,CURRENT_TIMESTAMP
    ,:updated_by
)
ON DUPLICATE KEY UPDATE 
    `year_month` = :year_month
    ,publishing = :publishing
    ,updated_at = CURRENT_TIMESTAMP
    ,updated_by = :updated_by
;
SQL;

private $resetOrder = 
<<<SQL
UPDATE monthly_stock 
SET `year_month` = null, monthly_order = null, updated_at = CURRENT_TIMESTAMP ,updated_by = :updated_by
WHERE `year_month` = :year_month;
SQL;

private $updateOrder = 
<<<SQL
UPDATE monthly_stock
SET 
    `year_month` = :year_month
    ,monthly_order = ELT(FIELD(id,:ids),:orders) 
    ,publishing = :publishing 
WHERE id IN (:ids)
SQL;

public $delete = 
<<<SQL
DELETE FROM months WHERE year_month = :year_month;
SQL;

}