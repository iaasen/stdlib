<?php
/**
 * Created by PhpStorm.
 * User: iaase
 * Date: 09.06.2018
 * Time: 22:39
 */

namespace Iaasen\Initializer;


use Laminas\View\Renderer\PhpRenderer;

interface ViewRendererAwareInterface
{
	public function setViewRenderer(PhpRenderer $viewRenderer);
}