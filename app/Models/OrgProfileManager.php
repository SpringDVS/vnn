<?php

namespace App\Models;

use SpringDvs\Core\NetServices\OrgProfileManagerInterface;
use SpringDvs\Core\NetServices\OrgProfile;

class OrgProfileManager
extends NodeDbModel
implements OrgProfileManagerInterface
{
	/**
	 * {@inheritDoc}
	 * @see \SpringDvs\Core\NetServices\OrgProfileManagerInterface::updateProfile()
	 */
	public function updateProfile(OrgProfile $profile) {
		$this->db->table('orgprofiles')
				->updateOrInsert(
						['nodeid' => $this->localNode->nodeid()],
						[
							'name' => $profile->name(),
							'website' => $profile->website(),
							'tags' => implode(',', $profile->tags())
						]);
	}
	/**
	 * {@inheritDoc}
	 * @see \SpringDvs\Core\NetServices\OrgProfileManagerInterface::getProfile()
	 */
	public function getProfile() {
		$profile = $this->db->table('orgprofiles')
						->select(['name','website','tags'])
						->where('nodeid','=',$this->localNode->nodeid())
						->first();
		
		if(!$profile){ return null; }
		
		return new OrgProfile($profile->name, $profile->website, explode(',', $profile->tags));
	}
}