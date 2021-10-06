<?php

namespace App\Manager\Blog;

use App\Entity\Blog\Post;
use App\Manager\AbstractPublicationManager;

class PostManager extends AbstractPublicationManager {

	const NAME = 'ladb_core.blog_post_manager';

	/////

	public function publish(Post $post, $flush = true) {
		parent::publishPublication($post, $flush);
	}

	public function unpublish(Post $post, $flush = true) {
		parent::unpublishPublication($post, $flush);
	}

	public function delete(Post $post, $withWitness = true, $flush = true) {
		parent::deletePublication($post, $withWitness, $flush);
	}

}