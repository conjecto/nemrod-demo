<?php

namespace Conjecto\Bundle\DemoBundle\Controller;

use Conjecto\Nemrod\ElasticSearch\Search;
use Conjecto\Nemrod\Resource;
use EasyRdf\Literal\Integer;
use Elastica\Filter\Bool;
use Elastica\Filter\Nested;
use Elastica\Filter\Prefix;
use Elastica\Filter\Term;
use Elastica\Util;
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
        //getting manager
        $manager = $this->container->get('rm');

        //getting repository
        $repository = $manager->getRepository('terms:LaureateAward');

        //getting years by querying directly the triplet store
        $years = $repository->getQueryBuilder()
            ->select("DISTINCT ?year")
            ->where('?s terms:year ?year')
            ->addFilter('?year > 0')
            ->OrderBy('?year')
            ->getQuery()
            ->execute();

        //getting all categories
        $categories = $manager->getRepository('terms:Category')->findAll();

        return array("years" => $years, "categories" => $categories);
    }

    /**
     * @Route("/year/{year}", name="laureate.year")
     * @Template("DemoBundle:Nobel:year.html.twig")
     */
    public function yearAction($year)
    {
        //getting manager
        $manager = $this->container->get('rm') ;

        //getting repository
        $repository = $manager->getRepository('terms:LaureateAward');

        //finding laureates for given year
        $laureates = $repository->findBy(array('terms:year' => New Integer($year)));

        return array("year" => $year, "laureates" => $laureates);
    }

    /**
     * @Route("/category/{category}/page/{page}", name="laureate.category", requirements={"category" = ".+"}, defaults={"page" = 1})
     * @Template("DemoBundle:Nobel:category.html.twig")
     * @ParamConverter("category", class="terms:Category")
     */
    public function categoryAction($category, $page)
    {
        $itemsPerPage = 10;
        //getting manager
        $manager = $this->container->get('rm');

        //getting query builder
        $qb = $manager->getRepository('terms:LaureateAward')->getQueryBuilder();

        //Counting elements in category
        $laureates = $qb
            ->select('(COUNT(DISTINCT ?instance) AS ?count)')
            ->where('?instance a terms:LaureateAward; terms:category <' . $category->getUri() . ">")
            ->getQuery()
            ->execute();

        $num = current($laureates)->count->getValue();
        $repository = $manager->getRepository('terms:LaureateAward');

        //getting laureates
        $laureates = $repository->findBy(array('terms:category' => $category), array('orderBy' => 'uri', 'limit' => $itemsPerPage, 'offset' => ($page * $itemsPerPage)));

        return array("category" => $category, "laureates" => $laureates, "page" => $page, "lastpage" => floor($num/10) );
    }

    /**
     * @Route("/view/{uri}", name="laureate.view", requirements={"uri" = ".+"})
     * @Template("DemoBundle:Nobel:view.html.twig")
     * @ParamConverter("laureateAward", class="terms:LaureateAward")
     */
    public function viewAction($laureateAward)
    {
        return array("award" => $laureateAward);
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
