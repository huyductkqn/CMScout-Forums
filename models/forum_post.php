<?php
class ForumPost extends ForumsAppModel
{
 var $name = 'ForumPost';
 var $belongsTo = array('ForumThread' => array('className' => 'Forums.ForumThread', 'counterCache' => true), 
 						'User' => array('fields' => array("id", "username", "avatar", "signature")),
 						'EditUser' => array('fields' => array("id", "username"),
 											'className' => 'User',
 											'foreignKey' => 'edit_user'));
 var $actsAs = array('Tag'=>array('table_label'=>'tags', 'tags_label'=>'tag', 'separator'=>','), 'Sluggable');

 var $hasAndBelongsToMany = "Tag";

 function getPageNumber($pageId, $perPage=25)
 {
 	if(is_numeric($pageId))
  		$viewPost = $this->find('first', array('conditions' => array('ForumPost.id' => $pageId), 'fields' => array('id', 'forum_thread_id','created'),
 										'contain' => false));
  	else
  		$viewPost = $this->find('first', array('conditions' => array('ForumPost.slug' => $pageId), 'fields' => array('id', 'forum_thread_id','created'),
 										'contain' => false));
  	
  	$numberOfPost = $this->find('count', array("conditions" => array ('ForumPost.forum_thread_id' => $viewPost['ForumPost']['forum_thread_id'], 'ForumPost.id <=' => $pageId)));
  	return ceil($numberOfPost / $perPage);
 }
}
?>