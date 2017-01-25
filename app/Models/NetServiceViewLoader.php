<?php

namespace App\Models;

use SpringDvs\Core\NetServiceViewLoaderInterface;
use Illuminate\Contracts\View\Factory;


class NetServiceViewLoader
implements NetServiceViewLoaderInterface{
	/**
	 * @var \Illuminate\View\Factory
	 */
	private $viewFactory;
	public function __construct(\Illuminate\View\Factory $viewFactory) {
		$this->viewFactory = $viewFactory;
	}
	/**
	 * {@inheritDoc}
	 * @see \SpringDvs\Core\NetServiceViewLoaderInterface::load()
	 */
	public function load($view, $data) {
		$name = "ns.$view";
		if(!$this->viewFactory->exists($name)) {
			return "";
		}

		return $this->viewFactory->make($name, $data)->render();
	}
}