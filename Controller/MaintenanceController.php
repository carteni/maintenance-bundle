<?php

/*
 * This file is part of the MesMaintenanceBundle package.
 *
 * (c) Francesco Cartenì <http://www.multimediaexperiencestudio.it/>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Mes\Misc\MaintenanceBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Templating\EngineInterface;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * Class MaintenanceController.
 */
class MaintenanceController
{
    /** @var EngineInterface */
    private $engine;

    public function __construct(EngineInterface $engine)
    {
        $this->engine = $engine;
    }

    /**
     * @return Response
     */
    public function showAction(Request $request)
    {
        if ($request->isXmlHttpRequest()) {
            return new JsonResponse(array('message' => 'Site Under Mantainance'));
        }

        return $this->engine->renderResponse('@MesMaintenance/index.html.twig');
    }
}
