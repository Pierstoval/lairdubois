<?php

namespace App\Controller\Core;

use App\Controller\AbstractController;
use App\Entity\Core\TagUsage;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;

/**
 * @Route("/tags")
 */
class TagController extends AbstractController {

	/**
	 * @Route("/usage/{id}/highlight/create", requirements={"id" = "\d+"}, defaults={"action" = "create"}, name="core_tag_usage_highlight_create")
	 * @Route("/usage/{id}/highlight/delete", requirements={"id" = "\d+"}, defaults={"action" = "delete"}, name="core_tag_usage_highlight_delete")
	 */
	public function usageHighlightToggle(Request $request, $id, $action) {
		$om = $this->getDoctrine()->getManager();
		$tagUsageRepository = $om->getRepository(TagUsage::class);

		$tagUsage = $tagUsageRepository->findOneById($id);
		if (is_null($tagUsage)) {
			throw $this->createNotFoundException('Unable to find TagUsage entity (id='.$id.').');
		}

		if (!$this->get('security.authorization_checker')->isGranted('ROLE_ADMIN')) {
			throw $this->createNotFoundException('Not allowed (core_tag_usage_highlight_create or core_tag_usage_highlight_delete)');
		}

		if ($action == 'delete') {
			$tagUsage->setHighlighted(false);
		} else {
			$tagUsage->setHighlighted(true);
		}

		$om->flush();
	}

}