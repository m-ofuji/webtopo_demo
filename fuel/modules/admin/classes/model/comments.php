<?php
namespace Admin;

class Model_Comments extends \Model_Abstract
{
    protected static $_tabel_name = 'comments';
    protected static $_primary_key = 'id';
    protected static $_rules = array(
        'problem_id' => 'required',
        'comment' => 'required'
    );

    protected static $_labels = array(
        'problem_id' => '年月',
        'comment' => 'コメント'
    );

    public function getComments(string $problemId): array
    {
        $select = 
<<<SQL
(
    SELECT id, problem_id, comment, created_by, created_at, DATE_FORMAT(created_at, '%m/%d %H:%i') create_at
    FROM comments
) t
SQL;
        $ps = \DB::select()
            ->from(\DB::expr($select))->where('problem_id', '=', $problemId)->order_by('created_at', 'desc')
            ->execute()->as_array();

        return $ps;
    }

    public function register(array $input): bool
    {
        try {
            $user = \Session::get('user')['nick_name'];
            $id = $this->getSeq('comment_id_seq');
            $problemId = $input['problem_id'];
            $comment = $input['comment'];
            $query = \DB::query($this->upsert);
            $query->param('id', $id);
            $query->param('problem_id', $problemId);
            $query->param('comment', $comment);
            $query->param('created_by', $user);
            $res = $query->execute();

            \Logger::Write('upsert', implode('/', \Uri::segments()), 'upsert: comment = '.$id);
            return true;
        } catch (Exception $e) {
            \Log::warning($e->getMessage());
            return false;
        }
    }

private $upsert = 
<<<SQL
INSERT INTO comments
(
    id
    ,problem_id
    ,comment
    ,created_by
 )
VALUES
(
    :id
    ,:problem_id
    ,:comment
    ,:created_by
)
ON DUPLICATE KEY UPDATE 
    problem_id = :problem_id
    ,comment = :comment
;
SQL;

public $delete = 
<<<SQL
DELETE FROM comments WHERE id = :id;
SQL;

}