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

//    /**
//     * @Route("/year/{year}", name="laureate.year")
//     * @Template("DemoBundle:Nobel:year_es.html.twig")
//     */
//    public function yearESAction($year)
//    {
//        /** @var Search $search */
//        $search = $this->get('nemrod.elastica.search.nobel.laureate');
//        $search->addTermFilter('terms:year', $year);
//
//        $result = $search->search();
//        return array('items' => $result['items'], 'year' => $year);
//    }


    /**
     * @Route("/category/{category}/page/{page}", name="laureate.category", requirements={"category" = ".+"}, defaults={"page" = 1})
     * @Template("DemoBundle:Nobel:category.html.twig")
     */
    public function categoryAction($category, $page)
    {
        $laureates = $this->container->get('rm')->getRepository('terms:LaureateAward')
        ->getQueryBuilder()->reset()->select('(COUNT(DISTINCT ?instance) AS ?count)')->where('?instance a terms:LaureateAward; terms:category <'.$category.">")->getQuery()
        ->execute();

        $num = current($laureates)->count->getValue();
        $laureates = $this->container->get('rm')->getRepository('terms:LaureateAward')->findBy(array('terms:category' => new Resource($category)), array('orderBy' => 'uri', 'limit' => 10, 'offset'=> ($page *10)));

        return array("category" => $category, "laureates" => $laureates, "page" => $page, "lastpage" => floor($num/10) );
    }

//    /**
//     * @Route("/category/{category}/page/{page}", name="laureate.category", requirements={"category" = ".+"}, defaults={"page" = 1})
//     * @Template("DemoBundle:Nobel:category_es.html.twig")
//     */
//    public function categoryESAction($category, $page)
//    {
//        /** @var Search $search */
//        $search = $this->get('nemrod.elastica.search.nobel.laureate');
//        $categoryParts = explode('/',$category);
//        $categoryName = strtolower($categoryParts[count($categoryParts)-1]);
//        $search->addTermFilter('terms:category._id', $categoryName);
//        $search->setPage($page);
//        $result = $search->search();
//
//
//        $maxPage = ceil($result['total'] / $result['pageSize']);
//
//        return array('laureates' => $result['items'], 'category' => $category, "page" => $page, "lastpage" => $maxPage);
//
//    }

    /**
     * @Route("/view/{uri}", name="laureate.view", requirements={"uri" = ".+"})
     * @Template("DemoBundle:Nobel:view.html.twig")
     */
    public function viewAction($uri)
    {
        $laureateaward = $this->container->get('rm')->getRepository('terms:LaureateAward')->find($uri);

        $place = $laureateaward->get('terms:laureate/dbpediaowl:birthPlace');
        //getting more infos.
        if ($place) {
            $laureatebirthplace = $this->container->get('rm')->getRepository('dbpediaowl:City')->find($place->getUri());
            $laureatebirthplace = $laureatebirthplace ? $laureatebirthplace : "";
        }

        $laureatedeathplace = $laureateaward->get('terms:laureate/dbpediaowl:deathPlace') ? $this->container->get('rm')->getRepository('dbpediaowl:Country')->find($laureateaward->get('terms:laureate/dbpediaowl:deathPlace')->getUri()) : null;

        $categ = $laureateaward->get("terms:category/rdfs:label");

        return array(
            "award" => $laureateaward ,
            "category" => $categ,
            "laureate" => $laureateaward->get('terms:laureate'),
            "places" => array (
                "birth" => isset($laureatebirthplace) ? $laureatebirthplace : "unknown",
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
