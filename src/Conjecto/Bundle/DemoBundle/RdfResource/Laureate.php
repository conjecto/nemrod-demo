<?php

namespace Conjecto\Bundle\DemoBundle\RdfResource;

use Conjecto\Nemrod\Resource as BaseResource;
use Conjecto\Nemrod\ResourceManager\Annotation\Resource;

/**
 * Class ExampleResource
 * @package Conjecto\Bundle\DemoBundle\RdfResource
 * @Resource(types={"terms:Laureate"}, uriPattern = "http://data.nobelprize.org/resource/laureate/")
 */
class Laureate extends BaseResource
{

}