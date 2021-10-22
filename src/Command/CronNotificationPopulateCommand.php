<?php

namespace App\Command;

use App\Entity\Core\Member;
use App\Model\IdentifiableInterface;
use App\Model\MentionSourceInterface;
use App\Model\TypableInterface;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use App\Entity\AbstractAuthoredPublication;
use App\Entity\Core\Activity\AbstractActivity;
use App\Entity\Core\Follower;
use App\Entity\Core\Notification;
use App\Entity\Core\Watch;
use App\Model\TitledInterface;
use App\Model\WatchableInterface;
use App\Model\WatchableChildInterface;
use App\Model\PublicationInterface;
use App\Utils\TypableUtils;

class CronNotificationPopulateCommand extends AbstractCommand {

	protected function configure() {
		$this
			->setName('ladb:cron:notification:populate')
			->addOption('force', null, InputOption::VALUE_NONE, 'Force updating')
			->setDescription('Process activities to populate notifications')
			->setHelp(<<<EOT
The <info>ladb:cron:notification:populate</info> process activities to populate notifications
EOT
			);
	}

	/////

	protected function execute(InputInterface $input, OutputInterface $output) {

		$forced = $input->getOption('force');
		$verbose = $input->getOption('verbose');

		$om = $this->getContainer()->get('doctrine')->getManager();
		$activityRepository = $om->getRepository(AbstractActivity::CLASS_NAME);
		$watchRepository = $om->getRepository(Watch::CLASS_NAME);
		$notificationRepository = $om->getRepository(Notification::CLASS_NAME);
		$typableUtils = $this->getContainer()->get(TypableUtils::class);

		$notifiedUsers = array();
		$groupIdentifiers = array();
		$freshNotificationCounters = array();
		$activites = $activityRepository->findByPendingNotifications();
		if ($verbose) {
			$output->writeln('<info>'.count($activites).' activities to process...</info>');
		}
		foreach ($activites as $activity) {

			$actorUser = $activity->getUser();

			// Comment /////

			if ($activity instanceof \App\Entity\Core\Activity\Comment) {

				$comment = $activity->getComment();
				$commentEntity = $typableUtils->findTypable($comment->getEntityType(), $comment->getEntityId());
				if ($commentEntity instanceof WatchableInterface || $commentEntity instanceof WatchableChildInterface) {

					$groupIdentifier = $this->_generateGroupIdentifierFromActivityAndEntity($om, $activity, $commentEntity);

					if ($commentEntity instanceof WatchableChildInterface) {
						$watchable = $typableUtils->findTypable($commentEntity->getParentEntityType(), $commentEntity->getParentEntityId());
					} else {
						$watchable = $commentEntity;
					}

					if ($watchable->getWatchCount() > 0) {

						$watches = $watchRepository->findByEntityTypeAndEntityIdExcludingUser($watchable->getType(), $watchable->getId(), $actorUser);
						if (!is_null($watches)) {
							foreach ($watches as $watch) {
								$this->_forwardNotification($om, $watch->getUser(), $activity, $groupIdentifier, $notifiedUsers, $groupIdentifiers, $freshNotificationCounters);
								if ($verbose) {
									$output->writeln('<info>--> Notifying <fg=white>@'.$watch->getUser()->getUsername(). '</fg=white> for new comment='.mb_strimwidth($comment->getBody(), 0, 50, '[...]').' on='.$watchable->getTitle().'</info>');
								}
							}
						}

					}

				}

			}

			// Contribute /////

			else if ($activity instanceof \App\Entity\Core\Activity\Contribute) {

				// No Notification

			}

			// Follow /////

			else if ($activity instanceof \App\Entity\Core\Activity\Follow) {

				$follower = $activity->getFollower();

				// Notification
				$this->_forwardNotification($om, $follower->getFollowingUser(), $activity, null, $notifiedUsers, $groupIdentifiers, $freshNotificationCounters);
				if ($verbose) {
					$output->writeln('<info>--> Notifying <fg=white>@'.$follower->getFollowingUser()->getUsername(). '</fg=white> for new follower=@'.$actorUser->getUsername().'</info>');
				}

			}

			// Like /////

			else if ($activity instanceof \App\Entity\Core\Activity\Like) {

				$like = $activity->getLike();
				$likeEntity = $typableUtils->findTypable($like->getEntityType(), $like->getEntityId());
				if ($likeEntity instanceof AbstractAuthoredPublication) {

					$groupIdentifier = $this->_generateGroupIdentifierFromActivityAndEntity($om, $activity, $likeEntity);

					if ($likeEntity instanceof WatchableInterface && $likeEntity->getWatchCount() > 0) {

						$allowedUsers = array();
						$watches = $watchRepository->findByEntityTypeAndEntityId($likeEntity->getType(), $likeEntity->getId());
						if (!is_null($watches)) {
							foreach ($watches as $watch) {
								$allowedUsers[] = $watch->getUser();
							}
						}

					}

					$this->_forwardNotification($om, $likeEntity->getUser(), $activity, $groupIdentifier, $notifiedUsers, $groupIdentifiers, $freshNotificationCounters, $allowedUsers);
					if ($verbose) {
						$output->writeln('<info>--> Notifying <fg=white>@'.$likeEntity->getUser()->getUsername(). '</fg=white> for new like from=@'.$actorUser->getUsername().' on='.$likeEntity->getTitle().'</info>');
					}
				}

			}

			// Mention /////

			else if ($activity instanceof \App\Entity\Core\Activity\Mention) {

				$mention = $activity->getMention();
				$mentionEntity = $typableUtils->findTypable($mention->getEntityType(), $mention->getEntityId());
				if ($mentionEntity instanceof MentionSourceInterface) {

					$groupIdentifier = $this->_generateGroupIdentifierFromActivityAndEntity($om, $activity, $mentionEntity);

					$this->_forwardNotification($om, $mention->getMentionedUser(), $activity, $groupIdentifier, $notifiedUsers, $groupIdentifiers, $freshNotificationCounters);
					if ($verbose) {
						$output->writeln('<info>--> Notifying <fg=white>@'.$mention->getMentionedUser()->getUsername(). '</fg=white> for new mention from=@'.$actorUser->getUsername().' on='.$mentionEntity->getId().'</info>');
					}
				}

			}

			// Publish /////

			else if ($activity instanceof \App\Entity\Core\Activity\Publish) {

				$publication = $typableUtils->findTypable($activity->getEntityType(), $activity->getEntityId());
				if (!is_null($publication)) {

					$groupIdentifier = $this->_generateGroupIdentifierFromActivityAndEntity($om, $activity, $publication);

					$notificationStrategy = $publication->getNotificationStrategy();
					$excludedUserIds = array( $activity->getPublisherUser()->getId() );		// Exclude publisher

					// Members
					if ($actorUser->getIsTeam()) {

						if ($actorUser->getMeta()->getMemberCount() > 0 && $publication instanceof TitledInterface) {

							$memberRepository = $om->getRepository(Member::CLASS_NAME);
							$members = $memberRepository->findByTeam($actorUser);
							if (!is_null($members)) {
								foreach ($members as $member) {
									if (in_array($member->getUser()->getId(), $excludedUserIds)) {
										continue;
									}
									$this->_createNotification($om, $member->getUser(), $activity, $groupIdentifier, $notifiedUsers, $groupIdentifiers, $freshNotificationCounters);
									$excludedUserIds[] = $member->getUser()->getId();
									if ($verbose) {
										$output->writeln('<info>--> Notifying <fg=white>@'.$member->getUser()->getUsername(). '</fg=white> for new publication='.$publication->getTitle().' (member)</info>');
									}
								}
							}

						}

					}

					// Follower strategy
					if ($notificationStrategy & PublicationInterface::NOTIFICATION_STRATEGY_FOLLOWER == PublicationInterface::NOTIFICATION_STRATEGY_FOLLOWER) {

						if ($actorUser->getMeta()->getFollowerCount() >= 0 && $publication instanceof TitledInterface) {

							$followerRepository = $om->getRepository(Follower::CLASS_NAME);
							$followers = $followerRepository->findByFollowingUser($actorUser);
							if (!is_null($followers)) {
								foreach ($followers as $follower) {
									if (in_array($follower->getUser()->getId(), $excludedUserIds)) {
										continue;
									}
									$this->_createNotification($om, $follower->getUser(), $activity, $groupIdentifier, $notifiedUsers, $groupIdentifiers, $freshNotificationCounters);
									$excludedUserIds[] = $follower->getUser()->getId();
									if ($verbose) {
										$output->writeln('<info>--> Notifying <fg=white>@'.$follower->getUser()->getUsername(). '</fg=white> for new publication='.$publication->getTitle().' (follower)</info>');
									}
								}
							}

						}

					}

					// Watch strategy
					if ($notificationStrategy & PublicationInterface::NOTIFICATION_STRATEGY_WATCH == PublicationInterface::NOTIFICATION_STRATEGY_WATCH) {

						$watchable = null;
						if ($publication instanceof WatchableInterface) {
							$watchable = $publication;
						} else if ($publication instanceof WatchableChildInterface) {
							$watchable = $typableUtils->findTypable($publication->getParentEntityType(), $publication->getParentEntityId());
						}

						if (!is_null($watchable) && $watchable->getWatchCount() > 0 && $publication instanceof TitledInterface) {

							$watches = $watchRepository->findByEntityTypeAndEntityIdExcludingUser($watchable->getType(), $watchable->getId(), $actorUser);
							if (!is_null($watches)) {
								foreach ($watches as $watch) {
									if (in_array($watch->getUser()->getId(), $excludedUserIds)) {
										continue;
									}
									$this->_forwardNotification($om, $watch->getUser(), $activity, $groupIdentifier, $notifiedUsers, $groupIdentifiers, $freshNotificationCounters);
									if ($verbose) {
										$output->writeln('<info>--> Notifying <fg=white>@'.$watch->getUser()->getUsername(). '</fg=white> for new publication='.$publication->getTitle().' (watch)</info>');
									}
								}
							}

						}

					}

				}

			}

			// Vote /////

			else if ($activity instanceof \App\Entity\Core\Activity\Vote) {

				$vote = $activity->getVote();
				$voteEntity = $typableUtils->findTypable($vote->getEntityType(), $vote->getEntityId());
				$groupIdentifier = $this->_generateGroupIdentifierFromActivityAndEntity($om, $activity, $voteEntity, array( $vote->getScore() ));
				$this->_forwardNotification($om, $voteEntity->getUser(), $activity, $groupIdentifier, $notifiedUsers, $groupIdentifiers, $freshNotificationCounters);
				if ($verbose) {
					$output->writeln('<info>--> Notifying <fg=white>@'.$voteEntity->getUser()->getUsername(). '</fg=white> for new vote from=@'.$actorUser->getUsername().'</info>');
				}

			}

			// Join /////

			else if ($activity instanceof \App\Entity\Core\Activity\Join) {

				$join = $activity->getJoin();
				$joinEntity = $typableUtils->findTypable($join->getEntityType(), $join->getEntityId());

				if ($joinEntity instanceof WatchableInterface && $joinEntity->getWatchCount() > 0 && $joinEntity instanceof TitledInterface) {

					$groupIdentifier = $this->_generateGroupIdentifierFromActivityAndEntity($om, $activity, $joinEntity);

					$watches = $watchRepository->findByEntityTypeAndEntityIdExcludingUser($joinEntity->getType(), $joinEntity->getId(), $actorUser);
					if (!is_null($watches)) {
						foreach ($watches as $watch) {
							$this->_forwardNotification($om, $watch->getUser(), $activity, $groupIdentifier, $notifiedUsers, $groupIdentifiers, $freshNotificationCounters);
							if ($verbose) {
								$output->writeln('<info>--> Notifying <fg=white>@'.$watch->getUser()->getUsername(). '</fg=white> for new join from=@'.$actorUser->getUsername().'</info>');
							}
						}
					}

				}

			}

			// Answer /////

			else if ($activity instanceof \App\Entity\Core\Activity\Answer) {

				$answer = $activity->getAnswer();
				$question = $answer->getQuestion();

				if ($question->getWatchCount() > 0) {

					$groupIdentifier = $this->_generateGroupIdentifierFromActivityAndEntity($om, $activity, $question);

					$watches = $watchRepository->findByEntityTypeAndEntityIdExcludingUser($question->getType(), $question->getId(), $actorUser);
					if (!is_null($watches)) {
						foreach ($watches as $watch) {
							$this->_forwardNotification($om, $watch->getUser(), $activity, $groupIdentifier, $notifiedUsers, $groupIdentifiers, $freshNotificationCounters);
							if ($verbose) {
								$output->writeln('<info>--> Notifying <fg=white>@'.$watch->getUser()->getUsername(). '</fg=white> for new answer='.mb_strimwidth($answer->getBody(), 0, 50, '[...]').' on='.$question->getTitle().'</info>');
							}
						}
					}

				}

			}

			// Testify /////

			else if ($activity instanceof \App\Entity\Core\Activity\Testify) {

				$testimonial = $activity->getTestimonial();
				$school = $testimonial->getSchool();

				if ($school->getWatchCount() > 0) {

					$groupIdentifier = $this->_generateGroupIdentifierFromActivityAndEntity($om, $activity, $school);

					$watches = $watchRepository->findByEntityTypeAndEntityIdExcludingUser($school->getType(), $school->getId(), $actorUser);
					if (!is_null($watches)) {
						foreach ($watches as $watch) {
							$this->_forwardNotification($om, $watch->getUser(), $activity, $groupIdentifier, $notifiedUsers, $groupIdentifiers, $freshNotificationCounters);
							if ($verbose) {
								$output->writeln('<info>--> Notifying <fg=white>@'.$watch->getUser()->getUsername(). '</fg=white> for new testimonial='.mb_strimwidth($testimonial->getBody(), 0, 50, '[...]').' on='.$school->getTitle().'</info>');
							}
						}
					}

				}

			}

			// Review /////

			else if ($activity instanceof \App\Entity\Core\Activity\Review) {

				$review = $activity->getReview();
				$reviewable = $typableUtils->findTypable($review->getEntityType(), $review->getEntityId());

				if ($reviewable instanceof WatchableInterface && $reviewable->getWatchCount() > 0) {

					$groupIdentifier = $this->_generateGroupIdentifierFromActivityAndEntity($om, $activity, $reviewable);

					$watches = $watchRepository->findByEntityTypeAndEntityIdExcludingUser($reviewable->getType(), $reviewable->getId(), $actorUser);
					if (!is_null($watches)) {
						foreach ($watches as $watch) {
							$this->_forwardNotification($om, $watch->getUser(), $activity, $groupIdentifier, $notifiedUsers, $groupIdentifiers, $freshNotificationCounters);
							if ($verbose) {
								$output->writeln('<info>--> Notifying <fg=white>@'.$watch->getUser()->getUsername(). '</fg=white> for new review='.mb_strimwidth($review->getBody(), 0, 50, '[...]').' on='.$reviewable->getTitle().'</info>');
							}
						}
					}

				}

			}

			// Feedback /////

			else if ($activity instanceof \App\Entity\Core\Activity\Feedback) {

				$feedback = $activity->getFeedback();
				$feedbackable = $typableUtils->findTypable($feedback->getEntityType(), $feedback->getEntityId());

				if ($feedbackable->getWatchCount() > 0) {

					$groupIdentifier = $this->_generateGroupIdentifierFromActivityAndEntity($om, $activity, $feedbackable);

					$watches = $watchRepository->findByEntityTypeAndEntityIdExcludingUser($feedbackable->getType(), $feedbackable->getId(), $actorUser);
					if (!is_null($watches)) {
						foreach ($watches as $watch) {
							$this->_forwardNotification($om, $watch->getUser(), $activity, $groupIdentifier, $notifiedUsers, $groupIdentifiers, $freshNotificationCounters);
							if ($verbose) {
								$output->writeln('<info>--> Notifying <fg=white>@'.$watch->getUser()->getUsername(). '</fg=white> for new feedback='.mb_strimwidth($feedback->getTitle(), 0, 50, '[...]').' on='.$feedbackable->getTitle().'</info>');
							}
						}
					}

				}

			}

			// Invite /////

			else if ($activity instanceof \App\Entity\Core\Activity\Invite) {

				$invitation = $activity->getInvitation();
				$this->_forwardNotification($om, $invitation->getRecipient(), $activity, null, $notifiedUsers, $groupIdentifiers, $freshNotificationCounters);
				if ($verbose) {
					$output->writeln('<info>--> Notifying <fg=white>@'.$invitation->getRecipient()->getUsername(). '</fg=white> for new invitation sender=@'.$invitation->getSender()->getUsername().' team=@'.$invitation->getTeam()->getUsername().'</info>');
				}

			}

			// Request /////

			else if ($activity instanceof \App\Entity\Core\Activity\Request) {

				$request = $activity->getRequest();
				$this->_forwardNotification($om, $request->getTeam(), $activity, null, $notifiedUsers, $groupIdentifiers, $freshNotificationCounters);
				if ($verbose) {
					$output->writeln('<info>--> Notifying <fg=white>@'.$request->getTeam()->getUsername(). '</fg=white> for new request sender=@'.$request->getSender()->getUsername().'</info>');
				}

			}

			// Flag activity as notified
			$activity->setIsPendingNotifications(false);

		}

		if ($forced) {
			$om->flush();
		}

		// Update fresh notification counters
		foreach ($notifiedUsers as $userId => $user) {

			// Notification folding
			$notificationsFoldingSince = $user->getMeta()->getNotificationsFoldingSince();
			if (is_null($notificationsFoldingSince) || $notificationsFoldingSince < (new \DateTime())->sub(new \DateInterval('P1D'))) {		// Maximize folding since to 1 day
				$notificationsFoldingSince = new \DateTime();
				$user->getMeta()->setNotificationsFoldingSince($notificationsFoldingSince);
			}

			foreach ($groupIdentifiers[$userId] as $userGroupIdentifiers) {

				$groupNotifications = $notificationRepository->findByNewerThanAndGroupIdentifierAndUser($notificationsFoldingSince, $userGroupIdentifiers, $user);

				if (!is_null($groupNotifications) && count($groupNotifications) > 1) {

					$folder = $groupNotifications[0];
					$folder->setIsFolder(true);
					$folder->setFolder(null);

					if ($verbose) {
						$output->write('foldable count='.count($groupNotifications).' folder_id='.$folder->getId().' - ');
					}

					foreach ($groupNotifications as $groupNotification) {
						if ($groupNotification == $folder) {
							continue;
						}
						$groupNotification->setIsFolder(false);
						$groupNotification->setFolder($folder);
					}

				}

			}

			$user->getMeta()->incrementFreshNotificationCount($freshNotificationCounters[$userId]);
			if ($verbose) {
				$output->writeln('<info>'.$user->getDisplayname().' <fg=yellow>['.$user->getMeta()->getFreshNotificationCount().' fresh ('.$freshNotificationCounters[$userId].' new)]</fg=yellow></info>');
			}

		}

		if ($forced) {
			$om->flush();
		}

        return Command::SUCCESS;

	}

