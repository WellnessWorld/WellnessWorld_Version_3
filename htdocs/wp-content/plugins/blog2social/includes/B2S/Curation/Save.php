<?php

class B2S_Curation_Save {

    public $data = null;

    public function __construct($data = array()) {
        $this->data = $data;
    }

    public function insertContent() {
        $post = array(
            'post_title' => wp_strip_all_tags($this->data['title']),
            'post_content' => $this->data['content'],
            'guid' => $this->data['url'],
            'post_status' => 'private',
            'post_author' => $this->data['author_id'],
            'post_type' => 'b2s_ex_post',
            'post_category' => array(0)
        );
        $res = wp_insert_post($post, true);
        return ($res > 0) ? (int) $res : false;
    }

}
