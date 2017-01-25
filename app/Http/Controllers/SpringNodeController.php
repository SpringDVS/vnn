<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use SpringDvs\Core\LocalNodeInterface;
use App\Models\LocalNodeLookup;
use SpringDvs\Core\NetServiceHandler;
use Illuminate\Support\Facades\App;
use Illuminate\Contracts\Console\Application;
use SpringDvs\Message;
use SpringDvs\CmdType;
use App\Models\ThinServices;

class SpringNodeController extends Controller
{
	/**
	 * @var LocalNodeLookup The lookup for the virtual node
	 */
	private $lookup;

	/**
	 * @var ThinServices The thin services manager
	 */
	private $thinServices;
	
	/**
	 * @var NetServiceHandler The singleton netservice handler
	 */
	private $serviceHandler;
	
	public function __construct(LocalNodeLookup $lookup, ThinServices $thinServices, NetServiceHandler $serviceHandler) {
		$this->lookup = $lookup;
		$this->thinServices = $thinServices;
		$this->serviceHandler = $serviceHandler;
	}
	
	public function spring($regional, $spring, Request $request) {
		$localNode = $this->lookup->fromSpring($spring, $regional);
		
		$req = $request->getContent();
		if(empty($req)) {
			return "104"; // Malformed content
		}
		try {
			$msg = Message::fromStr($req);

			$cs = $msg->getContentService();
			$uri = $cs->uri();
			$attributes = $cs->attributes();

		} catch(\Exception $e) {
			return "104";
		}

		if(isset($uri->res()[0])) {
			$module = $uri->res()[0];
			if(($service = $this->thinServices->service($module, $localNode->nodeid()))) {
				// Swap out the service for the thin service
				$uri->res()[0] = "$module.$service";
			}
		}
		
		return $this->serviceHandler->run($uri, $attributes, $localNode);
	}
}