	/////

	private function _generateGroupIdentifierFromActivityAndEntity($om, $activity, $entity, $values = null) {
		$data = array( $om->getMetadataFactory()->getMetadataFor(get_class($activity))->discriminatorValue );
		if ($entity instanceof TypableInterface) {
			$data[] = $entity->getType();
		}
		if ($entity instanceof IdentifiableInterface) {
			$data[] = $entity->getId();
		}
		if (!is_null($values)) {
			$data = array_merge($data, $values);
		}
		$hashids = new \Hashids\Hashids($this->getParameter('secret'), 5, 'ABCDEFGHIJKLMNOPQRSTUVWXYZ1234567890');
		return $hashids->encode($data);
	}

	private function _forwardNotification($om, $user, $activity, $groupIdentifier, &$notifiedUsers, &$groupIdentifiers, &$freshNotificationCount, $allowedUsers = null) {
		if ($user->getIsTeam()) {

			$memberRepository = $om->getRepository(Member::CLASS_NAME);
			$members = $memberRepository->findPaginedByTeam($user);

			foreach ($members as $member) {
				if ($member->getUser() == $activity->getUser()) {
					continue;	// Exclude activity user
				}
				if (!is_null($allowedUsers) && !in_array($member->getUser(), $allowedUsers)) {
					continue;
				}
				$this->_createNotification($om, $member->getUser(), $activity, $groupIdentifier, $notifiedUsers, $groupIdentifiers, $freshNotificationCount);
			}

		} else {
			if (!is_null($allowedUsers) && !in_array($user, $allowedUsers)) {
				return;
			}
			$this->_createNotification($om, $user, $activity, $groupIdentifier, $notifiedUsers, $groupIdentifiers, $freshNotificationCount);
		}
	}

	private function _createNotification($om, $user, $activity, $groupIdentifier, &$notifiedUsers, &$groupIdentifiers, &$freshNotificationCount) {

		$notification = new Notification();
		$notification->setUser($user);
		$notification->setActivity($activity);
		$notification->setGroupIdentifier($groupIdentifier);

		// Keep notified user
		$notifiedUsers[$user->getId()] = $user;

		// Keep groupIdentifier for user
		if (!isset($groupIdentifiers[$user->getId()])) {
			$groupIdentifiers[$user->getId()] = array();
		}
		$groupIdentifiers[$user->getId()][] = $groupIdentifier;

		// Keep fresh notification count for user
		if (!isset($freshNotificationCount[$user->getId()])) {
			$freshNotificationCount[$user->getId()] = 0;
		}
		$freshNotificationCount[$user->getId()]++;

		$om->persist($notification);
	}

}