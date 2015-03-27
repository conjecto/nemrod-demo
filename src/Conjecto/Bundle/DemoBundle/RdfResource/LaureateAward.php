<?php
/**
 * Created by PhpStorm.
 * User: maxime
 * Date: 26/03/2015
 * Time: 16:06
 */

namespace Conjecto\Bundle\DemoBundle\RdfResource;

use Conjecto\Nemrod\Resource as BaseResource;
use Conjecto\Nemrod\ResourceManager\Annotation\Resource;

/**
 * Class ExampleResource
 * @package Conjecto\Bundle\DemoBundle\RdfResource
 * @Resource(types={"terms:LaureateAward"}, uriPattern = "nobel:laureateaward/")
 */
class LaureateAward extends BaseResource
{

} 