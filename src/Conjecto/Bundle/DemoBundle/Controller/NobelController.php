<?php

namespace Conjecto\Bundle\DemoBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\ParamConverter;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

class NobelController extends Controller
{

    /**
     * @Route("/", name="laureate.index")
     * @Template("DemoBundle:Nobel:index.html.twig")
     */
    public function indexAction()
    {
        //getting years by querying directly the triplet store
        $years = $this->container->get('rm')->getRepository('terms:LaureateAward')->getQueryBuilder()
            ->select("DISTINCT ?year")
            ->where('?s terms:year ?year')
            ->addFilter('?year > 0')
            ->getQuery()
            ->execute();

        $categories = $this->container->get('rm')->getRepository('terms:Category')->findAll();

        return array("years" => $years, "categories" => $categories);
    }

    /**
     * @Route("/year/{year}", name="laureate.year")
     * @Template("DemoBundle:Nobel:year.html.twig")
     */
    public function yearAction($year)
    {
        $laureates = $this->container->get('rm')->getRepository('terms:LaureateAward')->findBy(array('terms:year' => $year));

        var_dump($laureates);

        return array("year" => $year, "laureates" => $laureates);
    }

    /**
     * @Route("/category/{category}", name="laureate.category", requirements={"category" = ".+"})
     * @Template("DemoBundle:Nobel:category.html.twig")
     * @ParamConverter("category", class="terms:Category")
     */
    public function categoryAction($category)
    {
        $laureates = $this->container->get('rm')->getRepository('terms:LaureateAward')->findBy(array('terms:category' => "<".$category.">"));

        return array("category" => $category, "laureates" => $laureates);
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

    /**
     * @Route("/create", name="laureate.index")
     * @Template("DemoBundle:Nobel:create.html.twig")
     */
    public function createAction(Request $request)
    {
        $laureateaward = $this->container->get('rm')->getRepository('terms:LaureateAward')->create();

        $form = $form = $this->createForm('award_type', $laureateaward);


        if ($request->getMethod() == "POST") {
            $form->handleRequest($request);

            $this->get('rm')->persist($laureateaward);

            $this->get('rm')->flush();

            return $this->redirect($this->generateUrl('laureate.index'));
        }


        return array("form" => $form->createView());
    }

}
