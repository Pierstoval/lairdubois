<?php
namespace App\Consumer;

use App\Model\IndexableInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;
use OldSound\RabbitMqBundle\RabbitMq\ConsumerInterface;
use PhpAmqpLib\Message\AMQPMessage;
use App\Entity\Core\User;
use App\Entity\Core\View;
use App\Model\AuthoredInterface;
use App\Utils\SearchUtils;
use App\Utils\TypableUtils;

class ViewConsumer implements ConsumerInterface {

	private $logger;
	private $om;
	private $userRepository;
	private $viewRepository;
	private $typableUtils;
	private $searchUtils;

	public function __construct(ContainerInterface $container) {

		$this->logger = $container->get('logger');

		$this->om = $container->get('doctrine')->getManager();
		$this->userRepository = $this->om->getRepository(User::class);
		$this->viewRepository = $this->om->getRepository(View::class);

		$this->typableUtils = $container->get(TypableUtils::class);
		$this->searchUtils = $container->get(SearchUtils::class);

	}

	/////

	private function _executeListedProcess($entityType, $entityIds, $userId) {

		if (!is_null($userId)) {

			$user = $this->userRepository->findOneById($userId);
			if (!is_null($user)) {

				$viewRepository = $this->om->getRepository(View::class);
				$viewedCount = $viewRepository->countByEntityTypeAndEntityIdsAndUserAndKind($entityType, $entityIds, $user, View::KIND_LISTED);
				if ($viewedCount < count($entityIds)) {

					$newViewCount = 0;
					foreach ($entityIds as $entityId) {

						if (!$viewRepository->existsByEntityTypeAndEntityIdAndUserAndKind($entityType, $entityId, $user, View::KIND_LISTED)) {

							// Create a new listed view
							$view = new View();
							$view->setEntityType($entityType);
							$view->setEntityId($entityId);
							$view->setUser($user);
							$view->setKind(View::KIND_LISTED);

							$this->om->persist($view);

							$newViewCount++;
						}

					}

					if ($newViewCount > 0) {
						$this->om->flush();
					}

				}

			}

		}

	}

	private function _executeShownProcess($entityType, $entityId, $userId) {

		// Retrieve viewable
		try {
			$viewable = $this->typableUtils->findTypable($entityType, $entityId);
		} catch(\Exception $e) {
			$this->logger->error('ViewConsumer/execute', array ( 'exception' => $e));
			return;
		}
		$updated = false;

		if (!is_null($userId)) {

			$user = $this->userRepository->findOneById($userId);
			if (!is_null($user)) {

				// Authenticated user -> use viewManager

				$view = $this->viewRepository->findOneByEntityTypeAndEntityIdAndUserAndKind($viewable->getType(), $viewable->getId(), $user, View::KIND_SHOWN);
				if (is_null($view)) {

					// Create a new view
					$view = new View();
					$view->setEntityType($viewable->getType());
					$view->setEntityId($viewable->getId());
					$view->setUser($user);
					$view->setKind(View::KIND_SHOWN);

					$this->om->persist($view);

					// Exclude self contribution view and non public viewables
					if ($viewable instanceof AuthoredInterface && $viewable->getUser()->getId() == $user->getId()) {
						return;
					}

					// Increment viewCount
					$viewable->incrementViewCount();

					$updated = true;

				} else {

					// Exclude self contribution view and non public viewables
					if ($viewable instanceof AuthoredInterface && $viewable->getUser()->getId() == $user->getId()) {
						return;
					}

					if ($view->getCreatedAt() <= (new \DateTime())->sub(new \DateInterval('P1D'))) { // 1 day

						// View is older than 1 day. Update view, increment view count.

						// Reset view createAt
						$view->setCreatedAt(new \DateTime());

						// Increment viewCount
						$viewable->incrementViewCount();

						$updated = true;

					}

				}

			}

		} else {

			// Increment viewCount
			$viewable->incrementViewCount();

			$updated = true;

		}

		if ($updated) {

			// Flush DB updates (view and/or entity)
			$this->om->flush();

			// Update in Elasticsearch

//			Elasticsearch update is temporarily removed due to a strange bug that remove item from index...

//			if ($viewable instanceof IndexableInterface && $viewable->isIndexable()) {
//				$this->searchUtils->replaceEntityInIndex($viewable);
//			}

		}

	}

	/////

	public function execute(AMQPMessage $msg) {

		try {

			$msgBody = unserialize($msg->getBody());

			$kind = $msgBody['kind'];
			$entityType = $msgBody['entityType'];
			$entityIds = $msgBody['entityIds'];
			$userId = $msgBody['userId'];

		} catch (\Exception $e) {
			$this->logger->error('ViewConsumer/execute', array ( 'exception' => $e));
			return false;
		}

		switch ($kind) {

			case View::KIND_LISTED:
				$this->_executeListedProcess($entityType, $entityIds, $userId);
				break;

			case View::KIND_SHOWN:
				if (is_array($entityIds) && count($entityIds) > 0) {
					$this->_executeShownProcess($entityType, $entityIds[0], $userId);
				}
				break;

			default:
				$this->logger->error('ViewConsumer/execute (Unknow kind='.$kind.')');
				return false;

		}

		return true;
	}

}