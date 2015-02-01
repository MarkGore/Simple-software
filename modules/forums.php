<?php

class forums
{

    public function execute()
    {
        global $db, $botwith, $render;
        $cates = $db->get('categories');

        foreach ($cates as $forum) {
            if ($forum->type == 'c') {
                $botwith->cache['cate'] = new stdClass();
                $botwith->cache['cate']->title = $forum->title;
                $render->parse('categories_start');
                $this->getSections($cates, $forum->cid);
                $render->parse('categories_end');
            }
        }
    }

    public function getSections($cates, $cid)
    {
        global $botwith, $render;
        $sections = array();
        foreach ($cates as $cate) {
            if ($cate->type == 's' && $cid == $cate->parent) {
                $botwith->cache['section'] = new stdClass();
                $botwith->cache['section']->title = $cate->title;
                $botwith->cache['section']->desc = $cate->desc;
                $botwith->cache['section']->sid = $cate->cid;
                array_push($sections, $cate);
                $render->parse('forumbit_topic');
            }
        }
        return $sections;
    }

}