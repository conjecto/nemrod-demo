<?php

namespace Conjecto\Bundle\DemoBundle\Controller;

use Conjecto\Nemrod\Resource;
use EasyRdf\Literal\Integer;
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
            ->OrderBy('?year')
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
        $laureates = $this->container->get('rm')->getRepository('terms:LaureateAward')->findBy(array('terms:year' => New Integer($year)));

        return array("year" => $year, "laureates" => $laureates);
    }

    /**
     * @Route("/category/{category}", name="laureate.category", requirements={"category" = ".+"})
     * @Template("DemoBundle:Nobel:category.html.twig")
     * @ParamConverter("category", class="terms:Category")
     */
    public function categoryAction($category)
    {
        $laureates = $this->container->get('rm')->getRepository('terms:LaureateAward')->findBy(array('terms:category' => $category));

        return array("category" => $category, "laureates" => $laureates);
    }

    /**
     * @Route("/view/{uri}", name="laureate.view", requirements={"uri" = ".+"})
     * @Template("DemoBundle:Nobel:view.html.twig")
     */
    public function viewAction($uri)
    {
        $laureateaward = $this->container->get('rm')->getRepository('terms:LaureateAward')->find($uri);

        //tweaks to get more infos.
        $laureatebirthplace = $this->container->get('rm')->getRepository('dbpediaowl:City')->find($laureateaward->get('terms:laureate/dbpediaowl:birthPlace')->getUri());
        $laureatedeathplace = $laureateaward->get('terms:laureate/dbpediaowl:deathPlace') ? $this->container->get('rm')->getRepository('dbpediaowl:Country')->find($laureateaward->get('terms:laureate/dbpediaowl:deathPlace')->getUri()) : null;

        $categ = $laureateaward->get("terms:category/rdfs:label");

        echo "###";
        $test = $this->container->get('rm')->getRepository('terms:LaureateAward')->findOneBy(array( "uri" => $uri));
        var_dump($test);
        echo "###";
        return array(
            "award" => $laureateaward ,
            "category" => $categ,
            "laureate" => $laureateaward->get('terms:laureate'),
            "places" => array (
                "birth" => $laureatebirthplace,
                "death" => $laureatedeathplace
            )
        );
    }

    /**
     * @Route("/edit/{uri}", name="laureate.edit", requirements={"uri" = ".+"})
     * @Template("DemoBundle:Nobel:edit.html.twig")
     */
    public function editAction(Request $request, $uri)
    {
        $laureateaward = $this->container->get('rm')->getRepository('terms:LaureateAward')->find($uri);

        $form = $form = $this->createForm('award_type', $laureateaward);

        if ($request->getMethod() == "POST") {
            $form->handleRequest($request);

            $this->get('rm')->persist($laureateaward);

            $this->get('rm')->flush();

            return $this->redirect($this->generateUrl('laureate.year', array ('year' => $laureateaward->get('terms:year'))));
        }

        return array(
            "form" => $form->createView(),
            "laureate" => $laureateaward,
            "year" => $laureateaward->get('terms:year'),
            "category" => array(
                "label" => $laureateaward->get('terms:category/rdfs:label'),
                "uri" => $laureateaward->get('terms:category')->getUri()
            )
        );
    }

    /**
     * @Route("/create", name="laureate.create")
     * @Template("DemoBundle:Nobel:create.html.twig")
     */
    public function createAction(Request $request)
    {
        $laureateaward = $this->container->get('rm')->getRepository('terms:LaureateAward')->create();

        if ($year = $request->get('year')) {
            $laureateaward->set('terms:year', $year);
        }

        if ($category = $request->get('category')) {
            $laureateaward->set('terms:category', $this->container->get('rm')->getRepository('terms:Category')->find($category));
        }

        $form = $form = $this->createForm('award_type', $laureateaward);

        if ($request->getMethod() == "POST") {
            $form->handleRequest($request);
            $this->get('rm')->persist($laureateaward);
            $this->get('rm')->flush();

            return $this->redirect($this->generateUrl('laureate.year', array ('year' => 'terms:year')));
        }

        $data = array("form" => $form->createView());
        if($year) {
            $data['year'] = $year ;
        }

        if($category) {
            $data['category'] = array ('uri' => $category, 'label' => $this->container->get('rm')->getRepository('terms:Category')->find($category)->get('rdfs:label')) ;
        }
        return $data;
    }

}
