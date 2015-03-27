<?php

namespace Conjecto\Bundle\DemoBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;

class NobelController extends Controller
{

    /**
     * @Route("/", name="laureate.random")
     * @Template("DemoBundle:Nobel:index.html.twig")
     */
    public function indexAction()
    {
        $random = $this->container->get('rm')->getRepository('terms:LaureateAward')->findBy(array('terms:year' => 2005));

        return array("laureates" => $random);
    }

    /**
     * @Route("/year/{year}", name="laureate.year")
     * @Template("DemoBundle:Nobel:year.html.twig")
     */
    public function yearAction($year)
    {
        $laureates = $this->container->get('rm')->getRepository('terms:LaureateAward')->findBy(array('terms:year' => $year));

        return array("year" => $year, "laureates" => $laureates);
    }

    /**
     * @Route("/view/{uri}", name="laureate.view", requirements={"uri" = ".+"})
     * @Template("DemoBundle:Nobel:view.html.twig")
     */
    public function viewAction($uri)
    {
        $laureateaward = $this->container->get('rm')->getRepository('terms:LaureateAward')->find($uri);

        return array("award" => $laureateaward , "laureate" => $laureateaward->get('terms:laureate'));
    }

}
