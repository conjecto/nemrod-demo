<?php

namespace Conjecto\Bundle\DemoBundle\Controller;

use Conjecto\Nemrod\Resource;
use EasyRdf\Literal\Integer;
use Elastica\Aggregation\DateHistogram;
use Elastica\Aggregation\Histogram;
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

class SearchController extends Controller
{
    /**
     * @Route("/search", name="search")
     * @Template("DemoBundle:Search:index.html.twig")
     */
    public function indexAction(Request $request)
    {
        $q = $request->get('q');
        $filters = $request->get('filters');
        $page = $request->get('page') ?: 1;
        $aggs = array();

        /** @var Index $index */
        $index = $this->get('nemrod.elastica.index.nobel');
        $query = new Query();

        //
        // keywords
        //
        if ($q) {
            $match = new Query\Match();
            $match->setField('rdfs:label', $q);
        }
        else {
            $match = new Query\MatchAll();
        }
        $query->setQuery($match);

        //
        // prepare filters
        //
        if ($filters) {
            foreach ($filters as $key => $value) {
                $filter = new Term(array($key => $value));
                $filters[$key] = $filter;
            }
        }

        //
        // aggs
        //
        $agg = new Terms('_type');
        $agg->setField('_type');
        $query->addAggregation($agg);

        // specifics facets
        if (isset($filters['_type'])) {
            // terms:LaureateAward
            if($filters['_type']->getParam('_type') === 'terms:LaureateAward') {
                // category
                $agg = new Terms('terms_category');
                $agg->setField('terms:category.rdfs:label');
                $query->addAggregation($agg);

                //year
                $agg = new Terms('terms_year');
                $agg->setField('terms:year');
                $query->addAggregation($agg);
            }
            else {
                unset($filters['terms:category.rdfs:label']);
                unset($filters['terms:year']);
            }

            // terms:Laureate
            if($filters['_type']->getParam('_type') === 'terms:Laureate') {
                $agg = new Terms('foaf_gender');
                $agg->setField('foaf:gender');
                $query->addAggregation($agg);
            }
            else {
                unset($filters['foaf:gender']);
            }
        }

        //
        // finalize filters
        //
        if($filters) {
            $bool = new BoolAnd();
            $bool->setFilters($filters);
            $query->setPostFilter($bool);
        }

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
