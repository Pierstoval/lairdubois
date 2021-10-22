<?php

namespace App\Composer;

use Composer\Script\Event;
use Symfony\Component\Filesystem\Filesystem;

class ScriptHandler {

	public static function updateDirectoryStructure(Event $event) {

		$uploadsDir = 'uploads';
		$downloadsDir = 'downloads';
		$keysDir = 'keys';
		$fixturesDir = 'src/Ladb/CoreBundle/Resources/fixtures';

		$fs = new Filesystem();

		// Create needed folders
		$fs->mkdir(array( $uploadsDir, $downloadsDir, $keysDir ));

		// Copy some fixtures
//		foreach (glob($fixturesDir.'/empty*.png') as $file) {
//			$fs->copy($file, $uploadsDir);
//		}

	}

}