<?php

namespace App\Models;

class VnnServerModel {
	public function hostname() {
		return $_SERVER['HTTP_HOST'];
	}
	
	public function hostpath($springname, $regional) {
		return "virt/$regional/$springname";
	}
	
	public function hostfield($springname, $regional) {
		return $this->hostname() . $this->hostpath($springname, $regional);
	}
}