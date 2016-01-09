<?php
namespace modules\defaultModule\models;
/**
* The modules\defaultModule\models\disqus
* @by Zinux Generator <b.g.dariush@gmail.com>
*/
class disqus extends \ActiveRecord\Model
{
    static $has_one = array("tag");
    public function user() {
        return user::find($this->created_by, array('select' => 'username', 'readonly' => true));
    }
    public function get_latest_toptics($offset, $limit, $tag_name = NULL) {
        if($tag_name) {
        }
        $ds = new self;
        $qb = new \ActiveRecord\SQLBuilder($ds->connection(), $ds->table_name());
        $qb
                ->select("disqusid, title, tag_id")
                ->where("parentid IS NULL")
                ->offset($offset)
                ->limit($limit);
        if($tag_name) {
            $tag = tag::fetch($tag_name);
            if(!$tag) return array();
            $qb->where("parentid IS NULL AND tag_id = ?", $tag->tag_id);
        }
        // fetch roots
        $roots = $ds->find_by_sql($qb->to_s(), $qb->bind_values());
        // fetch latest reply
        $qb
                ->select("disqusid, title, context, username, tag_id, disquses.updated_at")
                ->joins("INNER JOIN disquses ON disquses.disqusid = disquses.parentid")
                ->joins("INNER JOIN users ON users.userid= disquses.created_by")
                ->limit(1)
                ->order("disquses.created_at desc");
        $latest_topics = array();
        foreach($roots as $root) {
                $qb->where("parentid = ?", $root->disqusid);
                $query = $ds->find_by_sql($qb->to_s(), $qb->bind_values());
                // if no sub-topic?
                if(!count($query)) {
                    // consider the root topic!
                    $qb->where("disqusid = ?", $root->disqusid);
                    $query = $ds->find_by_sql($qb->to_s(), $qb->bind_values());
                }
                $topic = array_shift($query);
                $topic->readonly();
                $topic->title = $root->title;
                $topic->tag_id = @$root->tag_id;
                $topic->disqusid = $root->disqusid;
                $latest_topics[] = $topic;
        }
        // sort by updated_at desc
        usort($latest_topics, function($a, $b) { 
            if($a->updated_at == $b->updated_at)  return 0;
            if($a->updated_at < $b->updated_at) return 1;
            return -1;
        });
        return $latest_topics;
    }
}