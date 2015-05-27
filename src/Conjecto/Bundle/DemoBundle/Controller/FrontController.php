<?php

namespace Conjecto\Bundle\DemoBundle\Controller;

use Conjecto\Nemrod\Resource;
use EasyRdf\Literal\Integer;
use Elastica\Aggregation\Terms;
use Elastica\Filter\BoolAnd;
use Elastica\Filter\Term;
use Elastica\Index;
use Elastica\Query;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\ParamConverter;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

class FrontController extends Controller
{

    /**
     * @Route("/", name="index")
     * @Template("DemoBundle:Front:index.html.twig")
     */
    public function indexAction(Request $request)
    {
        $q = $request->get('q');
        $filters = $request->get('filters');
        $page = $request->get('page') ?: 1;
        $aggs = array();

        /** @var Index $index */
        $index = $this->get('nemrod.elastica.index.nemrod_demo');
        $query = new Query();

        //
        // keywords
        //
        if($q) {
            $match = new Query\Match();
            $match->setField('foaf:name', $q);
        } else {
            $match = new Query\MatchAll();
        }
        $query->setQuery($match);

        //
        // filters
        //
        if($filters) {
            $bool = new BoolAnd();
            foreach($filters as $key => $value) {
                $filter = new Term(array($key => $value));
                $bool->addFilter($filter);
                $filters[$key] = $filter;
            }
            $query->setPostFilter($bool);
        }

        //
        // aggs
        //
        $agg = new Terms('enssib_type');
        $agg->setField('enssib:type');
        $query->addAggregation($agg);

        //
        // pagination
        //
        $limit = 10;
        $query->setFrom(($page-1)*$limit);
        $query->setSize($limit);

        $rs = $index->search($query);
        return array(
            'q' => $q,
            'rs' => $rs,
            'limit' => $limit,
            'results'=> $rs->getResults(),
            'aggs' => $rs->getAggregations()
        );
    }
}
