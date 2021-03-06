<?php
class ForumCategory extends ForumsAppModel
{
 var $name = 'ForumCategory';
 var $hasMany = array('ForumForum' => array (
 							'className' => 'Forums.ForumForum',
 							'dependent' => true
 						));
 var $actsAs = array('Sluggable', 'Orderable');
 var $order = "ForumCategory.order ASC";

 function fetchCategories($slug, $userId)
 {
 	$returnData = array();

 	$conditions = array();
 	if ($slug != null)
 	{
 		$conditions['ForumCategory.slug'] = $slug;
 	}

 	$allCategories = $this->ForumForum->find('list', array('contain' => false, 'conditions' => array('ForumForum.category' => 1)));
 	$categories = $this->find('all', array('conditions' => $conditions, 'contain' => array('ForumForum' => array('order' => 'ForumForum.lft ASC', 'conditions'=>array('ForumForum.category' => 0, 'ForumForum.parent_id' => array_keys($allCategories))))));

 	foreach($categories as $category)
 	{
 		$returnCategory = array();
 		$returnCategory['slug'] = $category['ForumCategory']['slug'];
 		$returnCategory['title'] = $category['ForumCategory']['title'];
		$returnCategory['forums'] = array();
 		foreach($category['ForumForum'] as $forum)
 		{
 			$returnForum = array();

			$threadList = $this->ForumForum->ForumThread->find('list', array('contain' => false, 'conditions' => array('ForumThread.forum_forum_id' => $forum['id'])));
			$numberPosts = $this->ForumForum->ForumThread->find('first', array('fields' => array('SUM(ForumThread.forum_post_count) AS numberPosts'), 'contain' => false, 'conditions' => array('ForumThread.forum_forum_id' => $forum['id'])));
			$lastPost = $this->ForumForum->ForumThread->ForumPost->find('first', array('contain' => array('User', 'ForumThread'), 'conditions' => array('ForumPost.forum_thread_id' => array_keys($threadList)), 'order' => array('ForumPost.created DESC')));
			$hasUnread = $this->ForumForum->ForumThread->ForumUnreadPost->find('count', array('conditions' => array('ForumUnreadPost.user_id' => $userId, 'ForumUnreadPost.forum_thread_id' => array_keys($threadList))));

			$returnForum['title'] = $forum['title'];
			$returnForum['slug'] = $forum['slug'];
			$returnForum['description'] = $forum['description'];
			$returnForum['number_threads'] = $forum['forum_thread_count'];
			$returnForum['number_posts'] = $numberPosts[0]['numberPosts'];
			$returnForum['lastPost'] = $lastPost;
			$returnForum['unreadPost'] = $hasUnread;
			$returnForum['ChildForum'] = $this->ForumForum->find('all', array('order' => 'ForumForum.lft ASC', 'contain' => false, 'conditions' => array('ForumForum.parent_id' => $forum['id'])));

			$returnCategory['forums'][] = $returnForum;
 		}

 		$returnData[] = $returnCategory;
 	}

 	return $returnData;
 }
}
?>