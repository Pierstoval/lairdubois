<?php

namespace App\Controller\Howto;

use App\Controller\AbstractController;
use App\Controller\PublicationControllerTrait;
use App\Entity\Howto\Howto;
use App\Utils\CollectionnableUtils;
use App\Utils\CommentableUtils;
use App\Utils\EmbeddableUtils;
use App\Utils\ExplorableUtils;
use App\Utils\FollowerUtils;
use App\Utils\GlobalUtils;
use App\Utils\LikableUtils;
use App\Utils\WatchableUtils;

abstract class AbstractHowtoBasedController extends AbstractController {

	use PublicationControllerTrait;

    public static function getSubscribedServices() {
        return array_merge(parent::getSubscribedServices(), array(
            '?'.CollectionnableUtils::class,
            '?'.CommentableUtils::class,
            '?'.EmbeddableUtils::class,
            '?'.ExplorableUtils::class,
            '?'.FollowerUtils::class,
            '?'.GlobalUtils::class,
            '?'.LikableUtils::class,
            '?'.WatchableUtils::class,
        ));
    }

	protected function computeShowParameters(Howto $howto, $request) {
		$om = $this->getDoctrine()->getManager();
		$howtoRepository = $om->getRepository(Howto::class);

		$explorableUtils = $this->get(ExplorableUtils::class);
		$userHowtos = $explorableUtils->getPreviousAndNextPublishedUserExplorables($howto, $howtoRepository, $howto->getUser()->getMeta()->getPublicHowtoCount());
		$similarHowtos = $explorableUtils->getSimilarExplorables($howto, 'howto_howto', Howto::class, $userHowtos);

		$globalUtils = $this->get(GlobalUtils::class);
		$likableUtils = $this->get(LikableUtils::class);
		$watchableUtils = $this->get(WatchableUtils::class);
		$commentableUtils = $this->get(CommentableUtils::class);
		$collectionnableUtils = $this->get(CollectionnableUtils::class);
		$followerUtils = $this->get(FollowerUtils::class);
		$embaddableUtils = $this->get(EmbeddableUtils::class);

		$user = $globalUtils->getUser();

		return array(
			'howto'             => $howto,
			'permissionContext' => $this->getPermissionContext($howto),
			'userHowtos'        => $userHowtos,
			'similarHowtos'     => $similarHowtos,
			'likeContext'       => $likableUtils->getLikeContext($howto, $user),
			'watchContext'      => $watchableUtils->getWatchContext($howto, $user),
			'commentContext'    => $commentableUtils->getCommentContext($howto),
			'collectionContext' => $collectionnableUtils->getCollectionContext($howto),
			'followerContext'   => $followerUtils->getFollowerContext($howto->getUser(), $user),
			'referral'          => $embaddableUtils->processReferer($howto, $request),
		);
	}

}